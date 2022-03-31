<?php

class AccountModel{
    private $dbconnector;
    protected $dbconn;
    public $auth;

    public function __construct(){
        // connect to db
        require_once "../Config/DbConnect.php";
        $this->dbconnector = new DbConnect();
        $this->dbconn = $this->dbconnector->connect();

        // require the authentication library
        require '../Libraries/AuthLib/vendor/autoload.php';
        $this->auth = new \Delight\Auth\Auth($this->dbconn);
    }
}

?>