<?php

class BTS_Session {
    
    protected static $_instance;
    protected $_session;
    
    public static function instance($ns = "BTS") {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($ns);
        }
        return self::$_instance;
    }
    
    public function __construct($ns) {
        $this->_session = new Zend_Session_Namespace($ns);
    }
    
    public function __get($name) {
        return $this->_session->$name;
    }
    public function __set($name, $value) {
        $this->_session->$name = $value;
    }
    
}
