<?php


namespace Magento\AsynchronousOperations\Model\Repository;


class AbstractRepository
{
    /** @var \Magento\Framework\Model\AbstractModel */
    protected $entity;

    /**
     * @param \Magento\Framework\Model\AbstractModel
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }
}
