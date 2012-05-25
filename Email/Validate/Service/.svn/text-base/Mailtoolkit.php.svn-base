<?php

class BTS_Email_Validate_Service_Mailtoolkit {
    
    public function validate($email) {
        
        $uri = str_replace(":email", $email, BTS_Base::getAppConfig()->bts->email->validate->mailtoolkit->host);
        //var_dump($uri); exit;
        $client = new Zend_Http_Client();
        $client->setUri($uri);
        $client->setHeaders("Accept-Encoding", "");
        
        $response = Zend_Json::decode($client->request()->getBody());
        
        var_dump($response);
        
    }
    
}
