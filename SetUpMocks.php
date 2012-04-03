<?php

class OrbisLib_SetUpMocks {

    protected $_testCase;

    protected $_mockObjects=array();

    protected $_adapter;

    public  function __construct(PHPUnit_Framework_TestCase $testCase)
    {
        $this->_testCase = $testCase;
    }

    public function  __destruct()
    {
        foreach($this->_mockObjects as $name=>$obj){
            $this->deleteMock($name);
        }
    }

    public function createMock($className="Zend_Db_Table_Abstract")
    {
        $mock = $this->_testCase->getMock($className);
        return $mock;
    }

    public function registerMock($mapperName, PHPUnit_Framework_MockObject_MockObject $mock)
    {
        OrbisLib_DataMapperFactory::setDbTable($mapperName, $mock);
        $this->_mockObjects[$mapperName] = $mock;
    }

    /**
     * @param string $mapperName
     * @param string $className
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function setMock($mapperName, $className)
    {
        if (!isset($this->_mockObjects[$mapperName])){
            $this->deleteMock($mapperName);
        }
        $this->registerMock($mapperName, $this->createMock($className));
        return $this->getMock($mapperName);
    }

    public function getMock($mapperName)
    {
        if (isset($this->_mockObjects[$mapperName])) {
            return $this->_mockObjects[$mapperName];
        } else {
            return null;
        }
    }

    public function deleteMock($mapperName)
    {
        OrbisLib_DataMapperFactory::deleteDbTable($mapperName);
        unset($this->_mockObjects[$mapperName]);
    }

    public function run()
    {
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            if (preg_match("~^(mock)(.)+$~",$method)) {
                $this->$method();
            }
        }
    }

    /**
     * @return PHPUnit_Framework_TestCase
     */
    public function getTestCase()
    {
        return $this->_testCase;
    }

    /**
     * @return Zend_Test_DbAdapter
     */
    public function getAdapter()
    {
        if (is_null($this->_adapter)){
            $this->_adapter = new Zend_Test_DbAdapter();
        }
        return $this->_adapter;
    }
}




