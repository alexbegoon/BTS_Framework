<?php

abstract class BTS_Model extends BTS_Object {
    
    /**
     * Table name
     * @var string
     */
    protected $_table;
    
    /*
     * Original data array. Used to determine if data has changed.
     */
    protected $_origData = array();
    
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
    protected $_schema;
    
    public function __construct($id = null, $key = null) {
        if (!$this->_schema = BTS_Base::getCache()->load($this->getSchemaCacheKey())) {
            $this->_schema = BTS_Db::instance()->describeTable($this->table());
            BTS_Base::getCache()->save($this->_schema, $this->getSchemaCacheKey());
        }
        
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
                    if ($k == "_primary_key") {
                        $k = $this->primaryKey();
                    }
                    $select->where($this->table() . "." . $k . " = ?" , $v);
                }
            }
            else {
                if (is_null($key)) {
                    $select->where($this->table() . "." . $this->primaryKey() . " = ?", $id);
                }
                else {
                    if ($key == "_primary_key") {
                        $key = $this->primaryKey();
                    }
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
        $this->_origData = $this->_data;
    }
    /**
     * Super-function to call afterLoad from the Collection loader
     * @access private 
     */
    public function __afterLoad() {
        $this->_loaded = true;
        $this->_afterLoad();
    }
    
    /**
     * @return \BTS_Model 
     */
    public function save() {
        if (is_array($this->_extraData) && count($this->_extraData) > 0) {
            if (is_null($this->_extraDataField)) {
                throw new Exception("ExtraData has been defined, yet this model does not have an ExtraData field assigned.");
            }
            
            $this->setData($this->_extraDataField, base64_encode(serialize($this->_extraData)));
        }
        
        $this->_beforeSave();
        
        // this prevents sql errors caused by data keys sent to the model and the column
        // not existing in the database.
        $dataArray = array();
        foreach ($this->getData() as $key => $value) {
            if (isset($this->_schema[$key])) {
                if ($value == "" && $this->_schema[$key]['NULLABLE']) {
                    $value = null;
                }
                
                $dataArray[$key] = $value;
            }
        }
        
        // an isLoaded flag will be set when the model is loaded, and not set when a new model is created ready to be saved.
        if (!$this->isLoaded()) {
            $this->_getDb()->insert($this->table(), $dataArray);
            $this->setData($this->primaryKey(), $this->_getDb()->lastInsertId());
            
            // reload, not just to fetch last insert id, but to fetch default column data (current_timestamp, etc)..
            $select = $this->getSelect();
            $select->where($this->table() . "." . $this->primaryKey() . " = ?", $this->getId());
            $this->fetchOne($select);
        }
        else {
            unset($dataArray[$this->primaryKey()]);
            $this->_getDb()->update($this->table(), $dataArray, $this->primaryKey() . " = " . $this->getId());
        }
        
        $this->_afterSave();
        
        return $this;
    }
    
    // to be overriden
    protected function _beforeSave() {}
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
        return "BTS_MODEL_" . get_called_class() . "_OBJECT";
    }
    
    protected function getSchemaCacheKey() {
        return "BTS_MODEL_" . get_called_class() . "_SCHEMA";
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
        
        //$key = $this->getCacheKey() . "__" . md5((string)$select);
        //if (!$data = BTS_Base::getCache()->load($key)) {
            $data = $this->_getDb()->fetchRow($select);
            //BTS_Base::getCache()->save($data, $key);
        //}
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
    
    /**
     * @return \BTS_Model
     */
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
    
    public function getOrigData($key = null, $default = null) {
        if (is_null($key)) {
            return $this->_origData;
        }
        if (isset($this->_origData[$key])) {
            return $this->_origData[$key];
        }
        return $default;
    }
    
    public function hasFieldChanged($key) {
        return $this->getOrigData($key) != $this->getData($key);
    }
    
    public function __sleep() {
        $data = parent::__sleep();
        $data[] = "_schema";
        $data[] = "_origData";
        $data[] = "_loaded";
        return $data;
    }
}
