<?php

abstract class BTS_Controller_Cron_Abstract extends Zend_Controller_Action {
    
    public function preDispatch() {
        $this->view->layout()->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
    }
    public function postDispatch() {
        exit;
    }
    
}