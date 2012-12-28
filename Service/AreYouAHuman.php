<?php

class BTS_Service_AreYouAHuman extends Zend_Service_Abstract {
    
    const ENDPOINT = "ws.areyouahuman.com";
    
    protected $_publisherKey;
    protected $_scoringKey;
    
    public function __construct($publisherKey = null, $scoringKey = null) {
        if ($publisherKey !== null) {
            $this->setPublisherKey($publisherKey);
        }
        
        if ($scoringKey !== null) {
            $this->setScoringKey($scoringKey);
        }
    }
    
    public function setPublisherKey($publisherKey) {
        $this->_publisherKey = $publisherKey;
        return $this;
    }
    
    public function getPublisherKey() {
        return $this->_publisherKey;
    }
    
    public function setScoringKey($scoringKey) {
        $this->_scoringKey = $scoringKey;
        return $this;
    }
    
    public function getScoringKey() {
        return $this->_scoringKey;
    }
    
    public function getHtml() {
        if ($this->_publisherKey === null) {
            throw new Zend_Service_Exception("Missing publisher key");
        }
        
        $wsUrl = '/ws/setruntimeoptions/' . $this->getPublisherKey();
        
    }
    
    public function __toString() {
        try {
            $return = $this->getHtml();
        } catch (Exception $e) {
            $return = '';
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        return $return;
    }
}
