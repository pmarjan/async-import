<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperationsRedis\Model;

use Magento\AsynchronousOperationsRedis\Api\ConfigInterface;

class Connection
{
    /** @var \Credis_Client */
    private $client;

    /** @var \Magento\AsynchronousOperationsRedis\Api\ConfigInterface */
    protected $config;

    /**
     * EntityManagerFactory constructor.
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    /**
     * @return \Credis_Client
     */
    public function getClientInstance()
    {
        if (!$this->client) {
            $this->client = new \Credis_Client(
                $this->config->getHost(),
                $this->config->getPort(),
                $this->config->getTimeout(),
                $this->config->getPersistentIdentifier(),
                $this->config->getDatabase(),
                $this->config->getPassword()
            );
        }

        return $this->client;
    }
}
