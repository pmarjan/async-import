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
use Magento\Framework\Module\ModuleManagerInterface;

class EntityManagerFactory
{
    /** @var Magento\Framework\Module\ModuleManagerInterface  */
    private $moduleManager;

    /**
     * EntityManagerFactory constructor.
     * @param ModuleManagerInterface $moduleManager
     */
    public function __construct(
        ModuleManagerInterface $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    /**
     * @return Magento\Framework\EntityManager\EntityManager | Magento\AsynchronousOperationsRedis\EntityManager\EntityManager
     */
    public function create()
    {
        if (!$this->moduleManager->isEnabled('Magento_AsynchronousOperationsRedis')) {
            return ObjectManager::getInstance()->get(DefaultEntityManager::class);
        }

        /** @var ConfigInterface $config */
        $config = ObjectManager::getInstance()->get(ConfigInterface::class);

        if ($config->requiresRedis()) {
            return ObjectManager::getInstance()->get(RedisEntityManager::class);
        }

        return ObjectManager::getInstance()->get(DefaultEntityManager::class);
    }
}
