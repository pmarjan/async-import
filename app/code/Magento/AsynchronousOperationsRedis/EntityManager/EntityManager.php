<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperationsRedis\EntityManager;

use Magento\Framework\EntityManager\Operation\CheckIfExistsInterface;
use Magento\Framework\EntityManager\Operation\CreateInterface;
use Magento\Framework\EntityManager\Operation\DeleteInterface;
use Magento\Framework\EntityManager\Operation\ReadInterface;
use Magento\Framework\EntityManager\Operation\UpdateInterface;

/**
 * Class EntityManager
 * @package Magento\AsynchronousOperationsRedis\EntityManager
 */
class EntityManager
{
    /**
     * @var OperationPool
     */
    private $operationPool;

    /**
     * EntityManager constructor.
     * @param OperationPool $operationPool
     */
    public function __construct(
        OperationPool $operationPool
    ) {
        $this->operationPool = $operationPool;
    }

    /**
     * @param object $entity
     * @param string $identifier
     * @param array $arguments
     * @return mixed
     * @throws \LogicException
     */
    public function load($entity, $identifier, $arguments = [])
    {
        $operation = $this->operationPool->getOperation('read');
        if (!($operation instanceof ReadInterface)) {
            throw new \LogicException(get_class($operation) . ' must implement ' . ReadInterface::class);
        }

        try {
            $entity = $operation->execute($entity, $identifier, $arguments);
        } catch (\Exception $e) {
            throw $e;
        }

        return $entity;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return object
     * @throws \LogicException
     * @throws \Exception
     */
    public function save($entity, $arguments = [])
    {
        if ($this->has($entity)) {
            $operation = $this->operationPool->getOperation('update');
            if (!($operation instanceof UpdateInterface)) {
                throw new \LogicException(get_class($operation) . ' must implement ' . UpdateInterface::class);
            }
        } else {
            $operation = $this->operationPool->getOperation('create');
            if (!($operation instanceof CreateInterface)) {
                throw new \LogicException(get_class($operation) . ' must implement ' . CreateInterface::class);
            }
        }
        try {
            $entity = $operation->execute($entity, $arguments);
        } catch (\Exception $e) {
            throw $e;
        }
        return $entity;
    }

    /**
     * @param object $entity
     * @return bool
     * @throws \LogicException
     */
    public function has($entity)
    {
        $operation = $this->operationPool->getOperation('checkIfExists');
        if (!($operation instanceof CheckIfExistsInterface)) {
            throw new \LogicException(get_class($operation) . ' must implement ' . CheckIfExistsInterface::class);
        }

        try {
            $result = $operation->execute($entity);
        } catch (\Exception $e) {
            throw $e;
        }

        return $result;
    }

    /**
     * @param object $entity
     * @param array $arguments
     * @return bool
     * @throws \LogicException
     * @throws \Exception
     */
    public function delete($entity, $arguments = [])
    {
        $operation = $this->operationPool->getOperation('delete');
        if (!($operation instanceof DeleteInterface)) {
            throw new \LogicException(get_class($operation) . ' must implement ' . DeleteInterface::class);
        }

        try {
            $result = $operation->execute($entity, $arguments);
        } catch (\Exception $e) {
            throw $e;
        }

        return $result;
    }
}
