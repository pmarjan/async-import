<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ImportService\Api;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\UrlInterface;
use Magento\ImportService\Api\Data\SourceInterface;
use Magento\ImportService\Api\Data\SourceUploadResponseInterface;
use Magento\ImportService\Model\Import\SourceProcessorPool;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

/**
 * Tests the import service by given external source.
 */
class ExternalFileProcessorTest extends WebapiAbstract
{
    const SERVICE_NAME = 'sourceRepositoryV1';
    const SERVICE_VERSION = 'V1';
    const RESOURCE_PATH = '/V1/import/source';

    /**
     * The tested file extension
     */
    const EXTERNAL_FILE_TYPE = 'csv';

    /**
     * The type of import
     */
    const IMPORT_TYPE = 'external';

    /**
     * The temporary directory name
     */
    const TEMPORARY_DIR = 'tmp_importservice/';

    /** 
     * \Magento\Framework\Filesystem\Directory\WriteInterface $varWriter 
     */
    private $varWriter;

    /** 
     * \Magento\Framework\Filesystem\Directory\WriteInterface $mediaWriter 
     */
    private $mediaWriter;

    /**
     * Set Up
     */
    public function setUp()
    {
        parent::setUp();

        $this->varWriter = $this->getWriteInterface(DirectoryList::VAR_DIR);
        $this->mediaWriter = $this->getWriteInterface(DirectoryList::MEDIA);
    }

    /**
     * Test Import Data not set
     */
    public function testImportDataNotSet()
    {
        $result = $this->_webApiCall(
            $this->makeServiceInfo(),
            $this->makeRequestData(null)
        );

        $this->assertEquals(SourceInterface::STATUS_FAILED, $result['status']);
        $this->assertRegExp('/Invalid request/', $result['error']);
    }

    /**
     * Test non reachable file
     */
    public function testUnreachableFile()
    {
        $sampleFileName = 'non-existing' . '.' . self::EXTERNAL_FILE_TYPE;
        
        $result = $this->_webApiCall(
            $this->makeServiceInfo(),
            $this->makeRequestData($this->getExternalLink($sampleFileName))
        );

        $this->assertEquals(SourceInterface::STATUS_FAILED, $result['status']);
        $this->assertRegExp('/does not exist./', $result['error']);
    }

    /**
     * Test reachable file
     */
    public function testReachableFile()
    {
        $sampleFileName = 'importservice-test-' . time() . '.' . self::EXTERNAL_FILE_TYPE;
        $sampleFileContent = 'ABCDEFGHabcdefgh0123456789';

        /** Create the test file that should be copied */
        $this->mediaWriter->writeFile(
            $this->mediaWriter->getAbsolutePath(self::TEMPORARY_DIR) . $sampleFileName,
            $sampleFileContent
        );

        /** Make the Api call */
        $result = $this->_webApiCall(
            $this->makeServiceInfo(),
            $this->makeRequestData($this->getExternalLink($sampleFileName))
        );

        /** Assert the response status and the source_id */
        $this->assertEquals(SourceInterface::STATUS_UPLOADED, $result['status']);
        $this->assertNotNull($result['uuid']);

        if (isset($result['uuid'])) {

            /** @var string $nameCopiedFile */
            $nameCopiedFile = $result['uuid'];

            /** @var string $pathCopiedFile */
            $pathCopiedFile = $this->getPathToCopiedFile($nameCopiedFile);

            /** Assert that the content of the copied file matches the original content */
            $this->assertEquals(
                strlen($sampleFileContent),
                strlen($this->readCopiedFile($pathCopiedFile))
            );

            /** Remove the file from the working directory */
            $this->removeCopiedFile($pathCopiedFile);
        }

        /** Remove tmp dir */
        $this->remoteMediaTmpDir();
    }

