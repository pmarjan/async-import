<?php

namespace Magento\AsynchronousOperationsRedis\KeyManager\Key\Identifier;

interface GeneratorInterface
{
    /**
     * @param $entity
     * @return mixed
     */
    public function generateId($entity);
}
