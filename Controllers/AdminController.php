<?php

class AdminController{
    private $adminModel;

    public function __construct(){
        // create an account account model
        require "../Models/AccountModel.php";
        // create a admin model
        require "../Models/AdminModel.php";
        $this->adminModel = new AdminModel();
    }

    // perform an action
    public function PerformAction($action){
        switch($action){
            case "viewoffersnumber":
                $this->DisplayCategoriesNumber();
                break;
            case "viewoffers":
                $this->ViewOffers();
                break;
            default:
                break;
        }
    }

    // make sure that the current user is logged in and is an admin
    private function UserHasAdminPriveleges(){
        // the admin must be logged in
        if($this->adminModel->auth->isLoggedIn()){
            // create an account model
            $accountModel = new AccountModel();
            // assign the role of the user
            $accountModel->SetAccountRole($this->adminModel->auth->getEmail());
            // the user must be an admin
            if($accountModel->role === "admin"){
                return true;
            }
        }

        return false;
    }

    // display the number of offers of each category
    private function DisplayCategoriesNumber(){
        //if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST[""]))
        $response_array["valid_role"] = false;
        $response_array["offers_number_html"] = "";
        // the user must be logged in and a validated admin
        if($this->UserHasAdminPriveleges()){
            $response_array["valid_role"] = true;
            // get the number of offers of each category
            $offersNumber = $this->adminModel->OfferCategoriesNumber();
            $response_array["offers_number_html"] = "
        <div id='home' class='container tab-pane active'><br>
            <h3>Gestion des annonces</h3>
            <br>
            <div class='card-deck'>
                <div class='card'>
                  <div class='card-body text-center'>
                    <p class='card-text'>Annonces en attente de validation</p>
                    <p><b>" . $offersNumber[0]["pending"] . "</b>  Annonces</p>
                    <button class='btn btn-link' onclick=" .  '"'. "DisplayOfferCategory(0, 'Listes des annonces en attente de validation')" . '"' . ">View Details</button>
                  </div>
                </div>
                <div class='card'>
                  <div class='card-body text-center'>
                    <p class='card-text'>Annonces Validées</p>
                    <p> <b>" . $offersNumber[0]["validated"] . "</b>  Annonces</p>
                    <button class='btn btn-link' onclick=" .  '"'. "DisplayOfferCategory(1, 'Listes des annonces validées')" . '"' . ">View Details</button>
                  </div>
                </div>
                <div class='card'>
                  <div class='card-body text-center'>
                    <p class='card-text'>Annonce Refusées</p>
                    <p> <b>" . $offersNumber[0]["refused"] . "</b> Annonces</p>
                    <button class='btn btn-link' onclick=" .  '"'. "DisplayOfferCategory(2, 'Listes des annonces refusées')" . '"' . ">View Details</button>
                  </div>
                </div>
            </div>
            <hr>
        </div>";
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
    $adminAccount = new AdminController();
    $adminAccount->PerformAction($action->value);
}

?>