<?php

class BTS_View_Helper_ServerUrl extends Zend_View_Helper_ServerUrl {
    
    public function serverUrl($requestUri = null) {
        $url = parent::serverUrl($requestUri);
        
        $filepath = dirname(APPLICATION_PATH) . "/public" . $requestUri;
        if (file_exists($filepath)) {
            $url .= "?m=" . filemtime($filepath);
        }
        
        return $url;
    }
    
}
