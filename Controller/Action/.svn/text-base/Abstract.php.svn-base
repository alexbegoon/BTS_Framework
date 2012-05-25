<?php

abstract class BTS_Controller_Action_Abstract extends Zend_Controller_Action {
    protected $_requiresAuth = false;
    protected $_loginRoute = "/auth";
    protected $_authStorage = null;
    
    protected $_flashMessenger = null;

    public function init() {
        /* Initialize action controller here */
        $this->_flashMessenger = $this->_helper->getHelper('FlashMessenger');
        $msgs = array();
        $msgs = $this->_flashMessenger->getMessages();
        $messages = array();
        for ($i = 0; $i < count($msgs); $i++) {
            $message = array("msg" => $msgs[$i], "style" => $msgs[++$i]);
            array_push($messages, $message);
        }
        $this->view->messages = $messages;
    }
    
    public function preDispatch() {
        if ($this->_requiresAuth) {
            $auth = Zend_Auth::getInstance();
            if (!is_null($this->_authStorage)) {
                $authStorage = new Zend_Auth_Storage_Session($this->_authStorage);
                $auth->setStorage($authStorage);
            }
            if ($auth->hasIdentity()) {
                $this->view->user = $auth->getIdentity();
            } else {
                $referer = base64_encode($this->_request->getPathInfo());
                $this->_flashMessenger->addMessage("You need to login first");
                $this->_flashMessenger->addMessage("error_message");
                $this->_redirect($this->_loginRoute . "/index/referer/" . $referer);
            }
        }
    }
}
