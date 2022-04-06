<?php

class AdminModel extends AccountModel{
    // retrieve the offer's number of each category
    public function OfferCategoriesNumber(){
        $stmt = $this->dbconn->prepare("SELECT SUM(IF(status = 0, 1, 0)) AS pending, SUM(IF(status = 1, 1, 0)) AS validated, SUM(IF(status = 2, 1, 0)) AS refused FROM offers");
        $stmt->execute();
        $offersNumber = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $offersNumber;
    }

    // retrieve a category's offers
    public function RetrieveOffers($status){
        // retrieve the offers
        $stmt = $this->dbconn->prepare("SELECT * FROM offers WHERE status = :status");
        $stmt->bindParam(":status", $status);
        $stmt->execute();
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

    // retreive the necessary offer details to send an email to the teacher
    public function RetrieveOfferForEmail($offerId){
        $stmt = $this->dbconn->prepare("SELECT users.email, offers.state, offers.commune, offers.level, offers.subject FROM offers INNER JOIN teachers ON offers.teacher_id = teachers.id INNER JOIN users ON teachers.user_id = users.id WHERE offers.id = :offer_id");
        $stmt->bindParam(":offer_id", $offerId);
        $stmt->execute();
        $teacherEmail = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $teacherEmail;
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

    // change the status of an offer
    public function ChangeOfferStatus($offerId, $status){
        $stmt = $this->dbconn->prepare("UPDATE offers SET status = :status WHERE id = :id");
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":id", $offerId);
        $stmt->execute();
    }
}

?>