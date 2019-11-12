<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model\Repository;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Model\EntityManagerRegistry;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\AsynchronousOperations\Api\Data\OperationSearchResultsInterfaceFactory as SearchResultFactory;
use Magento\AsynchronousOperations\Api\Data\OperationExtensionInterfaceFactory;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation\CollectionFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\AsynchronousOperations\Model\ResourceModel\Operation as OperationResource;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Repository class for @see \Magento\AsynchronousOperations\Api\OperationRepositoryInterface
 */
class Operation implements \Magento\AsynchronousOperations\Api\OperationRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SearchResultFactory
     */
    private $searchResultFactory;

    /**
     * @var JoinProcessorInterface
     */
    private $joinProcessor;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\OperationExtensionInterfaceFactory
     */
    private $operationExtensionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var OperationResource
     */
    private $operationResource;

    /**
     * @var \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var MessageValidator
     */
    private $messageValidator;

    /**
     * Operation constructor.
     * @param EntityManager $entityManager
     * @param CollectionFactory $collectionFactory
     * @param SearchResultFactory $searchResultFactory
     * @param JoinProcessorInterface $joinProcessor
     * @param OperationExtensionInterfaceFactory $operationExtension
     * @param CollectionProcessorInterface $collectionProcessor
     * @param \Psr\Log\LoggerInterface $logger
     * @param OperationResource $operationResource
     * @param OperationInterfaceFactory $operationFactory
     * @param MessageValidator $messageValidator
     * @param MessageEncoder $messageEncoder
     * @param Json $jsonSerializer
     */
    public function __construct(
        EntityManager $entityManager,
        CollectionFactory $collectionFactory,
        SearchResultFactory $searchResultFactory,
        JoinProcessorInterface $joinProcessor,
        OperationExtensionInterfaceFactory $operationExtension,
        CollectionProcessorInterface $collectionProcessor,
        \Psr\Log\LoggerInterface $logger,
        OperationResource $operationResource,
        OperationInterfaceFactory $operationFactory,
        MessageValidator $messageValidator,
        MessageEncoder $messageEncoder,
        Json $jsonSerializer
    ) {
        $this->entityManager = $entityManager;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultFactory = $searchResultFactory;
        $this->joinProcessor = $joinProcessor;
        $this->operationExtensionFactory = $operationExtension;
        $this->collectionProcessor = $collectionProcessor;
        $this->logger = $logger;
        $this->collectionProcessor = $collectionProcessor;
        $this->operationResource = $operationResource;
        $this->operationFactory = $operationFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
    }

    /**
     * @param $topicName
     * @param $entityParams
     * @param $groupId
     * @param $requestId
     * @return mixed
     */
    public function createByTopic($topicName, $entityParams, $groupId, $requestId)
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
        return $this->save($operation);
    }

    /**
     * @inheritDoc
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Magento\AsynchronousOperations\Api\Data\OperationSearchResultsInterface $searchResult */
        $searchResult = $this->searchResultFactory->create();

        /** @var \Magento\AsynchronousOperations\Model\ResourceModel\Operation\Collection $collection */
        $collection = $this->collectionFactory->create();
        $this->joinProcessor->process($collection, \Magento\AsynchronousOperations\Api\Data\OperationInterface::class);
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setItems($collection->getItems());

        return $searchResult;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return \Magento\Framework\Model\AbstractModel
     * @throws CouldNotSaveException
     */
    public function save($entity)
    {
        try {
            $this->operationResource->save($entity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $entity;
    }

    /**
     * @param int $bulkUuid
     * @return \Magento\Framework\Model\AbstractModel $entity
     */
    public function getByUuid($bulkUuid)
    {
        /** @var \Magento\AsynchronousOperations\Api\Data\OperationInterface $operation */
        $operation = $this->operationFactory->create();
        $this->operationResource->load($operation, $bulkUuid, 'bulk_uuid');
        if (!$operation->getId()) {
            throw new NoSuchEntityException(__('The Operation with the "%1" UUID doesn\'t exist.', $bulkUuid));
        }

        return $operation;
    }
}
