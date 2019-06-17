<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperationsRedis\Model;

use Magento\AsynchronousOperationsRedis\Api\ConfigInterface;
use Magento\Framework\App\DeploymentConfig;

class Config implements ConfigInterface
{
    /** @var string */
    const CONNECT_PARAMS_PATH = self::OPERATIONS_NODE . '/' . self::REDIS . '/';

    /** @var Magento\Framework\App\DeploymentConfig */
    private $deploymentConfig;

    /**
     * Config constructor.
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        DeploymentConfig $deploymentConfig
    ) {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Checks whether redis is required for Asynchronous Operations
     *
     * @return bool
     */
    public function requiresRedis()
    {
        /** @var array $queueOperationsConfig */
        $queueOperationsConfig = $this->deploymentConfig->getConfigData(self::OPERATIONS_NODE);

        if ($queueOperationsConfig && is_array($queueOperationsConfig)) {
            return $this->validateConfig($queueOperationsConfig);
        }

        return false;
    }

    /**
     * Get host
     *
     * @return string
     */
    public function getHost()
    {
        return $this->deploymentConfig->get( self::CONNECT_PARAMS_PATH . self::HOST);
    }

    /**
     * Get port
     *
     * @return int
     */
    public function getPort()
    {
        return intval($this->deploymentConfig->get(self::CONNECT_PARAMS_PATH . self::PORT));
    }

    /**
     * Get connection timeout
     *
     * @return float
     */
    public function getTimeout()
    {
        return floatval($this->deploymentConfig->get(self::CONNECT_PARAMS_PATH . self::TIMEOUT));
    }

    /**
     * Get unique string for persistent connections
     *
     * @return string
     */
    public function getPersistentIdentifier()
    {
        return $this->deploymentConfig->get(self::CONNECT_PARAMS_PATH . self::PERSISTENT_IDENTIFIER);
    }

    /**
     * Get database
     *
     * @return int
     */
    public function getDatabase()
    {
        return intval($this->deploymentConfig->get(self::CONNECT_PARAMS_PATH . self::DATABASE));
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->deploymentConfig->get(self::CONNECT_PARAMS_PATH . self::PASSWORD);
    }

    /**
     * @param array $config
     * @return bool
     */
    private function validateConfig($config)
    {
        if (isset($config[self::STORAGE_NODE])
            && $config[self::STORAGE_NODE] == self::REDIS
            && isset($config[self::REDIS])
            && is_array($config[self::REDIS])) {
            return true;
        }

        return false;
    }
}
