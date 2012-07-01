<?php

class BTS_View_Helper_Url extends Zend_View_Helper_Abstract {
    public function url(array $urlOptions = array(), $name = null, $reset = false, $encode = true, $absolute = true, $host = null) {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        
        if (isset($urlOptions['_name'])) {
            $name = $urlOptions['_name'];
            unset($urlOptions['_name']);
        }
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
            }
            else {
                $reset = true;
            }
        }
        
        if (isset($urlOptions['_encode'])) {
            $reset = $urlOptions['_encode'];
            unset($urlOptions['_encode']);
        }
        if (isset($urlOptions['_absolute'])) {
            $reset = $urlOptions['_absolute'];
            unset($urlOptions['_absolute']);
        }
        if (isset($urlOptions['_host'])) {
            $reset = $urlOptions['_host'];
            unset($urlOptions['_host']);
        }
        
        if (isset($urlOptions['fragment'])) {
            $fragment = $urlOptions['fragment'];
            unset($urlOptions['fragment']);
        }
        
        if (is_null($name)) {
            $name = "default";
        }
        
        if ($absolute) {
            $hostHelper = new Zend_View_Helper_ServerUrl();
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
        
        return $url;
    }
}
