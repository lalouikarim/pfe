<?php

class DbConnect{
    private $host;
    private $username;
    private $password;
    private $dbname;

    public function __construct(){
        $this->host = "localhost";
        $this->username = "root";
        $this->password = "";
        $this->dbname = "pfe_db";
    }

    // connect to db
    public function connect(){
        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->dbname;
            $conn = new PDO($dsn, $this->username, $this->password);
            // set the PDO error mode to exception
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conn;
        } catch(PDOException $e) {
            return null;
        }
    }
}

?>