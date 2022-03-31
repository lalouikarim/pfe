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
} 

?>