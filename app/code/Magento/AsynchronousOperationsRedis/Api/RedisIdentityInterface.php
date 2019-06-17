<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperationsRedis\Api;

interface RedisIdentityInterface
{
    /** @var string  */
    const SEPARATOR = '::';

    /** @var string  */
    const TYPE_SET = 'set';

    /** @var string  */
    const TYPE_SORTED_SET = 'sorted_set';

    /** @var string  */
    const TYPE_HASH = 'hash';

    /**
     * @return string
     */
    public function getRedisKey();

    /**
     * @return string
     */
    public function getKeyType();
}