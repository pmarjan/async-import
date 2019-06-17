<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AsynchronousOperationsRedis\EntityManager;

use Magento\AsynchronousOperationsRedis\Api\RedisKeyInterface;

class Hydrator
{
    /** @var object */
    private $entity;

    /**
     * Extract data from object
     *
     * @param object $entity
     * @return array
     */
    public function extract()
    {
        return $this->entity->getData();
    }

    /**
     * Populate entity with data
     *
     * @param array $data
     * @return object
     */
    public function hydrate(array $data)
    {
        //@TODO Remove lock from array
        return $this->entity->setData($data);
    }

    /**
     * @param object $entity
     * @return $this
     * @TODO throws exception if entity is not of some expected kind ?
     */
    public function setEntity($entity)
    {
        $this->entity = $entity;
        return $this;
    }

    /**
     * @param bool $isCreate
     * @return $this
     */
    public function setMetaData($isCreate = false)
    {
        if ($isCreate) {
            $this->entity->setData(RedisKeyInterface::LOCK, 0);
            $this->entity->setData(RedisKeyInterface::META_CREATED, time());
        }

        $this->entity->setData(RedisKeyInterface::META_UPDATED, time());

        return $this;
    }
}
