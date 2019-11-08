<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model\ResourceModel\Operation;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory;
use Magento\AsynchronousOperations\Model\EntityManagerRegistry;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\AsynchronousOperationsRedis\EntityManager\EntityManagerFactory;

/**
 * Create operation for list of bulk operations.
 */
class OperationRepository
{
    /**
     * @var \Magento\AsynchronousOperations\Api\Data\OperationInterfaceFactory
     */
    private $operationFactory;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var Magento\Framework\EntityManager\EntityManager | Magento\AsynchronousOperationsRedis\EntityManager\EntityManager
     */
    private $entityManager;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var MessageValidator
     */
    private $messageValidator;

    /**
     * OperationRepository constructor.
     * @param OperationInterfaceFactory $operationFactory
     * @param EntityManagerRegistry $entityManagerRegistry
     * @param MessageValidator $messageValidator
     * @param MessageEncoder $messageEncoder
     * @param Json $jsonSerializer
     * @throws \Exception
     */
    public function __construct(
        OperationInterfaceFactory $operationFactory,
        EntityManagerRegistry $entityManagerRegistry,
        MessageValidator $messageValidator,
        MessageEncoder $messageEncoder,
        Json $jsonSerializer
    ) {
        $this->operationFactory = $operationFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
        $this->entityManager = $entityManagerRegistry->get();
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
        return $this->entityManager->save($operation);
    }
}
