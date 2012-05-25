<?php

class BTS_View_Helper_SwfUploader extends Zend_View_Helper_FormElement {
    
    public function swfUploader() {
        $html = '
<div id="swfupload-control">';
        
        $submitElement = new Zend_Form_Element_Button("btn_upload");
        $submitElement->setAttribs(array("type" => "submit", "helper" => "FormButton"));
        $submitElement->setLabel("Upload");
        $submitElement->removeDecorator('DtDdWrapper');
        $html .= $submitElement->render();
        
        $html .= '
    <p id="queuestatus" ></p>
    <ol id="log"></ol>
</div>';
        return $html;
    }
    
}