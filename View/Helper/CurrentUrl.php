<?php

class BTS_View_Helper_CurrentUrl
    extends Zend_View_Helper_Abstract
{
    
    public function currentUrl()
    {
        $fc = Zend_Controller_Front::getInstance();
        $request = $fc->getRequest();
        $url = $request->getServer("REQUEST_URI");
        return $url;
    }
    
}