<?php


namespace Doctrine\DBAL\Platforms;

use Doctrine\DBAL\Event\SchemaCreateTableColumnEventArgs;
use Doctrine\DBAL\Event\SchemaCreateTableEventArgs;
use Doctrine\DBAL\Event\SchemaDropTableEventArgs;
use Doctrine\DBAL\Events;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\Keywords\InterbaseKeywords;
use Doctrine\DBAL\Schema\ColumnDiff;
use Doctrine\DBAL\Schema\Identifier;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\TableDiff;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use Doctrine\DBAL\Schema\Table;


class InterbasePlatform extends AbstractPlatform
{
    /**
     * {@inheritDoc}
     */
    function getDateTimeTypeDeclarationSQL(array $column)
    {
        return "TIMESTAMP";
    }

    public function getCurrentTimestampSQL()
    {
        return "'now'";
    }


     /**
     * {@inheritDoc}
     */
    public function getTimeTypeDeclarationSQL(array $column)
    {
        return "TIME";
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentTimeSQL()
    {
        return "'now'";
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTypeDeclarationSQL(array $column)
    {
        return "DATE";
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentDateSQL()
    {
        return "'now'";
    }

    /**
     * {@inheritDoc}
     */
    protected function getVarcharTypeDeclarationSQLSnippet($length, $fixed)
    {
        return $fixed ? ($length > 0 ? 'CHAR(' . $length . ')' : 'CHAR(255)')
            : ($length > 0 ? 'VARCHAR(' . $length . ')' : 'VARCHAR(255)');
    }

    protected function getReservedKeywordsClass()
    {
        return InterbaseKeywords::class;
    }

    public function getBooleanTypeDeclarationSQL(array $column)
    {
        return 'INTEGER';
    }

    public function getIntegerTypeDeclarationSQL(array $column)
    {
        return 'INTEGER';
    }

    public function getBigIntTypeDeclarationSQL(array $column)
    {
        return 'BIGINT';
    }

    public function getSmallIntTypeDeclarationSQL(array $column)
    {
        return 'SMALLINT';
    }

    protected function _getCommonIntegerTypeDeclarationSQL(array $column)
    {
        return 'INTEGER';
    }

    protected function initializeDoctrineTypeMappings()
    {
        // TODO: Implement initializeDoctrineTypeMappings() method.
    }

    public function getClobTypeDeclarationSQL(array $column)
    {
        return 'BLOB SUB_TYPE 0';
    }

    public function getBlobTypeDeclarationSQL(array $column)
    {
        return 'BLOB SUB_TYPE 0';
    }

    public function getName()
    {
        return "interbase";
    }

    public function getCurrentDatabaseExpression() : string
    {
        return "* FROM RDB\$DATABASE";
    }

    public function getDummySelectSQL(): string
    {
        return "SELECT 1 FROM RDB\$DATABASE";
    }

    public function getListTablesSQL()
    {
        return 'select RDB$RELATION_NAME 
                from RDB$RELATIONS
                where RDB$VIEW_BLR is null
                and (RDB$SYSTEM_FLAG is null or RDB$SYSTEM_FLAG = 0)';
    }

    public function getListTableColumnsSQL($table, $database = null)
    {
        return 'SELECT     r.RDB$FIELD_NAME AS "Field",
                           r.RDB$DESCRIPTION AS "Comment",
                           r.RDB$DEFAULT_VALUE AS "Default",
                           r.RDB$NULL_FLAG AS "Null",
                           f.RDB$FIELD_LENGTH AS field_length,
                           f.RDB$FIELD_PRECISION AS field_precision,
                           f.RDB$FIELD_SCALE AS field_scale,
                           CASE f.RDB$FIELD_TYPE
                              WHEN 261 THEN \'BLOB\'
                              WHEN 14 THEN \'CHAR\'
                              WHEN 40 THEN \'CSTRING\'
                              WHEN 11 THEN \'D_FLOAT\'
                              WHEN 27 THEN \'DOUBLE\'
                              WHEN 10 THEN \'FLOAT\'
                              WHEN 16 THEN \'INT64\'
                              WHEN 8 THEN \'INTEGER\'
                              WHEN 9 THEN \'QUAD\'
                              WHEN 7 THEN \'SMALLINT\'
                              WHEN 12 THEN \'DATE\'
                              WHEN 13 THEN \'TIME\'
                              WHEN 35 THEN \'TIMESTAMP\'
                              WHEN 37 THEN \'VARCHAR\'
                              ELSE \'UNKNOWN\'
                            END AS "Type",
                            f.RDB$FIELD_SUB_TYPE AS field_subtype,
                            coll.RDB$COLLATION_NAME AS "Collation",
                            cset.RDB$CHARACTER_SET_NAME AS "CharacterSet"
                       FROM RDB$RELATION_FIELDS r
                       LEFT JOIN RDB$FIELDS f ON r.RDB$FIELD_SOURCE = f.RDB$FIELD_NAME
                       LEFT JOIN RDB$COLLATIONS coll ON r.RDB$COLLATION_ID = coll.RDB$COLLATION_ID
                        AND f.RDB$CHARACTER_SET_ID = coll.RDB$CHARACTER_SET_ID
                       LEFT JOIN RDB$CHARACTER_SETS cset ON f.RDB$CHARACTER_SET_ID = cset.RDB$CHARACTER_SET_ID
                      WHERE r.RDB$RELATION_NAME = \'' . strtoupper($table) . '\'
                    ORDER BY r.RDB$FIELD_POSITION';
    }


    public function getCreateTableSQL(Table $table, $createFlags = self::CREATE_INDEXES)
    {
        if (! is_int($createFlags)) {
            throw new InvalidArgumentException(
                'Second argument of AbstractPlatform::getCreateTableSQL() has to be integer.'
            );
        }

        if (count($table->getColumns()) === 0) {
            throw Exception::noColumnsSpecifiedForTable($table->getName());
        }

        $tableName                    = $table->getQuotedName($this);
        $options                      = $table->getOptions();
        $options['uniqueConstraints'] = [];
        $options['indexes']           = [];
        $options['primary']           = [];



        if (($createFlags & self::CREATE_FOREIGNKEYS) > 0) {
            $options['foreignKeys'] = [];

            foreach ($table->getForeignKeys() as $fkConstraint) {
                $options['foreignKeys'][] = $fkConstraint;
            }
        }

        $columnSql = [];
        $columns   = [];

        foreach ($table->getColumns() as $column) {
            if (
                $this->_eventManager !== null
                && $this->_eventManager->hasListeners(Events::onSchemaCreateTableColumn)
            ) {
                $eventArgs = new SchemaCreateTableColumnEventArgs($column, $table, $this);

                $this->_eventManager->dispatchEvent(Events::onSchemaCreateTableColumn, $eventArgs);

                $columnSql = array_merge($columnSql, $eventArgs->getSql());

                if ($eventArgs->isDefaultPrevented()) {
                    continue;
                }
            }

            $name = $column->getQuotedName($this);

            $columnData = array_merge($column->toArray(), [
                'name' => $name,
                'version' => $column->hasPlatformOption('version') ? $column->getPlatformOption('version') : false,
                'comment' => $this->getColumnComment($column),
            ]);

            if ($columnData['type'] instanceof Types\StringType && $columnData['length'] === null) {
                $columnData['length'] = 255;
            }

            if (in_array($column->getName(), $options['primary'], true)) {
                $columnData['primary'] = true;
            }

            $columns[$name] = $columnData;
        }

        if ($this->_eventManager !== null && $this->_eventManager->hasListeners(Events::onSchemaCreateTable)) {
            $eventArgs = new SchemaCreateTableEventArgs($table, $columns, $options, $this);

            $this->_eventManager->dispatchEvent(Events::onSchemaCreateTable, $eventArgs);

            if ($eventArgs->isDefaultPrevented()) {
                return array_merge($eventArgs->getSql(), $columnSql);
            }
        }

        $sql = $this->_getCreateTableSQL($tableName, $columns, $options);



        if ($this->supportsCommentOnStatement()) {
            if ($table->hasOption('comment')) {
                $sql[] = $this->getCommentOnTableSQL($tableName, $table->getOption('comment'));
            }

            foreach ($table->getColumns() as $column) {
                $comment = $this->getColumnComment($column);

                if ($comment === null || $comment === '') {
                    continue;
                }

                $sql[] = $this->getCommentOnColumnSQL($tableName, $column->getQuotedName($this), $comment);
            }
        }

        if (($createFlags & self::CREATE_INDEXES) > 0) {
            $counter = 0;
            foreach ($table->getIndexes() as $id => $index) {
                $counter++;
                if (! $index->isPrimary()) {
                    $options['indexes'][$index->getQuotedName($this)] = $index;
                    $sql[] = "create index idx_{$table->getShortestName($this)}_{$counter} on {$tableName} (".implode(",", $index->getQuotedColumns($this))."})";


                    continue;
                }

                $primaryKey = implode(",", $index->getQuotedColumns($this));
                $options['primary']       = $index->getQuotedColumns($this);
                $options['primary_index'] = $index;
                $sql[] = "alter table {$tableName} add constraint pk_{$table->getShortestName($this)}_{$counter} primary key ({$primaryKey})";

                if (strpos($primaryKey, ",") === false) //only make a generator trigger for a single primary key
                {
                    $sql[] = "create generator gen_{$table->getShortestName($this)}_id";
                    $sql[] = "CREATE TRIGGER tr_{$table->getShortestName($this)}_id FOR {$table->getShortestName($this)}
                                ACTIVE BEFORE INSERT POSITION 0
                                AS
                                DECLARE VARIABLE tmp DECIMAL(18,0);
                                BEGIN
                                  IF (NEW.{$primaryKey} IS NULL) THEN
                                    NEW.{$primaryKey} = GEN_ID(gen_{$table->getShortestName($this)}_id, 1);
                                  ELSE
                                  BEGIN
                                    tmp = GEN_ID(gen_{$table->getShortestName($this)}_id, 0);
                                    if (tmp < new.{$primaryKey}) then
                                      tmp = GEN_ID(gen_{$table->getShortestName($this)}_id, new.{$primaryKey}-tmp);
                                  END
                                END";
                }
            }

            foreach ($table->getUniqueConstraints() as $uniqueConstraint) {
                $options['uniqueConstraints'][$uniqueConstraint->getQuotedName($this)] = $uniqueConstraint;
            }
        }

        return $sql;
    }

    function checkIfTableExists($tableName) {

    }

    function checkIfGeneratorExists($generatorName) {

    }

    public function getDropTableSQL($table)
    {
        $tableArg = $table;
        $sql = [];

        if ($table instanceof Table) {
            $table = $table->getQuotedName($this);
        }

        if (! is_string($table)) {
            throw new InvalidArgumentException(
                __METHOD__ . '() expects $table parameter to be string or ' . Table::class . '.'
            );
        }

        if ($this->_eventManager !== null && $this->_eventManager->hasListeners(Events::onSchemaDropTable)) {
            $eventArgs = new SchemaDropTableEventArgs($tableArg, $this);
            $this->_eventManager->dispatchEvent(Events::onSchemaDropTable, $eventArgs);

            if ($eventArgs->isDefaultPrevented()) {
                $sql = $eventArgs->getSql();

                if ($sql === null) {
                    throw new UnexpectedValueException('Default implementation of DROP TABLE was overridden with NULL');
                }

                return $sql;
            }
        }

        $sql[] = 'DROP TABLE ' . $table;
        $sql[] = 'DROP GENERATOR GEN_'.$table.'_ID';

        return $sql;
    }
}