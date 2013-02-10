<?php

class BTS_View_Helper_CurrentUrl extends Zend_View_Helper_Abstract {
    
    public function currentUrl($base64 = false) {
        $fc = Zend_Controller_Front::getInstance();
        $request = $fc->getRequest();
        $url = $request->getServer("REQUEST_URI");
        if ($base64) {
            return base64_encode($url);
        }
        else {
            return $url;
        }
    }
    
}