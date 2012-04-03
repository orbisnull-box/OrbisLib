<?php
require_once ("Zend/Loader.php");
/**
 * plugin, singleton, abstact fabric
 */
class OrbisLib_DataMapperFactory
{
    protected static $_instance;

    protected $_dbTableRegistry=array();

    private function __construct() { /* ... */ }

    private function __clone() { /* ... */ }

    private function __wakeup() { /* ... */ }

    /**
     * @return OrbisLIb_DataMapperFactory
     */
    public static function getInstance() {
        if (is_null(self::$_instance) ) {
            self::$_instance = new OrbisLib_DataMapperFactory;
        }
        return self::$_instance;
    }

    /**
     *
     * @param string $mapperClass
     * @param Zend_Db_Table_Abstract|string $dbTable
     * @return boolean 
     */
    public static function setDbTable($mapperClass, $dbTable)
    {
        $instance = self::getInstance();
        if ($dbTable instanceof Zend_Db_Table_Abstract) {
            $instance->_dbTableRegistry[$mapperClass] = $dbTable;
            return true;
        } else {
            try {
                Zend_Loader::loadClass($dbTable);
                $instance->_dbTableRegistry[$mapperClass] = new $dbTable;
                return true;
            } catch (Exception $e) {
                return false;
            }
        }
    }

    /**
     *
     * @param string $mapperClass
     * @return Zend_Db_Table_Abstract 
     */
    public static function getDbTable($mapperClass)
    {
        $instance = self::getInstance();
        if (isSet($instance->_dbTableRegistry[$mapperClass])) {
            return $instance->_dbTableRegistry[$mapperClass];
        } else {
            return null;
        }
    }

    /**
     *
     * @param string $mapperClass
     * @return boolean 
     */
    public static function deleteDbTable($mapperClass)
    {
        $instance = self::getInstance();
        if (isSet($instance->_dbTableRegistry[$mapperClass])) {
            unset($instance->_dbTableRegistry[$mapperClass]);
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @param string $mapperClass
     * @return object
     */
    public static function create($mapperClass)
    {
        $instance = self::getInstance();
        $autoLoader = Zend_Loader_Autoloader::getInstance();
        if (class_exists($mapperClass) or false!==$autoLoader->autoload($mapperClass)) {
            $mapper = new $mapperClass($instance->getDbTable($mapperClass));
        } else {
            $mapper = null;
        }
        return $mapper;
    }

}


