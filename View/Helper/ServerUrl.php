<?php

class BTS_View_Helper_ServerUrl extends Zend_View_Helper_ServerUrl {
    
    public function serverUrl($requestUri = null, $filepath = null, $antiCache = true) {
        if (is_null($requestUri) || substr($requestUri, 0, 1) != "/") {
            $requestUri = "/" . $requestUri;
        }
        $url = parent::serverUrl($requestUri);
        
        if (is_null($filepath)) {
            $filepath = dirname(APPLICATION_PATH) . "/public" . $requestUri;
        }
        else {
            $filepath = dirname(APPLICATION_PATH) . "/public" . $filepath;
        }
        
        // $requestUri should be at least something. null means homepage.
        // file_exists will always be true, and we don't want an m= query
        // string on links back to the homepage...
        if ($requestUri != "/" && file_exists($filepath) && $antiCache) {
            $url .= "?m=" . filemtime($filepath);
        }
        
        return $url;
    }
    
}
