<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperationsRedis\EntityManager;

use Magento\Framework\App\ObjectManager;
use Magento\AsynchronousOperationsRedis\EntityManager\Operation\CheckIfExists;
use Magento\AsynchronousOperationsRedis\EntityManager\Operation\Read;
use Magento\AsynchronousOperationsRedis\EntityManager\Operation\Create;
use Magento\AsynchronousOperationsRedis\EntityManager\Operation\Update;
use Magento\AsynchronousOperationsRedis\EntityManager\Operation\Delete;
use Magento\AsynchronousOperationsRedis\Exception\OperationNotFoundException;

/**
 * Class OperationPool
 */
class OperationPool
{
    /**
     * @var array
     */
    private $defaultOperations = [
        'checkIfExists' => CheckIfExists::class,
        'read' => Read::class,
        'create' => Create::class,
        'update' => Update::class,
        'delete' => Delete::class,
    ];

    /**
     * @var array
     */
    private $operations;

    /**
     * OperationPool constructor.
     * @param array $operations
     */
    public function __construct(
        $operations = []
    ) {
        $this->operations = !empty($operations) ? $operations : $this->defaultOperations;
    }

    /**
     * @param $operationName
     * @return mixed
     * @throws OperationNotFoundException
     */
    public function getOperation($operationName)
    {
        if (isset($this->operations[$operationName])) {
            return ObjectManager::getInstance()->get($this->operations[$operationName]);
        }

        throw new OperationNotFoundException(__('No such operation available %1', $operationName));
    }
}