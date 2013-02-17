<?php

class BTS_Paypal {
    
    public static function getConfig() {
        return new Zend_Config_Ini(APPLICATION_PATH . "/configs/paypal.ini", APPLICATION_ENV);
    }
    
}
