<?php

class AccountController{
    private $accountModel;

    public function __construct(){
        // create an account account model
        require "../Models/AccountModel.php";
        $this->accountModel = new AccountModel();
    }

    public function performAction($action){
        switch($action){
            case "signup":
                $this->SignUp();
                break;
            default:
                break;
        }
    }

    // sign up users
    public function SignUp(){
        // this array will be sent as a response to the client
        $response_array["already_loggedin"] = false;
        $response_array["signed_up"] = false;

        // check if the user is logged in
        if ($this->accountModel->auth->isLoggedIn()) {
            $response_array["already_loggedin"] = true;
            // redirect the user to their panel
            $response_array["redirect_url"] = "";
        } else if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["account_type"]) && isset($_POST["email"]) && isset($_POST["username"]) && isset($_POST["password"]) && isset($_POST["cpass"])){
            // sanitize the user's input
            $accountType = new Input($_POST["account_type"]);
            $accountType->Sanitize();
            $email = new Input($_POST["email"]);
            $email->Sanitize();
            $username = new Input($_POST["username"]);
            $username->Sanitize();
            $password = new Input($_POST["password"]);
            $password->Sanitize();
            $confrimPassword = new Input($_POST["cpass"]);
            $confrimPassword->Sanitize();

            // intialize error variables
            $response_array[$accountType->value . "_email_error"] = "";
            $response_array[$accountType->value . "_username_error"] = "";
            $response_array[$accountType->value . "_password_error"] = "";
            $response_array[$accountType->value . "_requests_error"] = "";

            // ensure a valid password length
            if(strlen($password->value) < 8 || strlen($password->value) > 255){
                $response_array[$accountType->value . "_password_error"] = "Votre mot de passe doit comporter entre 8 et 255 caractères<br>";
            }
            // ensure identical password fields
            else if(!empty($password->value) && $password->value !== $confrimPassword->value){
                $response_array[$accountType->value . "_password_error"] = "Vos mots de passe ne correspondent pas<br>";
            } 
            // ensure a valid username length
            else if(strlen($username->value) < 8 || strlen($username->value) > 20){
                $response_array[$accountType->value . "_username_error"] = "Le nom d'utilisateur doit comporter entre 8 et 20 caractères<br>";
            }
            // ensure a valid username structure 
            else if(!preg_match("/^[a-z]+_{0,1}[a-z]+[0-9]*$/", $username->value)){
                $response_array[$accountType->value . "_username_error"] = "Le nom d'utilisateur doit contenir juste des lettres miniscules, un _ optionel au milieu, et des numeros optionaux a la fin<br>";
            } else{
                if($accountType->value === "teacher"){

                } else if($accountType->value === "student"){
                    $inputsAreValidated = true;
                } else{
                    $inputsAreValidated = false;
                }

                // sign up the user only if all inputs were validated
                if($inputsAreValidated === true){
                    try {
                        $userId = $this->accountModel->auth->registerWithUniqueUsername($email->value, $password->value, $username->value);
                        // indicate the the user succuessfully signed up
                        $response_array["signed_up"] = true;
                        // redirect the user to the sign in page
                        $response_array["redirect_url"] = "http://localhost/pfe/Views/SignInView.html";
                    } catch (\Delight\Auth\InvalidEmailException $e) {
                        $response_array[$accountType->value . "_email_error"] = "Adresse email invalide<br>";
                    } catch (\Delight\Auth\InvalidPasswordException $e) {
                        $response_array[$accountType->value . "_password_error"] = "Mot de passe invalid<br>";
                    } catch (\Delight\Auth\UserAlreadyExistsException $e) {
                        $response_array[$accountType->value . "_email_error"] = "L'utilisateur existe déja<br>";
                    } catch (\Delight\Auth\DuplicateUsernameException $e){
                        $response_array[$accountType->value . "_username_error"] = "L'utilisateur existe déja<br>";
                    } catch (\Delight\Auth\TooManyRequestsException $e) {
                        $response_array[$accountType->value . "_requests_error"] = "Trop de demandes<br>";
                    }
                }
            }
        }

        echo json_encode($response_array);
    }
}

// a request has been sent from a view
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"])){
    // require the input sanitizer
    require "InputController.php";

    // retrieve the action
    $action = new Input($_POST["action"]);
    $action->Sanitize();

    // perform the action
    $account = new AccountController();
    $account->performAction($action->value);
}

?>