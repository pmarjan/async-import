<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model\Entity;

interface ConfigInterface
{
    public const OPERATIONS_NODE = 'queue_operations';
    public const STORAGE_NODE = 'save';

    public const REDIS_SETTINGS = 'redis';
    public const HOST = 'host';
    public const PORT = 'port';
    public const TIMEOUT = 'timeout';
    public const PERSISTENT_IDENTIFIER = 'persistent_identifier';
    public const DATABASE = 'database';
    public const PASSWORD = 'password';

    public const DEFAULT_CONNECTION = "db";

    /**
     * Get current enabled storage for asynchronous operations
     *
     * @return string
     */
    public function getStorage();

    /**
     * Get host
     *
     * @return string
     */
    public function getHost();

    /**
     * Get port
     *
     * @return int
     */
    public function getPort();

    /**
     * Get connection timeout
     *
     * @return float
     */
    public function getTimeout();

    /**
     * Get unique string for persistent connections
     *
     * @return string
     */
    public function getPersistentIdentifier();

    /**
     * Get database
     *
     * @return int
     */
    public function getDatabase();

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword();
}