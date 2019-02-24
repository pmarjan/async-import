<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportService\Api;

/**
 * Source management interface.
 */
interface UuidManagementInterface
{
    /**
     * Generate a universally unique identifier
     */
    public function generateUuid();
}