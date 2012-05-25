<?php

abstract class BTS_SagePay_Payment_Abstract extends BTS_Object {
    protected $_type = null;
    
    // null, "card" or "token"
    protected $_paymentType = null;
    
    public function getType() {
        return $this->_type;
    }
    
    public function requiredPaymentType() {
        return $this->_paymentType;
    }
}