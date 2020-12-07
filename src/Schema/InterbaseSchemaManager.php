<?php


namespace Doctrine\DBAL\Schema;


use Doctrine\DBAL\Exception;

/**
 * Important to note, Firebird does not work with Schemas but works rather like SQLite
 * Class InterbaseSchemaManager
 * @package Doctrine\DBAL\Schema
 */
class InterbaseSchemaManager extends AbstractSchemaManager
{

    /**
     * Gets the name of the table
     * @param mixed[] $table
     * @return Column|mixed
     */
    protected function _getPortableTableColumnDefinition($table)
    {
        return $table['name'];
    }
}