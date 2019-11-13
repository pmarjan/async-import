<?php

namespace Magento\AsynchronousOperationsRedis\KeyManager\Key\Identifier;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\AsynchronousOperations\Model\Operation;
use Magento\AsynchronousOperationsRedis\Api\RedisIdentityInterface;

class RequestId implements GeneratorInterface
{
    /** @var string  */
    const KEY_PREFIX = 'AsynchronousOperations';

    /**
     * @param $entity
     * @param string $identifier
     * @return mixed|string
     */
    public function generateId($entity, $identifier)
    {
        /** @var string $bulkIdentifier */
        $bulkIdentifier = $identifier != null ? $identifier : $entity->getData(OperationInterface::BULK_ID);

        return self::KEY_PREFIX .
            RedisIdentityInterface::SEPARATOR .
            (new \ReflectionClass($entity))->getShortName() .
            RedisIdentityInterface::SEPARATOR .
            $bulkIdentifier .
            RedisIdentityInterface::SEPARATOR .
            $entity->getData(Operation::REQUEST_ID);
    }
}
