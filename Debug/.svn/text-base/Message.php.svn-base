<?php

class BTS_Debug_Message extends BTS_Object {
    
    const SEVERITY_INFO = 1;
    const SEVERITY_SUCCESS = 2;
    const SEVERITY_WARN = 3;
    const SEVERITY_ERROR = 4;
    
    protected $_fields = array(
        "message",
        "severity",
        "origin",
    );
    
    public function __construct() {
        $this->setOrigin(BTS_Base::getAppConfig()->bts->debug->origin);
        $this->setSeverity(self::SEVERITY_INFO);
        return $this;
    }
    
    public function setSeverity($value) {
        switch ($value) {
            case self::SEVERITY_INFO:
            case self::SEVERITY_SUCCESS:
            case self::SEVERITY_WARN:
            case self::SEVERITY_ERROR:
                $this->setData("severity", $value);
                break;
            default:
                throw new Exception("Unknown severity level");
        }
        return $this;
    }
    
}
