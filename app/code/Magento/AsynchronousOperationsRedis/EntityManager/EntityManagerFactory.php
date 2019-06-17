<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperationsRedis\EntityManager;

use Magento\AsynchronousOperationsRedis\Api\ConfigInterface;
use Magento\AsynchronousOperationsRedis\EntityManager\EntityManager as RedisEntityManager;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\EntityManager\EntityManager as DefaultEntityManager;

class EntityManagerFactory
{
    /** @var \Magento\AsynchronousOperationsRedis\Api\ConfigInterface */
    protected $config;

    /**
     * EntityManagerFactory constructor.
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * @return Magento\Framework\EntityManager\EntityManager | Magento\AsynchronousOperationsRedis\EntityManager\EntityManager
     */
    public function create()
    {
        if ($this->config->requiresRedis()) {
            return ObjectManager::getInstance()->get(RedisEntityManager::class);
        }

        return ObjectManager::getInstance()->get(DefaultEntityManager::class);
    }
}