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
            case "signin":
                $this->SignIn();
                break;
            default:
                $this->SignOut();
                break;
        }
    }

    // sign up users
    public function SignUp(){
        $inputsAreValidated = false;
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
            $response_array["first_name_error"] = $response_array["last_name_error"] = $response_array["phone_error"] = "";
            $response_array["cv_link_error"] = $response_array["card_img_error"] = $response_array["teacher_img_error"] = "";

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
                    if(isset($_POST["first_name"]) && isset($_POST["last_name"]) && isset($_POST["phone"]) && isset($_POST["cv_link"]) && isset($_POST["card_img"]) && isset($_POST["teacher_img"])){
                        // require the image model
                        require "../Models/ImageModel.php";

                        // create image models
                        $cardImgInput = new Input($_POST["card_img"]);
                        $cardImgInput->Sanitize();
                        $cardImg = new ImageModel($cardImgInput->value, "IDCards");
                        $teacherImgInput = new Input($_POST["teacher_img"]);
                        $teacherImgInput->Sanitize();
                        $teacherImg = new ImageModel($teacherImgInput->value, "Teachers");
                        // validate the images
                        if(!$cardImg->ValidateFile()){
                            $response_array["card_img_error"] = "Veuillez choisir une photo valide de votre carte d'identité";
                        }
                        if(!$teacherImg->ValidateFile()){
                            $response_array["teacher_img_error"] = "Veuillez choisir une photo valide de vous";
                        }

                        // proceed only if the images are valid
                        if($response_array["card_img_error"] === "" && $response_array["teacher_img_error"] === ""){
                            // sanitize the rest of the user's input
                            $firstName = new Input($_POST["first_name"]);
                            $firstName->Sanitize();
                            $lastName = new Input($_POST["last_name"]);
                            $lastName->Sanitize();
                            $phone = new Input($_POST["phone"]);
                            $phone->Sanitize();
                            $cvLink = new Input($_POST["cv_link"]);
                            $cvLink->Sanitize();
                            
                            // ensure a valid first name format
                            if(!preg_match("/^[A-Z][a-z]+$/", $firstName->value)){
                                $response_array["first_name_error"] = "Le prénom doit avoir la première lettre en majuscule et le reste en miniscule";
                            }
                            // ensure a valid last name format
                            else if(!preg_match("/^[A-Z]+$/", $lastName->value)){
                                $response_array["last_name_error"] = "Le nom doit comporter juste des lettres majuscules";
                            }
                            // ensure a valid phone number format
                            else if(!preg_match("/^0[5-7]{1}[0-9]{2}(\s[0-9]{2}){3}$/", $phone->value)){
                                $response_array["phone_error"] = "Veuillez choisir un numero de ce format: 0698 66 77 10";
                            }
                            // ensure a valid link format
                            else if(!filter_var($cvLink->value, FILTER_VALIDATE_URL)){
                                $response_array["cv_link_error"] = "Veuillez choisir un lien valide";
                            }
                            // all inputs were validated
                            else{
                                $inputsAreValidated = true;
                            }
                        }
                    }
                } else if($accountType->value === "student"){
                    $inputsAreValidated = true;
                }

                // sign up the user only if all inputs were validated
                if($inputsAreValidated === true){
                    try {
                        // register the user
                        $userId = $this->accountModel->auth->registerWithUniqueUsername($email->value, $password->value, $username->value);
                        
                        // if it's a teacher then store their info
                        if($accountType->value === "teacher"){
                            // require the teacher model
                            require "../Models/TeacherModel.php";
                            // create a teacher model
                            $teacherModel = new TeacherModel();
                            // store the images in the filesystem
                            $cardImg->StoreFile();
                            $teacherImg->StoreFile();
                            // store the teacher's info (the teacher account is disabled until validated by an admin)
                            $teacherModel->StoreTeacherInfo(array("user_id" => $userId, "sign_up_status" => 0, "first_name" => $firstName->value, "last_name" => $lastName->value, "phone" => $phone->value, "card_photo" => $cardImg->imgName, "teacher_photo" => $teacherImg->imgName, "cv_link" => $cvLink->value));
                        }
                        
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

    // sign in users
    public function SignIn(){
        $canLogin = false;
        // this array will be sent as a response to the client
        $response_array["error"] = "";
        $response_array["signed_in"] = false;
        // don't keep the user logged in after the session ends
        $rememberDuration = null;

        // check if the user is logged in
        if ($this->accountModel->auth->isLoggedIn()){
            $response_array["signed_in"] = true;
            // set the role of the account
            $this->accountModel->SetAccountRole($this->accountModel->auth->getEmail());
            // redirect the user based on their role
            if($this->accountModel->role === "teacher"){
                $response_array["redirect_url"] = "http://localhost/pfe/Views/TeacherPanelView.html";
            } else if($this->accountModel->role === "student"){
                $response_array["redirect_url"] = "http://localhost/pfe";
            } else if($this->accountModel->role === "admin"){
                $response_array["redirect_url"] = "http://localhost/pfe/Views/AdminPanelView.html";
            }
        } else if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["username_or_email"]) && isset($_POST["password"])){
            // sanitize the user's input
            $usernameOrEmail = new Input($_POST["username_or_email"]);
            $usernameOrEmail->Sanitize();
            $password = new Input($_POST["password"]);
            $password->Sanitize();

            try {
                // check if the user selected the "remember me" checkbox
                if (isset($_POST["remember_me"])){
                    $remember_me = new Input($_POST["remember_me"]);
                    $remember_me->Sanitize();
                    if($remember_me->value === "on"){
                        // keep the user logged in for one month
                        $rememberDuration = (int) (60 * 60 * 24 * 30);
                    }
                }

                // assign the account role
                $this->accountModel->SetAccountRole($usernameOrEmail->value);

                // if it's a teacher then check their sign up status
                if($this->accountModel->role === "teacher"){
                    // get the password (if found) associated to this username or email
                    $storedPassword = $this->accountModel->getPasswordByUsernameOrEmail($usernameOrEmail->value);
                    // verify that the provided password and the hashed one match
                    // this is to not provide the teacher's info without the appropriate password
                    if(password_verify($password->value, $storedPassword)){
                        // require the teacher model
                        require "../Models/TeacherModel.php";
                        // create a teacher model
                        $teacherModel = new TeacherModel();

                        // allow login only to validated teachers
                        if($teacherModel->GetTeacherStatus($usernameOrEmail->value) == 0){
                            $response_array["error"] = "votre compte est en attente de validation par un admin";
                        } else{
                            $canLogin = true;
                            // redirect the student to the teacher to their panel
                            $response_array["redirect_url"] = "http://localhost/pfe/Views/TeacherPanelView.html";
                        }
                    } else{
                        $response_array["error"] = "Votre nom d'utilisateur/email et votre mot de passe ne correspondent pas<br>";
                    }
                } else if($this->accountModel->role === "student"){
                    $canLogin = true;
                    // redirect the student to the homepage
                    $response_array["redirect_url"] = "http://localhost/pfe";
                } else if($this->accountModel->role === "admin"){
                    $canLogin = true;
                    // redirect the admin to their panel
                    $response_array["redirect_url"] = "http://localhost/pfe/Views/AdminPanelView.html";
                }

                if($canLogin){
                    // if the identifier appears to be an email address then login with the email
                    if(strpos($usernameOrEmail->value, '@') !== false){
                        $this->accountModel->auth->login($usernameOrEmail->value, $password->value, $rememberDuration);
                    }
                    // if the identifier appears to be a username then login with the username
                    else{
                        $this->accountModel->auth->loginWithUsername($usernameOrEmail->value, $password->value, $rememberDuration);
                    }
                    // indicate that the user has successfully signed in
                    $response_array["signed_in"] = true;
                }
            }
            catch (\Delight\Auth\InvalidEmailException $e) {
                $response_array["error"] = "Votre nom d'utilisateur/email et votre mot de passe ne correspondent pas<br>";
            }
            catch (\Delight\Auth\UnknownUsernameException $e) {
                $response_array["error"] = "Votre nom d'utilisateur/email et votre mot de passe ne correspondent pas<br>";
            }
            catch (\Delight\Auth\InvalidPasswordException $e) {
                $response_array["error"] = "Votre nom d'utilisateur/email et votre mot de passe ne correspondent pas<br>";
            }
            catch (\Delight\Auth\AmbiguousUsernameException $e) {
                $response_array["error"] = "Votre nom d'utilisateur/email et votre mot de passe ne correspondent pas<br>";
            }
            catch (\Delight\Auth\TooManyRequestsException $e) {
                $response_array["error"] = "Trop de demandes<br>";
            }
        }

        echo json_encode($response_array);
    }

    // sign out the user
    public function SignOut(){
        $this->accountModel->auth->logOut();
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