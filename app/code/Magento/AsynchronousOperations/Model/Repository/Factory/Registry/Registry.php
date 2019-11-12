<?php

namespace Magento\AsynchronousOperations\Model\Repository\Factory\Registry;

class Registry
{
    /** @var array */
    private $types;

    /**
     * Registry constructor.
     * @param array $types
     */
    public function __construct(
        $types = []
    ) {
        $this->types = $types;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param $entity
     * @return |null
     * @throws \ReflectionException
     */
    public function getDbRepository($entity)
    {
        /** @var string $classShortName */
        $classShortName = (new \ReflectionClass($entity))->getShortName();

        if(isset($this->types['db'][$classShortName])) {
            return $this->types['db'][$classShortName];
        };

        return null;
    }
}
