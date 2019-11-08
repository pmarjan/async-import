<?php

namespace Magento\AsynchronousOperationsRedis\KeyManager\Key\Identifier;

interface GeneratorInterface
{
    /**
     * @param $entity
     * @param $entityConfig
     * @return mixed
     */
    public function generateId($entity, $entityConfig);
}
