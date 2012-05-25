<?php

abstract class BTS_SagePay_Transaction_Type_Abstract extends BTS_Object {
    protected $_transactionType = null;
    
    protected $_needsBillingAddress = false;
    protected $_needsShippingAddress = false;
    
    public function getTransactionType() {
        return $this->_transactionType;
    }
    
    public function needsBillingAddress() {
        return $this->_needsBillingAddress;
    }
    public function needsShippingAddress() {
        return $this->_needsShippingAddress;
    }
}