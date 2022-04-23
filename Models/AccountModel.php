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

    // get the password associated to a username or an email
    public function getPasswordByUsernameOrEmail($usernameOrEmail){
        $stmt = $this->dbconn->prepare("SELECT password FROM users WHERE username = :username_or_email OR email = :username_or_email");
        $stmt->bindParam(":username_or_email", $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(empty($result)){
            return "";
        } else{
            return $result[0]["password"];
        };
    }

    // retrieve the offer's number of each category
    public function OfferCategoriesNumber($teacherId){
        // this is in case the teacher controller called this method
        if($teacherId !== "all"){
            $whereClause = " WHERE teacher_id = :teacher_id";
        } else{
            $whereClause = "";
        }

        $stmt = $this->dbconn->prepare("SELECT SUM(IF(status = 0, 1, 0)) AS pending, SUM(IF(status = 1, 1, 0)) AS validated, SUM(IF(status = 2, 1, 0)) AS refused FROM offers" . $whereClause);
        $stmt->bindParam(":teacher_id", $teacherId);
        $stmt->execute();
        $offersNumber = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $offersNumber;
    }

    // retrieve offers
    public function RetrieveOffers($query, $params){
       // retrieve the offers
       $stmt = $this->dbconn->prepare($query);
       $stmt->execute($params);
       $offersList = $stmt->fetchAll(PDO::FETCH_ASSOC);
       return $offersList;
    }

    // retrieve an offer's teacher info
    public function RetrieveOfferTeacherInfo($offerId){
        $stmt = $this->dbconn->prepare("SELECT users.email, users.username, teachers.first_name, teachers.last_name, teachers.phone, teachers.card_photo, teachers.teacher_photo, teachers.cv_link FROM offers INNER JOIN teachers ON offers.teacher_id = teachers.id INNER JOIN users ON teachers.user_id = users.id WHERE offers.id = :offer_id");
        $stmt->bindParam(":offer_id", $offerId);
        $stmt->execute();
        $teacherInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $teacherInfo;
    }

    // check if an offer has a given status
    public function OfferHasStatus($offerId, $status){
        $stmt = $this->dbconn->prepare("SELECT id FROM offers WHERE id = :id AND status = :status");
        $stmt->bindParam(":id", $offerId);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(empty($result)){
            return false;
        } else{
            return true;
        }
    }

    // get the offer refusal info
    public function GetOfferRefusal($offerId){
        $stmt = $this->dbconn->prepare("SELECT * FROM offers_refusals WHERE offer_id = :offer_id");
        $stmt->bindParam(":offer_id", $offerId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    // check if a teacher has a given status
    public function TeacherHasStatus($query, $param){
        $stmt = $this->dbconn->prepare($query);
        $stmt->execute($param);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(empty($result)){
            return false;
        } else{
            return true;
        }
    }

    // delete an offer's rating details
    public function DeleteOfferRatings($offerId){
        $stmt = $this->dbconn->prepare("DELETE FROM ratings WHERE offer_id = :offer_id");
        $stmt->bindParam(":offer_id", $offerId);
        $stmt->execute();
    }

    // delete an offer's refusal info
    public function DeleteOfferRefusal($offerId){
        $stmt = $this->dbconn->prepare("DELETE FROM offers_refusals WHERE offer_id = :offer_id");
        $stmt->bindParam(":offer_id", $offerId);
        $stmt->execute();
    }

    // delete an offer
    public function DeleteOffer($offerId){
        $stmt = $this->dbconn->prepare("DELETE FROM offers WHERE id = :id");
        $stmt->bindParam(":id", $offerId);
        $stmt->execute();
    }

    // check if an offer belongs to a given teacher
    public function TeacherOwnsOffer($offerId, $userId){
        $stmt = $this->dbconn->prepare("SELECT id FROM offers WHERE id = :offer_id AND teacher_id = (SELECT id FROM teachers WHERE user_id = :user_id)");
        $stmt->bindParam(":offer_id", $offerId);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(empty($result)){
            return false;
        } else{
            return true;
        }
    }

    // insert an offer's rating
    public function InsertOfferRating($userId, $offerId, $rating){
        $stmt = $this->dbconn->prepare("SELECT id FROM ratings WHERE user_id = :user_id AND offer_id = :offer_id");
        $stmt->bindParam(":user_id", $userId);
        $stmt->bindParam(":offer_id", $offerId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(empty($result)){
            $stmt = $this->dbconn->prepare("INSERT INTO ratings (user_id, offer_id, rating) VALUES (:user_id, :offer_id, :rating)");
            $stmt->bindParam(":user_id", $userId);
            $stmt->bindParam(":offer_id", $offerId);
            $stmt->bindParam(":rating", $rating);
            $stmt->execute();
        } else{
            $this->UpdateOfferRating($userId, $offerId, $rating);
        }
    }

    // update a offer's rating
    public function UpdateOfferRating($userId, $offerId, $rating){
        $stmt = $this->dbconn->prepare("UPDATE ratings SET rating = :rating WHERE offer_id = :offer_id AND user_id = :user_id");
        $stmt->bindParam(":rating", $rating);
        $stmt->bindParam(":offer_id", $offerId);
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
    }

    // retrieve an offer's rating details
    public function RetrieveOfferRatings($offerId){
        $stmt = $this->dbconn->prepare("SELECT COUNT(id) as rates_number, ROUND(AVG(rating), 1) as avg_rating FROM ratings WHERE offer_id = :offer_id");
        $stmt->bindParam(":offer_id", $offerId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    // retrieve a user's rating of an offer
    public function RetrieveUserOfferRating($userId, $offerId){
        $stmt = $this->dbconn->prepare("SELECT rating FROM ratings WHERE user_id = :user_id AND offer_id = :offer_id");
        $stmt->bindParam(":user_id" , $userId);
        $stmt->bindParam(":offer_id" , $offerId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}

?>