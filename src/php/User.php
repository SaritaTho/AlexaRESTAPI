<?php

/**
 * A class representing a user
 */
class User {

    /**
     *
     * @var string The user's email address
     */
    public $email;

    /**
     *
     * @var string The user's login token
     */
    public $token;

    /**
     *
     * @var integer The user's internal ID
     */
    public $userid;

    public function __construct() {
	
    }

    public function getEmail() {
	return $this->email;
    }

    public function getToken() {
	return $this->token;
    }

    public function getUserid() {
	return $this->userid;
    }

    public function setEmail($email) {
	$this->email = $email;
	return $this;
    }

    public function setToken($token) {
	$this->token = $token;
	return $this;
    }

    public function setUserid($userid) {
	$this->userid = $userid;
	return $this;
    }

}
