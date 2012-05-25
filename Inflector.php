<?php

class BTS_Inflector {
    
    private static $_instance;
    
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function getInflectorConfig() {
        $cache = false;
        if($cache === false) {
            $element = new Zend_Config_Xml(dirname(__FILE__) . "/Data/inflector.xml");
            return $element;
        }
        return $cache;
    }
    
    public function isUncountable($str) {
        if (isset($this->getInflectorConfig()->uncountable->$str)) {
            return true;
        }
        else {
            return false;
        }
    }
    public function isIrregular($str) {
        if ($this->getIrregular($str) != $str) {
            return true;
        }
        else {
            return false;
        }
    }
    public function getIrregular($str) {
        if (isset($this->getInflectorConfig()->irregular->$str)) {
            return $this->getInflectorConfig()->irregular->$str;
        }
        else {
            return $str;
        }
    }
    
    public function singularize($str) {
        // Remove garbage
        $str = strtolower(trim($str));

        if ($this->IsUncountable($str)) {
            return $str;
        }

        if ($this->isIrregular($str)) {
            $str = $this->getIrregular($str);
        }
        elseif (preg_match('/us$/', $str)) {
            // http://en.wikipedia.org/wiki/Plural_form_of_words_ending_in_-us
            // Already singular, do nothing
        }
        elseif (preg_match('/[sxz]es$/', $str) OR preg_match('/[^aeioudgkprt]hes$/', $str)) {
            // Remove "es"
            $str = substr($str, 0, -2);
        }
        elseif (preg_match('/[^aeiou]ies$/', $str)) {
            // Replace "ies" with "y"
            $str = substr($str, 0, -3).'y';
        }
        elseif (substr($str, -1) === 's' AND substr($str, -2) !== 'ss') {
            // Remove singular "s"
            $str = substr($str, 0, -1);
        }

        return $str;
    }
    
    public function pluralize($str, $count = null) {
        if (!is_null($count) && $count == 1) {
            return $str;
        }
        
        // Remove garbage
        $str = trim($str);

        // Check uppercase
        $is_uppercase = ctype_upper($str);

        if ($this->isUncountable($str)) {
            return $str;
        }
        
        if ($this->isIrregular($str)) {
            $str = $this->getIrregular($str);
        }
        elseif (preg_match('/[sxz]$/', $str) OR preg_match('/[^aeioudgkprt]h$/', $str)) {
            $str .= 'es';
        }
        elseif (preg_match('/[^aeiou]y$/', $str)) {
            // Change "y" to "ies"
            $str = substr_replace($str, 'ies', -1);
        }
        else {
            $str .= 's';
        }

        // Convert to uppsecase if nessasary
        if ($is_uppercase) {
            $str = strtoupper($str);
        }
        
        return $str;
    }
    
    public function wrapInPTags($text) {                                                                                                                                                        
                                                                                                                                                                                                
        $text = str_replace(array("<p>", "</p>"), array("", "<br />"), $text);                                                                                                                  
        $text = trim(str_replace(array("<br />", "<br>", "\r"), "\n", $text));                                                                                                                  
                                                                                                                                                                                                
        $lines = explode("\n", $text);                                                                                                                                                          
                                                                                                                                                                                                
        $finalText = "";
        foreach ($lines as $line) {
            if ($line == "") {
                continue;
            }
            
            $finalText .= "<p>" . $line . "</p>\n";
        }
        
        return $finalText;
    }
}
