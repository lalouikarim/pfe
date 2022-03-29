<?php

class Input{
    public $value;

    public function __construct($value){
        $this->value = $value;
    }

    public function Sanitize(){
        $this->value= trim($this->value);
        $this->value= stripslashes($this->value);
        $this->value = htmlspecialchars($this->value);
    }
}

?>