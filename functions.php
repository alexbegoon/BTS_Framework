<?php

/**
 * Miscellaneous procedural functions 
 * 
 * @package BTS
 */

/**
 * Replacement for the get_called_class method that was introduced in PHP 5.3.0.
 * 
 * Apparently, according to the author of the code below, it breaks if you use
 * this and call get_called_class() twice on the same line. 
 */
if (!function_exists('get_called_class')):
    function get_called_class() {
        $bt = debug_backtrace();
        $l = count($bt) - 1;
        $matches = array();
        
        while(empty($matches) && $l > -1){
            $lines = file($bt[$l]['file']);
            $callerLine = $lines[$bt[$l]['line']-1];
            preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l--]['function'].'/',
            $callerLine,
            $matches);
        }
        
        if (!isset($matches[1])) $matches[1] = NULL; //for notices
        
        if ($matches[1] == 'self') {
            $line = $bt[$l]['line']-1;
            while ($line > 0 && strpos($lines[$line], 'class') === false) {
                $line--;
            }
            preg_match('/class[\s]+(.+?)[\s]+/si', $lines[$line], $matches);
        }
        return $matches[1];
    }
endif;
