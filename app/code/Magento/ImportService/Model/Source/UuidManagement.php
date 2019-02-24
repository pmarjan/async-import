<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportService\Model\Source;

use Magento\ImportService\Api\UuidManagementInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;
use Magento\ImportService\Exception as ImportServiceException;

class UuidManagement implements UuidManagementInterface
{

    /**
     * @return string
     * @throws ImportServiceException
     */
    public function generateUuid()
    {
        try {
            return Uuid::uuid1()->toString();
        } catch (UnsatisfiedDependencyException $e) {
            throw new ImportServiceException(
                __('Generation of UUID failed fo %1.', $e->getMessage())
            );
        }

    }
}
