<?php

class TeacherController{
    private $teacherModel;

    public function __construct(){
        // create an account account model
        require "../Models/AccountModel.php";
        // create a teacher model
        require "../Models/TeacherModel.php";
        $this->teacherModel = new TeacherModel();
    }

    public function PerformAction($action){
        switch($action){
            case "add":
                $this->AddOffer();
                break;
            case "viewoffers":
                $this->ViewOffers();
                break;
            default:
                break;
        }
    }

    // make sure that the current user is logged in and a validated teacher
    private function UserHasTeacherPriveleges(){
        // the teacher must be logged in
        if($this->teacherModel->auth->isLoggedIn()){
            // create an account model
            $accountModel = new AccountModel();
            // assign the role of the user
            $accountModel->SetAccountRole($this->teacherModel->auth->getEmail());
            // the user must be a teacher
            if($accountModel->role === "teacher"){
                // the teacher must be validated
                if($this->teacherModel->GetTeacherStatus($this->teacherModel->auth->getEmail()) != 0){
                    return true;
                }
            }
        }

        return false;
    }

    // view offers
    private function ViewOffers(){
        $response_array["valid_role"] = false;
        $response_array["offers_html"] = "
        <div id='home' class='container tab-pane active'><br>
            <h3>Gestion des annonces</h3>
            <br>";
        // the user must be logged in and a validated teacher
        if($this->UserHasTeacherPriveleges()){
            $response_array["valid_role"] = true;
            // retrieve the teacher's offers
            $teacherOffers = $this->teacherModel->RetrieveTeacherOffers($this->teacherModel->auth->getUserId());
            if(empty($teacherOffers["offers_list"])){
                $response_array["offers_html"] .= "
            <div class='card-deck'>
                <div class='card'>
                  <div class='card-body text-center'>
                    <p class='card-text'>Annonces en attente de validation</p>
                    <p><b>0</b>  Annonces</p>
                  </div>
                </div>
                <div class='card'>
                  <div class='card-body text-center'>
                    <p class='card-text'>Annonces Validées</p>
                    <p> <b>0</b>  Annonces</p>
                  </div>
                </div>
                <div class='card'>
                  <div class='card-body text-center'>
                    <p class='card-text'>Annonce Refusées</p>
                    <p> <b>0</b> Annonces</p>
                  </div>
                </div>
            </div>
            <hr>
            <div class='offers'>";
            } else{
                $response_array["offers_html"] .= "
            <div class='card-deck'>
                <div class='card'>
                  <div class='card-body text-center'>
                    <p class='card-text'>Annonces en attente de validation</p>
                    <p><b>" . $teacherOffers["offers_number"][0]["pending"] . "</b>  Annonces</p>
                    <button class='btn btn-link' onclick=" .  '"'. "DisplayOfferCategory(0, 'Listes des annonces en attente de validation')" . '"' . ">View Details</button>
                  </div>
                </div>
                <div class='card'>
                  <div class='card-body text-center'>
                    <p class='card-text'>Annonces Validées</p>
                    <p> <b>" . $teacherOffers["offers_number"][0]["validated"] . "</b>  Annonces</p>
                    <button class='btn btn-link' onclick=" .  '"'. "DisplayOfferCategory(1, 'Listes des annonces validées')" . '"' . ">View Details</button>
                  </div>
                </div>
                <div class='card'>
                  <div class='card-body text-center'>
                    <p class='card-text'>Annonce Refusées</p>
                    <p> <b>" . $teacherOffers["offers_number"][0]["refused"] . "</b> Annonces</p>
                    <button class='btn btn-link' onclick=" .  '"'. "DisplayOfferCategory(2, 'Listes des annonces refusées')" . '"' . ">View Details</button>
                  </div>
                </div>
            </div>
            <hr>
            <div class='offers'>
                <h3 id='offers_header'>Listes des annonces en attente de validation</h3>
                <div id='accordion'>";
                foreach($teacherOffers["offers_list"] as $offer){
                    $response_array["offers_html"] .= "
                    <div class='card offers_". $offer["status"] . "'>
                        <div class='card-header'>
                            <a class='card-link' data-toggle='collapse' href='#collapse_offer_". $offer["id"] . "'>
                                Annonce N° " . $offer["id"] . "
                            </a>
                        </div>
                        <div id='collapse_offer_". $offer["id"] . "' class='collapse show' data-parent='#accordion'>
                            <div class='card-body'>
                                <div class='mycard order-card row'>
                                    <div class='col-sm-3 address-infos'>
                                        <h4>Location</h3>
                                        <p>Wilaya: " . $offer["state"] . "</p>
                                        <p>Commune: " . $offer["commune"] ."</p>
                                    </div>
                                    <div class='col-sm-3 studies-infos'>
                                        <h4>Etudes</h4>
                                        <p>Palier: " . $offer["level"] . "</p>
                                        <p>Matière: " . $offer["subject"] . "</p>
                                    </div>
                                    <div class='col-sm-6 price-infos'>
                                        <h4>Prix</h3>
                                        <p>". $offer["price"] . " DA</p>
                                    </div>
                                </div>
                            </div>";
                    if($offer["status"] == 0){
                        $response_array["offers_html"] .="
                            <div class='options btn-group btn-block'>
                                <button class='btn btn-success'>Modifier</button>
                                <button class='btn btn-danger'>Supprimer</button>
                            </div>";
                    }
                    $response_array["offers_html"] .= "
                        </div>
                    </div>";
                }
            $response_array["offers_html"] .= "
                </div>
            </div>
        </div>";
            }
        }

        return $response_array;
    }

