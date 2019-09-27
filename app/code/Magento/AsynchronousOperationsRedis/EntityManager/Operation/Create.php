<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperationsRedis\EntityManager\Operation;

use Magento\AsynchronousOperationsRedis\EntityManager\Hydrator;
use Magento\AsynchronousOperationsRedis\Exception\CouldNotSaveToRedisException;
use Magento\AsynchronousOperationsRedis\KeyManager\KeyPool;
use Magento\AsynchronousOperationsRedis\Model\Connection;
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\EntityManager\Operation\CreateInterface;
use Magento\AsynchronousOperationsRedis\Model\EntitiesPool;

class Create implements CreateInterface
{
    /** @var \Magento\Framework\EntityManager\EventManager */
    private $eventManager;

    /** @var Magento\AsynchronousOperationsRedis\Model\Connection */
    private $connection;

    /** @var \Magento\AsynchronousOperationsRedis\EntityManager\Hydrator */
    private $hydrator;

    /** @var \Magento\AsynchronousOperationsRedis\KeyManager\KeyPool */
    private $keyPool;

    /** @var \Magento\AsynchronousOperationsRedis\Model\EntitiesPool  */
    private $entitiesPool;

    /**
     * Create constructor.
     * @param EventManager $eventManager
     * @param Connection $connection
     * @param Hydrator $hydrator
     * @param KeyPool $keyPool
     * @param EntitiesPool $entitiesPool
     */
    public function __construct(
        EventManager $eventManager,
        Connection $connection,
        Hydrator $hydrator,
        KeyPool $keyPool,
        EntitiesPool $entitiesPool
    ) {
        $this->eventManager = $eventManager;
        $this->connection = $connection;
        $this->hydrator = $hydrator;
        $this->keyPool = $keyPool;
        $this->entitiesPool = $entitiesPool;
    }

    /**
     * Create redis hash set
     *
     * @param object $entity
     * @param string $identifier
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        /** @var array $entityConfig */
        $entityConfig = $this->entitiesPool->getEntityConfig($entity);
        /** @var \Magento\AsynchronousOperationsRedis\Api\RedisKeyInterface $keyManager */
        $keyManager = $this->keyPool->getKeyManager($entityConfig['type']);
        /** @var string $key */
        $key = $keyManager->getId($entity, $entityConfig);

        /** @var array $data */
        $data = $this->hydrator->setEntity($entity)
            ->setMetaData(true)
            ->extract();

        $this->eventManager->dispatch(
            'redis_entity_manager_save_before',
            [
                'manager' => $keyManager,
                'data' => $data
            ]
        );

        /** @var bool $result */
        $result = $keyManager->insert($key, $data);

        $this->eventManager->dispatch(
            'redis_entity_manager_save_after',
            [
                'manager' => $keyManager,
                'data' => $data,
                'result' => $result
            ]
        );

        if (!$result) {
            throw new CouldNotSaveToRedisException(__('This entity was not properly saved in redis'));
        }

        return $entity->setData('key', $key);
    }
}
