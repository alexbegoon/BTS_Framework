<?php

class BTS_SagePay_Payment_Card_Testcard extends BTS_SagePay_Payment_Card_Abstract {
    protected $_cardType = "VISA";
    
    public function __construct() {
        $config = BTS_SagePay::instance()->getConfig();
        
        $this->setData(array(
            "card_holder" => $config->testcard->card_holder,
            "card_number" => $config->testcard->card_number,
            "expiry_date" => $config->testcard->exp_month . substr($config->testcard->exp_year, -2, 2),
            "cv_2" => $config->testcard->cv_2,
        ));
    }
}
