<?php
abstract class OrbisLib_Abstract_File
{
    /**
     * patch to file from db
     * @var string 
     */
    protected $_path;

    /**
     * return full filesystem path to files 
     * @return string 
     */
    abstract public function getLocalDir();
    
    /**
     * return full web path to files 
     * @return string 
     */
    abstract public function getPublicDir();
    
    public function __construct($path = NULL)
    {
        if (!is_null($path)) {
            $this->setPath($path);
        }
    }

    public function getUri()
    {
        return $this->getPublicDir()."/".$this->_path;
    }
    
    public function getFullPath($path = null)
    {
        if (is_null($path)) {
            return $path = $this->_path;
        }
        return $this->getLocalDir()."/".$path;
    }
    
    public function setPath($path)
    {
        $file = $this->getFullPath($path);
        if (!file_exists($file)) {
            throw new Exception ("file not found: $file");
        }
        $this->_path = $path; 
        return $this;
    }

    public function getPath()
    {
        return $this->_path;
    }

    public function receive($remotePath, $localPath) 
    {   
        $localPath = $this->getFullPath($localPath);
        if (!copy($remotePath, $localPath)) {
            throw  new Exception ("failed copy $remotePath to $localPath");
        }
        unlink($remotePath);        
    }
    
    public function toString()
    {
        return (string) $this->_path;
    }
    
}