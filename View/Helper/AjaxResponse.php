<?php

class BTS_View_Helper_AjaxResponse extends BTS_Object {
    
    public function __construct() {
        parent::__construct();
        
        $this->setData(array(
            "success" => false,
            "errors" => array(),
            "data" => null
        ));
    }
    
    public function addError($message) {
        $errors = $this->getErrors();
        array_push($errors, $message);
        $this->setErrors($errors);
        return $this;
    }
    
    public function isSuccessful($success = true) {
        $this->setSuccess($success);
        return $this;
    }
    
    public function setResponse($data) {
        $this->setData("data", $data);
        return $this;
    }
    
}