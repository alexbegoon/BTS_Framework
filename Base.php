<?php

require_once(dirname(__FILE__) . "/functions.php");

class BTS_Base {
    static $_appConfig;
    static $_moduleConfig;
    static $_sessions;
    static $_cache;
    
    static $_messenger;
    static $_user;
    static $_registry = array();
    
    static function capitalize($str) {
        return str_replace(' ', '', ucwords(preg_replace('/[\s_]+/', ' ', $str)));
    }
    static function decapitalize($str, $sep = " ") {
        return strtolower(substr(preg_replace("/([A-Z])/", "_\\1", "x" . $str), 2));
    }
    static function camelize($str) {
        $str = 'x'.strtolower(trim($str));
        $str = ucwords(preg_replace('/[\s_]+/', ' ', $str));
        return substr(str_replace(' ', '', $str), 1);
    }
    static function decamelize($str, $sep = " ") {
        return strtolower(preg_replace('/([a-z0-9])([A-Z])/', '$1'.$sep.'$2', trim($str)));
    }
    static function underscore($str) {
        return preg_replace('/\s+/', '_', trim($str));
    }
    static function humanize($str) {
        return preg_replace('/[_-]+/', ' ', trim($str));
    }
    static function getAppConfig() {
        if (is_null(self::$_appConfig)) {
            if (!self::$_appConfig = self::getCache()->load("BTS_BASE_APPCONFIG")) {
                self::$_appConfig = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
                self::getCache()->save(self::$_appConfig, "BTS_BASE_APPCONFIG");
            }
        }
        
        return self::$_appConfig;
    }
    static function getModuleConfig() {
        if (is_null(self::$_moduleConfig)) {
            $moduleName = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
            if (!self::$_moduleConfig = self::getCache()->load("BTS_BASE_MODULECONFIG_" . $moduleName)) {
                self::$_moduleConfig = new Zend_Config_Ini(APPLICATION_PATH . "/modules/" . $moduleName . "/configs/module.ini", APPLICATION_ENV);
                self::getCache()->save(self::$_moduleConfig, "BTS_BASE_MODULECONFIG_" . $moduleName);
            }
        }
        return self::$_moduleConfig;
    }
    static function getModel($modelClass, $constructionArgs = null) {
        return new $modelClass($constructionArgs);
    }
    /**
     * @return \BTS_Model
     */
    static function getActiveUser($storage_key = "Auth") {
        $auth = Zend_Auth::getInstance();
        
        $storage = new Zend_Auth_Storage_Session($storage_key);
        $auth->setStorage($storage);
        
        if ($auth->hasIdentity()) {
            if (is_null(self::$_user)) {
                $user = $auth->getIdentity();
                
                $userClass = self::getAppConfig()->bts->base->usermodel;

                // compensate for the sites that don't store the model in the auth storage
                if (!($user instanceof $userClass)) {
                    if (is_numeric($user)) {
                        $user = new $userClass($user);
                    }
                    elseif (is_string($user)) {
                        $user = new $userClass($user, "username");
                    }
                }
                
                self::$_user = $user;
            }
            
            return self::$_user;
        }
        return false;
    }
    
    static function log($data) {
        if (!BTS_Base::getAppConfig()->bts->debug) {
            return true;
        }
        
        if (!is_string($data) && !is_numeric($data)) {
            $data = print_r($data, true);
        }
        
        $logFilePath = dirname(APPLICATION_PATH) . "/logs/" . self::getAppConfig()->bts->base->logfile;
        
        $logger = new Zend_Log();
        $logger->addWriter(new Zend_Log_Writer_Stream($logFilePath));
        //$logger->addWriter(new Zend_Log_Writer_Db(BTS_Db::instance(), BTS_Db::getTable("log")));
        $logger->log($data, Zend_Log::INFO);
    }
    
    /**
     * @return Zend_Db_Adapter_Abstract
     */
    static function getDb() {
        return BTS_Db::instance();
    }
    
    static function getSession($ns = "BTS_Base") {
        return BTS_Session::instance($ns);
    }
    
    /**
     * @return \Zend_Cache_Core|false
     */
    static function getCache() {
        if (Zend_Registry::isRegistered("cache")) {
            $cache = Zend_Registry::get('cache');
            return $cache;
        }
        else {
            return new Zend_Cache_Backend_BlackHole();
        }
    }
    
    static function exec($cmd) {
        exec($cmd, $output);

        $rtnstr = "";
        foreach ($output as $line) {
            $rtnstr .= $line . "\n";
        }
        return $rtnstr;
    }
    
    static function register($key, $value) {
        self::$_registry[$key] = $value;
    }
    static function unregister($key) {
        if (isset(self::$_registry[$key])) {
            unset(self::$_registry[$key]);
        }
    }
    static function registry($key, $default = null) {
        if (isset(self::$_registry[$key])) {
            return self::$_registry[$key];
        }
        else {
            return $default;
        }
    }
    static function debug() {
        return self::getAppConfig()->bts->debug;
    }
}
