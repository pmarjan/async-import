<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperationsRedis\Api;

use Magento\AsynchronousOperationsRedis\Exception\LockWaitTimeExceeded;
use Magento\AsynchronousOperationsRedis\Exception\CouldNotSaveToRedisException;
use Magento\AsynchronousOperationsRedis\Exception\CouldNotRemoveFromRedisException;

interface RedisKeyInterface
{
    /** @var string  */
    const LOCK = 'lock';

    /** @var string  */
    const META_CREATED = 'created';

    /** @var string  */
    const META_UPDATED = 'updated';

    /** @var float */
    const MAX_LOCK_WAIT_TIME = 1;

    /** @var float  */
    const LOCK_WAIT_BEFORE_RETRY = 0.005;

    /**
     * @param object $entity
     * @return string
     */
    public function getId($entity);

    /**
     * Check if a key exists
     *
     * @param string $id
     * @return bool
     */
    public function exists($id);

    /**
     * Check if key has lock on it
     *
     * @param string $id
     * @return bool
     */
    public function hasLock($id);

    /**
     * @param $id
     * @return bool
     */
    public function setLock($id);

    /**
     * @param $id
     * @return bool
     */
    public function removeLock($id);

    /**
     * @param string $id
     * @return bool
     * @throws LockWaitTimeExceeded
     */
    public function ensureLockOff($id);

    /**
     * @param string $id
     * @return string|array $data
     */
    public function read($id);

    /**
     * @param string $id
     * @param string|array $data
     * @return bool
     * @throws CouldNotSaveToRedisException
     */
    public function insert($id, $data);

    /**
     * @param string $id
     * @param string|array $data
     * @return bool
     * @throws CouldNotSaveToRedisException
     */
    public function update($id, $data);

    /**
     * Remove key from database
     *
     * @param string $id
     * @return bool
     * @throws CouldNotRemoveFromRedisException
     */
    public function drop($id);

    /**
     * @param string $id
     * @return bool
     */
    public function validate($id);
}
