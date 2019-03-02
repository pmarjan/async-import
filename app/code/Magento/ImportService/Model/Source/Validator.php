<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportService\Model\Source;

use Magento\Framework\File\Mime\Proxy as Mime;
use Magento\Framework\Filesystem\Driver\Http\Proxy as Http;
use Ramsey\Uuid\Uuid;

/**
 * Class Validator
 */
class Validator
{
    /**
     * @var string[]
     */
    private $allowedMimeTypes;

    /**
     * @var \Magento\Framework\File\Mime\Proxy
     */
    private $mime;

    /**
     * @var \Magento\Framework\Filesystem\Driver\Http
     */
    private $httpDriver;

    /**
     * @param string[] $allowedMimeTypes
     * @param Mime $mime
     * @param Http $httpDriver
     */
    public function __construct(
        array $allowedMimeTypes,
        Mime $mime,
        Http $httpDriver
    ) {
        $this->allowedMimeTypes = $allowedMimeTypes;
        $this->mime = $mime;
        $this->httpDriver = $httpDriver;
    }

    /**
     * @return string[]
     */
    public function getAllowedMimeTypes()
    {
        return $this->allowedMimeTypes;
    }

    /**
     * @param \Magento\ImportService\Api\Data\SourceInterface $source
     * @return array|null
     */
    public function validateRequest(\Magento\ImportService\Api\Data\SourceInterface $source)
    {
        $errors = [];

        if (!$this->validateSourceType($source)) {
            $errors[] = __('%1 cannot be empty', $source::SOURCE_TYPE);
        }

        if (!$this->validateImportData($source)) {
            $errors[] = __('%1 cannot be empty', $source::IMPORT_DATA);
        }

        if (count($errors) > 0) {
            return $errors;
        }

        return null;
    }

    /**
     * @param \Magento\ImportService\Api\Data\SourceInterface $source
     * @return bool
     */
    public function validateSourceType(\Magento\ImportService\Api\Data\SourceInterface $source)
    {
        if (!$source->getSourceType()) {
            return false;
        }

        return true;
    }

    /**
     * @param \Magento\ImportService\Api\Data\SourceInterface $source
     * @return bool
     */
    public function validateImportData(\Magento\ImportService\Api\Data\SourceInterface $source)
    {
        if (!$source->getImportData()) {
            return false;
        }

        return true;
    }

    /**
     * @param \Magento\ImportService\Api\Data\SourceInterface $source
     * @return bool
     */
    public function validateUuid(\Magento\ImportService\Api\Data\SourceInterface $source)
    {
        if (!Uuid::isValid($source->getUuid())) {
            return false;
        }

        return true;
    }

    /**
     * @param string $absoluteFilePath
     * @return bool
     */
    public function validateMimeTypeForLocalFile(string $absoluteFilePath)
    {
        /** @var string $mimeType */
        $mimeType = $this->mime->getMimeType($absoluteFilePath);

        if (!in_array($mimeType, $this->allowedMimeTypes)) {
            return false;
        }

        return true;
    }

    /**
     * @param string $url
     * @return bool
     */
    public function validateMimeTypeForRemoteFile(string $url)
    {
        /** @var array $stat */
        $stat = $this->httpDriver->stat($this->getSourceLocation($url));

        if (!isset($stat['type']) || !in_array($stat['type'], $this->getAllowedMimeTypes())) {
            return false;
        }

        return true;
    }

    /**
     * @param string $url
     * @return bool
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function checkIfRemoteFileExists($url)
    {
        if (!$this->httpDriver->isExists($this->getSourceLocation($url))) {
            return false;
        }

        return true;
    }

    /**
     * @param string $url
     * @return string
     */
    private function getSourceLocation($url)
    {
        return preg_replace("(^https?://)", "", $url);
    }
}
