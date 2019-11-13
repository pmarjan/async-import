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
     * @param string $identifier
     * @return mixed|string
     */
    public function generateId($entity, $identifier = null)
    {
        /** @var string $bulkIdentifier */
        $bulkIdentifier = $identifier != null ? $identifier : $entity->getData(BulkSummaryInterface::BULK_ID);

        return self::KEY_PREFIX .
            RedisIdentityInterface::SEPARATOR .
            (new \ReflectionClass($entity))->getShortName() .
            RedisIdentityInterface::SEPARATOR .
            $bulkIdentifier;
    }
}
