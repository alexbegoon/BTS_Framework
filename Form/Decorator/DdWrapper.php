<?php

class BTS_Form_Decorator_DdWrapper extends Zend_Form_Decorator_DtDdWrapper {

    protected $_placement = null;

    public function render($content) {
        $elementName = $this->getElement()->getName();
        return '<dd id="' . $elementName . '-element">' . $content . '</dd>';
    }
}
