<?php

class BTS_View_Helper_Inflector extends Zend_View_Helper_Abstract {
    /**
     * @return \BTS_Inflector 
     */
    public function inflector() {
        return new BTS_Inflector();
    }
}
