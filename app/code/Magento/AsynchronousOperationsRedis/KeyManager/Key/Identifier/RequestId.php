<?php

namespace Magento\AsynchronousOperationsRedis\KeyManager\Key\Identifier;

use Magento\AsynchronousOperations\Model\Operation;
use Magento\AsynchronousOperationsRedis\Api\RedisIdentityInterface;

class RequestId implements GeneratorInterface
{
    /**
     * @param $entity
     * @param $entityConfig
     * @return mixed|string
     */
    public function generateId($entity, $entityConfig)
    {
        return $entityConfig['keyPrefix'] .
            RedisIdentityInterface::SEPARATOR .
            $entity->getData(Operation::BULK_ID) .
            RedisIdentityInterface::SEPARATOR .
            $entity->getData(Operation::REQUEST_ID);
    }
}
