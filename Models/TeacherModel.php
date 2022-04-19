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

    // check if an offer's details already exist
    public function OfferDetailsExist($columnsValues){
        $stmt = $this->dbconn->prepare("SELECT id FROM offers WHERE teacher_id = :teacher_id AND state = :state AND commune = :commune AND level = :level AND subject = :subject AND price = :price");
        $stmt->bindParam(":teacher_id", $columnsValues["teacher_id"]);
        $stmt->bindParam("state", $columnsValues["state"]);
        $stmt->bindParam(":commune", $columnsValues["commune"]);
        $stmt->bindParam(":level", $columnsValues["level"]);
        $stmt->bindParam(":subject", $columnsValues["subject"]);
        $stmt->bindParam(":price", $columnsValues["price"]);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(empty($result)){
            return false;
        } else{
            return true;
        }
    }

    // get the teacher id using the user id
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

    // retrieve a category's teacher's offers
    public function RetrieveTeacherOffersDetails($teacherId, $status){
        // retrieve the offers
        $stmt = $this->dbconn->prepare("SELECT * FROM offers WHERE teacher_id = :teacher_id AND status = :status");
        $stmt->bindParam(":teacher_id", $teacherId);
        $stmt->bindParam(":status", $status);
        $stmt->execute();
        $offersList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $offersList;
    }

    // check if the offer can be updated (modified or deleted) by a teacher
    public function OfferUpdatableByTeacher($offerId, $teacherId, $acceptedStatuses){
        // a string of questions marks to bind the $acceptedStatuses array
        $qMarks = str_repeat('?,', count($acceptedStatuses) - 1) . '?';
        $stmt = $this->dbconn->prepare("SELECT id FROM offers WHERE id = ? AND teacher_id = ? AND status IN ($qMarks)");

        // create an array to bind the parameters
        $columnsValues = array($offerId, $teacherId);
        // push each accepted status to the parameters array
        foreach($acceptedStatuses as $status){
            array_push($columnsValues, $status);
        }

        $stmt->execute($columnsValues);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(empty($result)){
            return false;
        } else{
            return true;
        }
    }

    // update the details of an offer
    public function UpdateOfferDetails($columnsValues){
        $stmt = $this->dbconn->prepare("UPDATE offers SET status = :status, state = :state, commune = :commune, level = :level, subject = :subject, price = :price WHERE id = :offer_id");
        $stmt->bindParam(":status", $columnsValues["status"]);
        $stmt->bindParam(":state", $columnsValues["state"]);
        $stmt->bindParam(":commune", $columnsValues["commune"]);
        $stmt->bindParam(":level", $columnsValues["level"]);
        $stmt->bindParam(":subject", $columnsValues["subject"]);
        $stmt->bindParam(":price", $columnsValues["price"]);
        $stmt->bindParam(":offer_id", $columnsValues["offer_id"]);
        $stmt->execute();
    }
} 

?>