<?php

abstract class BTS_Model_Collection implements Iterator, Countable, ArrayAccess {
    
    protected $_dataArray = array();
    protected $_position = 0;
    
    /**
     * @var boolean
     */
    protected $_loaded = false;
    
    protected $_table;
    protected $_modelClass;
    protected $_primaryKey;
    
    /**
     * @var Zend_Db_Adapter_Mysqli
     */
    protected $_db;
    
    /**
     * @var Zend_Db_Select
     */
    protected $_select;
    
    public function __construct() {}
    
    /**
     * @return Zend_Db_Adapter_Mysqli
     */
    protected function _getDb() {
        return BTS_Db::instance();
    }
    
    protected function _reset() {
        $this->_loaded = false;
        $this->_dataArray = array();
        return $this;
    }
    
    protected function getCacheKey() {
        return get_called_class();
    }
    
    /**
     * @return \BTS_Model_Collection 
     */
    protected function _load() {
        if (!$this->_loaded) {
            $key = $this->getCacheKey() . "__" . md5((string)$this->getSelect());
            if (!$data = BTS_Base::getCache()->load($key)) {
                $data = $this->_getDb()->fetchAll($this->getSelect());
                BTS_Base::getCache()->save($data, $key);
            }
            
            foreach ($data as $row) {
                $obj = new $this->_modelClass;
                $obj->setData($row);
                $obj->__afterLoad();
                array_push($this->_dataArray, $obj);
            }
            $this->_loaded = true;
        }
        return $this;
    }
    
    /**
     * @return Zend_Db_Select
     */
    public function getSelect() {
        if (is_null($this->_select)) {
            $this->_select = $this->_getDb()->select()->from($this->table());
        }
        return $this->_select;
    }
    
    /**
     * @param string $key Database column
     * @param string $sign Database where comparator
     * @param string $value Column value (automatically quoted into)
     * @return \BTS_Model_Collection 
     */
    public function addWhere($key, $sign, $value) {
        $this->getSelect()->where($key . " " . $sign . " ?", $value);
        $this->_reset();
        return $this;
    }
    public function addOrWhere($key, $sign, $value) {
        $this->getSelect()->orWhere($key . " " . $sign . " ?", $value);
        $this->_reset();
        return $this;
    }
    
    public function setLimit($count, $offset = 0) {
        $this->getSelect()->limit($count, $offset);
        $this->_reset();
        return $this;
    }
    
    /**
     * @return boolean
     */
    public function isLoaded() {
        return $this->_loaded;
    }
    
    // Iterator methods
    /**
     * @return \BTS_Model
     */
    public function current() {
        $this->_load();
        return $this->_dataArray[$this->_position];
    }
    public function next() {
        $this->_load();
        $this->_position++;
    }
    /**
     * @return int
     */
    public function key() {
        $this->_load();
        return $this->_position;
    }
    /**
     * @return boolean
     */
    public function valid() {
        $this->_load();
        return isset($this->_dataArray[$this->_position]);
    }
    public function rewind() {
        $this->_load();
        $this->_position = 0;
    }
    
    // Countable methods
    /**
     * @return int
     */
    public function count() {
        $this->_load();
        return count($this->_dataArray);
    }
    
    // ArrayAccess methods
    public function offsetExists($offset) {
        $this->_load();
        return isset($this->_dataArray[$this->_position]);
    }
    public function offsetGet($offset) {
        $this->_load();
        return isset($this->_dataArray[$offset]) ? $this->_dataArray[$offset] : null;
    }
    public function offsetSet($offset, $value) {
        throw new Exception("Collections are read-only");
    }
    public function offsetUnset($offset) {
        throw new Exception("Collections are read-only");
    }
    
    // other stuff
    
    /**
     * @param BTS_Model_Collection $collection
     * @param string $where Either PREPEND (default) or APPEND
     * @return \BTS_Model_Collection 
     */
    public function merge(BTS_Model_Collection $collection, $where = "PREPEND") {
        $this->_load();
        
        $arr = array();
        foreach ($collection as $obj) {
            array_push($arr, $obj);
        }
        switch ($where) {
            case "PREPEND":
                $this->_dataArray = array_merge($arr, $this->_dataArray);
                break;
            case "APPEND":
                $this->_dataArray = array_merge($this->_dataArray, $arr);
                break;
        }
        return $this;
    }
    
    public function toArray() {
        $rtn = array();
        foreach ($this as $obj) {
            array_push($rtn, $obj->toArray());
        }
        return $rtn;
    }
    
    public function table() {
        if ($prefix = BTS_Base::getAppConfig()->resources->db->params->tblprefix) {
            return $prefix . "_" . $this->_table;
        }
        else {
            return $this->_table;
        }
    }
    public function primaryKey() {
        if (!$this->_primaryKey) {
            $model = new $this->_modelClass();
            /* @var $model \BTS_Model */
        
            $this->_primaryKey = $model->primaryKey();
        }
        
        return $this->_primaryKey;
    }
}
