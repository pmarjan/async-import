<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperationsRedis\KeyManager\Key;

use Magento\AsynchronousOperationsRedis\Exception\CouldNotSaveToRedisException;
use Magento\AsynchronousOperationsRedis\Exception\LockWaitTimeExceeded;

class Hash extends AbstractKey
{
    /**
     * Check if key has lock on it
     *
     * @param string $id
     * @return bool
     */
    public function hasLock($id)
    {
        return $this->connection->getClientInstance()->hGet($id, self::LOCK);
    }

    /**
     * @param string $id
     * @return bool
     */
    public function ensureLockOff($id)
    {
        /** @var float $totalWaitTime */
        $totalWaitTime = 0;

        while ($this->hasLock($id) && $totalWaitTime < self::MAX_LOCK_WAIT_TIME) {
            if ($totalWaitTime + self::LOCK_WAIT_BEFORE_RETRY >= self::MAX_LOCK_WAIT_TIME) {
                throw new LockWaitTimeExceeded(__('The Redis key is locked for too long. Aborting operation.'));
            }

            sleep(self::LOCK_WAIT_BEFORE_RETRY);
            $totalWaitTime += self::LOCK_WAIT_BEFORE_RETRY;
        }

        return true;
    }

    /**
     * @param string $id
     * @return array $data
     */
    public function read($id)
    {
        return $this->connection->getClientInstance()->hGetAll($id);
    }

    /**
     * @param string $id
     * @param array $data
     * @return bool
     * @throws CouldNotSaveToRedisException
     */
    public function insert($id, $data)
    {
        return $this->connection->getClientInstance()->hMSet($id, $data);
    }

    /**
     * @param string $id
     * @param array $data
     * @return bool
     * @throws CouldNotSaveToRedisException
     */
    public function update($id, $data)
    {
        return $this->connection->getClientInstance()->hMSet($id, $data);
    }

    /**
     * @param $id
     * @return bool
     */
    public function setLock($id)
    {
        return $this->connection->getClientInstance()->hSet($id, self::LOCK, 1);
    }

    /**
     * @param $id
     * @return bool
     */
    public function removeLock($id)
    {
        return $this->connection->getClientInstance()->hSet($id, self::LOCK, 0);
    }
}
