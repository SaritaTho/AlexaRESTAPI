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
    public $id;

    public function __construct() {
	
    }
}
