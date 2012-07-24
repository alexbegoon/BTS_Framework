<?php

class BTS_Model_Email_Queue extends BTS_Model {
    
    const STATUS_BLOCKED = -2;
    const STATUS_BOUNCED = -1;
    const STATUS_UNSENT = 0;
    const STATUS_SENT = 1;
    
    protected $_table = "email_queue";
    
    protected $_message;
    protected $_processed = false;
    protected $_data = array();
    
    public function getMessage($key = null, $default = null) {
        if (is_null($this->_message)) {
            $this->_message = new BTS_Model_Email_Message($this->getMessageId());
        }
        if (!is_null($key)) {
            if (isset($this->_message[$key])) {
                return $this->_message[$key];
            }
            else {
                return $default;
            }
        }
        else {
            return $this->_message;
        }
    }
    
    public function process() {
        if (is_null($this->_message)) {
            $this->_message = new BTS_Model_Email_Message($this->getMessageId());
            $this->getMessage()->setMessageData(array_merge($this->getMessageData(), array("unique_id" => $this->getUniqueId())));
        }
    }
    
    public function generateUnsubscribeAddress() {
        return "unsubscribe-" . $this->getUniqueId() . "@" . BTS_Base::getAppConfig()->bts->email->catchall_process_domain;
    }
    
    public function send() {
        if ($this->getSent() == 1) {
            throw new Exception("This message has already been sent.");
        }
        
        $blocked = new BTS_Model_Email_Blocked($this->getRecipientEmail(), "email_address");
        if ($blocked->isLoaded()) {
            $this->setSent(self::STATUS_BLOCKED);
            $this->save();
            return false;
        }
        
        if (!$this->_processed) {
            $this->process();
        }
        
        $config = BTS_Base::getAppConfig();
        
        $mail = new Zend_Mail();
        
        if ($this->getMessageType() & BTS_Model_Email_Message::TYPE_NEWSLETTER && $this->getMessageData('unsubscribe_link')) {
            $mail->addHeader("List-Unsubscribe", "<mailto:" . $this->generateUnsubscribeAddress() . ">");
        }
        
        $mail->addHeader("Content-ID", $this->getUniqueId());
        
        if (is_object($config->bts->email->return_path) && is_object($config->bts->email->return_path->address)) {
            $mail->setReturnPath($config->bts->email->return_path->address);
        }
        
        $mail->setSubject($this->getMessage()->getSubject());
        
        if ($this->getMessage()->getSenderEmail() != "" && $config->bts->email->sendmethod != "ses") {
            $mail->setFrom($this->getMessage()->getSenderEmail(), $this->getMessage()->getSenderName());
        }
        else {
            $mail->setFrom($config->bts->email->from_address, ($this->getMessage()->getSenderName() != "" ? $this->getMessage()->getSenderName() : $config->bts->email->from_name));
            if ($this->getMessageType() & BTS_Model_Email_Message::TYPE_NEWSLETTER) {
                $mail->setReplyTo($this->getMessage()->getSenderEmail());
            }
            else {
                if ($config->bts->email->reply_to) {
                    $mail->setReplyTo($config->bts->email->reply_to);
                }
            }
        }

        $mail->addTo($this->getRecipientEmail(), $this->getRecipientName());
        if ($this->getMessageType() & BTS_Model_Email_Message::TYPE_NOTIFICATION) {
            // insert your email here to get bcc'd any email that you sent with this class
            //$mail->addBcc("you@me.com");
        }
        
        $msgbody = $this->getMessage()->render();
        if ($this->getMessage()->getHtml() == 1) {
            $mail->setBodyHtml($msgbody);
        }
        else {
            $mail->setBodyText($msgbody);
        }
        
        switch ($config->bts->email->sendmethod) {
            case "ses":
                $sesConfig = array(
                    'accessKey' => $config->bts->email->ses->access_key,
                    'privateKey' => $config->bts->email->ses->secret_key
                );
                $transport = new BTS_Mail_Transport_AmazonSES($sesConfig);
                break;
            case "smtp":
                $smtpConfig = array(
                    'ssl' => $config->bts->email->smtp->ssl_mode,
                    'port' => $config->bts->email->smtp->port,
                    'auth' => $config->bts->email->smtp->auth,
                    'username' => $config->bts->email->smtp->username,
                    'password' => $config->bts->email->smtp->password
                );
                $transport = new Zend_Mail_Transport_Smtp($config->bts->email->smtp->host, $smtpConfig);
                break;
            case "native":
            default:
                $transport = new Zend_Mail_Transport_Sendmail("-f" . $mail->getReturnPath());
        }
        
        $mail->setDefaultTransport($transport);
        
        try {
            $mail->send();
            
            $this
                ->setEmailId($mail->getMessageId())
                ->setSent(self::STATUS_SENT)
                ->save()
                ;
            
            $this->getMessage()
                 ->setSentCount($this->getMessage()->getSentCount() + 1)
                 ->save();
            
            return true;
        }
        
        catch (Exception $e) {
            echo "Couldn't send email to '" . $this->getRecipientEmail() . "' via transport " . strtoupper($config->bts->email->sendmethod) . "\n";
            if (strtoupper($config->bts->email->sendmethod) == "SES") {
                $errorXml = @simplexml_load_string($e->getMessage());
                if ($errorXml instanceof SimpleXMLElement) {
                    echo "\tError was: " . $errorXml->Error->Code . ": " . $errorXml->Error->Message . "\n";
                
                    if ($errorXml->Error->Code == "InvalidParameterValue" || $errorXml->Error->Code == "MessageRejected") {
                        $this->setSent(self::STATUS_BLOCKED)->save();
                    }
                }
                else {
                    echo "\tUnknown error. Data returned was:\n" . $e->getMessage() . "\n";
                }
            }
            else {
                echo "\tError was: " . $e->getMessage() . "\n";
                print_r($e);
            }
            return false;
        }
    }
    public function setMessageData($data) {
        $this->setData('message_data', serialize($data));
        return $this;
    }
    public function getMessageData() {
        return unserialize($this->getData('message_data'));
    }
    public function generateUniqueId() {
        if ($this->getUniqueId() == "") {
            $this->setUniqueId(uniqid(null, true));
        }
    }
    public function _beforeSave() {
        parent::_beforeSave();
        $this->generateUniqueId();
        return $this;
    }
}
