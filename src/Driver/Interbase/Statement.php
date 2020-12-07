<?php


namespace Doctrine\DBAL\Driver\Interbase;


use Doctrine\DBAL\Driver\Interbase\Exception\StatementError;
use Doctrine\DBAL\Driver\Statement as StatementInterface;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Driver\Result as ResultInterface;


use interbase;

class Statement implements StatementInterface
{
    protected $REGEX_PARAMS= '/(:\w+)/m'; //any params with :prefix

    protected $_dbh;
    protected $_preparedQuery;
    protected $_params;
    protected $_lastSQL;

    /**
     * Statement constructor.
     * Firebird supports ? params not by name, so going to make it able to support by name and value bindings in the form of :
     * @param $dbh
     * @param $sql
     * @throws StatementError
     */
    public function __construct($dbh, $sql)
    {
        $this->_dbh = $dbh;
        $this->_lastSQL = $sql;

        preg_match_all($this->REGEX_PARAMS, $sql, $params, PREG_SET_ORDER, 0);

        $this->_params = [];
        foreach ($params as $param) {
            $sql = str_replace($param[0], "?", $sql);
        }

        $this->_preparedQuery = $sql;
        $this->_params[] = $this->_dbh;
        $this->_params[] = $this->_preparedQuery;

        foreach ($params as $param) {
            $this->_params[] = ["name" => $param[0], "value" => null];
        }

        if (!$this->_preparedQuery) {
            throw StatementError::new ($this->_dbh);
        }
    }

    public function getParamIndex($paramName) : int
    {
        if (is_int($paramName) && $paramName > 0) {
            return $paramName+1;
        } else {
            throw StatementError::error("Param less than 1");
        }

        foreach ($this->_params as $index => $value) {
            if (strtolower($value["name"]) === strtolower($paramName))
            {
                return $index;
            }
        }

        return -1;
    }

    public function bindValue($param, $value, $type = ParameterType::STRING)
    {
        $paramIndex = $this->getParamIndex($param);
        if ($paramIndex !== -1) {
            $this->_params[$paramIndex]["value"] = $value;
        }
    }

    public function bindParam($param, &$variable, $type = ParameterType::STRING, $length = null)
    {
        $paramIndex = $this->getParamIndex($param);
        if ($paramIndex !== -1) {
            $this->_params[$paramIndex]["value"] = $variable;
        }
    }

    private function getParams(): array
    {
        $queryParams = [];
        foreach ($this->_params as $id => $param) {
            if (is_array($param) && isset($param["value"])) {
                $queryParams[] = $param["value"];
            } else {
                $queryParams[] = $param;
            }
        }
        return $queryParams;
    }

    public function addBlobs($params) {
        foreach ($params as $id => $param) {
            if ($id === 0) continue; //Skip the query
            if (!is_resource($param)) continue;
            $data = stream_get_contents($param);
            if ($data !== false) {
                $blobHandle = ibase_blob_create($this->_dbh);
                ibase_blob_add($blobHandle, $data);
                $blobId = ibase_blob_close($blobHandle);
                $params[$id] = $blobId;
            }
        }
        return $params;
    }

    public function execute($params = null): ResultInterface
    {
        if (empty($params)) {
            $params = $this->getParams();
        } else {
            $params = array_merge($this->_params, $params);
        }


        $params = $this->addBlobs($params);

        $result = @call_user_func_array('ibase_query', $params);
        $error = ibase_errcode();

        if ($result !== false || $error === false) {
            return new Result($result, $this->_dbh);
        } else {
            throw StatementError::new($this->_dbh);
        }
    }
}