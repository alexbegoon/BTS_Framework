<?php

class BTS_Model_User extends BTS_Model {
    protected $_table = "users";
    
    /**
     * Validate given password to stored password
     * @param string $password
     * @return bool
     */
    public function validatePassword($password) {
        return $this->getHasher()->CheckPassword($password, $this->getPassword());
    }
    
    /**
     * Set the user's password
     * @param string $password
     * @return \BTS_Model_User
     */
    public function setPassword($password) {
        $this->setData("password", $this->getHasher()->HashPassword($password));
        return $this;
    }
    
    /**
     * Get Password Hasher instance
     * @return \PasswordHash
     */
    public function getHasher() {
        $hasher = new PasswordHash(BTS_Base::getAppConfig()->passwordhash->iterations, false);
        return $hasher;
    }
}
