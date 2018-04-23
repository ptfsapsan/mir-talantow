<?php

namespace Application\Model\Repository;


final class RepositoryFactory
{
    /**
     * @param string $tableName
     * @return AbstractRepository
     */
    public static function factory(string $tableName)
    {
        $class = '\Application\Model\Repository\\' . ucfirst($tableName);
        if (class_exists($class)) return new $class();

        return new Orders();
    }
}