<?php

class AdminModel extends AccountModel{
    // retreive the necessary offer details to send an email to the teacher
    public function RetrieveOfferForEmail($offerId){
        $stmt = $this->dbconn->prepare("SELECT users.email, offers.state, offers.commune, offers.level, offers.subject FROM offers INNER JOIN teachers ON offers.teacher_id = teachers.id INNER JOIN users ON teachers.user_id = users.id WHERE offers.id = :offer_id");
        $stmt->bindParam(":offer_id", $offerId);
        $stmt->execute();
        $teacherEmail = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $teacherEmail;
    }

    // change the status of an offer
    public function ChangeOfferStatus($offerId, $status){
        $stmt = $this->dbconn->prepare("UPDATE offers SET status = :status WHERE id = :id");
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $offerId);
        $stmt->execute();
    }

    // insert the offer refusal reason
    public function InsertOfferRefusal($columnsValues){
        $stmt = $this->dbconn->prepare("INSERT INTO offers_refusals (offer_id, refusal_reason) VALUES (:offer_id, :refusal_reason)");
        $stmt->bindParam(":offer_id", $columnsValues["offer_id"]);
        $stmt->bindParam(":refusal_reason", $columnsValues["refusal_reason"]);
        $stmt->execute();
    }

    // retrieve the teachers singups' number of each category
    public function TeachersSignUpsCategoriesNumber(){
        $stmt = $this->dbconn->prepare("SELECT SUM(IF(sign_up_status = 0, 1, 0)) AS pending, SUM(IF(sign_up_status = 1, 1, 0)) AS validated FROM teachers");
        $stmt->execute();
        $signUpsNumber = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $signUpsNumber;
    }

    // retrieve all teachers
    public function RetrieveTeachersSignUps($status){
        $stmt = $this->dbconn->prepare("SELECT users.id AS user_id, users.email, users.username, teachers.id AS teacher_id, teachers.sign_up_status, teachers.first_name, teachers.last_name, teachers.phone, teachers.card_photo, teachers.teacher_photo, teachers.cv_link FROM teachers INNER JOIN users ON teachers.user_id = users.id WHERE teachers.sign_up_status = :sign_up_status");
        $stmt->bindParam(":sign_up_status", $status);
        $stmt->execute();
        $signUpsList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $signUpsList;
    }

    // get the teacher's info by id
    public function GetTeacherInfoById($teacherId){
        $stmt = $this->dbconn->prepare("SELECT users.id AS user_id, users.email, teachers.id AS teacher_id, teachers.first_name, teachers.last_name, teachers.card_photo, teachers.teacher_photo FROM teachers INNER JOIN users ON teachers.user_id = users.id WHERE teachers.id = :teacher_id");
        $stmt->bindParam(":teacher_id", $teacherId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }

    // validate or refuse a teacher
    public function ChangeTeacherStatus($teacherId, $status){
        $stmt = $this->dbconn->prepare("UPDATE teachers SET sign_up_status = :sign_up_status WHERE id = :teacher_id");
        $stmt->bindParam(":sign_up_status", $status);
        $stmt->bindParam(":teacher_id", $teacherId);
        $stmt->execute();
    }

    // delete a teacher
    public function DeleteTeacher($teacherId){
        $stmt = $this->dbconn->prepare("DELETE FROM teachers WHERE id = :teacher_id");
        $stmt->bindParam(":teacher_id", $teacherId);
        $stmt->execute();
    }

    // get the offers of a teacher
    public function GetOffersIdsByTeacherId($teacherId){
        $stmt = $this->dbconn->prepare("SELECT offers.id AS offer_id FROM offers INNER JOIN teachers ON offers.teacher_id = teachers.id WHERE teachers.id = :teacher_id");
        $stmt->bindParam(":teacher_id", $teacherId);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result;
    }
}

?>