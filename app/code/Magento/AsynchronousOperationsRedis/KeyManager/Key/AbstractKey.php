<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperationsRedis\KeyManager\Key;

use Magento\AsynchronousOperationsRedis\Api\RedisIdentityInterface;
use Magento\AsynchronousOperationsRedis\Api\RedisKeyInterface;
use Magento\AsynchronousOperationsRedis\Model\Connection;
use Ramsey\Uuid\Uuid;
use Magento\Framework\Encryption\Encryptor;

abstract class AbstractKey implements RedisKeyInterface
{
    /**
     * @var Magento\AsynchronousOperationsRedis\Model\Connection
     */
    protected $connection;

    /**
     * @var Magento\Framework\Encryption\Encryptor
     */
    protected $encryptor;

    /**
     * AbstractKey constructor.
     * @param Connection $connection
     * @param Encryptor $encryptor
     */
    public function __construct(
        Connection $connection,
        Encryptor $encryptor
    ) {
        $this->connection = $connection;
        $this->encryptor = $encryptor;
    }

    /**
     * @param object $entity
     * @param array $entityConfig
     * @return string
     */
    public function getId($entity, $entityConfig)
    {
        /** @var \Magento\AsynchronousOperationsRedis\KeyManager\Key\Identifier\GeneratorInterface $identifierGenerator */
        $identifierGenerator = $entityConfig['identifierGenerator'];

        return $identifierGenerator->generateId($entity);
    }


    /**
     * Check if a key exists
     *
     * @param string $id
     * @return bool
     */
    public function exists($id)
    {
        return $this->connection->getClientInstance()->exists($id);
    }

    /**
     * Remove key from database
     *
     * @param string $id
     * @return bool
     */
    public function drop($id)
    {
        return $this->connection->getClientInstance()->del($id);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function validate($id)
    {
        /** @var int $seapratorPosition */
        $separatorPosition = strpos($id, RedisIdentityInterface::SEPARATOR);

        if (!$separatorPosition) {
            return false;
        }

        /** @var string $uuid */
        $uuid = substr($id, $separatorPosition + strlen(RedisIdentityInterface::SEPARATOR));

        if (!Uuid::isValid($uuid)) {
            return false;
        }

        return true;
    }
}
