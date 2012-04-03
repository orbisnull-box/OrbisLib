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
        foreach ($options as $key => $value) {
            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function __set($name, $value)
    {
        $method = "set" . ucfirst($name);
        if (!method_exists($this, $method)) {
            throw new UnexpectedValueException("Invalid Entry set class property: \"$name\"");
        }
        $this->$method($value);
    }

    public function __get($name)
    {
        $method = "get" . ucfirst($name);
        if (!method_exists($this, $method)) {
            throw new UnexpectedValueException("Invalid Entry get class property: \"$name\"");
        }
        return $this->$method();
    }
    
    public function toArray()
    {
        $vars=get_class_vars(__CLASS__);
        $data=array();
        foreach ($vars as $key=>$value) {
            $name = substr($key,1);
            $method = "get" . ucfirst($name);
            if (method_exists($this, $method)) {
                $data[$name] = $this->$method();
            }
        }
        return $data;
    }


}
