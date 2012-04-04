<?php
abstract class Orbislib_Abstract_ModelMapper 
{
        /**
     * @var Application_Model_DbTable_Entry
     */
    protected $_dbTable;

    public function __construct($dbTable = null)
    {
        if (!is_null($dbTable)) {
            $this->setDbTable($dbTable);
        }
    }
    
    /**
     * return default name db table for this mapper
     * @return string 
     */
    abstract public function getDbTableDefault();
            

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable)) {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract) {
            throw new UnexpectedValueException("Invalid table data gateway provided");
        }
        $this->_dbTable = $dbTable;

        return true;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable) {
            $this->setDbTable($this->getDbTableDefault());
        }
        return $this->_dbTable;
    }
    
    public function find($id, OrbisLib_Abstract_Model $entry)
    {
        $result = $this->getDbTable()->find($id);
        if (count($result) === 0) {
            return false;
        } else {
            $row = $result->current();
            $entry->setOptions($row->toArray());
            return $entry;
        }
    }
    
    public function prepareToSave(OrbisLib_Abstract_Model $entry)
    {
        return $entry->toArrayStr();
    }

    public function save(OrbisLib_Abstract_Model $entry)
    {
        $data =  $this->prepareToSave($entry);
        $id = $entry->id;
        if ($id === 0) {
            unset($data["id"]);
            return $this->getDbTable()->insert($data);
        } else {            
            unset($data["id"]);
            return $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }
    
    public function fetchAll($classOfEntrys)
    {
        if (!is_subclass_of($classOfEntrys, "OrbisLib_Abstract_Model")) {
            throw new UnexpectedValueException("Invalid class given");
        }
        $resultSet = $this->getDbTable()->fetchAll();
        $entries   = array();
        foreach ($resultSet as $row) {
            $entry = new $classOfEntrys();
            $entry->setOptions($row->toArray());
            $entries[] = $entry;
        }
        return $entries;
    }

    public function delete(OrbisLib_Abstract_Model $entry)
    {
        $where = $this->getDbTable()->getAdapter()->quoteInto("id = ?", $entry->id);
        return $this->getDbTable()->delete($where);
    }
    
}
