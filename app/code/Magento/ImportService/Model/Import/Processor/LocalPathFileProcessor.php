<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportService\Model\Import\Processor;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Filesystem\Io\File;
use Magento\ImportService\Api\Data\SourceInterface;
use Magento\ImportService\Api\Data\SourceUploadResponseInterface;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\ImportService\Api\SourceRepositoryInterface;
use Magento\ImportService\ImportServiceException;
use Magento\ImportService\Model\Import\SourceTypesValidatorInterface;
use Magento\ImportService\Model\Source\Validator;
use Magento\Framework\DataObject\IdentityGeneratorInterface as IdentityGenerator;

/**
 * CSV files processor for asynchronous import
 */
class LocalPathFileProcessor implements SourceProcessorInterface
{
    /**
     * Import Type
     */
    const IMPORT_TYPE = 'local_path';

    /**
     * CSV Source Type
     */
    const SOURCE_TYPE_CSV = 'csv';
  
    /**
     * @var SourceTypesValidatorInterface
     */
    private $sourceTypesValidator;

    /**
     * @var File
     */
    private $fileSystemIo;

    /**
     * @var Filesystem
     */
    private $fileSystem;

    /**
     * @var WriteInterface
     */
    private $directoryWrite;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var string
     */
    private $newFileName;

    /**
     * @var SourceInterface
     */
    private $source;

    /**
     * @var \Magento\Framework\DataObject\IdentityGeneratorInterface
     */
    private $identityGenerator;

    /**
     * @var \Magento\ImportService\Model\Source\Validator
     */
    private $validator;

    /**
     * LocalPathFileProcessor constructor
     *
     * @param File $fileSystemIo
     * @param Filesystem $fileSystem
     * @param SourceTypesValidatorInterface $sourceTypesValidator
     * @param SourceRepositoryInterface $sourceRepository
     * @param IdentityGenerator $identityGenerator
     * @param Validator $validator
     */
    public function __construct(
        File $fileSystemIo,
        Filesystem $fileSystem,
        SourceTypesValidatorInterface $sourceTypesValidator,
        SourceRepositoryInterface $sourceRepository,
        IdentityGenerator $identityGenerator,
        Validator $validator
    ) {
        $this->fileSystemIo = $fileSystemIo;
        $this->sourceTypesValidator = $sourceTypesValidator;
        $this->fileSystem = $fileSystem;
        $this->sourceRepository = $sourceRepository;
        $this->identityGenerator = $identityGenerator;
        $this->validator = $validator;
    }

    /**
     * Uploads process
     *
     * @inheritdoc
     * @throws FileSystemException
     * @throws ImportServiceException
     */
    public function processUpload(SourceInterface $source, SourceUploadResponseInterface $response)
    {
        $this->source = $source;
        try {
            $this->validateSource();
            $this->saveFile();
            $source = $this->saveSource();
            $response->setStatus($source->getStatus());
            $response->setUuid($source->getUuid());
        } catch (CouldNotSaveException $e) {
            $this->removeFile($source->getImportData());
            throw new ImportServiceException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * Saves source in DB
     *
     * @return SourceInterface
     */
    private function saveSource()
    {
        $this->source->setImportData($this->getNewFileName());
        $this->source->setStatus(SourceInterface::STATUS_UPLOADED);

        return $this->sourceRepository->save($this->source);
    }

    /**
     * Saves file at the storage
     *
     * @return string
     * @throws FileSystemException
     */
    private function saveFile()
    {
        $this->directoryWrite->copyFile(
            $this->source->getImportData(),
            $this->getNewFileName()
        );

        return $this->getNewFileName();
    }

    /**
     * Generates new file name
     *
     * @return string
     */
    private function getNewFileName()
    {
        if (!$this->newFileName) {
            $this->newFileName = self::IMPORT_SOURCE_FILE_PATH . '/'
                . $this->generateId()
                . '.' . $this->source->getSourceType();
        }

        return $this->newFileName;
    }

    /**
     * Generates UUID, unless already set on $this->source
     * 
     * @return string
     */
    private function generateId()
    {
        /** @var string $fileId */
        $fileId = $this->source->getUuid();

        if (!$fileId || !$this->validator->validateUuid($this->source)) {
            $fileId = $this->identityGenerator->generateId();
            $this->source->setUuid($fileId);
        }

        return $fileId;
    }

    /**
     * Provides configured directoryWrite
     *
     * @return WriteInterface
     * @throws FileSystemException
     */
    private function getDirectoryWrite()
    {
        if (!$this->directoryWrite) {
            $this->directoryWrite = $this->fileSystem
                ->getDirectoryWrite(DirectoryList::ROOT);
        }

        return $this->directoryWrite;
    }

    /**
     * Validates source
     *
     * @throws FileSystemException
     * @throws ImportServiceException
     */
    private function validateSource()
    {
        $absoluteSourcePath = $this->getDirectoryWrite()
            ->getAbsolutePath($this->source->getImportData());
        if (!$this->fileSystemIo->read($absoluteSourcePath)) {
            throw new ImportServiceException(
                __("Cannot read from file system. File not existed or cannot be read")
            );
        }
        $this->sourceTypesValidator->execute($this->source);
    }

    /**
     * Removes source
     *
     * @param string $filename
     * @return bool
     * @throws FileSystemException
     */
    private function removeFile($filename)
    {
        return $this->getDirectoryWrite()->delete($filename);
    }
}
