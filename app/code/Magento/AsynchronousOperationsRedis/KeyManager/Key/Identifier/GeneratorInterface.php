<?php

namespace Magento\AsynchronousOperationsRedis\KeyManager\Key\Identifier;

interface GeneratorInterface
{
    /**
     * @param $entity
     * @param $identifier
     * @return mixed
     */
    public function generateId($entity, $identifier);
}
