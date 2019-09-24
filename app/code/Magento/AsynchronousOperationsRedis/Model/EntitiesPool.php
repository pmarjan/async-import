<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperationsRedis\Model;

use Magento\AsynchronousOperationsRedis\Exception\RedisIdentityNoFoundException;

/**
 * Class OperationPool
 */
class EntitiesPool
{
    /**
     * @var array
     */
    private $entities;

    /**
     * OperationPool constructor.
     * @param array $operations
     */
    public function __construct(
        $entities = []
    ) {
        $this->entities = $entities;
    }

    /**
     * @param $entity
     * @return array
     * @throws RedisIdentityNoFoundException
     */
    public function getEntityConfig($entity)
    {
        foreach ($this->entities as $entityConfig) {
            if ($entity instanceof $entityConfig['interface']) {
                return $entityConfig;
            }
        }

        throw new RedisIdentityNoFoundException(__('No such entity is available for redis storage %1', get_class($entity)));
    }
}
