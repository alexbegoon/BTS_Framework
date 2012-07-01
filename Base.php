<?php

require_once(dirname(__FILE__) . "/functions.php");

class BTS_Base {
    static $_appConfig;
    static $_moduleConfig;
    static $_sessions;
    static $_cache;
    
    static $_messenger;
    static $_user;
    
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
            self::$_appConfig = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);
        }
        return self::$_appConfig;
    }
    static function getModuleConfig() {
        if (is_null(self::$_moduleConfig)) {
            $moduleName = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
            self::$_moduleConfig = new Zend_Config_Ini(APPLICATION_PATH . "/modules/" . $moduleName . "/configs/module.ini", APPLICATION_ENV);
        }
        return self::$_moduleConfig;
    }
    static function getModel($modelClass, $constructionArgs = null) {
        return new $modelClass($constructionArgs);
    }
    /**
     * @return \BTS_Model
     */
    static function getActiveUser() {
        $auth = Zend_Auth::getInstance();
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
        
        $data = date("Y-m-d H:i:s") . ": " . $data;
        
        $fh = fopen(self::getAppConfig()->bts->logpath, "a");
        fwrite($fh, $data);
        fclose($fh);
    }
    
    /**
     * @return Zend_Db_Adapter_Abstract
     */
    static function getDb() {
        return BTS_Db::instance();
    }
    
    static function getSession($ns = "BTS_Base") {
        if (is_null(self::$_sessions[$ns])) {
            self::$_sessions[$ns] = new Zend_Session_Namespace($ns);
        }
        return self::$_sessions[$ns];
    }
    
    /**
     * @return \Zend_Cache_Core
     */
    static function getCache() {
        $cache = Zend_Registry::get('cache');
        return $cache;
    }
    
    static function getVersion() {
        $str = self::exec("svn info --xml " . dirname(APPLICATION_PATH));
        $xml = simplexml_load_string($str);
        return "r" . (string)$xml->entry->attributes()->revision;
    }
    
    static function exec($cmd) {
        exec($cmd, $output);

        $rtnstr = "";
        foreach ($output as $line) {
            $rtnstr .= $line . "\n";
        }
        return $rtnstr;
    }
    
}
