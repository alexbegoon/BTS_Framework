<?php

class BTS_Debug {
    public static function log($msg) {
        if (!is_string($msg) && !is_numeric($msg)) {
            $msg = print_r($msg, true);
        }
        
        echo date("H:i:s d/m/Y") . ": " . $msg . PHP_EOL;
    }
}