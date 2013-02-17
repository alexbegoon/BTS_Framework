<?php

abstract class BTS_Controller_Action_Abstract extends Zend_Controller_Action {
    protected $_requiresAuth = false;
    protected $_loginRoute = "/auth/login";
    protected $_authStorage = "Auth";
    
    protected $_flashMessenger = null;

    public function init() {
        if ($this->getRequest()->isXmlHttpRequest() || $this->getRequest()->getParam('isAjax')) {
            $this->view->layout()->disableLayout();
            $this->_helper->viewRenderer->setNoRender();
            $this->isAjax = true;
        }
    }
    
    public function preDispatch() {
        if ($this->_requiresAuth) {
            if (!BTS_Base::getActiveUser($this->_authStorage)) {
                $referer = $this->view->currentUrl(true);
                
                $this->_addInfo("Please login to continue.");
                $this->_redirect($this->_loginRoute . "/redir/" . $referer);
            }
        }
        $this->view->user = BTS_Base::getActiveUser($this->_authStorage);
    }
    
    protected function _redirectReferer() {
        $this->_redirect($this->getRefererUrl());
    }
    
    /**
     * Get referer URL
     * @param string $encoded Base64 encode URL
     * @return string
     */
    public function getRefererUrl($encoded = false) {
        if ($this->getRequest()->getParam("redir")) {
            $url = base64_decode($this->getRequest()->getParam("redir"));
        }
        else if (Zend_Registry::isRegistered("referer_url") && Zend_Registry::get("referer_url") != "") {
            $url = Zend_Registry::get("referer_url");
        }
        else {
            $url = $this->getRequest()->getServer("HTTP_REFERER");
        }
        
        if ($encoded) {
            $url = base64_encode($url);
        }
        
        return $url;
    }
    
    protected function _addSuccess($message, $immediate = false) {
        if (!$immediate) {
            $this->_helper->FlashMessenger(array("success" => $message));
        }
        else {
            $this->_addImmediateMessage($message, "success");
        }
        return $this;
    }
    
    protected function _addInfo($message, $immediate = false) {
        if (!$immediate) {
            $this->_helper->FlashMessenger(array("info" => $message));
        }
        else {
            $this->_addImmediateMessage($message, "info");
        }
        return $this;
    }
    
    protected function _addError($message, $immediate = false) {
        if (!$immediate) {
            $this->_helper->FlashMessenger(array("error" => $message));
        }
        else {
            $this->_addImmediateMessage($message, "error");
        }
        return $this;
    }
    protected function _addNotice($message, $immediate = false) {
        if (!$immediate) {
            $this->_helper->FlashMessenger(array("notice" => $message));
        }
        else {
            $this->_addImmediateMessage($message, "notice");
        }
        return $this;
    }
    
    protected function _addImmediateMessage($message, $severity = "info") {
        if (!in_array($severity, array("info", "success", "notice", "error"))) {
            throw new Exception("Invalid severity type");
        }
        
        if (!Zend_Registry::isRegistered("_immediateMessages")) {
            $messages = array();
        }
        else {
            $messages = Zend_Registry::get("_immediateMessages");
        }
        array_push($messages, array("severity" => $severity, "message" => $message));
        Zend_Registry::set("_immediateMessages", $messages);
        return $this;
    }
    
}