    /**
     * Test reachable file when sending UUID
     */
    public function testReachableFileWhenSendingUuid()
    {
        $sampleFileName = 'importservice-test-send-uuid-' . time() . '.' . self::EXTERNAL_FILE_TYPE;
        $sampleFileContent = 'ABCDEFGH-abcdefgh-0123456789-00000000';

        /** Create the test file that should be copied */
        $this->mediaWriter->writeFile(
            $this->mediaWriter->getAbsolutePath(self::TEMPORARY_DIR) . $sampleFileName,
            $sampleFileContent
        );

        /** @var string $desiredUuid */
        $desiredUuid = $this->generateId();

        /** Make the Api call */
        $result = $this->_webApiCall(
            $this->makeServiceInfo(),
            $this->makeRequestDataWithUuid(
                $this->getExternalLink($sampleFileName),
                $desiredUuid
            )
        );

        /** Assert the response status and the source_id */
        $this->assertEquals(SourceInterface::STATUS_UPLOADED, $result['status']);
        $this->assertEquals($desiredUuid, $result['uuid']);

        if (isset($result['uuid'])) {

            /** @var string $nameCopiedFile */
            $nameCopiedFile = $result['uuid'];

            /** Remove the file from the working directory */
            $this->removeCopiedFile($this->getPathToCopiedFile($nameCopiedFile));
        }

        /** Remove tmp dir */
        $this->remoteMediaTmpDir();
    }

    /**
     * Sets up the service info
     * @return array
     */
    private function makeServiceInfo()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => self::RESOURCE_PATH,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ],
            'soap' => [
                'service' => self::SERVICE_NAME,
                'serviceVersion' => self::SERVICE_VERSION,
                'operation' => self::SERVICE_NAME . 'Save',
            ],
        ];

        return $serviceInfo;
    }

    /**
     * Sets up the request array
     * @param string $import_data
     * @return array
     */
    private function makeRequestData($import_data)
    {
        return ['source' => [
                SourceInterface::SOURCE_TYPE => self::EXTERNAL_FILE_TYPE,
                SourceInterface::IMPORT_TYPE => self::IMPORT_TYPE,
                SourceInterface::IMPORT_DATA => $import_data
            ]
        ];
    }

        /**
     * Sets up the request array
     * @param string $import_data
     * @param string $uuid
     * @return array
     */
    private function makeRequestDataWithUuid($import_data, $uuid)
    {
        return ['source' => [
                SourceInterface::SOURCE_TYPE => self::EXTERNAL_FILE_TYPE,
                SourceInterface::IMPORT_TYPE => self::IMPORT_TYPE,
                SourceInterface::IMPORT_DATA => $import_data,
                SourceInterface::UUID => $uuid
            ]
        ];
    }

    /**
     * Gets the public link to the file to be copied
     * @param $sampleFileName
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getExternalLink($sampleFileName)
    {
        /** @var \Magento\Store\Model\StoreManagerInterface $storeManager */
        $storeManager = Bootstrap::getObjectManager()
            ->create(\Magento\Store\Model\StoreManagerInterface::class);

        return $storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
            . self::TEMPORARY_DIR
            . $sampleFileName;
    }

    /**
     * Gets a Write object
     * @param string $dir The directory to write to
     * @return \Magento\Framework\Filesystem\Directory\WriteInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getWriteInterface($dir)
    {
        $fileSystem = Bootstrap::getObjectManager()
            ->create(\Magento\Framework\Filesystem::class);

        return $fileSystem->getDirectoryWrite($dir);
    }

    /**
     * Generate UUID
     * @return string
     */
    private function generateId()
    {
        /** @var \Magento\Store\Model\StoreManagerInterface $identityGenerator */
        $identityGenerator = Bootstrap::getObjectManager()
            ->create(\Magento\Framework\DataObject\IdentityGeneratorInterface::class);

        return $identityGenerator->generateId();
    }

    /**
     * @param string $nameCopiedFile
     * @return string
     */
    private function getPathToCopiedFile($nameCopiedFile)
    {
        return $this->varWriter->getAbsolutePath(SourceProcessorPool::WORKING_DIR)
            . $nameCopiedFile
            . '.'
            . self::EXTERNAL_FILE_TYPE;
    }

    /**
     * @param string $pathCopiedFile
     * @return string
     */
    private function readCopiedFile($pathCopiedFile)
    {
        return $this->varWriter->readFile($pathCopiedFile);
    }

    /**
     * @param string $pathCopiedFile
     * @return bool
     */
    private function removeCopiedFile($pathCopiedFile)
    {
        return $this->varWriter->getDriver()->deleteFile($pathCopiedFile);
    }

    /**
     * Remove the temporary directory
     */
    private function remoteMediaTmpDir()
    {
        $this->mediaWriter->delete(
            $this->mediaWriter->getAbsolutePath(self::TEMPORARY_DIR)
        );
    }
}
