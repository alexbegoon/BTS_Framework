<?php

class BTS_View_Helper_FormatDate extends Zend_View_Helper_Abstract {

    /**
     * Format a timestamp in the user's locale and timezone 
     * 
     * @param  integer $timestamp 
     * @param  string  $format 
     * @return string 
     */
    public function formatDate($timestamp, $format = Zend_Date::DATE_SHORT) {
        if (Zend_Registry::isRegistered('locale')) { // "en_US" 
            $locale = Zend_Registry::get('locale');
        } else {
            $locale = null;
        }

        $timestamp = strtotime($timestamp);
        $date = new Zend_Date($timestamp, Zend_Date::TIMESTAMP, $locale);
        if (Zend_Registry::isRegistered('timezome')) { // "America/New_York" 
            $date->setTimezone(Zend_Registry::get('timezone'));
        }

        return $date->get($format, $locale);
    }

}

