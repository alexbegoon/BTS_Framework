<?php

class BTS_SagePay_Transaction_Type_Repeat extends BTS_SagePay_Transaction_Type_Abstract {
    protected $_transactionType = "REPEAT";
    
    protected $_needsBillingAddress = BTS_SagePay_Address::ADDRESS_NOTREQUIRED;
    protected $_needsShippingAddress = BTS_SagePay_Address::ADDRESS_NOTREQUIRED;
    
    /**
     * @var BTS_SagePay_Model_Subscription
     */
    private $_subscription;
    
    public function setSubscriptionData(BTS_SagePay_Model_Subscription $subscription) {
        $this->_subscription = $subscription;
        return $this;
    }
    
    protected function _preparePrettyArray() {
        foreach ($this->_subscription->getData() as $key => $value) {
            if ($key == "id") { continue; }
            elseif ($key == "vpstxid") { $key = "vps_tx_id"; }
            elseif ($key == "txauthno") { $key = "tx_auth_no"; }
            elseif ($key == "txcode") { $key = "vendor_tx_code"; }
            
            $this->setData("related_" . $key, $value);
        }
        return $this;
    }
    
}
