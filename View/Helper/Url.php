<?php

class BTS_View_Helper_Url extends Zend_View_Helper_Abstract {
    public function url(array $urlOptions = array(), $name = null, $reset = false, $encode = true, $absolute = true, $host = null, $shorten = false) {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        /* @var $router Zend_Controller_Router_Rewrite */
        
        if (isset($urlOptions['_reset'])) {
            $reset = $urlOptions['_reset'];
            unset($urlOptions['_reset']);
        }
        else {
            // attempt to intelligently detect whether to reset or not.
            // 
            // take a copy of the options array
            $regularKeys = 0;
            foreach ($urlOptions as $key => $val) {
                // remove keys which translate to actions for the router assembly
                if (substr($key, 0, 1) != "_") {
                    $regularKeys++;
                }
            }
            
            if (!isset($urlOptions['controller']) && !isset($urlOptions['action']) || $regularKeys > 0) {
                // didn't specify a controller or action, or specified other keys (possibly url paramters)
                // so probably wants current url. don't reset
                $reset = false;
                
                if ($router->getCurrentRouteName() != "default") {
                    // reset this if route name is not default, else it might not get re-built correctly
                    $urlOptions['_name'] = $router->getCurrentRouteName();
                }
            }
            else {
                $reset = true;
            }
        }
        
        if (isset($urlOptions['_name'])) {
            $name = $urlOptions['_name'];
            unset($urlOptions['_name']);
        }
        if (isset($urlOptions['_encode'])) {
            $encode = $urlOptions['_encode'];
            unset($urlOptions['_encode']);
        }
        if (isset($urlOptions['_absolute'])) {
            $absolute = $urlOptions['_absolute'];
            unset($urlOptions['_absolute']);
        }
        if (isset($urlOptions['_host'])) {
            $host = $urlOptions['_host'];
            unset($urlOptions['_host']);
        }
        
        if (isset($urlOptions['fragment'])) {
            $fragment = $urlOptions['fragment'];
            unset($urlOptions['fragment']);
        }
        
        if (isset($urlOptions['_removeParam'])) {
            $currentParams = Zend_Controller_Front::getInstance()->getRequest()->getParams();
            
            if (is_string($urlOptions['_removeParam'])) {
                $urlOptions['_removeParam'] = array($urlOptions['_removeParam']);
            }
            
            foreach ($urlOptions['_removeParam'] as $param) {
                if (isset($currentParams[$param])) {
                    unset($currentParams[$param]);
                }
            }
            unset($urlOptions['_removeParam']);
            
            // force a reset to remove the requested param(s)
            $reset = true;
            
            $urlOptions = $urlOptions + $currentParams;
        }
        
        if (isset($urlOptions['_removeExtraParams'])) {
            $currentParams = Zend_Controller_Front::getInstance()->getRequest()->getParams();
            
            foreach ($currentParams as $param => $value) {
                if (!in_array($param, array("module", "controller", "action"))) {
                    unset($currentParams[$param]);
                }
            }
            unset($urlOptions['_removeExtraParams']);
            
            // force a reset to remove the requested param(s)
            $reset = true;
            
            $urlOptions = $urlOptions + $currentParams;
        }
        
        if (isset($urlOptions['_shorten'])) {
            $shorten = true;
            unset($urlOptions['_shorten']);
            if (!$absolute) {
                // $host must be forced to true so we get an absolute url, else the
                // shortened url won't work.
                $absolute = true;
            }
        }
        
        if (is_null($name)) {
            $name = "default";
        }
        
        if ($absolute) {
            $hostHelper = new BTS_View_Helper_ServerUrl();
            if (!is_null($host)) {
                $hostHelper->setHost($host);
            }
            $url = $hostHelper->serverUrl($router->assemble($urlOptions, $name, $reset, $encode));
        }
        else {
            $url = $router->assemble($urlOptions, $name, $reset, $encode);
        }
        
        if (isset($fragment)) {
            $url .= "#" . $fragment;
        }
        
        if ($shorten) {
            $client = new Zend_Http_Client("http://api.bit.ly/v3/shorten");
            $client->setParameterGet(array(
                "longUrl" => $url,
                "login" => BTS_Base::getAppConfig()->services->bitly->login,
                "apiKey" => BTS_Base::getAppConfig()->services->bitly->apikey,
            ));
            $response = $client->request();
            if ($response->isSuccessful()) {
                $json = Zend_Json::decode($response->getBody());
                if ($json['status_code'] == 200) {
                    $url = $json['data']['url'];
                }
            }
        }
        
        return $url;
    }
}
