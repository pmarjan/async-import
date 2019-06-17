<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperationsRedis\EntityManager\Operation;

use Magento\AsynchronousOperationsRedis\Api\RedisIdentityInterface;
use Magento\AsynchronousOperationsRedis\Exception\RedisIdentityNoFoundException;
use Magento\AsynchronousOperationsRedis\Model\Connection;
use Magento\Framework\EntityManager\EventManager;
use Magento\Framework\EntityManager\Operation\DeleteInterface;
use Magento\AsynchronousOperationsRedis\EntityManager\Hydrator;
use Magento\AsynchronousOperationsRedis\KeyManager\KeyPool;

class Delete implements DeleteInterface
{
    /** @var \Magento\Framework\EntityManager\EventManager */
    private $eventManager;

    /** @var Magento\AsynchronousOperationsRedis\Model\Connection */
    private $connection;

    /** @var \Magento\AsynchronousOperationsRedis\EntityManager\Hydrator */
    private $hydrator;

    /** @var \Magento\AsynchronousOperationsRedis\KeyManager\KeyPool */
    private $keyPool;

    /**
     * Create constructor.
     * @param EventManager $eventManager
     * @param Connection $connection
     * @param Hydrator $hydrator
     * @param KeyPool $keyPool
     */
    public function __construct(
        EventManager $eventManager,
        Connection $connection,
        Hydrator $hydrator,
        KeyPool $keyPool
    ) {
        $this->eventManager = $eventManager;
        $this->connection = $connection;
        $this->hydrator = $hydrator;
        $this->keyPool = $keyPool;
    }

    /**
     * Check if redis key exists
     *
     * @param object $entity
     * @param string $identifier
     * @param array $arguments
     * @return object
     * @throws \Exception
     */
    public function execute($entity, $arguments = [])
    {
        if (! $entity instanceof RedisIdentityInterface) {
            throw new RedisIdentityNoFoundException(__('This entity does not has Redis identity'));
        }

        /** @var \Magento\AsynchronousOperationsRedis\Api\RedisKeyInterface $keyManager */
        $keyManager = $this->keyPool->getKeyManager($entity->getKeyType());

        return $keyManager->drop($keyManager->getId($entity));
    }
}
