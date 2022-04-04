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
}

?>