<?php

class BTS_Model_Email_Message extends BTS_Model {
    
    const PROCESS_UNPROCESSED = 0;
    const PROCESS_IN_PROCESS = 1;
    const PROCESS_COMPLETE = 2;
    
    const TYPE_NOTIFICATION = 1;
    const TYPE_NEWSLETTER = 2;

    protected $_table = "email_messages";
    
    protected $_messageData = array();
    
    public function getUnsentQueueItems($count = 0) {
        $queue = new BTS_Model_Email_Queue_Collection();
        $queue->addWhere("message_id", "=", $this->getId());
        $queue->addWhere("sent", "=", self::PROCESS_UNPROCESSED);
        
        if ($count > 0) {
            $queue->setLimit($count);
        }
        
        return $queue;
    }
    
    public function setMessageData($data) {
        $this->_messageData = $data;
        return $this;
    }
    
    public function render() {
        $message = $this->getMessage();
        foreach ($this->_messageData as $key => $value) {
            $message = str_replace("[" . strtoupper($key) . "]", $value, $message);
        }
        
        if (isset($this->_messageData["unique_id"])) {
            $message = str_replace("[EXTERNAL_URL]", $this->_messageData["base_url"] . '/message/view/id/' . $this->_messageData["unique_id"], $message);
        
            if ($this->getTracking() == 1) {
                $message .= PHP_EOL;
                $message .= '<img src="' . $this->_messageData["base_url"] . '/message/track/id/' . $this->_messageData["unique_id"] . '/" width="1" height="1" />';
            }
        }
        
        return $message;
    }
}
