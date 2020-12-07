<?php


namespace Doctrine\DBAL\Driver\Interbase;


use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Driver\FetchUtils;
use Doctrine\DBAL\Driver\Interbase\Exception\StatementError;
use Doctrine\DBAL\Driver\Result as ResultInterface;

class Result implements ResultInterface
{
    protected $_resource;


    public function fetchBlobs($row) {
        $iColumn = 0;

        $result = [];
        foreach ($row as $key => $value) {
            $fieldInfo = ibase_field_info($this->_resource, $iColumn);
            if ($fieldInfo["type"] === "BLOB") {
                $blobData = ibase_blob_info( $this->_dbh, $value );
                $blobHandle = ibase_blob_open( $this->_dbh,  $value );
                $content   = ibase_blob_get( $blobHandle, $blobData["length"] );
                $result[$key] = $content;
            } else {
                $result[$key] = $value;
            }
            $iColumn++;
        }
        return $result;
    }

    /**
     * Result constructor.
     * @param $resource result of ibase_query
     * @param $dbh
     */
    public function __construct($resource, $dbh) {
        $this->_resource = $resource;
        $this->_dbh = $dbh;

    }

    /**
     * {@inheritDoc}
     */
    public function fetchNumeric()
    {
        $row = null;
        if (!empty($this->_resource) && $this->_resource !== 1) {
            $row = ibase_fetch_row($this->_resource);

            if ($row === false) {
               return false;
            }
            //see if we need to fetch a blob field
            $row = $this->fetchBlobs($row);

        }
        return $row;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAssociative()
    {
        $row = null;

        if (!empty($this->_resource) && $this->_resource !== 1) {
            $row = ibase_fetch_assoc($this->_resource);
            $error = ibase_errcode();
            if ($row === false && $error !== false) {
                throw StatementError::new($this->_resource);
            }
        }
        return $row;
    }

    /**
     * {@inheritDoc}
     */
    public function fetchOne()
    {
        return FetchUtils::fetchOne($this);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAllNumeric(): array
    {
        return FetchUtils::fetchAllNumeric($this);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchAllAssociative(): array
    {
        return FetchUtils::fetchAllAssociative($this);
    }

    /**
     * {@inheritDoc}
     */
    public function fetchFirstColumn(): array
    {
        return FetchUtils::fetchFirstColumn($this);
    }

    public function rowCount(): int
    {
        return ibase_affected_rows();
    }

    public function columnCount(): int
    {
        return ibase_num_fields($this->_resource);
    }

    public function free(): void
    {
        ibase_free_result($this->_resource);
    }
}