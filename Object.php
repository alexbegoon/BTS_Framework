<?php

class BTS_Object {
    /*
     * Data array
     */
    protected $_data = array();
    
    /*
     * Array of fields to use to define what keys can and can't be used in the object
     */
    protected $_fields = array();
    
    /*
     * Set to true to only allow data set into fields which have been pre-specified
     */
    protected $_protectData = false;
    
    /*
     * A string prefix to use for the data keys in toArray()
     */
    protected $_fieldPrefix = null;

    public function __call($name, $arguments) {
        $matches = array();
        
        preg_match("/^(set|get|unset|has|incr|decr)([A-Z]?.*)$/", $name, $matches);
        
        if (!empty($matches)) {
            $key = BTS_Base::decamelize($matches[2], "_");
            if (is_array($arguments) && count($arguments) > 0) {
                $value = $arguments[0];
            }
            else {
                $value = null;
            }
            switch ($matches[1]) {
                case "set":
                    $this->setData($key, $value);
                    return $this;
                    break;
                case "get":
                    return $this->getData($key, $value);
                    break;
                case "unset":
                    $this->unsetData($key);
                    return $this;
                    break;
                case "has":
                    return $this->hasData($key);
                    break;
                case "incr":
                    return $this->incr($key, $value);
                    break;
                case "decr":
                    return $this->decr($key, $value);
                    break;
            }
        }
        else {
            throw new Exception("Invalid Method: " . get_class($this) . "::" . $name);
        }
    }
    
    public function incr($key, $value = 1) {
        if (is_null($this->getData($key))) {
            $this->setData($key, 0);
        }
        
        if (is_numeric($this->getData($key))) {
            $this->setData($key, $this->getData($key) + $value);
            return $this->getData($key);
        }
        else {
            throw new Exception("Cannot increment key '" . $key . "'. Not a number.");
        }
    }
    
    public function decr($key, $value = 1) {
        if (is_null($this->getData($key))) {
            $this->setData($key, 0);
        }
        
        if (is_numeric($this->getData($key))) {
            $this->setData($key, $this->getData($key) - $value);
            return $this->getData($key);
        }
        else {
            throw new Exception("Cannot decrement key '" . $key . "'. Not a number.");
        }
    }
    
    
    public function getData($key = null, $default = null) {
        if (is_null($key)) {
            return $this->_data;
        }
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }
        return $default;
    }
    public function hasData($key) {
        return isset($this->_data[$key]);
    }
    public function setData($key, $value = null) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->setData($k, $v);
            }
        }
        else {
            if ($this->_protectData) {
                if (!in_array($key, $this->_fields)) {
                    throw new Exception("Cannot set value into key '" . $key . "' - Protect Fields is enabled");
                }
            }
            $key = BTS_Base::decamelize($key, "_");
            $this->_data[$key] = $value;
        }
        return $this;
    }
    public function unsetData($key) {
        if (isset($this->_data[$key])) {
            unset($this->_data[$key]);
        }
        return $this;
    }
    public function resetData() {
        $this->_data = array();
        return $this;
    }
    
    /**
     * Abstract function to be overloaded 
     */
    protected function _preparePrettyArray() { }
    
    public function toPrettyArray() {
        $this->_preparePrettyArray();
        
        $data = $this->getData();
        
        $arr = array();
        foreach ($data as $key => $value) {
            if ($value == "") {
                continue;
            }
            
            $arr[$this->_fieldPrefix . BTS_Base::capitalize($key)] = $value;
        }
        return $arr;
    }

    public function toArray() {
        return $this->getData();
    }
    
    public function __sleep() {
        return array("_data");
    }
}
