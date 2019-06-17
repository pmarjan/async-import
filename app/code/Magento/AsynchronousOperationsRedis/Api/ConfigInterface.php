<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperationsRedis\Api;

interface ConfigInterface
{
    const OPERATIONS_NODE = 'queue_operations';
    const STORAGE_NODE = 'save';
    const REDIS = 'redis';
    const HOST = 'host';
    const PORT = 'port';
    const TIMEOUT = 'timeout';
    const PERSISTENT_IDENTIFIER = 'persistent_identifier';
    const DATABASE = 'database';
    const PASSWORD = 'password';

    /**
     * Checks whether redis is required for Asynchronous Operations
     *
     * @return bool
     */
    public function requiresRedis();

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