    // add an offer
    private function AddOffer(){
        // this array will be sent as a response to the client
        $response_array["action_completed"] = $response_array["valid_role"] = $response_array["display_alert"] = false;
        $response_array["errors"]["state_error"] = $response_array["errors"]["commune_error"] = "";
        $response_array["errors"]["level_error"] = ""; $response_array["errors"]["subject_error"] = "";
        $response_array["errors"]["price_error"] = "";
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["state"]) && isset($_POST["commune"]) && isset($_POST["level"]) && isset($_POST["subject"]) && isset($_POST["price"])){
            // the user must be logged in and a validated teacher
            if($this->UserHasTeacherPriveleges()){
                $response_array["valid_role"] = true;
                // sanitize the user's input
                $state = new Input($_POST["state"]);
                $state->Sanitize();
                $commune = new Input($_POST["commune"]);
                $commune->Sanitize();
                $level = new Input($_POST["level"]);
                $level->Sanitize();
                $subject = new Input($_POST["subject"]);
                $subject->Sanitize();
                $price = new Input($_POST["price"]);
                $price->Sanitize();

                // ensure a non-empty state
                if($state->value === ""){
                    $response_array["errors"]["state_error"] = "Veuillez spécifier une wilaya";
                }
                // ensure a non-empty commune
                else if($commune->value === ""){
                    $response_array["errors"]["commune_error"] = "Veuillez spécifier une commune";
                }
                // ensure a valid level format
                else if(!preg_match("/^primary|middle|high|college$/", $level->value)){
                    $response_array["errors"]["level_error"] = "Veuillez choisir un palier valide<br>";
                }
                // ensure a non-empty subject
                else if($subject->value === ""){
                    $response_array["errors"]["subject_error"] = "Veuillez spécifier une matière";
                }
                // ensure a valid price format
                else if(!preg_match("/^[1-9][0-9]+$/", $price->value)){
                    $response_array["errors"]["price_error"] = "Veuillez spécifier un prix valide<br>";
                } else{
                    // check if the teacher already added an offer with these details
                    if($this->teacherModel->OfferDetailsExist(array("teacher_id" => $this->teacherModel->auth->getUserId(), "state" => $state->value, "commune" => $commune->value, "level" => $level->value, "subject" => $subject->value))){
                        $response_array["display_alert"] = true;
                        $response_array["alert_title"] = "Ajouter Annonce";
                        $response_array["alert_icon"] = "warning";
                        $response_array["alert_text"] = "Vous avez déja une annonce avec ces détails";
                        $response_array["danger_mode"] = true;
                    } 
                    // check if there's already a refused offer with these details
                    else if($this->teacherModel->OfferDetailsAreRefused(array("state" => $state->value, "commune" => $commune->value, "subject" => $subject->value))){
                        $response_array["display_alert"] = true;
                        $response_array["alert_title"] = "Ajouter Annonce";
                        $response_array["alert_icon"] = "warning";
                        $response_array["alert_text"] = "Il y a déjà une annonce refusée avec ces détails. Veuillez spécifier d'autres détails";
                        $response_array["danger_mode"] = true;
                    } else{
                        $this->teacherModel->AddOffer(array("status" => 0, "teacher_id" => $this->teacherModel->auth->getUserId(), "state" => $state->value, "commune" => $commune->value, "level" => $level->value, "subject" => $subject->value, "price" => $price->value));
                        // indicate that the offer was added
                        $response_array["display_alert"] = true;
                        $response_array["alert_title"] = "Ajouter Annonce";
                        $response_array["action_completed"] = true;
                        $response_array["alert_icon"] = "success";
                        $response_array["alert_text"] = "Annonce ajoutée avec succès";
                        $response_array["danger_mode"] = false;
                        $response_array["offers_html"] = $this->ViewOffers()["offers_html"];
                    }
                }
            }
    
            echo json_encode($response_array);
        }
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
    $teacherAccount = new TeacherController();
    $teacherAccount->PerformAction($action->value);
}
?>