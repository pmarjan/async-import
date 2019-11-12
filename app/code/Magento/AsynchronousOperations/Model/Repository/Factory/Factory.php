<?php


namespace Magento\AsynchronousOperations\Model\Repository\Factory;

use Magento\AsynchronousOperations\Model\Repository\Factory\Registry\Registry;
use Magento\AsynchronousOperations\Model\Repository\Factory\Configuration\Config;

class Factory
{
    /** @var Magento\AsynchronousOperations\Model\Repository\Factory\Registry\Registry */
    private $registry;

    /** @var Magento\AsynchronousOperations\Model\Repository\Factory\Configuration\Config */
    private $config;

    /**
     * BulkSummary constructor.
     * @param Registry $registry
     * @param Config $config
     */
    public function __construct(
        Registry $registry,
        Config $config
    ) {
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
        $activeEntityStorage = $this->config->getStorage();
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

        return $registryTypes[$activeEntityStorage];
    }
}
