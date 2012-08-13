<?php

class BTS_Auth_Adapter_Http_Resolver_Config implements Zend_Auth_Adapter_Http_Resolver_Interface {
    
    private $_configFile = null;
    
    public function __construct($configFile = null) {
        if (!is_null($configFile)) {
            $this->setConfigFile($configFile);
        }
    }
    
    public function setConfigFile($configFile) {
        $this->_configFile = $configFile;
        return $this;
    }
    
    public function resolve($username, $realm = null) {
        $config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/" . $this->_configFile, $realm);
        if (isset($config->$username)) {
            return $config->$username;
        }
    }
    
}
