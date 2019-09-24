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
use Magento\Framework\EntityManager\Operation\UpdateInterface;
use Magento\AsynchronousOperationsRedis\Model\EntitiesPool;

class Update implements UpdateInterface
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
     * Update constructor.
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

        if ($keyManager->ensureLockOff($keyManager->getId($entity, $entityConfig))) {
            $keyManager->setLock($keyManager->getId($entity, $entityConfig));

            /** @var array $data */
            $data = $this->hydrator->setEntity($entity)
                ->setMetaData()
                ->extract();

            $this->eventManager->dispatch(
                'redis_entity_manager_update_before',
                [
                    'manager' => $keyManager,
                    'data' => $data
                ]
            );

            /** @var bool $result */
            $result = $keyManager->insert($keyManager->getId($entity, $entityConfig), $data);

            $keyManager->removeLock($keyManager->getId($entity, $entityConfig));

            $this->eventManager->dispatch(
                'redis_entity_manager_update_after',
                [
                    'manager' => $keyManager,
                    'data' => $data,
                    'result' => $result
                ]
            );

            if (!$result) {
                throw new CouldNotSaveToRedisException(__('This entity was not properly updated in redis'));
            }
        }

        return $entity;
    }
}
