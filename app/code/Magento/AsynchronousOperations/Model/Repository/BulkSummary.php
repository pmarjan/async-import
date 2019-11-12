<?php


namespace Magento\AsynchronousOperations\Model\Repository;

use Magento\AsynchronousOperations\Model\ResourceModel\Bulk as BulkSummaryResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\AsynchronousOperations\Api\Data\BulkSummaryInterfaceFactory;

class BulkSummary implements \Magento\AsynchronousOperations\Api\BulkSummaryRepositoryInterface
{
    /** @var BulkSummaryResource */
    private $bulkSummaryResource;

    /** @var BulkSummaryInterfaceFactory */
    private $bulkSummaryFactory;

    /**
     * BulkSummary constructor.
     * @param BulkSummaryResource $bulkSummaryResource
     * @param BulkSummaryInterfaceFactory $bulkSummaryFactory
     */
    public function __construct(
        BulkSummaryResource $bulkSummaryResource,
        BulkSummaryInterfaceFactory $bulkSummaryFactory
    ) {
        $this->bulkSummaryResource = $bulkSummaryResource;
        $this->bulkSummaryFactory = $bulkSummaryFactory;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return \Magento\Framework\Model\AbstractModel
     * @throws CouldNotSaveException
     */
    public function save($entity)
    {
        try {
            $this->bulkSummaryResource->save($entity);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $entity;
    }

    /**
     * @param int $bulkUuid
     * @return \Magento\Framework\Model\AbstractModel $entity
     */
    public function getByUuid($bulkUuid)
    {
        /** @var \Magento\AsynchronousOperations\Api\Data\BulkSummaryInterface $bulkSummary */
        $bulkSummary = $this->bulkSummaryFactory->create();
        $this->bulkSummaryResource->load($bulkSummary, $bulkUuid, 'uuid');
        if (!$bulkSummary->getId()) {
            throw new NoSuchEntityException(__('The Bulk summary with the "%1" UUID doesn\'t exist.', $bulkUuid));
        }

        return $bulkSummary;
    }
}
