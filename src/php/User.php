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
    public $userId;

    public function __construct() {
	
    }

    public function getEmail() {
	return $this->email;
    }

    public function getToken() {
	return $this->token;
    }

    public function getUserId() {
	return $this->userId;
    }

    public function setEmail($email) {
	$this->email = $email;
	return $this;
    }

    public function setToken($token) {
	$this->token = $token;
	return $this;
    }

    public function setUserId($userId) {
	$this->userId = $userId;
	return $this;
    }

}
