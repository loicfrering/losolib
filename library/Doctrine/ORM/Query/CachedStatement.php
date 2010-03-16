<?php

namespace Doctrine\ORM\Query;


class CachedStatement implements \Doctrine\DBAL\Driver\Statement
{
    private $_sqlResultSet;
    
    public function __construct(array $sqlResultSet)
    {
        $this->_sqlResultSet = $sqlResultSet;
    }
    
    /**
     * Fetches all rows from the result set.
     *
     * @return array
     */
    public function fetchAll($fetchStyle = null, $columnIndex = null, array $ctorArgs = null)
    {
        return $this->_resultSet;
    }
    
    public function fetchColumn($columnNumber = 0)
    {
        $row = current($this->_resultSet);
        return is_array($row) ? $row[$columnNumber] : false;
    }
    
    /**
     * Fetches the next row in the result set.
     */
    public function fetch($fetchStyle = null)
    {
        $current = current($this->_resultSet);
        next($this->_resultSet);
        return $current;
    }
    
    /**
     * Closes the cursor, enabling the statement to be executed again.
     *
     * @return boolean
     */
    public function closeCursor()
    {
        return true;
    }
    
    public function setResultSet(array $resultSet)
    {
        reset($resultSet);
        $this->_resultSet = $resultSet;
    }
    
    public function bindColumn($column, &$param, $type = null)
    {
    }

    public function bindValue($param, $value, $type = null)
    {
    }

    public function bindParam($column, &$variable, $type = null, $length = null, $driverOptions = array())
    {
    }
    
    public function columnCount()
    {
    }

    public function errorCode()
    {
    }
    
    public function errorInfo()
    {
        
    }
    
    public function execute($params = array())
    {
        return true;
    }
    
    public function rowCount()
    {
    } 
    
    
}
