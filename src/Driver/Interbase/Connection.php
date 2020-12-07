<?php


namespace Doctrine\DBAL\Driver\Interbase;


use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Driver\Interbase\Exception\DatabaseConnection;
use Doctrine\DBAL\Driver\Interbase\Exception\StatementError;
use Doctrine\DBAL\Driver\Result;
use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Driver\Statement as DriverStatement;


class Connection implements ServerInfoAwareConnection
{

    private $autoCommit = true;
    private $VERSION_SQL = "SELECT rdb\$get_context('SYSTEM', 'ENGINE_VERSION') as version from rdb\$database";

    /**
     * @var Interbase Database Handle
     */
    private $_dbh;

    /**
     * Connection constructor.
     * @param $host
     * @param $databasePath
     * @param $username
     * @param $password
     * @param $port
     * @throws StatementError
     */
    public function __construct($host, $databasePath, $username, $password, $port)
    {
        $this->_dbh = @ibase_pconnect($host . "/" . $port . ":" . $databasePath, $username, $password);

        if ($this->_dbh === false)
        {
            throw StatementError::new($this->_dbh);
        }
    }

    public function prepare(string $sql): DriverStatement
    {
        return new Statement($this->_dbh, $sql);
    }

    public function query(string $sql): Result
    {
        $result = $this->prepare($sql)->execute();
        return $result;
    }

    public function quote($value, $type = ParameterType::STRING)
    {
         return "'" . str_replace("'", "''", $value)."'";
    }

    public function exec(string $sql): int
    {

        $resource = ibase_prepare($sql);

        $result = @ibase_execute($resource);
        $error = ibase_errcode();
        if ($result === true || $error === false) {
            $affectedRows = ibase_affected_rows($this->_dbh);
            if ($this->autoCommit) {
                $this->commit();
            }
            return $affectedRows;
        } else {
            throw StatementError::new($this->_dbh);
        }

        return 0;
    }

    public function lastInsertId($name = null)
    {
        // TODO: Implement lastInsertId() method.
    }

    public function beginTransaction($args = IBASE_COMMITTED)
    {
        $this->autoCommit = false;
        return ibase_trans($args, $this->_dbh);
    }

    public function commit($transactionId = null): ?bool
    {
        $this->autoCommit = true;
        if (!empty($transactionId)) {
            return ibase_commit_ret($transactionId);
        } else {
            return ibase_commit_ret($this->_dbh);
        }
    }

    public function rollBack($transactionId = null): ?bool
    {
        if (!empty($transactionId)) {
            return ibase_rollback($transactionId);
        } else {
            return ibase_rollback($this->_dbh);
        }
    }

    public function getServerVersion()
    {
       $version = $this->query($this->VERSION_SQL);
    }


    public function close() {
        ibase_close($this->_dbh);
    }



}