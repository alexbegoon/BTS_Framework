<?php

class BTS_Paypal_Query_TransactionDetails extends BTS_Paypal_Query_Abstract {
    
    protected function _preExecute() {
        $this->setData("method", "GetTransactionDetails");
    }
    
}
