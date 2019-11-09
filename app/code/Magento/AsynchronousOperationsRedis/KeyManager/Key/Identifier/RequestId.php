<?php

namespace Magento\AsynchronousOperationsRedis\KeyManager\Key\Identifier;

use Magento\AsynchronousOperations\Model\Operation;
use Magento\AsynchronousOperationsRedis\Api\RedisIdentityInterface;

class RequestId implements GeneratorInterface
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
            $entity->getData(Operation::BULK_ID) .
            RedisIdentityInterface::SEPARATOR .
            $entity->getData(Operation::REQUEST_ID);
    }
}
