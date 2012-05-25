<?php

class BTS_Currency {
    
    /**
     * @param float $amount Amount to convert
     * @param string $to Target currency
     * @param string $from Source currency (optional - defaults to GBP)
     */
    public static function convert($amount, $to, $from = "GBP") {
        $to = strtoupper($to);
        $from = strtoupper($from);
        
        if ($to == $from) {
            return sprintf("%0.2f", $amount);
        }
        
        $toCurrency = new BTS_Model_Currency($to, "code");
        $fromCurrency = new BTS_Model_Currency($from, "code");
        
        // convert to database's base currency
        $amount1 = $amount * $fromCurrency->getBack();
        
        // convert to target currency from database currency
        $amount2 = $amount1 * $toCurrency->getValue();
        
        return sprintf("%0.2f", $amount2);
    }
    
}
