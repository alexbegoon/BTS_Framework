<?php

class BTS_Amazon_SES_Request extends BTS_Object {
    
    protected $_action;
    
    public function __construct($action) {
        $this->_action = $action;
        return $this;
    }
    
    public function getResponse() {
        
        $date = gmdate('D, d M Y H:i:s e');
        
        $headers = array();
        $headers[] = 'Date: ' . $date;
        $headers[] = 'Host: ' . BTS_Base::getAppConfig()->bts->email->ses->host;

        $auth = 'AWS3-HTTPS AWSAccessKeyId=' . BTS_Base::getAppConfig()->bts->email->ses->access_key;
        $auth .= ',Algorithm=HmacSHA256,Signature=' . $this->_getSignature($date);
        $headers[] = 'X-Amzn-Authorization: '.$auth;
        
        $params['Action'] = $this->_action;
        
        $uri = "https://" . BTS_Base::getAppConfig()->bts->email->ses->host . "/?" . http_build_query($params);
        
        $client = new Zend_Http_Client();
        $client->setUri($uri);
        $client->setHeaders($headers);
        
        $response = new BTS_Amazon_SES_Response($client->request()->getBody());
        
        return $response;
    }
    
    protected function _getSignature($string) {
        return base64_encode(hash_hmac('sha256', $string, BTS_Base::getAppConfig()->bts->email->ses->secret_key, true));
    }
    
}
