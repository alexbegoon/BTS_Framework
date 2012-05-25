<?php

class BTS_Form extends Zend_Form {
    
    public function render(Zend_View_Interface $view = null) {
        foreach ($this->getElements() as $element) {
            if ($element->hasErrors()) {
                $oldClass = $element->getAttrib('class');
                if (!empty($oldClass)) {
                    $element->setAttrib('class', $oldClass . ' validate-error');
                } else {
                    $element->setAttrib('class', 'validate-error');
                }
            }
        }
        
        return parent::render($view);
    }
    
}
