<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperationsRedis\KeyManager;

use Magento\AsynchronousOperationsRedis\KeyManager\Key\Hash;
use Magento\Framework\App\ObjectManager;
use Magento\AsynchronousOperationsRedis\Exception\KeyTypeNotFoundException;

class KeyPool
{
    /**
     * @var array
     */
    private $keyImplementations = [
        'hash' => Hash::class
    ];

    /**
     * @var array
     */
    private $implementations;

    /**
     * OperationPool constructor.
     * @param array $operations
     */
    public function __construct(
        $implementations = []
    ) {
        $this->implementations = !empty($implementations) ? $implementations : $this->keyImplementations;
    }

    /**
     * @param $name
     * @return \Magento\AsynchronousOperationsRedis\Api\RedisKeyInterface
     * @throws OperationNotFoundException
     */
    public function getKeyManager($name)
    {
        if (isset($this->implementations[$name])) {
            return ObjectManager::getInstance()->get($this->implementations[$name]);
        }

        throw new KeyTypeNotFoundException(__('No such key type is available %1', $name));
    }
}
