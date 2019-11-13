<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Api\EntityRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\IdentityGeneratorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\AsynchronousOperations\Api\Data\ItemStatusInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\AsyncResponseInterface;
use Magento\AsynchronousOperations\Api\Data\AsyncResponseInterfaceFactory;
use Magento\AsynchronousOperations\Api\Data\ItemStatusInterface;
use Magento\Framework\Bulk\BulkManagementInterface;
use Magento\Framework\Exception\BulkException;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Encryption\Encryptor;
use Magento\AsynchronousOperations\Model\Repository\Factory\Factory as RepositoryFactory;

/**
 * Class MassSchedule used for adding multiple entities as Operations to Bulk Management with the status tracking
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Suppressed without refactoring to not introduce BiC
 */
class MassSchedule
{
    /**
     * @var \Magento\Framework\DataObject\IdentityGeneratorInterface
     */
    private $identityService;

    /**
     * @var AsyncResponseInterfaceFactory
     */
    private $asyncResponseFactory;

    /**
     * @var ItemStatusInterfaceFactory
     */
    private $itemStatusInterfaceFactory;

    /**
     * @var \Magento\Framework\Bulk\BulkManagementInterface
     */
    private $bulkManagement;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    private $userContext;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @var Magento\AsynchronousOperations\Model\Repository\Factory\Factory
     */
    private $repositoryFactory;

    /**
     * MassSchedule constructor.
     * @param IdentityGeneratorInterface $identityService
     * @param ItemStatusInterfaceFactory $itemStatusInterfaceFactory
     * @param AsyncResponseInterfaceFactory $asyncResponseFactory
     * @param BulkManagementInterface $bulkManagement
     * @param LoggerInterface $logger
     * @param UserContextInterface|null $userContext
     * @param Encryptor|null $encryptor
     * @param RepositoryFactory $repositoryFactory
     */
    public function __construct(
        IdentityGeneratorInterface $identityService,
        ItemStatusInterfaceFactory $itemStatusInterfaceFactory,
        AsyncResponseInterfaceFactory $asyncResponseFactory,
        BulkManagementInterface $bulkManagement,
        LoggerInterface $logger,
        UserContextInterface $userContext = null,
        Encryptor $encryptor = null,
        RepositoryFactory $repositoryFactory,
        OperationInterfaceFactory $operationFactory,
        MessageValidator $messageValidator,
        MessageEncoder $messageEncoder,
        Json $jsonSerializer
    ) {
        $this->identityService = $identityService;
        $this->itemStatusInterfaceFactory = $itemStatusInterfaceFactory;
        $this->asyncResponseFactory = $asyncResponseFactory;
        $this->bulkManagement = $bulkManagement;
        $this->logger = $logger;
        $this->userContext = $userContext ?: ObjectManager::getInstance()->get(UserContextInterface::class);
        $this->encryptor = $encryptor ?: ObjectManager::getInstance()->get(Encryptor::class);
        $this->repositoryFactory = $repositoryFactory;
        $this->operationFactory = $operationFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
    }

    /**
     * Schedule new bulk operation based on the list of entities
     *
     * @param string $topicName
     * @param array $entitiesArray
     * @param string $groupId
     * @param string $userId
     * @return AsyncResponseInterface
     * @throws BulkException
     * @throws LocalizedException
     */
    public function publishMass($topicName, array $entitiesArray, $groupId = null, $userId = null)
    {
        $bulkDescription = __('Topic %1', $topicName);

        if ($userId == null) {
            $userId = $this->userContext->getUserId();
        }

        if ($groupId == null) {
            $groupId = $this->identityService->generateId();

            /** create new bulk without operations */
            if (!$this->bulkManagement->scheduleBulk($groupId, [], $bulkDescription, $userId)) {
                throw new LocalizedException(
                    __('Something went wrong while processing the request.')
                );
            }
        }

        $operations = [];
        $requestItems = [];
        $bulkException = new BulkException();
        foreach ($entitiesArray as $key => $entityParams) {
            /** @var \Magento\AsynchronousOperations\Api\Data\ItemStatusInterface $requestItem */
            $requestItem = $this->itemStatusInterfaceFactory->create();

            try {
                /** @var OperationInterface $operation */
                $operation = $this->initializeOperationByTopic($topicName, $entityParams, $groupId, $key);
                /** @var EntityRepositoryInterface $entityRepository */
                $entityRepository = $this->repositoryFactory->create($operation);
                $entityRepository->save($operation);
                $operations[] = $operation;
                $requestItem->setId($key);
                $requestItem->setStatus(ItemStatusInterface::STATUS_ACCEPTED);
                $requestItem->setDataHash(
                    $this->encryptor->hash($operation->getSerializedData(), Encryptor::HASH_VERSION_SHA256)
                );
                $requestItems[] = $requestItem;
            } catch (\Exception $exception) {
                $this->logger->error($exception);
                $requestItem->setId($key);
                $requestItem->setStatus(ItemStatusInterface::STATUS_REJECTED);
                $requestItem->setErrorMessage($exception);
                $requestItem->setErrorCode($exception);
                $requestItems[] = $requestItem;
                $bulkException->addException(new LocalizedException(
                    __('Error processing %key element of input data', ['key' => $key]),
                    $exception
                ));
            }
        }

        if (!$this->bulkManagement->scheduleBulk($groupId, $operations, $bulkDescription, $userId)) {
            throw new LocalizedException(
                __('Something went wrong while processing the request.')
            );
        }
        /** @var AsyncResponseInterface $asyncResponse */
        $asyncResponse = $this->asyncResponseFactory->create();
        $asyncResponse->setBulkUuid($groupId);
        $asyncResponse->setRequestItems($requestItems);

        if ($bulkException->wasErrorAdded()) {
            $asyncResponse->setErrors(true);
            $bulkException->addData($asyncResponse);
            throw $bulkException;
        } else {
            $asyncResponse->setErrors(false);
        }

        return $asyncResponse;
    }

    /**
     * @param $topicName
     * @param $entityParams
     * @param $groupId
     * @param $requestId
     * @return OperationInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function initializeOperationByTopic($topicName, $entityParams, $groupId, $requestId)
    {
        $this->messageValidator->validate($topicName, $entityParams);
        $encodedMessage = $this->messageEncoder->encode($topicName, $entityParams);

        $serializedData = [
            'entity_id'        => null,
            'entity_link'      => '',
            'meta_information' => $encodedMessage,
        ];
        $data = [
            'data' => [
                OperationInterface::BULK_ID         => $groupId,
                OperationInterface::REQUEST_ID      => $requestId,
                OperationInterface::TOPIC_NAME      => $topicName,
                OperationInterface::SERIALIZED_DATA => $this->jsonSerializer->serialize($serializedData),
                OperationInterface::STATUS          => OperationInterface::STATUS_TYPE_OPEN,
            ],
        ];

        /** @var \Magento\AsynchronousOperations\Api\Data\OperationInterface $operation */
        $operation = $this->operationFactory->create($data);
        $operation->setHasDataChanges(true);
        return $operation;
    }
}
