<?php

class AccountModel{
    private $dbconnector;
    protected $dbconn;
    public $auth;
    public $role;

    public function __construct(){
        // connect to db
        require_once "../Config/DbConnect.php";
        $this->dbconnector = new DbConnect();
        $this->dbconn = $this->dbconnector->connect();

        // require the authentication library
        require '../Libraries/AuthLib/vendor/autoload.php';
        $this->auth = new \Delight\Auth\Auth($this->dbconn);
    }

    // set the role of a user
    public function SetAccountRole($usernameOrEmail){
        $stmt = $this->dbconn->prepare("SELECT id FROM teachers WHERE user_id = (SELECT id FROM users WHERE username = :username_or_email OR email = :username_or_email)");
        $stmt->bindParam(":username_or_email", $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(empty($result)){
            $stmt = $this->dbconn->prepare("SELECT id FROM admins WHERE user_id = (SELECT id FROM users WHERE username = :username_or_email OR email = :username_or_email)");
            $stmt->bindParam(":username_or_email", $usernameOrEmail);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(empty($result)){
                $this->role = "student";
            } else{
                $this->role = "admin";
            }
        } else{
            $this->role = "teacher";
        }
    }
}

?>