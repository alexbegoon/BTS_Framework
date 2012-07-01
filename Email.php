<?php

class BTS_Email extends BTS_Object {
    
    protected $_messageDataKeys = array();
    
    protected $_trackingFlag = false;
    protected $_html = false;
    
    public function __construct($template_file = null) {
        if (!is_null($template_file)) {
            if (!file_exists($template_file)) {
                throw new Exception("File '" . $template_file . "' doesn't exist");
            }

            $pathinfo = pathinfo($template_file);

            $contents = file_get_contents($template_file);

            $matches = array();
            preg_match_all("/@([a-z_]*):(.*)/", $contents, $matches);

            $i = 0;
            foreach ($matches[1] as $key) {
                $this->setData($key, $matches[2][$i]);
                $i++;
            }

            $matches = array();
            preg_match_all("/#([A-Za-z]*):(.*)/", $contents, $matches);

            $i = 0; $prepend = "";
            foreach ($matches[1] as $key) {
                switch ($key) {
                    case "include":
                        $filepath = $pathinfo['dirname'] . "/" . $matches[2][$i];
                        $include_file = pathinfo($filepath);
                        switch ($include_file['extension']) {
                            case "css":
                                $prepend .= '<style type="text/css">' . PHP_EOL;
                                $prepend .= file_get_contents($filepath) . PHP_EOL;
                                $prepend .= '</style>' . PHP_EOL;
                                break;
                        }
                        break;
                }
            }

            $contents = $prepend . $contents;

            $this->setData('message', preg_replace("/\<!--([^\-\-\>]*)--\>/", "", $contents));
            $this->setData('original_message', $this->getData('message'));

            preg_match_all("/\[([A-Z_]*)\]/", $this->getMessage(), $matches);
            foreach ($matches[1] as $match) {
                $this->_messageDataKeys[strtolower($match)] = null;
            }
            
            $this->_html = true;
        }
    }
    
    public function setBody($txt, $html = false) {
        $this->setData("original_message", $txt);
        $this->setData("message", $txt);
        $this->_html = $html;
        return $this;
    }
    
    public function setMessageData($key, $value = null) {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->setMessageData($k, $v);
            }
        }
        else {
            // base_url is a special case..
            if (!array_key_exists($key, $this->_messageDataKeys) && $key != "base_url") {
                throw new Exception("Key '" . $key . "' is not defined in this message.");
            }
            $this->_messageDataKeys[$key] = $value;
        }
        
        return $this;
    }
    public function getMessageDataKeys() {
        return array_keys($this->_messageDataKeys);
    }
    
    public function getMessage() {
        $message = $this->getData('original_message');
        
        $default_keys = $this->_getDefaultMessageData();
        
        foreach (array_merge($this->_messageDataKeys, $default_keys) as $key => $value) {
            $message = str_replace("[" . strtoupper($key) . "]", $value, $message);
        }
        $this->setData('message', $message);
        return $message;
    }
    // designed to be overriden
    protected function _getDefaultMessageData() { return array(); }
    
    public function enableOpenTracking() {
        $this->_trackingFlag = true;
        return $this;
    }
    
    public function send($email, $appears_name = null, $send_now = false) {
        $message = new BTS_Model_Email_Message();
        $user = BTS_Base::getActiveUser();
        
        $message->setData(
                array(
                    "user_id" => ($user instanceof BTS_Model ? $user->getId() : null),
                    "subject" => $this->getSubject(),
                    "message" => $this->getData('original_message'),
                    "html" => ($this->_html ? "1" : "0"),
                    "tracking" => ($this->_trackingFlag ? "1" : "0"),
                    "processing" => ($send_now ? BTS_Model_Email_Message::PROCESS_IN_PROCESS : BTS_Model_Email_Message::PROCESS_UNPROCESSED)
                ));
        if ($this->hasSenderEmail()) {
            $message->setSenderEmail($this->getSenderEmail());
        }
        if ($this->hasSenderName()) {
            $message->setSenderName($this->getSenderName());
        }
        
        $message->save();
        
        $queue = new BTS_Model_Email_Queue();
        $queue->setData(
                array(
                    "message_id" => $message->getId(),
                    "recipient_email" => $email,
                    "recipient_name" => $appears_name
                ));
        $queue->setMessageData(array_merge($this->_messageDataKeys, $this->_getDefaultMessageData()));
        $queue->save();
        
        if ($send_now) {
            if ($queue->send()) {
                $message
                    ->setProcessing(BTS_Model_Email_Message::PROCESS_COMPLETE)
                    ->setSentCount(1)
                    ->save();
            }
            
        }
        
        return $this;
    }
    
    public function sendNow($email, $appears_name = null) {
        $this->send($email, $appears_name, true);
        return $this;
    }
    
}
