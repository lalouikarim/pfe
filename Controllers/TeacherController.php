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
            case "accesspanel":
                $this->DisplayTeacherPanel();
                break;
            case "addoffermenu":
                $this->DisplayAddOfferMenu();
                break;
            case "add":
                $this->AddOffer();
                break;
            case "viewoffersnumber":
                $this->DisplayCategoriesNumber("echo");
                break;
            case "displayoffercategory":
                $this->ViewOffers();
                break;
            case "displaymodifyofferpopup":
                $this->DisplayModifyOfferPopup();
                break;
            case "modify":
                $this->ModifyOffer();
                break;
            case "delete":
                $this->DeleteOffer();
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
                $query = "SELECT id FROM teachers WHERE id = ? AND sign_up_status = ?";
                if($this->teacherModel->TeacherHasStatus($query, array($this->teacherModel->GetTeacherId($this->teacherModel->auth->getUserId())[0]["id"], 1))){
                    return true;
                }
            }
        }

        return false;
    }

    // display the initial teacher panel
    private function DisplayTeacherPanel(){
        $response_array["valid_role"] = false;
        // the user must be logged in and a validated teacher
        if($this->UserHasTeacherPriveleges()){
            $response_array["valid_role"] = true;

            // the head part of the teacher panel
            $response_array["head"] = "
            <script src='../Scripts/TeacherScripts.js'></script>
            <title>Paneau de l'enseignant</title>
            <link rel='stylesheet' href='../Styles/PanelsStyles.css'>";
            // the body part of the teacher panel
            $response_array["body"] = "
            <div class='container-fluid row'>
                <div class='col-sm-2'>
                    <br>
                    <h2>Commands</h2>
                    <br>
                    <!-- Nav pills -->
                    <ul class='nav nav-pills flex-column ' role='tablist'>
                        <li class='nav-item'>
                            <a class='nav-link' data-toggle='pill' onclick='DisplayOfferNumber()' href='#home'>Voir Annonces</a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' data-toggle='pill' onclick='DisplayAddOfferMenu()' href='#add_offer_section'>Ajouter Annonce</a>
                        </li>
                    </ul>  
                </div>
                <!-- Tab panes -->
                <div class='tab-content col-sm-10' id='tab_content'>
                </div>
            </div>";
        }

        echo json_encode($response_array);
    }

    // display the number of offers of each category
    private function DisplayCategoriesNumber($returnOrEcho){
        $response_array["valid_role"] = false;
        $response_array["offers_number_html"] = "";
        // the user must be logged in and a validated teacher
        if($this->UserHasTeacherPriveleges()){
            $response_array["valid_role"] = true;
            // get the teacher id
            $teacherId = $this->teacherModel->GetTeacherId($this->teacherModel->auth->getUserId());
            // get the number of offers of each category
            $offersNumber = $this->teacherModel->OfferCategoriesNumber($teacherId[0]["id"]);
            $response_array["offers_number_html"] = "
        <div id='home' class='container tab-pane active'><br>
            <h3>Gestion des annonces</h3>
            <br>
            <div class='card-deck'>
                <div class='card'>
                  <div class='card-body text-center'>
                    <p class='card-text'>Annonces en attente de validation</p>
                    <p><b>" . $offersNumber[0]["pending"] . "</b>  Annonces</p>
                    <form id='display_offers_0_form'>
                        <button class='btn btn-link' onclick=" .  '"'. "DisplayOfferCategory(0)" . '"' . ">Voir Détails</button>
                    </form>
                  </div>
                </div>
                <div class='card'>
                  <div class='card-body text-center'>
                    <p class='card-text'>Annonces Validées</p>
                    <p> <b>" . $offersNumber[0]["validated"] . "</b>  Annonces</p>
                    <form id='display_offers_1_form'>
                        <button class='btn btn-link' onclick=" .  '"'. "DisplayOfferCategory(1)" . '"' . ">Voir Détails</button>
                    </form>
                  </div>
                </div>
                <div class='card'>
                  <div class='card-body text-center'>
                    <p class='card-text'>Annonce Refusées</p>
                    <p> <b>" . $offersNumber[0]["refused"] . "</b> Annonces</p>
                    <form id='display_offers_2_form'>
                        <button class='btn btn-link' onclick=" .  '"'. "DisplayOfferCategory(2)" . '"' . ">Voir Détails</button>
                    </form>
                  </div>
                </div>
            </div>
            <hr>
            <div id='offers'></div>
        </div>";
        }

        // the "return" is for displaying the new number of offers of each category
        if($returnOrEcho === "echo"){
            echo json_encode($response_array);
        } else if($returnOrEcho === "return"){
            return $response_array["offers_number_html"];
        }
    }

    // view offers
    private function ViewOffers(){
        $response_array["valid_role"] = false;
        $response_array["offers_html"] = $response_array["error"] = "";
        // this array holds the rating details of all offers
        $offerRatings = [];
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["status"])){
            // the user must be logged in and a validated teacher
            if($this->UserHasTeacherPriveleges()){
                $response_array["valid_role"] = true;
                // sanitize the user's input
                $status = new Input($_POST["status"]);
                $status->Sanitize();
                if(!preg_match("/^(0|1|2)$/", $status->value)){
                    $response_array["error"] = "Invalid status";
                } else{
                    // get the teacher id
                    $teacherId = $this->teacherModel->GetTeacherId($this->teacherModel->auth->getUserId());
                    // retrieve the teacher's offers
                    $query = "SELECT * FROM offers WHERE status = ? AND teacher_id = ?";
                    $teacherOffers = $this->teacherModel->RetrieveOffers($query, array($status->value, $teacherId[0]["id"]));
                    if(empty($teacherOffers)){
                        $response_array["offers_html"] = "Pas d'annonces de cette catégorie";
                    } else{
                        $response_array["offers_html"] .= "
                    <div class='offers'>";
                    // display the literal version of the status
                    if($status->value == 0){
                        $statusStr = "en attente de validation";
                    } else if($status->value == 1){
                        $statusStr = "validées";
                    } else if($status->value == 2){
                        $statusStr = "refusées";
                    }
                    $response_array["offers_html"] = "
                        <h3 id='offers_header'>Listes des annonces " . $statusStr . "</h3>
                        <div id='accordion'>";
                        foreach($teacherOffers as $offer){
                            $response_array["offers_html"] .= "
                            <div class='card offers_". $offer["status"] . "'>
                                <div class='card-header'>
                                    <a class='card-link' id='offer_title_" . $offer["id"] . "' data-toggle='collapse' href='#collapse_offer_". $offer["id"] . "'>
                                        Annonce N° " . $offer["id"] . "
                                    </a>
                                </div>
                                <div id='collapse_offer_". $offer["id"] . "' class='collapse show' data-parent='#accordion'>
                                    <div class='card-body'>
                                        <div class='mycard order-card row'>
                                            <div class='col-sm-3 address-infos'>
                                                <h4>Location</h4>
                                                <p>Wilaya: " . $offer["state"] . "</p>
                                                <p>Commune: " . $offer["commune"] ."</p>
                                            </div>
                                            <div class='col-sm-3 studies-infos'>
                                                <h4>Etudes</h4>";
                            if($offer["level"] === "primary"){
                                $level = "Primaire";
                            } else if($offer["level"] === "middle"){
                                $level = "Moyenne";
                            } else if($offer["level"] === "high"){
                                $level = "Secondaire";
                            } else if($offer["level"] === "college"){
                                $level = "Universitaire";
                            }
                            $response_array["offers_html"] .= "
                                                <p>Palier: " . $level . "</p>
                                                <p>Matière: " . $offer["subject"] . "</p>
                                            </div>
                                            <div class='col-sm-3 price-infos'>
                                                <h4>Prix</h4>
                                                <p>". $offer["price"] . " DA</p>
                                            </div>";

                            // if it's a validated offer then display its rating details
                            if($offer["status"] == 1){
                                $offerRating = $this->teacherModel->RetrieveOfferRatings($offer["id"]);
                                if(empty($offerRating) || $offerRating[0]["avg_rating"] === null){
                                    $offerRatings[$offer["id"]] = 0;
                                    $ratesNumber = 0;
                                } else{
                                    $offerRatings[$offer["id"]] = $offerRating[0]["avg_rating"];
                                    $ratesNumber = $offerRating[0]["rates_number"];
                                }

                                $response_array["offers_html"] .= "
                                            <div class='col-sm-3 address-infos'>
                                                <h4>Notes</h4>
                                                <p>
                                                    Note moyenne: 
                                                    <div class='post-action'>
                                                        <div class='d-inline-flex'>
                                                            <select class='avg-rating' id='offer_rating_" . $offer["id"] . "' data-id='offer_rating_" . $offer["id"] . "'>
                                                                <option value='0'>0</option>
                                                                <option value='1' >1</option>
                                                                <option value='2' >2</option>
                                                                <option value='3' >3</option>
                                                                <option value='4' >4</option>
                                                                <option value='5' >5</option>
                                                            </select>
                                                            <div style='clear: both;'></div>
                                                        </div>
                                                    </div>
                                                </p>
                                                <p>Nombre de noteurs: " . $ratesNumber . "</p>
                                            </div>";
                            }
                            // if it's a refused offer then display the refusal info
                            else if($offer["status"] == 2){
                                $refusalInfo = $this->teacherModel->GetOfferRefusal($offer["id"]);
                                if(!empty($refusalInfo)){
                                    $response_array["offers_html"] .= "
                                            <div class='col-sm-3 refusal-infos'>
                                                <h4>Refus</h4>
                                                <p>Raison: " . $refusalInfo[0]["refusal_reason"] . "</p>
                                            </div>
                                    ";
                                }
                            }
                            $response_array["offers_html"] .= "
                                        </div>
                                    </div>";
                            // a teacher can modify only pending and accepted offers and can delete all offers regardless of the category
                            if($offer["status"] == 0 || $offer["status"] == 1){
                                $response_array["offers_html"] .="
                                    <div class='options btn-group btn-block'>
                                        <form class='btn btn-success' id='display_modify_offer_popup_" . $offer["id"] . "_form'>
                                            <button class='btn btn-success' onclick='DisplayModifyOfferPopup(" . $offer["id"] . ")'>Modifier</button>
                                        </form>
                                        <div class='modify-offer-hide' id='modify_offer_popup_" . $offer["id"] . "'> 
                                            <button class='btn btn-primary' id='modify_offer_hidepopup_" . $offer["id"] . "' onclick=" . '"' . "hidepopup('modify_offer_popup_" . $offer["id"] . "')" . '"' . ">&times;</button>
                                            <br><label class='form-label'>Modifier <button class='btn btn-link' onclick='RedirectTeacherToOffer(" . $offer["id"] . ")'> l'annonce N° " . $offer["id"] . "</button></label>
                                            <form id='modify_offer_" . $offer["id"] . "_form'>
                                                <div class='col-md-9'>
                                                    <div class='form-group'>
                                                        <input type='text' class='form-control' name='state' value='" . $offer["state"] . "'/>
                                                        <span class='help-block' id='state_error_offer_" . $offer["id"] . "'></span>
                                                    </div>
                                                    <div class='form-group'>
                                                        <input type='text' class='form-control' name='commune' value='" . $offer["commune"] . "'/>
                                                        <span class='help-block' id='commune_error_offer_" . $offer["id"] . "'></span>
                                                    </div>
                                                    <div class='form-group'>
                                                        <select class='custom-select' name='level'>";

                                // this array holds levels info
                                $levelsArray = [];
                                $levelsArray["primary"] = "Primaire";
                                $levelsArray["middle"] = "Moyenne";
                                $levelsArray["high"] = "Secondaire";
                                $levelsArray["college"] = "Universitaire";
                                // the level of the offer should be selected
                                $response_array["offers_html"] .= "
                                                            <option value='" . $offer["level"] . "' selected>" . $levelsArray[$offer["level"]] . "</option>";
                                // remove the level of the offer
                                unset($levelsArray[$offer["level"]]);
                                // display the remaining levels
                                foreach($levelsArray as $key => $value){
                                    $response_array["offers_html"] .="
                                                            <option value='" . $key . "'>" . $value . "</option>";
                                }

                                $response_array["offers_html"] .= "
                                                        </select>
                                                        <span class='help-block' id='level_error_offer_" . $offer["id"] . "'></span>
                                                    </div>
                                                    <div class='form-group'>
                                                        <input type='text' class='form-control' name='subject'  value='" . $offer["subject"] . "'/>
                                                        <span class='help-block' id='subject_error_offer_" . $offer["id"] . "'></span>
                                                    </div>
                                                    <div class='form-group'>
                                                        <input type='text' class='form-control' name='price'  value='" . $offer["price"] . "'/>
                                                        <span class='help-block' id='price_error_offer_" . $offer["id"] . "'></span>
                                                    </div>
                                                </div>
                                            </form>
                                            <button type='submit' class='btnPerform' style='float:left;' onclick=" . '"' . "ConfirmClick('modify', " . $offer["id"] . ", 'Modifier Annonce', 'Etes vous sur de modifier cette annonce? Elle devrait etre ré-validée par un admin', 'Annuler', 'Procéder')" . '"' . ">Mettre a jour</button>
                                        </div>
                                        <form id='delete_offer_" . $offer["id"] . "_form'></form>
                                        <button class='btn btn-danger' onclick=" . '"' . "ConfirmClick('delete', " . $offer["id"] . ", 'Supprimer Annonce', 'Etes vous sur de supprimer cette annonce?', 'Annuler', 'Procéder')" . '"' . ">Supprimer</button>
                                    </div>";
                            } else{
                                $response_array["offers_html"] .= "
                                    <div class='options btn-group btn-block'>
                                        <form id='delete_offer_" . $offer["id"] . "_form'></form>
                                        <button class='btn btn-danger' onclick=" . '"' . "ConfirmClick('delete', " . $offer["id"] . ", 'Supprimer Annonce', 'Etes vous sur de supprimer cette annonce?', 'Annuler', 'Procéder')" . '"' . ">Supprimer</button>
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
            }
        }

        // assign the ratings array
        $response_array["avg_ratings"] = $offerRatings;

        echo json_encode($response_array);
    }

    // display the add offer html
    private function DisplayAddOfferMenu(){
        $response_array["valid_role"] = false;
        // the user must be logged in and a validated teacher
        if($this->UserHasTeacherPriveleges()){
            $response_array["valid_role"] = true;

            // the add offer menu
            $response_array["section"] = "
            <div id='add_offer_section' class='container tab-pane active'><br>
                <h3>Ajouter Annonce</h3>
                <div class='card-body'>
                    <div class='mycard order-card row'>
                        <div class='col-sm-9'>
                            <form id='add_offer__form' method='post'>
                                <div class='row'>
                                    <div class='col-md-9'>
                                        <div class='form-group'>
                                            <input type='text' class='form-control' name='state' placeholder='Wilaya'/>
                                            <span class='help-block' id='state_error'></span>
                                        </div>
                                        <div class='form-group'>
                                            <input type='text' class='form-control' name='commune' placeholder='Commune'/>
                                            <span class='help-block' id='commune_error'></span>
                                        </div>
                                        <div class='form-group'>
                                            <select class='custom-select' name='level'>
                                                <option value='choose_level' selected>--Choisir Palier--</option>
                                                <option value='primary'>Primaire</option>
                                                <option value='middle'>Moyenne</option>
                                                <option value='high'>Secondaire</option>
                                                <option value='college'>Universitaire</option>
                                            </select>
                                            <span class='help-block' id='level_error'></span>
                                        </div>
                                        <div class='form-group'>
                                            <input type='text' class='form-control' name='subject'  placeholder='Matière'/>
                                            <span class='help-block' id='subject_error'></span>
                                        </div>
                                        <div class='form-group'>
                                            <input type='text' class='form-control' name='price'  placeholder='Prix de la séance'/>
                                            <span class='help-block' id='price_error'></span>
                                        </div>
                                    </div>
                                    <button type='submit' class='btnPerform' onclick=" . '"' . "Offer('add', '')" . '"' . ">Créer</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            ";
        }

        echo json_encode($response_array);
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
                    // get the teacher id
                    $teacherId = $this->teacherModel->GetTeacherId($this->teacherModel->auth->getUserId());

                    // check if the teacher already added an offer with these details
                    if($this->teacherModel->OfferDetailsExist(array("teacher_id" => $teacherId[0]["id"], "state" => $state->value, "commune" => $commune->value, "level" => $level->value, "subject" => $subject->value, "price" => $price->value))){
                        $response_array["display_alert"] = true;
                        $response_array["alert_title"] = "Ajouter Annonce";
                        $response_array["alert_icon"] = "warning";
                        $response_array["alert_text"] = "Vous avez déja une annonce avec ces détails";
                        $response_array["danger_mode"] = true;
                    } else{
                        // add the offer
                        $this->teacherModel->AddOffer(array("status" => 0, "teacher_id" => $teacherId[0]["id"], "state" => $state->value, "commune" => $commune->value, "level" => $level->value, "subject" => $subject->value, "price" => $price->value));
                        // indicate that the offer was added
                        $response_array["display_alert"] = true;
                        $response_array["alert_title"] = "Ajouter Annonce";
                        $response_array["action_completed"] = true;
                        $response_array["alert_icon"] = "success";
                        $response_array["alert_text"] = "Annonce ajoutée avec succès";
                        $response_array["danger_mode"] = false;
                        // the "home" of the teacher panel should display the new number of offers of each category
                        $response_array["offers_number_html"] = $this->DisplayCategoriesNumber("return");
                    }
                }
            }
    
            echo json_encode($response_array);
        }
    }

    // display the modify offer popup
    private function DisplayModifyOfferPopup(){
        $response_array["valid_role"] = $response_array["display_modify_offer_popup"] = false;
        $response_array["danger_mode"] = true;
        $response_array["alert_title"] = "Refuser Annonce";
        $response_array["alert_text"] = $response_array["alert_icon"] = $response_array["error"] = "";
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["offer_id"])){
            // the user must be logged in and a validated teacher
            if($this->UserHasTeacherPriveleges()){
                $response_array["valid_role"] = true;
                // sanitize the user's input
                $offerId = new Input($_POST["offer_id"]);
                $offerId->Sanitize();
                // ensure a valid offer id
                if(!preg_match("/^(0|[1-9][0-9]*)$/", $offerId->value)){
                    $response_array["error"] = "Invalid input";
                } else{
                    // get the teacher id
                    $teacherId = $this->teacherModel->GetTeacherId($this->teacherModel->auth->getUserId());

                    // make sure that the offer exists, is indeed in a pending or accepted state, and belongs to this teacher
                    if($this->teacherModel->OfferUpdatableByTeacher($offerId->value, $teacherId[0]["id"], array(0, 1))){
                        $response_array["display_modify_offer_popup"] = true;
                        $response_array["modify_offer_popup_id"] = "modify_offer_popup_" . $offerId->value;
                    } else{
                        $response_array["alert_text"] = "L'annonce soit n'existe pas soit elle est refusée soit elle ne vous appartient pas";
                        $response_array["alert_icon"] = "warning";
                    }
                }
            }
        }

        echo json_encode($response_array);
    }

    // modify an offer
    private function ModifyOffer(){
        $response_array["valid_role"] = $response_array["display_alert"] = $response_array["action_completed"] = false;
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["offer_id"]) && isset($_POST["state"]) && isset($_POST["commune"]) && isset($_POST["level"]) && isset($_POST["subject"]) && isset($_POST["price"])){
            // the user must be logged in and a validated teacher
            if($this->UserHasTeacherPriveleges()){
                $response_array["valid_role"] = true;
                // sanitize the user's input
                $offerId = new Input($_POST["offer_id"]);
                $offerId->Sanitize();
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

                // ensure a valid offer id
                if(!preg_match("/^(0|[1-9][0-9]*)$/", $offerId->value)){
                    $response_array["error"] = "Invalid input";
                } else{
                    $response_array["errors"]["state_error_offer_" . $offerId->value] = $response_array["errors"]["commune_error_offer_" . $offerId->value] = "";
                    $response_array["errors"]["level_error_offer_" . $offerId->value] = ""; $response_array["errors"]["subject_error_offer_" . $offerId->value] = "";
                    $response_array["errors"]["price_error_offer_" . $offerId->value] = "";

                    // ensure a non-empty state
                    if($state->value === ""){
                        $response_array["errors"]["state_error_offer_" . $offerId->value] = "Veuillez spécifier une wilaya";
                    }
                    // ensure a non-empty commune
                    else if($commune->value === ""){
                        $response_array["errors"]["commune_error_offer_" . $offerId->value] = "Veuillez spécifier une commune";
                    }
                    // ensure a valid level format
                    else if(!preg_match("/^primary|middle|high|college$/", $level->value)){
                        $response_array["errors"]["level_error_offer_" . $offerId->value] = "Veuillez choisir un palier valide<br>";
                    }
                    // ensure a non-empty subject
                    else if($subject->value === ""){
                        $response_array["errors"]["subject_error_offer_" . $offerId->value] = "Veuillez spécifier une matière";
                    }
                    // ensure a valid price format
                    else if(!preg_match("/^[1-9][0-9]+$/", $price->value)){
                        $response_array["errors"]["price_error_offer_" . $offerId->value] = "Veuillez spécifier un prix valide<br>";
                    } else{
                        $response_array["display_alert"] = true;

                        // get the teacher id
                        $teacherId = $this->teacherModel->GetTeacherId($this->teacherModel->auth->getUserId());

                        // make sure that the offer exists, is indeed in a pending or accepted state, and belongs to this teacher
                        if($this->teacherModel->OfferUpdatableByTeacher($offerId->value, $teacherId[0]["id"], array(0, 1))){
                            // check if the teacher already has an offer with these details
                            if($this->teacherModel->OfferDetailsExist(array("teacher_id" => $teacherId[0]["id"], "state" => $state->value, "commune" => $commune->value, "level" => $level->value, "subject" => $subject->value, "price" => $price->value))){
                                $response_array["display_alert"] = true;
                                $response_array["alert_title"] = "Modifier Annonce";
                                $response_array["alert_icon"] = "warning";
                                $response_array["alert_text"] = "Vous avez déja une annonce avec ces détails";
                                $response_array["danger_mode"] = true;
                            } else{
                                // update the offer's info
                                $this->teacherModel->UpdateOfferDetails(array("status" => 0, "state" => $state->value, "commune" => $commune->value,"level" => $level->value, "subject" => $subject->value, "price" => $price->value, "offer_id" => $offerId->value));
                                $response_array["action_completed"] = true;
                                $response_array["alert_title"] = "Modifier Annonce";
                                $response_array["alert_text"] = "Annonce modifiée avec succés";
                                $response_array["alert_icon"] = "success";
                                $response_array["danger_mode"] = false;
                                // display the new number of offers of each category
                                $response_array["offers_number_html"] = $this->DisplayCategoriesNumber("return");
                            }
                        } else{
                            $response_array["alert_title"] = "Modifier Annonce";
                            $response_array["alert_text"] = "L'annonce soit n'existe pas soit elle est refusée soit elle ne vous appartient pas";
                            $response_array["alert_icon"] = "warning";
                            $response_array["danger_mode"] = true;
                        }
                    }
                }
            }
        }

        echo json_encode($response_array);
    }

    // delete an offer
    private function DeleteOffer(){
        $response_array["valid_role"] = $response_array["display_alert"] = $response_array["action_completed"] = false;
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["offer_id"])){
            // the user must be logged in and a validated teacher
            if($this->UserHasTeacherPriveleges()){
                $response_array["valid_role"] = true;
                // sanitize the user's input
                $offerId = new Input($_POST["offer_id"]);
                $offerId->Sanitize();

                // ensure a valid offer id
                if(!preg_match("/^(0|[1-9][0-9]*)$/", $offerId->value)){
                    $response_array["error"] = "Invalid input";
                } else{
                    $response_array["display_alert"] = true;

                    // get the teacher id
                    $teacherId = $this->teacherModel->GetTeacherId($this->teacherModel->auth->getUserId());

                    // make sure that the offer exists and belongs to this teacher
                    if($this->teacherModel->OfferUpdatableByTeacher($offerId->value, $teacherId[0]["id"], array(0, 1, 2))){
                        // delete the offer's ratings (if found)
                        $this->teacherModel->DeleteOfferRatings($offerId->value);
                        // delete the offer's refusal info (if found)
                        $this->teacherModel->DeleteOfferRefusal($offerId->value);
                        // delete the offer's info
                        $this->teacherModel->DeleteOffer($offerId->value);
                        
                        $response_array["action_completed"] = true;
                        $response_array["alert_title"] = "Supprimer Annonce";
                        $response_array["alert_text"] = "Annonce supprimée avec succés";
                        $response_array["alert_icon"] = "success";
                        $response_array["danger_mode"] = false;
                        // display the new number of offers of each category
                        $response_array["offers_number_html"] = $this->DisplayCategoriesNumber("return");
                    } else{
                        $response_array["alert_title"] = "Supprimer Annonce";
                        $response_array["alert_text"] = "L'annonce soit n'existe pas soit elle ne vous appartient pas";
                        $response_array["alert_icon"] = "warning";
                        $response_array["danger_mode"] = true;
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
    $teacherAccount = new TeacherController();
    $teacherAccount->PerformAction($action->value);
}
?>