<?php

class BTS_SagePay_Transaction_Request extends BTS_Object {

    protected $_fields = array(
        "vps_protocol",
        "tx_type",
        "vendor",
        "vendor_tx_code",
        "currency",
        "account_type",
        "amount",
        "description",
    );
    protected $_protectFields = true;

    protected $_billingAddress = null;
    protected $_shippingAddress = null;
    protected $_cardDetails = null;
    protected $_caSubscription = null;
    
    protected $_transactionType = null;
    
    protected $_origData = array();
    
    public function setTransactionType(BTS_SagePay_Transaction_Type_Abstract $type) {
        $this->_transactionType = $type;
        return $this;
    }
    
    public function addAddress(BTS_SagePay_Address_Abstract $address) {
        if ($address->getType() == "billing") {
            $this->_billingAddress = $address;
        }
        else if ($address->getType() == "shipping") {
            $this->_shippingAddress = $address;
        }
        return $this;
    }
    
    public function run() {
        if (is_null($this->_transactionType)) {
            throw new Exception("Transaction Type not set");
        }
        
        $this->setData("tx_type", $this->_transactionType->getTransactionType());
        
        if ($this->_transactionType->getTransactionType() != "3DCALLBACK") {
            $this->setData(array(
                "vendor_tx_code" => $this->_generateVendorTxCode(),
                "vps_protocol" => BTS_SagePay::VPS_PROTOCOL,
                "vendor" => BTS_SagePay::instance()->getConfig()->vendorname,
                "account_type" => BTS_SagePay::instance()->getConfig()->account_type,
                "currency" => BTS_SagePay::instance()->getConfig()->default_currency,
                "apply_avs_cv2" => BTS_SagePay::instance()->getConfig()->applyavscv2,
                "apply_3d_secure" => BTS_SagePay::instance()->getConfig()->apply3dsecure,
            ));
        }

        $data = array();
        foreach ($this->getData() as $key => $value) {
            $data[BTS_Base::capitalize($key)] = $value;
        }
        
        if ($addressRequirement = $this->_transactionType->needsBillingAddress()) {
            if (($addressRequirement & BTS_SagePay_Address::ADDRESS_REQUIRED) && is_null($this->_billingAddress)) {
                throw new Exception("Billing Address is required for this transaction");
            }
            if (!is_null($this->_billingAddress)) {
                $data = array_merge($data, $this->_billingAddress->toPrettyArray());
                $data['CustomerName'] = $data['BillingFirstnames'] . " " . $data['BillingSurname'];
            }
        }
        
        if ($addressRequirement = $this->_transactionType->needsShippingAddress()) {
            if ($addressRequirement & BTS_SagePay_Address::ADDRESS_REQUIRED && is_null($this->_shippingAddress)) {
                throw new Exception("Shipping Address is required for this transaction");
            }
            if (!is_null($this->_shippingAddress)) {
                $data = array_merge($data, $this->_shippingAddress->toPrettyArray());
            }
        }
        
        $data = array_merge($data, $this->_transactionType->toPrettyArray());
        $this->_origData = $data;
        
        $response = BTS_SagePay::instance()->fetchResponse($data);
        
        return $response;
    }
    
    protected function _generateVendorTxCode() {
        $vendorname = BTS_SagePay::instance()->getConfig()->vendorname;
        $strTimeStamp = time();
        $intRandNum = rand(0,32000)*rand(0,32000);
        
        return $vendorname . "-" . $strTimeStamp . "-" . $intRandNum;
    }
}
