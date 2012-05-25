<?php

class BTS_SagePay_Crypt {
    
    public static function encrypt($strIn) {
        $obj = new self();
        return $obj->_encrypt($strIn);
    }
    
    public function _encrypt($strIn) {
        if ($this->_getConfig()->encryption_type == "XOR") {
            //** XOR encryption with Base64 encoding **
            $obj = new self();
            return base64_encode($obj->_simpleXor($strIn));
            //return base64Encode(simpleXor($strIn,$strEncryptionPassword));
	}
        else {
            //** AES encryption, CBC blocking with PKCS5 padding then HEX encoding - DEFAULT **

            //** use initialization vector (IV) set from $strEncryptionPassword
            $strIV = $this->_getConfig()->encryption_password;
    	
            //** add PKCS5 padding to the text to be encypted
            $strIn = $this->_addPKCS5Padding($strIn);

            //** perform encryption with PHP's MCRYPT module
            $strCrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->_getConfig()->encryption_password, $strIn, MCRYPT_MODE_CBC, $strIV);
		
            //** perform hex encoding and return
            return "@" . bin2hex($strCrypt);
	}
    }
    
    public static function decrypt($strIn) {
        $obj = new self();
        return $obj->_decrypt($strIn);
    }
    
    public function _decrypt($strIn) {
        if (substr($strIn,0,1)=="@") {
            //** HEX decoding then AES decryption, CBC blocking with PKCS5 padding - DEFAULT **
		
            //** use initialization vector (IV) set from $strEncryptionPassword
            $strIV = $this->_getConfig()->encryption_key;
    	
            //** remove the first char which is @ to flag this is AES encrypted
            $strIn = substr($strIn,1); 
    	
            //** HEX decoding
            $strIn = pack('H*', $strIn);
    	
            //** perform decryption with PHP's MCRYPT module
            return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->_getConfig()->encryption_key, $strIn, MCRYPT_MODE_CBC, $strIV); 
	} 
	else {
            //** Base 64 decoding plus XOR decryption **
            return $this->_simpleXor(base64Decode($strIn));
	}
    }
    
    private function _getConfig() {
        return BTS_SagePay::instance()->getConfig();
    }
    
    private function _addPKCS5Padding($input) {
        $blocksize = 16;
        $padding = "";

        // Pad input to an even block size boundary
        $padlength = $blocksize - (strlen($input) % $blocksize);
        for($i = 1; $i <= $padlength; $i++) {
            $padding .= chr($padlength);
        }
   
        return $input . $padding;
    }
    
    private function _simpleXor($InString) {
        $Key = $this->_getConfig()->encryption_key;
        // Initialise key array
        $KeyList = array();
        // Initialise out variable
        $output = "";

        // Convert $Key into array of ASCII values
        for($i = 0; $i < strlen($Key); $i++){
            $KeyList[$i] = ord(substr($Key, $i, 1));
        }

        // Step through string a character at a time
        for($i = 0; $i < strlen($InString); $i++) {
            // Get ASCII code from string, get ASCII code from key (loop through with MOD), XOR the two, get the character from the result
            // % is MOD (modulus), ^ is XOR
            $output.= chr(ord(substr($InString, $i, 1)) ^ ($KeyList[$i % strlen($Key)]));
        }

        // Return the result
        return $output;
    }
    
}
