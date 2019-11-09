<?php

namespace Magento\AsynchronousOperationsRedis\KeyManager\Key\Identifier;

use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface;
use Magento\AsynchronousOperationsRedis\Api\RedisIdentityInterface;

class BulkId implements GeneratorInterface
{
    /** @var string  */
    const KEY_PREFIX = 'AsynchronousOperations';

    /**
     * @param $entity
     * @return mixed|string
     */
    public function generateId($entity)
    {
        return self::KEY_PREFIX .
            RedisIdentityInterface::SEPARATOR .
            (new \ReflectionClass($entity))->getShortName() .
            RedisIdentityInterface::SEPARATOR .
            $entity->getData(BulkSummaryInterface::BULK_ID);
    }
}
