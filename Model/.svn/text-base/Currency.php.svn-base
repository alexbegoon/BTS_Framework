<?php

class BTS_Model_Currency extends BTS_Model {
    protected $_table = "currencies";
    
    public function formatForDisplay($amount) {
        if (is_null($this->getSymbol())) {
            return sprintf("%0.2f " . $this->getCode(), $amount);
        }
        else {
            return sprintf($this->getSymbol() . "%0.2f", $amount);
        }
    }
}
