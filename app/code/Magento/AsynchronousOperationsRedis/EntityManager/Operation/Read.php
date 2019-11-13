<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperationsRedis\EntityManager\Operation;

use Magento\AsynchronousOperationsRedis\Model\Connection;
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\EntityManager\Operation\ReadInterface;
use Magento\AsynchronousOperationsRedis\EntityManager\Hydrator;
use Magento\AsynchronousOperationsRedis\KeyManager\KeyPool;
use Magento\AsynchronousOperationsRedis\Model\EntitiesPool;

class Read implements ReadInterface
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
     * Read constructor.
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
     * Read data
     *
     * @param object $entity
     * @param string $identifier
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $identifier, $arguments = [])
    {
        /** @var array $entityConfig */
        $entityConfig = $this->entitiesPool->getEntityConfig($entity);
        /** @var \Magento\AsynchronousOperationsRedis\Api\RedisKeyInterface $keyManager */
        $keyManager = $this->keyPool->getKeyManager($entityConfig['type']);
        /** @var string $id */
        $id = $keyManager->getId($entity, $entityConfig, $identifier);

        if ($keyManager->exists($id)) {
            if ($keyManager->ensureLockOff($id)) {
                $keyManager->setLock($id);

                $this->eventManager->dispatch(
                    'redis_entity_manager_read_before',
                    [
                        'manager' => $keyManager,
                        'data' => $entity->getData()
                    ]
                );

                /** @var array $data */
                $data = $keyManager->read($id);

                $keyManager->removeLock($id);

                $entity = $this->hydrator->setEntity($entity)->hydrate($data);

                $this->eventManager->dispatch(
                    'redis_entity_manager_read_after',
                    [
                        'manager' => $keyManager,
                        'data' => $data
                    ]
                );
            }
        }

        return $entity;
    }
}
