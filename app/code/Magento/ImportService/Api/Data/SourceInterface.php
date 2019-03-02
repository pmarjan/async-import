<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportService\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface SourceInterface
 */
interface SourceInterface extends ExtensibleDataInterface
{
    const ENTITY_ID = 'entity_id';
    const UUID = 'uuid';
    const SOURCE_TYPE = 'source_type';
    const IMPORT_TYPE = 'import_type';
    const IMPORT_DATA = 'import_data';
    const CREATED_AT = 'created_at';
    const STATUS = 'status';
    const STATUS_UPLOADED = 'uploaded';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @return string
     */
    public function getUuid();
    
    /**
     * Set data source uuid
     *
     * @param string $uuid
     * @return $this
     */
    public function setUuid($uuid);

    /**
     * Retrieve data source type
     *
     * @return string
     */
    public function getSourceType();

    /**
     * Set data source type
     *
     * @param string $sourceType
     * @return $this
     */
    public function setSourceType($sourceType);

    /**
     * Retrieve Import type
     *
     * @return string
     */
    public function getImportType();

    /**
     * Set Import type
     *
     * @param string $importType
     * @return $this
     */
    public function setImportType($importType);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * Retrieve Import data
     *
     * @return string
     */
    public function getImportData();

    /**
     * Set Import data
     *
     * @param string $importData
     * @return $this
     */
    public function setImportData($importData);

    /**
     * Retrieve Import data
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Retrieve existing extension attributes object or create a new one.
     *
     * @return \Magento\ImportService\Api\Data\SourceExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Magento\ImportService\Api\Data\SourceExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(\Magento\ImportService\Api\Data\SourceExtensionInterface $extensionAttributes);
}
