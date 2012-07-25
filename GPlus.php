<?php

class BTS_GPlus {
    
    public function getUser($id) {
        var_dump($this->_fetch("/people/" . $id));
        
        exit;
        $response = $this->_client->request();
        
        var_dump($response);
        
        $user = new BTS_GPlus_User();
        
        var_dump($user);
    }
    
    
    protected function _fetch($url) {
        $client = new Zend_Http_Client(BTS_Base::getAppConfig()->bts->gplus->baseurl . $url . "?key=" . BTS_Base::getAppConfig()->bts->gplus->apikey);
        //var_dump($client); exit;
        return $client->request();
        return $this;
    }
    
}
