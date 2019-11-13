<?php

namespace Magento\AsynchronousOperationsRedis\Model\Repository;

use Magento\AsynchronousOperationsRedis\EntityManager\EntityManager;
use Magento\AsynchronousOperations\Model\Repository\AbstractRepository;

class Entity extends AbstractRepository implements \Magento\AsynchronousOperations\Api\BulkSummaryRepositoryInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * Entity constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return \Magento\Framework\Model\AbstractModel $entity
     */
    public function save($entity)
    {
        return $this->entityManager->save($entity);
    }

    /**
     * @param int $bulkUuid
     * @return \Magento\Framework\Model\AbstractModel $entity
     */
    public function getByUuid($bulkUuid)
    {
        return $this->entityManager->load($this->entity, $bulkUuid);
    }
}
