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
        
        if (is_null($name)) {
            $name = "default";
        }
        
        if ($absolute) {
            $hostHelper = new Zend_View_Helper_ServerUrl();
            if (!is_null($host)) {
                $hostHelper->setHost($host);
            }
            return $hostHelper->serverUrl($router->assemble($urlOptions, $name, $reset, $encode));
        }
        else {
            return $router->assemble($urlOptions, $name, $reset, $encode);
        }
    }
}
