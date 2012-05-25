<?php

class BTS_Db {
    
    static $_db;
    
    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public static function instance() {
        if (is_null(self::$_db)) {
            $config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/application.ini", APPLICATION_ENV);

            self::$_db = Zend_Db::factory($config->resources->db);
            self::$_db->getProfiler()->setEnabled($config->resources->db->profiler->enabled);
        }
        return self::$_db;
    }
    
    public static function getTable($tbl) {
        if ($prefix = BTS_Base::getAppConfig()->resources->db->params->tblprefix) {
            return $prefix . "_" . $tbl;
        }
        else {
            return $tbl;
        }
    }
    
    public static function getTablePrefix() {
        try {
            $prefix = BTS_Base::getAppConfig()->resources->db->params->tblprefix;
            if ($prefix != "") {
                return $prefix . "_";
            }
            else {
                return "";
            }
        }
        catch (Exception $e) {
            return "";
        }
    }
}
