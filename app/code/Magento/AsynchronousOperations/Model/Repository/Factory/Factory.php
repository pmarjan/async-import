<?php


namespace Magento\AsynchronousOperations\Model\Repository\Factory;

use Magento\AsynchronousOperations\Model\Repository\Factory\Registry\Registry;
use Magento\AsynchronousOperations\Model\Repository\Factory\Configuration\Config;
use Magento\Framework\Module\ModuleManagerInterface;

class Factory
{
    /** @var Magento\Framework\Module\ModuleManagerInterface  */
    private $moduleManager;

    /** @var Magento\AsynchronousOperations\Model\Repository\Factory\Registry\Registry */
    private $registry;

    /** @var Magento\AsynchronousOperations\Model\Repository\Factory\Configuration\Config */
    private $config;

    /**
     * Factory constructor.
     * @param ModuleManagerInterface $moduleManager
     * @param Registry $registry
     * @param Config $config
     */
    public function __construct(
        ModuleManagerInterface $moduleManager,
        Registry $registry,
        Config $config
    ) {
        $this->moduleManager = $moduleManager;
        $this->registry = $registry;
        $this->config = $config;
    }

    /**
     * @param $entity
     * @return Magento\AsynchronousOperations\Api\EntityRepositoryInterface
     * @throws \ReflectionException
     */
    public function create($entity)
    {
        /** @var string $activeEntityStorage */
        $activeEntityStorage = $this->config->getStorage($this->getStorageFlag());
        /** @var array $registryTypes */
        $registryTypes = $this->registry->getTypes();

        if (!isset($registryTypes[$activeEntityStorage])){
            throw new \Exception(
                __('%1 is not a valid type.', $activeEntityStorage)
            );
        }

        if (is_array($registryTypes[$activeEntityStorage])) {
            return $this->registry->getDbRepository($entity);
        }

        /** @var \Magento\AsynchronousOperations\Model\Repository\AbstractRepository $abstractRepository */
        $abstractRepository = $registryTypes[$activeEntityStorage];

        if (!$abstractRepository instanceof \Magento\AsynchronousOperations\Model\Repository\AbstractRepository) {
            throw new \Exception(
                __('Invalid abstract repository %1 provided in DI', $abstractRepository)
            );
        }

        return $abstractRepository->setEntity($entity);
    }

    /**
     * @return string
     */
    private function getStorageFlag()
    {
        /** @var string $storageFlag */
        $storageFlag = null;

        if (!$this->moduleManager->isEnabled('Magento_AsynchronousOperationsRedis')) {
            $storageFlag = 'db';
        }

        return $storageFlag;
    }
}
