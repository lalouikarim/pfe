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
                $this->DisplayCategoriesNumber("echo");
                break;
            case "displayoffercategory":
                $this->ViewOffers();
                break;
            case "validate":
                $this->ValidateOffer();
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
    private function DisplayCategoriesNumber($returnOrEcho){
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
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["status"])){
            // the user must be logged in and a validated admin
            if($this->UserHasAdminPriveleges()){
                $response_array["valid_role"] = true;
                // sanitize the user's input
                $status = new Input($_POST["status"]);
                $status->Sanitize();
                if(!preg_match("/^(0|1|2)$/", $status->value)){
                    $response_array["error"] = "Invalid status";
                } else{
                    // retrieve all offers
                    $allOffers = $this->adminModel->RetrieveOffers($status->value);
                    if(empty($allOffers)){
                        $response_array["offers_html"] = "Pas d'annonces de cette catégorie";
                    } else{
                        // these are used for pagination
                        $offersPerPage = 0;
                        $pageNumber = 0;

                        // display the literal version of the status
                        if($status->value == 0){
                            $statusStr = "en attente de validation";
                        } else if($status->value == 1){
                            $statusStr = "validées";
                        } else if($status->value == 2){
                            $statusStr = "refusées";
                        }
                        $response_array["offers_html"] .= "
            <div class='offers'>
                <h3 id='offers_header'>Listes des annonces " . $statusStr . "</h3>";
                    // display the previous and next buttons only if there are more than 6 offers
                    if(count($allOffers) > 6){
                        $response_array["offers_html"] .= "
                <div class='centring pagination'>
                    <a id='previous_page_btn_offers' class='pagination-btn-active btn btn-link' onclick=" . '"' . "pagination(0, 'offers')" . '"' . " hidden>&ltPrécédent</a>
                    <a id='next_page_btn_offers' class='pagination-btn-active btn btn-link' onclick=" . '"' . "pagination(1, 'offers')" . '"' . ">Suivant&gt</a>
                </div>";
                    }
                        $response_array["offers_html"] .="
                <div id='accordion'>";
                        foreach($allOffers as $offer){
                            // if it's the first offer of the page
                            if($offersPerPage === 0){
                                $response_array["offers_html"] .= '
                    <div id="pagination_offers_' . $pageNumber . '" class="page';

                                // the first page should be automatically visible
                                if($pageNumber === 0)
                                {
                                    $response_array["offers_html"] .= ' page-active-offers';
                                }

                                $response_array["offers_html"] .= '">';

                                // increment the page number
                                $pageNumber += 1;
                            }
                            $response_array["offers_html"] .= "
                        <div class='card'>
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
                                            <h4>Etudes</h4>";
                            if($offer["level"] === "primary"){
                                $offer["level"] = "Primaire";
                            } else if($offer["level"] === "middle"){
                                $offer["level"] = "Moyenne";
                            } else if($offer["level"] === "high"){
                                $offer["level"] = "Secondaire";
                            } else if($offer["level"] === "college"){
                                $offer["level"] = "Universitaire";
                            }
                            $response_array["offers_html"] .= "
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
                                    <form id='validate_offer_" . $offer["id"] . "_form'class='btn btn-success'>
                                        <button class='btn btn-success' onclick=" . '"' . "ValidateOffers('validate', " . $offer["id"] . ')"' . ">Valider</button>
                                    </form>
                                    <button class='btn btn-danger'>Refuser</button>
                                </div>";
                            }
                            $response_array["offers_html"] .= "
                            </div>
                        </div>";
                            
                            // indicate that a offer has been echoed
                            $offersPerPage += 1;

                            // if it's the last offer of the page
                            if($offersPerPage === 6){
                                $response_array["offers_html"] .= "
                    </div>";
                                // reinitialize the number of offers echoed in the page
                                $offersPerPage = 0;
                            }
                        }
                        // this is in case the last page has less than 6 offers
                        if($offersPerPage > 0 && $offersPerPage < 6){
                            $response_array["offers_html"] .= "
                    </div>";
                        }

                    $response_array["offers_html"] .= "
                </div>
            </div>";
                    }
                }
            }
        }

        echo json_encode($response_array);
    }

    // validate an offer
    private function ValidateOffer(){
        $response_array["valid_role"] = false;
        $response_array["danger_mode"] = true;
        $response_array["alert_title"] = "Valider Annonce";
        $response_array["alert_text"] = $response_array["alert_icon"] = $response_array["error"] = "";
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["offer_id"])){
            // the user must be logged in and a validated admin
            if($this->UserHasAdminPriveleges()){
                $response_array["valid_role"] = true;
                // sanitize the user's input
                $offerId = new Input($_POST["offer_id"]);
                $offerId->Sanitize();
                // ensure a valid offer id
                if(!preg_match("/^(0|[1-9][0-9]*)$/", $offerId->value)){
                    $response_array["error"] = "Invalid input";
                } else{
                    // make sure that the offer exists and is indeed in a pending state
                    if($this->adminModel->OfferHasStatus($offerId->value, 0)){
                        // update the status of the offer
                        $this->adminModel->ChangeOfferStatus($offerId->value, 1);
                        $response_array["alert_text"] = "Annonce validée avec succés";
                        $response_array["alert_icon"] = "success";
                        $response_array["danger_mode"] = false;
                        // display the new number of offers of each category
                        $response_array["offers_number_html"] = $this->DisplayCategoriesNumber("return");

                        // get the necessary details of the offer to send an email to the teacher
                        $offerDetails = $this->adminModel->RetrieveOfferForEmail($offerId->value);
                        // send an email to the teacher
                        if(!empty($offerDetails)){
                            if($offerDetails[0]["level"] === "primary"){
                                $offerDetails[0]["level"] = "Primaire";
                            } else if($offerDetails[0]["level"] === "middle"){
                                $offerDetails[0]["level"] = "Moyenne";
                            } else if($offerDetails[0]["level"] === "high"){
                                $offerDetails[0]["level"] = "Secondaire";
                            } else if($offerDetails[0]["level"] === "college"){
                                $offerDetails[0]["level"] = "Universitaire";
                            }
                            
                            $to = $offerDetails[0]["email"];
                            $subject = "Validation de votre annonce des cours particuliers";
                            $message = "Féliciations! Cette annonce des cours particuliers a été validée:
                            Wilaya: " . $offerDetails[0]["state"] . "
                            Commune: " . $offerDetails[0]["commune"] . "
                            Palier: " . $offerDetails[0]["level"] . "
                            Matière: " . $offerDetails[0]["subject"];
                            $headers = 'From:emailprogrammingtest@gmail.com' . "\r\n"; 
                            // send the verification email to the user
                            mail($to, $subject, $message, $headers);
                        }
                    } else{
                        $response_array["alert_text"] = "L'annonce soit n'existe pas soit elle est déja validée ou refusée";
                        $response_array["alert_icon"] = "warning";
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
    $adminAccount = new AdminController();
    $adminAccount->PerformAction($action->value);
}

?>