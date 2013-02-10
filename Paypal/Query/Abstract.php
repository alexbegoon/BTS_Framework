<?php

abstract class BTS_Paypal_Query_Abstract extends BTS_Object {
    
    protected $_endpoint = "https://api-3t.paypal.com/nvp";
    protected $_version = "60.0";
    
    protected $_response;
    
    public function getConfig() {
        return new Zend_Config_Ini(APPLICATION_PATH . "/configs/paypal.ini", APPLICATION_ENV);
    }
    
    protected function _preExecute() {}
        
    public function execute() {
        $this->_preExecute();
        
        $nvp = array();
        
        $nvp['VERSION'] = $this->_version;
        
        $nvp['USER'] = $this->getConfig()->api->username;
        $nvp['PWD'] = $this->getConfig()->api->password;
        $nvp['SIGNATURE'] = $this->getConfig()->api->signature;
        
        foreach ($this->getData() as $key => $value) {
            $nvp[strtoupper($key)] = $value;
        }
        
        $nvpStr = http_build_query($nvp);
        $client = new Zend_Http_Client($this->getConfig()->api->endpoint);
        $client->setMethod(Zend_Http_Client::POST);
        $client->setRawData($nvpStr);
        
        $request = $client->request();
        
        parse_str($request->getBody(), $this->_response);
        
        $this->_postExecute();
        
        return $this;
    }
    
    protected function _postExecute() {}
    
    public function getResponse() {
        return $this->_response;
    }
    
}
