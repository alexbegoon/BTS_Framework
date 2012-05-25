<?php

class BTS_SagePay_Transaction_Type_Payment extends BTS_SagePay_Transaction_Type_Abstract {
    protected $_transactionType = "PAYMENT";
    
    protected $_needsBillingAddress = BTS_SagePay_Address::ADDRESS_REQUIRED;
    protected $_needsShippingAddress = BTS_SagePay_Address::ADDRESS_OPTIONAL;
    
    protected $_paymentType = "card";
    
    /**
     * @var BTS_SagePay_Payment_Card_Abstract
     */
    private $_card;
    
    public function setPaymentData(BTS_SagePay_Payment_Card_Abstract $card) {
        $this->_card = $card;
        return $this;
    }
    
    protected function _preparePrettyArray() {
        $this->setData($this->_card->getData());
        $this->setData("card_type", $this->_card->getCardType());
        return $this;
    }
}
