<?php

namespace Magento\AsynchronousOperations\Model\Repository\Factory\Registry;

class Registry
{
    /** @var array */
    private $types;

    /** @var string */
    private $defaultConnection;

    /**
     * Registry constructor.
     * @param array $types
     * @param $defaultConnection
     */
    public function __construct(
        $types = [],
        $defaultConnection
    ) {
        $this->types = $types;
        $this->defaultConnection = $defaultConnection;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->defaultConnection;
    }

    /**
     * @param $activeEntityStorage
     * @param $entity
     * @return \Magento\AsynchronousOperations\Model\Repository\AbstractRepository
     * @throws \ReflectionException
     */
    public function getRepository($activeEntityStorage, $entity)
    {
        if (!isset($this->types[$activeEntityStorage])){
            throw new \Exception(
                __('%1 is not a valid storage type.', $activeEntityStorage)
            );
        }

        /** @var string $classShortName */
        $classShortName = (new \ReflectionClass($entity))->getShortName();

        if (!isset($this->types[$activeEntityStorage][$classShortName])) {
            throw new \Exception(
                __('%1 is not a valid repository type.', $classShortName)
            );
        }

        /** @var \Magento\AsynchronousOperations\Model\Repository\AbstractRepository $abstractRepository */
        $abstractRepository = $this->types[$activeEntityStorage][$classShortName];

        if (!$abstractRepository instanceof \Magento\AsynchronousOperations\Model\Repository\AbstractRepository) {
            throw new \Exception(
                __('The %1 does not comply with the AbstractRepository', $abstractRepository)
            );
        }

        return $abstractRepository->setEntity($entity);
    }
}
