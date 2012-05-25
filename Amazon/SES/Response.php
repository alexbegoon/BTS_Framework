<?php

class BTS_Amazon_SES_Response extends BTS_Object {
    
    public function __construct($xmlstr) {
        $xml = simplexml_load_string($xmlstr);
        foreach ($xml->GetSendQuotaResult->children() as $key => $value) {
            $this->setData($key, (string)$value);
        }
        return $this;
    }
}