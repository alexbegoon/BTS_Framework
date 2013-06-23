<?php

class BTS_Service_Bitly {
    
    const CACHE_KEY_PREFIX = "BTS_SERVICE_BITLY_";
    
    public static function shorten($longUrl) {
        if (!BTS_Base::getAppConfig()->services->bitly->enabled) {
            return $longUrl;
        }
        
        $select = BTS_Db::instance()
                    ->select()
                    ->from(BTS_Db::getTable("bitly_cache"))
                    ->where("url_hash = ?", md5($longUrl));
        
        $row = BTS_Db::instance()->fetchRow($select);
        if ($row) {
            return $row['short_url'];
        }
        
        $client = new Zend_Http_Client("http://api.bit.ly/v3/shorten");
        $client->setParameterGet(array(
            "longUrl" => $longUrl,
            "login" => BTS_Base::getAppConfig()->services->bitly->login,
            "apiKey" => BTS_Base::getAppConfig()->services->bitly->apikey,
        ));
        $response = $client->request();
        if ($response->isSuccessful()) {
            $json = Zend_Json::decode($response->getBody());
            if ($json['status_code'] == 200) {
                BTS_Db::instance()
                        ->insert(
                                BTS_Db::getTable("bitly_cache"),
                                array(
                                    "url_hash" => md5($longUrl),
                                    "short_url" => $json['data']['url']
                                )
                        );
                return $json['data']['url'];
            }
            else {
                throw new Exception("Non 200 response from api.bit.ly");
            }
        }
        else {
            throw new Exception("Error calling api.bit.ly");
        }
    }
    
}
