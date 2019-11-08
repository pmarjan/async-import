<?php

namespace Magento\AsynchronousOperationsRedis\KeyManager\Key\Identifier;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\AsynchronousOperationsRedis\Api\RedisIdentityInterface;

class BulkId implements GeneratorInterface
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
            $entity->getData(BulkSummaryInterface::BULK_ID);
    }
}
