<?php
abstract class Orbislib_Abstract_ModelMapper 
{
        /**
     * @var Zend_Db_Table_Abstract
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
            

    /**
     *
     * @param Zend_Db_Table_Abstract|string $dbTable
     * @return bool
     * @throws UnexpectedValueException 
     */
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

    /**
     *
     * @return Zend_Db_Table_Abstract
     */
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
        if ($id === 0 or (is_null($id))) {
            unset($data["id"]);
            $result = $this->getDbTable()->insert($data);
            $id = $this->getDbTable()->getAdapter()->lastInsertId($this->getDbTable()->info(Zend_Db_Table_Abstract::NAME));
            $entry->id = $id;
            return $result;
        } else {            
            unset($data["id"]);
            return $this->getDbTable()->update($data, array('id = ?' => $id));
        }
    }
    
    public function fetchAll($classOfEntrys, $where = null)
    {
        if (!is_subclass_of($classOfEntrys, "OrbisLib_Abstract_Model")) {
            throw new UnexpectedValueException("Invalid class given");
        }
        $resultSet = $this->getDbTable()->fetchAll($where);
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
    
    public function fetchAllPairs($classOfEntrys, $where = null)
    {
        $items = $this->fetchAll($classOfEntrys, $where);
        $array = array();
        foreach ($items as $item) {
            $array[$item->id] = $item->name;
        }
        return $array;
    }
    
    /**
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getAdapter()
    {
        return $this->getDbTable()->getAdapter();
    }
    
    public function getCount($where = null)
    {
        $select = $this->getAdapter()->select();
        $select->from(array($this->getDbTable()->info("name")),
                    array('count'=>"count(*)"));
        
        if (is_array($where)) {
            foreach ($where as $item) {
                $select->where($item);
            }
        } elseif (is_string($where)) {
            $select->where($where);
        } elseif (!is_null($where)) {
            throw new InvalidArgumentException ("bad where");
        }
        
        return (int) $select->query()->fetchColumn();
    }

    
}
