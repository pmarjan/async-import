<?php


namespace Magento\AsynchronousOperations\Api;


interface EntityRepositoryInterface
{
    /**
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return \Magento\Framework\Model\AbstractModel $entity
     */
    public function save($entity);

    /**
     * @param int $bulkUuid
     * @return \Magento\Framework\Model\AbstractModel $entity
     */
    public function getByUuid($bulkUuid);

    /**
     * @param \Magento\Framework\Model\AbstractModel $entity
     * @return boolean
     */
    //public function delete($entity);

    /**
     * @param int $entityId
     * @return boolean
     */
    //public function deleteById($entityId);
}
