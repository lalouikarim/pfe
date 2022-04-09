<?php

class TeacherModel extends AccountModel{
    // store a teacher's info
    public function StoreTeacherInfo($columnsValues){
        $stmt = $this->dbconn->prepare("INSERT INTO teachers (user_id, sign_up_status, first_name, last_name, phone, card_photo, teacher_photo, cv_link) VALUES (:user_id, :sign_up_status, :first_name, :last_name, :phone, :card_photo, :teacher_photo, :cv_link)");
        $stmt->bindParam(":user_id", $columnsValues["user_id"]);
        $stmt->bindParam(":sign_up_status", $columnsValues["sign_up_status"]);
        $stmt->bindParam(":first_name", $columnsValues["first_name"]);
        $stmt->bindParam(":last_name", $columnsValues["last_name"]);
        $stmt->bindParam(":phone", $columnsValues["phone"]);
        $stmt->bindParam(":card_photo", $columnsValues["card_photo"]);
        $stmt->bindParam(":teacher_photo", $columnsValues["teacher_photo"]);
        $stmt->bindParam(":cv_link", $columnsValues["cv_link"]);
        $stmt->execute();
    }

    // get the status of a teacher's account
    public function GetTeacherStatus($usernameOrEmail){
        $stmt = $this->dbconn->prepare("SELECT sign_up_status FROM teachers WHERE user_id = (SELECT id FROM users WHERE username = :username_or_email OR email = :username_or_email)");
        $stmt->bindParam(":username_or_email", $usernameOrEmail);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result[0]["sign_up_status"];
    }

    // check if an offer's details already exist
    public function OfferDetailsExist($columnsValues){
        $stmt = $this->dbconn->prepare("SELECT id FROM offers WHERE teacher_id = :teacher_id AND state = :state AND commune = :commune AND level = :level AND subject = :subject");
        $stmt->bindParam(":teacher_id", $columnsValues["teacher_id"]);
        $stmt->bindParam("state", $columnsValues["state"]);
        $stmt->bindParam(":commune", $columnsValues["commune"]);
        $stmt->bindParam(":level", $columnsValues["level"]);
        $stmt->bindParam(":subject", $columnsValues["subject"]);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(empty($result)){
            return false;
        } else{
            return true;
        }
    }

    // get the teacher id
    public function GetTeacherId($userId){
        $stmt = $this->dbconn->prepare("SELECT id FROM teachers WHERE user_id = :user_id");
        $stmt->bindParam(":user_id", $userId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    // add an offer to db
    public function AddOffer($columnsValues){
        $stmt = $this->dbconn->prepare("INSERT INTO offers (status, teacher_id, state, commune, level, subject, price) VALUES (:status, :teacher_id, :state, :commune, :level, :subject, :price)");
        $stmt->bindParam(":status", $columnsValues["status"]);
        $stmt->bindParam(":teacher_id", $columnsValues["teacher_id"]);
        $stmt->bindParam("state", $columnsValues["state"]);
        $stmt->bindParam(":commune", $columnsValues["commune"]);
        $stmt->bindParam(":level", $columnsValues["level"]);
        $stmt->bindParam(":subject", $columnsValues["subject"]);
        $stmt->bindParam(":price", $columnsValues["price"]);
        $stmt->execute();
    }

    // retrieve the teacher' offer's number of each category
    public function OfferCategoriesNumber($teacherId){
        $stmt = $this->dbconn->prepare("SELECT SUM(IF(status = 0, 1, 0)) AS pending, SUM(IF(status = 1, 1, 0)) AS validated, SUM(IF(status = 2, 1, 0)) AS refused FROM offers WHERE teacher_id = :teacher_id");
        $stmt->bindParam(":teacher_id", $teacherId);
        $stmt->execute();
        $offersNumber = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $offersNumber;
    }

    // retrieve a category's teacher's offers
    public function RetrieveTeacherOffers($teacherId, $status){
        // retrieve the offers
        $stmt = $this->dbconn->prepare("SELECT * FROM offers WHERE teacher_id = :teacher_id AND status = :status");
        $stmt->bindParam(":teacher_id", $teacherId);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        $offersList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $offersList;
    }
} 

?>