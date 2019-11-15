<?php


namespace Magento\AsynchronousOperations\Model\Repository\Factory;

use Magento\AsynchronousOperations\Model\Repository\Factory\Registry\Registry;
use Magento\AsynchronousOperations\Model\Repository\Factory\Configuration\Config;
use Magento\Framework\Module\Manager;

class Factory
{
    /** @var Magento\Framework\Module\Manager  */
    private $moduleManager;

    /** @var Magento\AsynchronousOperations\Model\Repository\Factory\Registry\Registry */
    private $registry;

    /** @var Magento\AsynchronousOperations\Model\Repository\Factory\Configuration\Config */
    private $config;

    /**
     * Factory constructor.
     * @param Manager $moduleManager
     * @param Registry $registry
     * @param Config $config
     */
    public function __construct(
        Manager $moduleManager,
        Registry $registry,
        Config $config
    ) {
        $this->moduleManager = $moduleManager;
        $this->registry = $registry;
        $this->config = $config;
    }

    /**
     * @param string $entity
     * @return \Magento\AsynchronousOperations\Model\Repository\AbstractRepository
     * @throws \ReflectionException
     */
    public function create($entity)
    {
        /** @var string $activeEntityStorage */
        $activeEntityStorage = $this->config->getStorage($this->getStorageFlag());

        return $this->registry->getRepository($activeEntityStorage, $entity);
    }

    /**
     * @return string
     */
    private function getStorageFlag()
    {
        /** @var string $storageFlag */
        $storageFlag = null;

        if (!$this->moduleManager->isEnabled('Magento_AsynchronousOperationsRedis')) {
            $storageFlag = $this->registry->getDefaultConnection();
        }

        return $storageFlag;
    }
}
