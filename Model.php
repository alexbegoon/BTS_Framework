<?php

abstract class BTS_Model extends BTS_Object {
    
    /**
     * Table name
     * @var string
     */
    protected $_table;
    
    /**
     * Primary Key column
     * @var string
     */
    protected $_primaryKey = "id";
    
    protected $_extraDataField = null;
    protected $_extraData = null;
    
    /**
     * @var boolean
     */
    protected $_loaded = false;
    
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;
    
    public function __construct($id = null, $key = null) {
        if (!is_null($id)) {
            return $this->load($id, $key);
        }
    }
    
    /**
     * @param type $id
     * @param type $key
     * @return \BTS_Model 
     */
    public function load($id = null, $key = null) {
        $this->_beforeLoad();
        if (!is_null($id)) {
            $select = $this->getSelect();
            if (is_array($id)) {
                foreach ($id as $k => $v) {
                    $select->where($this->table() . "." . $k . " = ?" , $v);
                }
            }
            else {
                if (is_null($key)) {
                    $select->where($this->table() . "." . $this->primaryKey() . " = ?", $id);
                }
                else {
                    $select->where($this->table() . "." . $key . " = ?", $id);
                }
            }
        }
        
        $this->fetchOne($select);
        $this->_afterLoad();
        return $this;
    }
    protected function _beforeLoad() {}
    protected function _afterLoad() {
        if (!is_null($this->_extraDataField) && $this->getData($this->_extraDataField) != "") {
            $this->_extraData = unserialize(base64_decode($this->getData($this->_extraDataField)));
        }
    }
    /**
     * Super-function to call afterLoad from the Collection loader
     * @access private 
     */
    public function __afterLoad() {
        $this->_afterLoad();
    }
    
    /**
     * @return \BTS_Model 
     */
    public function save() {
        $this->_beforeSave();
        
        // an isLoaded flag will be set when the model is loaded, and not set when a new model is created ready to be saved.
        if (!$this->isLoaded()) {
            $this->_getDb()->insert($this->table(), $this->getData());
            $this->setData($this->primaryKey(), $this->_getDb()->lastInsertId());
            
            $select = $this->getSelect();
            $select->where($this->table() . "." . $this->primaryKey() . " = ?", $this->getId());
            $this->fetchOne($select);
        }
        else {
            $data = $this->getData();
            unset($data[$this->primaryKey()]);
            $this->_getDb()->update($this->table(), $data, $this->primaryKey() . " = " . $this->getId());
        }
        
        $this->_afterSave();
        
        return $this;
    }
    
    // to be overriden
    protected function _beforeSave() {
        if (is_array($this->_extraData) && count($this->_extraData) > 0) {
            if (is_null($this->_extraDataField)) {
                throw new Exception("ExtraData has been defined, yet this model does not have an ExtraData field assigned.");
            }
            
            $this->setData($this->_extraDataField, base64_encode(serialize($this->_extraData)));
        }
    }
    protected function _afterSave() {}
    
    /**
     * Delete a row. Return value represents deletion success.
     * @return boolean
     */
    public function delete() {
        $val = $this->_getDb()->delete($this->table(), $this->primaryKey() . " = " . $this->getData($this->primaryKey()));
        return ($val > 0);
    }
    
    protected function getCacheKey() {
        return get_called_class();
    }
    /**
     * Fetch a single row
     * @param Zend_Db_Select $select
     * @return \BTS_Model 
     */
    public function fetchOne(Zend_Db_Select $select, $order = "DESC") {
        if (count($select->getPart(Zend_Db_Select::WHERE)) > 1) {
            $select->order($this->primaryKey() . " " . $order);
        }
        
        $key = $this->getCacheKey() . "__" . md5((string)$select);
        if (!$data = BTS_Base::getCache()->load($key)) {
            $data = $this->_getDb()->fetchRow($select);
            BTS_Base::getCache()->save($data, $key);
        }
        if ($data) {
            $this->_loaded = true;
            $this->setData($data);
        }
        return $this;
    }
    
    /**
     * Fetch multiple rows into an array
     * @param Zend_Db_Select $select
     * @return array 
     */
    public function fetchAll(Zend_Db_Select $select) {
        $results = $this->_getDb()->fetchAll($select);
        $return = array();
        if ($results) {
            $this->resetData();
            $this->_loaded = true;
            foreach ($results as $result) {
                $model = clone $this;
                $model->setData($result);
                array_push($return, $model);
            }
            $this->_loaded = false;
        }
        return $return;
    }
    
    /**
     * @return Zend_Db_Adapter_Mysqli
     */
    protected function _getDb() {
        return BTS_Base::getDb();
    }
    
    /**
     * @return Zend_Db_Select
     */
    public function getSelect() {
        return $this->_getDb()->select()->from($this->table());
    }
    
    /**
     * @return \BTS_Model 
     */
    public function resetData() {
        parent::resetData();
        $this->_loaded = false;
        return $this;
    }
    
    /**
     * Determine if 
     * @return boolean
     */
    public function isLoaded() {
        return $this->_loaded;
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
        return $this->_primaryKey;
    }
    
    public function getId() {
        return $this->getData($this->primaryKey());
    }
    
    static function instance() {
        $class = get_called_class();
        return new $class();
    }
    
    public function setExtraData($key, $value) {
        if (substr($key, 0, 1) == "_") {
            $key = substr($key, 1);
        }
        
        if (!is_array($this->_extraData)) {
            $this->_extraData = array();
        }

        $this->_extraData[$key] = $value;

        return $this;
    }
    
    public function getExtraData($key = '', $idx = null) {
        if ($key == "") {
            return $this->_extraData;
        }

        if (substr($key, 0, 1) == "_") {
            $key = substr($key, 1);
        }

        if (isset($this->_extraData[$key])) {
            $value = $this->_extraData[$key];
            if (is_array($value) && $idx !== null && is_numeric($idx)) {
                if (isset($value[$idx])) {
                    return $value[$idx];
                }
                else {
                    return null;
                }
            }
            else {
                return $value;
            }
        }
        
        return null;
    }
    
    public function addHook($hook, $something) {
        // do..something.
    }
}