<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AsynchronousOperations\Model;

use Magento\AsynchronousOperations\Model\Entity\ConfigInterface;

/**
 * Class EntityManagerRegistry
 */
class EntityManagerRegistry
{

    /**
     * EntityManagerRegistry constructor.
     *
     * @param ConfigInterface $config
     * @param array $entityManagers
     */
    public function __construct(
        ConfigInterface $config,
        array $entityManagers = []
    ) {
        $this->config = $config;
        $this->entityManagers = $entityManagers;
    }

    /**
     * Receiev
     *
     * @return mixed
     * @throws \Exception
     */
    public function get()
    {
        $activeEntityStorage = $this->config->getStorage();

        if (!isset($this->entityManagers[$activeEntityStorage])){
            throw new \Exception(
                __('%1 is not valid type for queue operations storage.', $activeEntityStorage)
            );
        }

        return $this->entityManagers[$activeEntityStorage];

    }
}