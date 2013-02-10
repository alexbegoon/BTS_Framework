<?php

class BTS_Paypal_Form extends BTS_Object {
    
    public function getConfig() {
        return new Zend_Config_Ini(APPLICATION_PATH . "/configs/paypal.ini", APPLICATION_ENV);
    }
    
    public function toString() {
        $config = $this->getConfig();
        
        $urlHelper = new BTS_View_Helper_Url();
        $notifyUrl = $urlHelper->url(
                array(
                    "controller" => $config->ipn->url->controller, 
                    "action" => $config->ipn->url->notify,
                ),
                null,
                true,
                null,
                true,
                $this->getConfig()->ipn->url->host
        );
        $cancelUrl = $urlHelper->url(
                array(
                    "controller" => $config->ipn->url->controller, 
                    "action" => $config->ipn->url->cancel,
                    "redir" => base64_encode(BTS_Base::getSession()->referer_url),
                ),
                null,
                true,
                null,
                true,
                $this->getConfig()->ipn->url->host
        );
        $returnUrl = $urlHelper->url(
                array(
                    "controller" => $config->ipn->url->controller, 
                    "action" => $config->ipn->url->return,
                    "redir" => base64_encode(BTS_Base::getSession()->referer_url),
                ),
                null,
                true,
                null,
                true,
                $this->getConfig()->ipn->url->host
        );
        
        $custom = $this->hasCustom() ? $this->getCustom() : array();
        $custom = array_merge($custom, array("user" => BTS_Base::getActiveUser()->getId()));
        
        $this->setData(array(
            'cmd'           => $this->hasData("cmd") ? $this->getData("cmd") : '_xclick',
            'business'      => $config->email,
            'cert_id'       => $config->cert->id,
            'notify_url'    => $notifyUrl,
            'cancel_return' => $cancelUrl,
            'return'        => $returnUrl,
            'custom'        => base64_encode(serialize($custom)),
        ));
        
        var_dump($this->getData());
        
        if (strlen($this->getData('custom') > 255)) {
            throw new Exception("Field 'custom' is too long (255 character limit, " . strlen($this->getData('custom') . " presented)"));
        }
        
        $form = new Zend_Form();
        $form->setMethod("post");
        $form->setName("paypal_form");
        $form->setAction($this->getConfig()->url);
        
        if ($config->encrypt) {
            $cmdElement = new Zend_Form_Element_Hidden("cmd");
            $cmdElement->setValue("_s-xclick");
            $cmdElement->removeDecorator("label");
            $form->addElement($cmdElement);

            $encrElement = new Zend_Form_Element_Hidden("encrypted");
            $encrElement->setValue($this->paypal_encrypt($this->getData()));
            $encrElement->removeDecorator("label");
            $form->addElement($encrElement);
        }
        else {
            foreach ($this->getData() as $key => $value) {
                $element = new Zend_Form_Element_Hidden($key);
                $element->setValue($value);
                $element->removeDecorator("label");
                $form->addElement($element);
            }
        }
        
        if (APPLICATION_ENV != "production") {
            $submitElement = new Zend_Form_Element_Button("btn_send");
            $submitElement->setAttrib("type", "submit");
            $submitElement->setLabel("Send");
            $submitElement->removeDecorator('DtDdWrapper');
            $form->addElement($submitElement);
        }

        return $form;
    }
    
    function paypal_encrypt($hash) {
        $config = $this->getConfig();
        
        $keyFileBasePath = APPLICATION_PATH . "/configs/paypal/" . $config->cert->directory . "/";

	if (!file_exists($keyFileBasePath . $config->cert->private_key)) {
            throw new Exception("Private key file (" . $keyFileBasePath . $config->cert->private_key . ") not found");
	}
        if (!file_exists($keyFileBasePath . $config->cert->public_cert)) {
            throw new Exception("Public certificate file (" . $keyFileBasePath . $config->cert->public_cert . ") not found");
	}
	if (!file_exists($keyFileBasePath . $config->cert->paypal_cert)) {
            throw new Exception("Paypal certificate file (" . $keyFileBasePath . $config->cert->paypal_cert . ") not found");
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
