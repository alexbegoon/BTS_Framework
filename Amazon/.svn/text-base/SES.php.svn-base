<?php

class BTS_Amazon_SES extends BTS_Object {
    
    static $_instance;
    
    private $_cacheData = array();
    
    /**
     * @return BTS_Amazon_SES
     */
    static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function getSendQuota() {
        if (!isset($this->_cacheData["GetSendQuota"])) {
            $request = new BTS_Amazon_SES_Request("GetSendQuota");
            $response = $request->getResponse();
            $this->_cacheData["GetSendQuota"] = $response;
        }
        return $this->_cacheData["GetSendQuota"];
    }
    
    /**
     * Get the number of emails allowed to be sent per second
     * 
     * @return int 
     */
    public function getSendRateLimit() {
        return sprintf("%d", $this->getSendQuota()->getMaxSendRate());
    }
    
    public function get24hrSentCount() {
        return sprintf("%d", $this->getSendQuota()->getSentLast24Hours());
    }
    
    public function getSendLimit() {
        return sprintf("%d", $this->getSendQuota()->getMax24HourSend());
    }
    
}
