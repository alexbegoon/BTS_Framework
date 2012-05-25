<?php

class BTS_Paypal_Form extends BTS_Object {
    
    public function getConfig() {
        return new Zend_Config_Ini(APPLICATION_PATH . "/configs/paypal.ini", APPLICATION_ENV);
    }
    
    public function toString() {
        
        $config = $this->getConfig();
        
        $urlHelper = new BTS_View_Helper_Url();
        $url = $urlHelper->url(array("controller" => $config->ipn->url->controller, "action" => $config->ipn->url->action), null, true, null, true, $this->getConfig()->ipn->url->host);
        
        $this->setData(array(
            'cmd'        => '_xclick',
            'business'   => $config->email,
            'cert_id'    => $config->cert->id,
            'notify_url' => $url,
            'custom'     => base64_encode(serialize(array("user" => BTS_Base::getActiveUser()->getId()))),
        ));
        
        if (strlen($this->getData('custom') > 255)) {
            throw new Exception("Field 'custom' is too long (255 character limit, " . strlen($this->getData('custom') . " presented)"));
        }
        
        $form = new Zend_Form();
        $form->setMethod("post");
        $form->setName("paypal_form");
        $form->setAction($this->getConfig()->url);
        
        $element = new Zend_Form_Element_Hidden("cmd");
        $element->setValue("_s-xclick");
        $element->removeDecorator("label");
        $form->addElement($element);
        
        $element = new Zend_Form_Element_Hidden("encrypted");
        $element->setValue($this->paypal_encrypt($this->getData()));
        $element->removeDecorator("label");
        $form->addElement($element);

        return $form;
    }
    
    function paypal_encrypt($hash) {
        
        $keyFileBasePath = APPLICATION_PATH . "/configs/paypal/" . APPLICATION_ENV . "/";
        
        $config = $this->getConfig();

	if (!file_exists($keyFileBasePath . $config->cert->private_key)) {
            throw new Exception("Public key file (" . $keyFileBasePath . $config->cert->private_key . ") not found");
	}
        if (!file_exists($keyFileBasePath . $config->cert->public_cert)) {
            throw new Exception("Public key file (" . $keyFileBasePath . $config->cert->public_cert . ") not found");
	}
	if (!file_exists($keyFileBasePath . $config->cert->paypal_cert)) {
            throw new Exception("Public key file (" . $keyFileBasePath . $config->cert->paypal_cert . ") not found");
	}
        
	$hash['bn']= 'ZF/BTS.Paypal';

	$data = "";
	foreach ($hash as $key => $value) {
            if ($value != "") {
                $data .= "$key=$value\n";
            }
	}

	$openssl_cmd = "(" . $config->openssl . " smime -sign -signer " .
                $keyFileBasePath . $config->cert->public_cert . " -inkey " .
                $keyFileBasePath . $config->cert->private_key . " -outform der -nodetach -binary <<_EOF_\n$data\n_EOF_\n) | " .
                $config->openssl . " smime -encrypt -des3 -binary -outform pem " .
                    $keyFileBasePath . $config->cert->paypal_cert;

        $output = ""; $error = "";
	exec($openssl_cmd, $output, $error);

	if (!$error) {
            return implode("\n", $output);
	}
        else {
            throw new Exception("Encryption failed");
	}
    }
    
}
