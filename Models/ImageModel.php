<?php

class ImageModel{
    private $imgInput;
    private $imgContent;
    private $dbconnector;
    private $dbconn;
    private $imgFolder;
    public $imgName;

    public function __construct($imgInput, $imgFolder){
        $this->imgInput = $imgInput;
        $this->imgFolder = $imgFolder;

        // connect to db
        require_once "../Config/DbConnect.php";
        $this->dbconnector = new DbConnect();
        $this->dbconn = $this->dbconnector->connect();
    }

    // extract the content of an image input
    private function ExtractContent(){
        $this->imgInput = str_replace('data:image/jpeg;base64,', '', $this->imgInput);
        $this->imgInput = str_replace(' ', '+', $this->imgInput);
        $this->imgContent = base64_decode($this->imgInput);
    }

    // check if a file is a valid image
    public function ValidateFile(){
        // check if it's a valid mime type (jpeg)
        if(strpos($this->imgInput, "image/jpeg")){
            // extract the content of the image
            $this->ExtractContent();
        } else{
            return false;
        }

        // create a tmp file for the image to validate it
        $imgTmpFile = tmpfile();
        if(!$imgTmpFile){
            return false;
        }
        // write the content of the image to the tmp file
        if(!fwrite($imgTmpFile, $this->imgContent)){
            return false;
        }
        // move the position indicator to the beginning of the tmp file
        fseek($imgTmpFile, 0);
        // get stats on the tmp file
        $imgTmpFileStats = fstat($imgTmpFile);
        if(!$imgTmpFileStats){
            return false;
        }

        // check if the image is of valid size
        if($imgTmpFileStats["size"] < 67 || $imgTmpFileStats["size"] > 2000000){
            return false;
        }

        // check if the file is an actual image
        // assign the path of the tmp file
        $imgTmpFilePath = stream_get_meta_data($imgTmpFile)['uri'];
        // create a new image based on the uploaded file
        $newImage = call_user_func("imagecreatefromjpeg", $imgTmpFilePath);
        // if the image wasn't successfully created then the file should be considered as invalid
        if($newImage === false){
            return false;
        }

        // all the tests have been successfully passed and the image is valid
        return true;
    }

    // check if an image name already exists
    private function imgNameExists(){
        $stmt = $this->dbconn->prepare("SELECT user_id FROM teachers WHERE card_photo = :photo OR teacher_photo = :photo LIMIT 1");
        $stmt->bindParam(":photo", $this->imgName);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if(empty($result)){
            return false;
        } else{
            return true;
        }
    }

    // assign a path to a file
    private function FilePath(){
        // assign an initial file name
        $this->imgName = uniqid() . ".jpeg";
        // ensure a unique file name
        while($this->imgNameExists()){
            $this->imgName = uniqid() . ".jpeg";
        }

        return "../Images/" . $this->imgFolder . "/" . md5($this->imgName) . ".jpeg";
    }

    // store an image in the filesystem
    public function StoreFile(){
        if(file_put_contents($this->FilePath(), $this->imgContent) === false){
            return false;
        } else{
            return true;
        }
    }
}

?>