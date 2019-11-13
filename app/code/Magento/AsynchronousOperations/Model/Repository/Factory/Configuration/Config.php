<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperations\Model\Repository\Factory\Configuration;

use Magento\Framework\App\DeploymentConfig;

class Config implements ConfigInterface
{

    /** @var string */
    const CONNECT_PARAMS_PATH = self::OPERATIONS_NODE . '/' . self::REDIS_SETTINGS . '/';

    /** @var DeploymentConfig */
    private $deploymentConfig;

    /**
     * Config constructor.
     *
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(
        DeploymentConfig $deploymentConfig
    ) {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getStorage($connection = null){

        if (!$connection) {
            $key = self::OPERATIONS_NODE . "/" . self::STORAGE_NODE;
        } else {
            $key = self::OPERATIONS_NODE . "/" . $connection;
        }

        /** @var array $queueOperationsConfig */
        $entityStorage = $this->deploymentConfig->get(
            $key,
            self::DEFAULT_CONNECTION
        );

        return $entityStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        return $this->deploymentConfig->get( self::CONNECT_PARAMS_PATH . self::HOST);
    }

    /**
     * {@inheritdoc}
     */
    public function getPort()
    {
        return intval($this->deploymentConfig->get(self::CONNECT_PARAMS_PATH . self::PORT));
    }

    /**
     * {@inheritdoc}
     */
    public function getTimeout()
    {
        return floatval($this->deploymentConfig->get(self::CONNECT_PARAMS_PATH . self::TIMEOUT));
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentIdentifier()
    {
        return $this->deploymentConfig->get(self::CONNECT_PARAMS_PATH . self::PERSISTENT_IDENTIFIER);
    }

    /**
     * {@inheritdoc}
     */
    public function getDatabase()
    {
        return intval($this->deploymentConfig->get(self::CONNECT_PARAMS_PATH . self::DATABASE));
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->deploymentConfig->get(self::CONNECT_PARAMS_PATH . self::PASSWORD);
    }

}
