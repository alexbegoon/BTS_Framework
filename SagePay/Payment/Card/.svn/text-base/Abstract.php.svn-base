<?php

abstract class BTS_SagePay_Payment_Card_Abstract extends BTS_Object {
    protected $_cardType = null;
    
    protected $_fields = array(
        'card_holder',
        'card_number',
        'expiry_date',
        'start_date',
        'issue_number',
        'cv_2',
    );
    
    protected $_protectFields = true;
    
    // this because the cv2 field needs to be cv_2 for sagepay's api, and __call() doesn't
    // camelize/underscore it right.
    public function getCv2() {
        return $this->getData("cv_2");
    }
    public function setCv2($cv2) {
        $this->setData("cv_2", $cv2);
        return $this;
    }
    
    
    public function getExpMonth() {
        return substr($this->getExpiryDate(), 0, 2);
    }
    public function setExpMonth($month) {
        $this->setExpiryDate(sprintf("%02d", $month) . $this->getExpYear());
        return $this;
    }
    public function getExpYear() {
        return "20" . substr($this->getExpiryDate(), 2);
    }
    public function setExpYear($year) {
        if (strlen($year) == 4) {
            $year = substr($year, 2);
        }
        $this->setExpiryDate($this->getExpMonth() . $year);
        return $this;
    }
    
    public function getStartMonth() {
        return substr($this->getStartDate(), 0, 2);
    }
    public function setStartMonth($month) {
        $this->setStartDate(sprintf("%02d", $month) . $this->getStartYear());
        return $this;
    }
    public function getStartYear() {
        return "20" . substr($this->getStartDate(), 2);
    }
    public function setStartYear($year) {
        if (strlen($year) == 4) {
            $year = substr($year, 2);
        }
        $this->setStartDate($this->getStartMonth() . $year);
        return $this;
    }
    
    public function getCardType() {
        return $this->_cardType;
    }
    public function toPrettyArray() {
        $data = parent::toPrettyArray();
        $data['CardType'] = $this->_cardType;
        
        $data['ApplyAVSCV2'] = BTS_SagePay::instance()->getConfig()->applyavscv2;
        $data['Apply3DSecure'] = BTS_SagePay::instance()->getConfig()->apply3dsecure;
        
        return $data;
    }
    
    public function __clone() {
        $this->setData("card_number", str_repeat("x", strlen($this->getCardNumber()) - 4) . substr($this->getCardNumber(), -4));
        $this->unsetData("cv_2");
    }
}
