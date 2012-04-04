<?php
abstract class OrbisLib_Abstract_Model
{
    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function setOptions(array $options)
    {
        $dbVars = $this->_getDbVars();
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            } else {
                if (in_array($key, $dbVars)) {
                    $varName = "_" . $key;
                    $this->$varName = $value;
                } 
            }
        }
        return $this;
    }

    public function __set($name, $value)
    {
        $dbVars = $this->_getDbVars();
        $method = "set" . ucfirst($name);
        if (method_exists($this, $method)) {
            $this->$method($value);            
        } else {
            if (in_array($name, $dbVars)) {
                $varName = "_".$name;
                $this->$varName = $value;
            } else {
                throw new UnexpectedValueException("Invalid Entry set class property: \"$name\"");                
            }
        }
    }

    public function __get($name)
    {
        $dbVars = $this->_getDbVars();
        $method = "get" . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();            
        } else {
            if (in_array($name, $dbVars)) {
                $varName = "_".$name;
                return $this->$varName;
            } else {
                throw new UnexpectedValueException("Invalid Entry get class property: \"$name\"");                
            }
        }
    }
    
    protected function _setValue() {
        
    }
    
    protected function _getValue($name) {
        $dbVars = $this->_getDbVars();  
        $method = "get" . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->$method();
        } else {
            if (in_array($name, $dbVars)) {
                $varName = "_" . $name;
                return $this->$varName;
            } else {
                return new UnexpectedValueException("Invalid Entry get class property: \"$name\"");
            }
        }                        
    }


    public function __call($name, $arguments)
    {
        $dbVars = $this->_getDbVars();        
        if (strpos($name, "get")!==false) {
            $name = lcfirst(substr($name, 3));
            $varData = $this->_getValue($name);
            if ($varData instanceof Exception){
                throw new UnexpectedValueException("Invalid Entry get class method: \"$name\"");
            }
            return $varData;
        } elseif (strpos($name, "set")!==false) {
            $name = lcfirst(substr($name, 3));
            $method = "set" . ucfirst($name);
            $value = $arguments[0];
            if (method_exists($this, $method)) {
                $this->$method($value);
            } else {
                if (in_array($name, $dbVars)) {
                    $varName = "_" . $name;
                    $this->$varName = $value;
                } else {
                    throw new UnexpectedValueException("Invalid Entry set class method: \"$name\"");
                }
            }
        } else {
            throw new UnexpectedValueException("Invalid Entry class method: \"$name\"");            
        }
    }
    
    public function getClass()
    {
        return get_class($this);
    }
    
    /**
     * return array with names vars associeted with database
     * @return array
     */
    protected function _getDbVars() {
        $vars = array_keys(get_class_vars($this->getClass()));
        
        foreach ($vars as $key=>$value){
            if (substr($value, 0, 1)=="_") {
                $vars[$key] = substr($value, 1);
            } else {
                unset($vars[$key]);
            }
        }
        return $vars;
    }

    public function toArray()
    {
        $vars=get_class_vars($this->getClass());
        $data=array();
        foreach ($vars as $key=>$value) {
            $name = substr($key,1);
            $varData = $this->_getValue($name);
            if (!($varData instanceof Exception)) {
                $data[$name] = $varData;
            }
        }
        return $data;
    }
    
    public function toArrayStr() {
        $vars = $this->toArray();
        foreach ($vars as $name=>$value) {
            if (is_object($value)) {
                $method = "get" . ucfirst($name)."ToString";
                if (method_exists($this, "toString")){
                    $vars[$name] = $this->$method;                                       
                } elseif (method_exists($value, "toString")) {                
                     $vars[$name] = $value->toString();                    
                } else {
                    $vars[$name] = "Object ".get_class($value);                    
                }                
            }
        }
        return $vars;
    }
}
