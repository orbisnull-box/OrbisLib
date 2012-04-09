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
        return $this->getPublicDir()."/".$this->getPath();
    }
    
    public function getFullPath($path = null)
    {
        if (is_null($path)) {
            $path = $this->getPath();
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
        $path = $localPath;
        $localPath = $this->getFullPath($localPath);
        if (!copy($remotePath, $localPath)) {
            throw  new Exception ("failed copy $remotePath to $localPath");
        }
        $this->setPath($path);
        unlink($remotePath);        
    }
    
    public function toString()
    {
        return (string) $this->getPath();
    }
    
    public function generateName($currName)
    {
        $ext = pathinfo($currName, PATHINFO_EXTENSION);
        mt_srand();
        $random = mt_rand();
        return md5($currName.date("YmdHis").$random.__FILE__).".".$ext;
    }
    
    public function delete()
    {
        if (is_null($this->getPath())) {
            throw new Exception ("file not set");
        } else {
            if (file_exists($this->getFullPath())) {
                return unlink($this->getFullPath());
            }
        }
        $this->setPath(null);
        return true;
    }
    
}