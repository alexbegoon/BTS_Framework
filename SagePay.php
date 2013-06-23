<?php

class BTS_SagePay {
    const VPS_PROTOCOL = "2.23";
    
    private $_config;
    private static $_instance;
    
    static function generateSubscriptionId() {
        return uniqid('', true);
    }
    
    static function instance() {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function fetchResponse(array $data) {
        //var_dump($data);
        $strData = http_build_query($data);
        $encrStr = BTS_SagePay_Crypt::encrypt($strData);
        
	$curlSession = curl_init();

        switch ($data['TxType']) {
            case "3DCALLBACK":
                $url = $this->getConfig()->url->{strtolower($this->getConfig()->environment)}->threedcallback;
                break;
            case "REPEAT":
                $url = $this->getConfig()->url->{strtolower($this->getConfig()->environment)}->repeat;
                break;
            case "PAYMENT":
            default:
                $url = $this->getConfig()->url->{strtolower($this->getConfig()->environment)}->purchase;
        }
        
	curl_setopt($curlSession, CURLOPT_URL, $url);
	curl_setopt($curlSession, CURLOPT_HEADER, 0);
	curl_setopt($curlSession, CURLOPT_POST, 1);
	curl_setopt($curlSession, CURLOPT_POSTFIELDS, $strData);
	curl_setopt($curlSession, CURLOPT_RETURNTRANSFER,1); 
	curl_setopt($curlSession, CURLOPT_TIMEOUT,30); 
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curlSession, CURLOPT_SSL_VERIFYHOST, 2);

        $rawresponse = curl_exec($curlSession);
        
        $output = new BTS_SagePay_Transaction_Response();
        
        if (curl_error($curlSession)){
            $output->Status = "FAIL";
            $output->StatusDetail = curl_error($curlSession);
	}
        else {
            $response = explode(chr(10), $rawresponse);
            
            for ($i=0; $i<count($response); $i++){
		$splitAt = strpos($response[$i], "=");
                $key = trim(substr($response[$i], 0, $splitAt));
                $value = trim(substr($response[$i], ($splitAt+1)));
                if ($key == "") continue;
		$output->setData($key, $value);
            }
        }
        
        curl_close($curlSession);
        
        return $output;
    }
    
    public function getConfig() {
        if (!$this->_config) {
            $this->_config = new Zend_Config_Ini(APPLICATION_PATH . "/configs/sagepay.ini", APPLICATION_ENV);
        }
        return $this->_config;
    }
}