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
            case "access":
                $this->DisplayAdminPanel();
                break;
            case "viewoffersnumber":
                $this->DisplayCategoriesNumber("echo");
                break;
            case "displayoffercategory":
                $this->ViewOffers();
                break;
            case "validate":
                $this->ValidateOffer();
                break;
            case "displayofferrefusalpopup":
                $this->DisplayOfferRefusalPopup();
                break;
            case "refuse":
                $this->RefuseOffer();
                break;
            case "delete":
                $this->DeleteOffer();
                break;
            case "viewteacherssignupsnumber":
                $this->DisplayTeachersSignUpsCategoriesNumber("echo");
                break;
            case "displayteacherssignupsdetails":
                $this->DisplayTeachersSignUpsDetails();
                break;
            case "acceptteacher":
                $this->AcceptTeacher();
                break;
            case "displayteacherrefusalpopup":
                $this->DisplayTeacherRefusalPopup();
                break;
            case "refuseteacher":
                $this->RefuseTeacher();
                break;
            case "deleteteacher":
                $this->DeleteTeacher();
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

    // display the initial admin panel
    private function DisplayAdminPanel(){
        $response_array["valid_role"] = false;
        // the user must be logged in and an admin
        if($this->UserHasAdminPriveleges()){
            $response_array["valid_role"] = true;

            // the head part of the admin panel
            $response_array["head"] = "
            <script src='../Scripts/AdminScripts.js'></script>
            <title>Paneau de l'admin</title>
            <link rel='stylesheet' href='../Styles/PanelsStyles.css'>";
            // the body part of the admin panel
            $response_array["body"] = "
            <div class='container-fluid row'>
                <div class='col-sm-2'>
                    <br>
                    <h2>Commands</h2>
                    <br>
                    <!-- Nav pills -->
                    <ul class='nav nav-pills flex-column ' role='tablist'>
                        <li class='nav-item'>
                            <a class='nav-link' data-toggle='pill' onclick='DisplayOffersNumbers()' href='#home'>Voir Annonces</a>
                        </li>
                        <li class='nav-item'>
                            <a class='nav-link' data-toggle='pill' onclick='DisplayTeachersSignUpsCategories()' href='#teachers_sign_ups_section'>Voir Inscriptions des enseignants</a>
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
        // the user must be logged in and a validated admin
        if($this->UserHasAdminPriveleges()){
            $response_array["valid_role"] = true;
            // get the number of offers of each category
            $offersNumber = $this->adminModel->OfferCategoriesNumber("all");
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
                    $query = "SELECT * FROM offers WHERE status = ?";
                    $allOffers = $this->adminModel->RetrieveOffers($query, array($status->value));
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
                                <a class='card-link' id='offer_title_" . $offer["id"] . "' data-toggle='collapse' href='#collapse_offer_". $offer["id"] . "'>
                                    Annonce N° " . $offer["id"] . "
                                </a>
                            </div>
                            <div id='collapse_offer_". $offer["id"] . "' class='collapse show' data-parent='#accordion'>
                                <div class='card-body'>
                                    <div class='mycard order-card row'>";
                            // get the offer's teacher info
                            $teacherInfo = $this->adminModel->RetrieveOfferTeacherInfo($offer["id"]);
                            if(!empty($teacherInfo)){
                                // display the teacher's info
                                $response_array["offers_html"] .= "
                                        <div class='col-sm-6 list-infos'>
                                            <h4>Enseignant</h4>
                                            <div class='products-list'>
                                                <table class='table table-hover'>
                                                    <thead>
                                                        <tr>
                                                            <th>Propriété</th>
                                                            <th>Valeur</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>Nom</td>
                                                            <td>" . $teacherInfo[0]["last_name"] . "</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Prénom</td>
                                                            <td>" . $teacherInfo[0]["first_name"] . "</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Email</td>
                                                            <td>" . $teacherInfo[0]["email"] . "</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Nom D'utilisateur</td>
                                                            <td>" . $teacherInfo[0]["username"] . "</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Téléphone</td>
                                                            <td>" . $teacherInfo[0]["phone"] . "</td>
                                                        </tr>
                                                        <tr>
                                                            <td>Lien de CV</td>
                                                            <td><a href='" . $teacherInfo[0]["cv_link"] . "' target='_blank'>Cliquez ici</a></td>
                                                        </tr>
                                                        <tr>
                                                            <td>Carte d'identité</td>
                                                            <td><button class='btn btn-link' onclick=" . '"' ."showpopup('card_img_" . $offer["id"] . "')" . '"' . ">Voir image</button></td>
                                                            <div class='popup-hide' id='card_img_" .  $offer["id"] . "'> 
                                                                <button class='btn btn-primary' id='card_img_hidepopup_" . $offer["id"] . "' onclick=" . '"' . "hidepopup('card_img_" .  $offer["id"] . "')" . '"' . ">&times;</button>
                                                                <img class='card-img-top' src='../Images/IDCards/" . md5($teacherInfo[0]["card_photo"]) .".jpeg' alt='Card Img' style='height:200px'>
                                                            </div>
                                                        </tr>
                                                        <tr>
                                                            <td>Photo</td>
                                                            <td><button class='btn btn-link' onclick=" . '"' ."showpopup('teacher_img_" . $offer["id"] . "')" . '"' . ">Voir image</button></td>
                                                            <div class='popup-hide' id='teacher_img_" .  $offer["id"] . "'> 
                                                                <button class='btn btn-primary' id='teacher_img_hidepopup_" . $offer["id"] . "' onclick=" . '"' . "hidepopup('teacher_img_" .  $offer["id"] . "')" . '"' . ">&times;</button>
                                                                <img class='card-img-top' src='../Images/Teachers/" . md5($teacherInfo[0]["teacher_photo"]) .".jpeg' alt='Teacher Img' style='height:200px'>
                                                            </div>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>";
                            }
                            $response_array["offers_html"] .="
                                        <div class='col-sm-3 address-infos'>
                                            <h4>Séance</h4>
                                            <p>Wilaya: " . $offer["state"] . "</p>
                                            <p>Commune: " . $offer["commune"] ."</p>";
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
                                            <p>Prix: " . $offer["price"] . " DA
                                        </div>";
                            // if it's a validated offer then display its rating details
                            if($offer["status"] == 1){
                                $offerRating = $this->adminModel->RetrieveOfferRatings($offer["id"]);
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
                                $refusalInfo = $this->adminModel->GetOfferRefusal($offer["id"]);
                                if(!empty($refusalInfo)){
                                    $response_array["offers_html"] .= "
                                        <div class='col-sm-3 refusal-infos'>
                                            <h4>Refus</h4>
                                            <p>Raison: " . $refusalInfo[0]["refusal_reason"] . "</p>
                                        </div>
                                    ";
                                }
                            }
                            $response_array["offers_html"] .="
                                    </div>
                                </div>";
                            
                            // the admin can only accept and refuse pending offers
                            if($offer["status"] == 0){
                                $response_array["offers_html"] .="
                                <div class='options btn-group btn-block'>
                                    <form id='validate_offer_" . $offer["id"] . "_form' class='btn btn-success'>
                                        <button class='btn btn-success' onclick=" . '"' . "ValidateOffers('validate', " . $offer["id"] . ", 'Valider Annonce', 'Etes vous sur de valider cette annonce?', 'Annuler', 'Procéder')" . '"' . ">Valider</button>
                                    </form>
                                    <form id='display_offer_refusal_popup_" . $offer["id"] . "_form' class='btn btn-danger'>
                                        <button class='btn btn-danger' onclick=" . '"' . "DisplayRefusalPopup('offer', " . $offer["id"] . ")" . '"' . ">Refuser</button>
                                    </form>
                                    <div class='popup-hide' id='offer_refusal_popup_" . $offer["id"] . "'> 
                                        <button class='btn btn-primary' id='offer_refusal_hidepopup_" . $offer["id"] . "' onclick=" . '"' . "hidepopup('offer_refusal_popup_" . $offer["id"] . "')" . '"' . ">&times;</button>
                                        <br><label for='refusal_reason' class='form-label'>Raison de refus de<button class='btn btn-link' onclick=" . '"' . "RedirectAdminToCollapse('offer', " . $offer["id"] . ")" . '"' . "> l'annonce N° " . $offer["id"] . "</button></label>
                                        <form id='refuse_offer_" . $offer["id"] . "_form'>
                                            <textarea class='form-control' rows='3' id='refusal_reason' name='refusal_reason'></textarea>
                                            <button class='btn btn-danger' onclick=" . '"' . "ValidateOffers('refuse', " . $offer["id"] . ", 'Refuser Annonce', 'Etes vous sur de refuser cette annonce?', 'Annuler', 'Procéder')" . '"' . ">Refuser</button>
                                        </form>
                                    </div>
                                </div>";
                            }
                            // the admin can only delete refused offers
                            else if($offer["status"] == 2){
                                $response_array["offers_html"] .= "
                                    <div class='options btn-group btn-block'>
                                        <form id='delete_offer_" . $offer["id"] . "_form' class='btn btn-danger'>
                                            <button class='btn btn-danger' onclick=" . '"' . "ValidateOffers('delete', " . $offer["id"] . ", 'Supprimer Annonce', 'Etes vous sur de supprimer cette annonce?', 'Annuler', 'Procéder')" . '"' . ">Supprimer</button>
                                        </form>
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

        // assign the ratings array
        $response_array["avg_ratings"] = $offerRatings;

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

    // display the offer refusal popup
    private function DisplayOfferRefusalPopup(){
        $response_array["valid_role"] = $response_array["display_refusal_popup"] = false;
        $response_array["danger_mode"] = true;
        $response_array["alert_title"] = "Refuser Annonce";
        $response_array["alert_text"] = $response_array["alert_icon"] = $response_array["error"] = "";
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["offer_id"])){
            // the user must be logged in and an admin
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
                        $response_array["display_refusal_popup"] = true;
                        $response_array["refusal_popup_id"] = "offer_refusal_popup_" . $offerId->value;
                    } else{
                        $response_array["alert_text"] = "L'annonce soit n'existe pas soit elle est déja validée ou refusée";
                        $response_array["alert_icon"] = "warning";
                    }
                }
            }
        }

        echo json_encode($response_array);
    }

    // refuse an offer
    private function RefuseOffer(){
        $response_array["valid_role"] = false;
        $response_array["danger_mode"] = true;
        $response_array["alert_title"] = "Refuser Annonce";
        $response_array["alert_text"] = $response_array["alert_icon"] = $response_array["error"] = "";
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["offer_id"])){
            // the user must be logged in and a validated admin
            if($this->UserHasAdminPriveleges()){
                $response_array["valid_role"] = true;
                // sanitize the user's input
                $offerId = new Input($_POST["offer_id"]);
                $offerId->Sanitize();
                $refusalReason = new Input($_POST["refusal_reason"]);
                $refusalReason->Sanitize();
                // ensure a valid offer id
                if(!preg_match("/^(0|[1-9][0-9]*)$/", $offerId->value)){
                    $response_array["error"] = "Invalid input";
                }
                // ensure a non-empty reason
                else if($refusalReason->value == ""){
                    $response_array["error"] = "Veuillez spécifier une raison pour le refus";
                } else{
                    // make sure that the offer exists and is indeed in a pending state
                    if($this->adminModel->OfferHasStatus($offerId->value, 0)){
                        // update the status of the offer
                        $this->adminModel->ChangeOfferStatus($offerId->value, 2);
                        // insert the refusal reason
                        $this->adminModel->InsertOfferRefusal(array("offer_id" => $offerId->value, "refusal_reason" => $refusalReason->value));
                        $response_array["alert_text"] = "Annonce refusée avec succés";
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
                            $message = "Malheureusement cette annonce des cours particuliers a été refsuée:
                            Wilaya: " . $offerDetails[0]["state"] . "
                            Commune: " . $offerDetails[0]["commune"] . "
                            Palier: " . $offerDetails[0]["level"] . "
                            Matière: " . $offerDetails[0]["subject"] . "
                            La raison de refus est : " . $refusalReason->value;
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

    // delete an offer
    private function DeleteOffer(){
        $response_array["valid_role"] = false;
        $response_array["danger_mode"] = true;
        $response_array["alert_title"] = "Supprimer Annonce";
        $response_array["alert_text"] = $response_array["alert_icon"] = $response_array["error"] = "";
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["offer_id"])){
            // the user must be logged in and an admin
            if($this->UserHasAdminPriveleges()){
                $response_array["valid_role"] = true;
                // sanitize the user's input
                $offerId = new Input($_POST["offer_id"]);
                $offerId->Sanitize();

                // ensure a valid offer id
                if(!preg_match("/^(0|[1-9][0-9]*)$/", $offerId->value)){
                    $response_array["error"] = "Invalid input";
                } else{
                    // make sure that the offer exists and is refused
                    if($this->adminModel->OfferHasStatus($offerId->value, 2)){
                        // delete the offer's ratings (if found)
                        $this->adminModel->DeleteOfferRatings($offerId->value);
                        // delete the offer's refusal info (if found)
                        $this->adminModel->DeleteOfferRefusal($offerId->value);
                        // delete the offer's info
                        $this->adminModel->DeleteOffer($offerId->value);
                        
                        $response_array["alert_text"] = "Annonce supprimée avec succés";
                        $response_array["alert_icon"] = "success";
                        $response_array["danger_mode"] = false;
                        // display the new number of offers of each category
                        $response_array["offers_number_html"] = $this->DisplayCategoriesNumber("return");
                    } else{
                        $response_array["alert_text"] = "L'annonce soit n'existe pas soit elle n'est pas refusée";
                        $response_array["alert_icon"] = "warning";
                        $response_array["danger_mode"] = true;
                    }
                }
            }
        }

        echo json_encode($response_array);
    }

    // display the number of teachers sign ups of each category
    private function DisplayTeachersSignUpsCategoriesNumber($returnOrEcho){
        $response_array["valid_role"] = false;
        $response_array["teachers_sign_ups_number_html"] = "";
        // the user must be logged in and an admin
        if($this->UserHasAdminPriveleges()){
            $response_array["valid_role"] = true;
            // get the number of teachers sign ups of each category
            $teachersSignUpsNumber = $this->adminModel->TeachersSignUpsCategoriesNumber();
            $response_array["teachers_sign_ups_number_html"] = "
        <div id='teachers_sign_ups_section' class='container tab-pane active'><br>
            <h3>Gestion des enseignants</h3>
            <br>
            <div class='card-deck'>
                <div class='card'>
                  <div class='card-body text-center'>
                    <p class='card-text'>Inscriptions en attente de validation</p>
                    <p><b>" . $teachersSignUpsNumber[0]["pending"] . "</b>  Inscriptions</p>
                    <form id='display_teachers_sign_ups_0_form'>
                        <button class='btn btn-link' onclick=" .  '"' . "DisplayTeachersSignUpsDetails(0)" . '"' . ">Voir Détails</button>
                    </form>
                  </div>
                </div>
                <div class='card'>
                  <div class='card-body text-center'>
                    <p class='card-text'>Inscriptions Acceptées</p>
                    <p> <b>" . $teachersSignUpsNumber[0]["validated"] . "</b>  Inscriptions</p>
                    <form id='display_teachers_sign_ups_1_form'>
                        <button class='btn btn-link' onclick=" .  '"' . "DisplayTeachersSignUpsDetails(1)" . '"' . ">Voir Détails</button>
                    </form>
                  </div>
                </div>
            </div>
            <hr>
            <div id='sign_ups_details'></div>
        </div>";
        }

        // the "return" is for displaying the new number of offers of each category
        if($returnOrEcho === "echo"){
            echo json_encode($response_array);
        } else if($returnOrEcho === "return"){
            return $response_array["teachers_sign_ups_number_html"];
        }
    }

    // display the details of all teachers sign ups
    private function DisplayTeachersSignUpsDetails(){
        $response_array["valid_role"] = false;
        $response_array["sign_ups_details_html"] = $response_array["error"] = "";
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
                    // retrieve all sign ups of the specified status
                    $allSignUps = $this->adminModel->RetrieveTeachersSignUps($status->value);
                    if(empty($allSignUps)){
                        $response_array["sign_ups_details_html"] = "Pas d'inscriptions de cette catégorie";
                    } else{
                        // these are used for pagination
                        $signUpsPerPage = 0;
                        $pageNumber = 0;

                        // display the literal version of the status
                        if($status->value == 0){
                            $statusStr = "en attente de validation";
                        } else if($status->value == 1){
                            $statusStr = "acceptées";
                        }
                        $response_array["sign_ups_details_html"] .= "
            <div class='sign-ups'>
                <h3 id='sign_ups_header'>Listes des inscriptions " . $statusStr . "</h3>";
                    // display the previous and next buttons only if there are more than 6 sign ups
                    if(count($allSignUps) > 6){
                        $response_array["sign_ups_details_html"] .= "
                <div class='centring pagination'>
                    <a id='previous_page_btn_sign-ups' class='pagination-btn-active btn btn-link' onclick=" . '"' . "pagination(0, 'sign-ups')" . '"' . " hidden>&ltPrécédent</a>
                    <a id='next_page_btn_sign-ups' class='pagination-btn-active btn btn-link' onclick=" . '"' . "pagination(1, 'sign-ups')" . '"' . ">Suivant&gt</a>
                </div>";
                    }
                        $response_array["sign_ups_details_html"] .="
                <div id='accordion_sign_ups'>";
                        foreach($allSignUps as $signUp){
                            // if it's the first sign up of the page
                            if($signUpsPerPage === 0){
                                $response_array["sign_ups_details_html"] .= '
                    <div id="pagination_sign-ups_' . $pageNumber . '" class="page';

                                // the first page should be automatically visible
                                if($pageNumber === 0)
                                {
                                    $response_array["sign_ups_details_html"] .= ' page-active-sign-ups';
                                }

                                $response_array["sign_ups_details_html"] .= '">';

                                // increment the page number
                                $pageNumber += 1;
                            }
                            $response_array["sign_ups_details_html"] .= "
                        <div class='card'>
                            <div class='card-header'>
                                <a class='card-link' id='sign_up_title_" . $signUp["teacher_id"] . "' data-toggle='collapse' href='#collapse_sign_up_". $signUp["teacher_id"] . "'>
                                    Inscription N° " . $signUp["teacher_id"] . "
                                </a>
                            </div>
                            <div id='collapse_sign_up_". $signUp["teacher_id"] . "' class='collapse show' data-parent='#accordion_sign_ups'>
                                <div class='card-body'>
                                    <div class='mycard order-card row'>
                                        <div class='col-sm-3'>
                                            <h4>Infos du compte</h4>
                                            <p>Email: " . $signUp["email"] . "</p>
                                            <p>Nom d'utilisateur: " . $signUp["username"] ."</p>
                                        </div>
                                        <div class='col-sm-3'>
                                            <h4>Infos Légales</h4>
                                            <p>Nom: " . $signUp["last_name"] . "</p>
                                            <p>Prénom: " . $signUp["first_name"] ."</p>
                                        </div>
                                        <div class='col-sm-3'>
                                            <h4>Photos</h4>
                                            <p>Carte d'identité: <button class='btn btn-link' onclick=" . '"' ."showpopup('sign_up_card_img_" . $signUp["teacher_id"] . "')" . '"' . ">Voir image</button></p>
                                            <div class='popup-hide' id='sign_up_card_img_" .  $signUp["teacher_id"] . "'> 
                                                <button class='btn btn-primary' id='sign_up_card_img_hidepopup_" . $signUp["teacher_id"] . "' onclick=" . '"' . "hidepopup('sign_up_card_img_" .  $signUp["teacher_id"] . "')" . '"' . ">&times;</button>
                                                <img class='card-img-top' src='../Images/IDCards/" . md5($signUp["card_photo"]) .".jpeg' alt='Card Img' style='height:200px'>
                                            </div>
                                            <p>Enseignant: <button class='btn btn-link' onclick=" . '"' ."showpopup('sign_up_teacher_img_" . $signUp["teacher_id"] . "')" . '"' . ">Voir image</button></p>
                                            <div class='popup-hide' id='sign_up_teacher_img_" .  $signUp["teacher_id"] . "'> 
                                                <button class='btn btn-primary' id='sign_up_teacher_img_hidepopup_" . $signUp["teacher_id"] . "' onclick=" . '"' . "hidepopup('sign_up_teacher_img_" .  $signUp["teacher_id"] . "')" . '"' . ">&times;</button>
                                                <img class='card-img-top' src='../Images/Teachers/" . md5($signUp["teacher_photo"]) .".jpeg' alt='Teacher Img' style='height:200px'>
                                            </div>
                                        </div>
                                        <div class='col-sm-3'>
                                            <h4>Infos Prefossionnelles</h4>
                                            <p>Téléphone: " . $signUp["phone"] . "</p>
                                            <p>Lien de CV: <a target='_blank' href='" . $signUp["cv_link"] ."'>Cliquez ici</a></p>
                                        </div>";
                            $response_array["sign_ups_details_html"] .="
                                    </div>
                                </div>";
                            
                            // the admin can only accept and refuse pending sign ups
                            if($signUp["sign_up_status"] == 0){
                                $response_array["sign_ups_details_html"] .="
                                <div class='options btn-group btn-block'>
                                    <form class='btn btn-success' id='acceptteacher_" . $signUp["teacher_id"] . "_form'>
                                        <button class='btn btn-success' onclick=" . '"' . "UpdateTeachers('acceptteacher', " . $signUp["teacher_id"] . ", 'Accepter Inscription', 'Etes vous sur d\\'accepter cet enseignant?', 'Annuler', 'Procéder')" . '"' . ">Accepter</button>
                                    </form>
                                    <form id='display_teacher_refusal_popup_" . $signUp["teacher_id"] . "_form' class='btn btn-danger'>
                                        <button class='btn btn-danger' onclick=" .  '"' . "DisplayRefusalPopup('teacher', " . $signUp["teacher_id"] . ")" . '"' . ">Refuser et Supprimer enseignant</button>
                                    </form>
                                    <div class='popup-hide' id='teacher_refusal_popup_" . $signUp["teacher_id"] . "'> 
                                        <button class='btn btn-primary' id='teacher_refusal_hidepopup_" . $signUp["teacher_id"] . "' onclick=" . '"' . "hidepopup('teacher_refusal_popup_" . $signUp["teacher_id"] . "')" . '"' . ">&times;</button>
                                        <br><label for='refusal_reason' class='form-label'>Raison de refus de<button class='btn btn-link' onclick=" . '"' . "RedirectAdminToCollapse('sign_up', " . $signUp["teacher_id"] . ")" . '"' . "> l'inscription N° " . $signUp["teacher_id"] . "</button></label>
                                        <form id='refuseteacher_" . $signUp["teacher_id"] . "_form'>
                                            <textarea class='form-control' rows='3' id='refusal_reason' name='refusal_reason'></textarea>
                                            <button class='btn btn-danger' onclick=" . '"' . "UpdateTeachers('refuseteacher', " . $signUp["teacher_id"] . ", 'Refuser Inscription', 'Etes vous sur de refuser et supprimer cet enseignant?', 'Annuler', 'Procéder')" . '"' . ">Refuser</button>
                                        </form>
                                    </div>
                                </div>";
                            }
                            // the admin can only delete accepted teachers
                            else if($signUp["sign_up_status"] == 1){
                                $response_array["sign_ups_details_html"] .= "
                                    <div class='options btn-group btn-block'>
                                        <form class='btn btn-danger' id='deleteteacher_" . $signUp["teacher_id"] . "_form'>
                                            <button class='btn btn-danger' onclick=" .  '"' . "UpdateTeachers('deleteteacher', " . $signUp["teacher_id"] . ", 'Supprimer Enseignant', 'Etes vous sur de supprimer cet enseignant?', 'Annuler', 'Procéder')" . '"' . ">Supprimer enseignant</button>
                                        </form>
                                    </div>";
                            }
                            $response_array["sign_ups_details_html"] .= "
                            </div>
                        </div>";
                            
                            // indicate that a sign up has been echoed
                            $signUpsPerPage += 1;

                            // if it's the last sign up of the page
                            if($signUpsPerPage === 6){
                                $response_array["sign_ups_details_html"] .= "
                    </div>";
                                // reinitialize the number of sign ups echoed in the page
                                $signUpsPerPage = 0;
                            }
                        }
                        // this is in case the last page has less than 6 sign ups
                        if($signUpsPerPage > 0 && $signUpsPerPage < 6){
                            $response_array["sign_ups_details_html"] .= "
                    </div>";
                        }

                    $response_array["sign_ups_details_html"] .= "
                </div>
            </div>";
                    }
                }
            }
        }

        echo json_encode($response_array);
    }

    // accept a teacher sign up
    private function AcceptTeacher(){
        $response_array["valid_role"] = false;
        $response_array["danger_mode"] = true;
        $response_array["alert_title"] = "Accepter Inscription";
        $response_array["alert_text"] = $response_array["alert_icon"] = $response_array["error"] = "";
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["teacher_id"])){
            // the user must be logged in and an admin
            if($this->UserHasAdminPriveleges()){
                $response_array["valid_role"] = true;
                // sanitize the user's input
                $teacherId = new Input($_POST["teacher_id"]);
                $teacherId->Sanitize();
                // ensure a valid teacher id
                if(!preg_match("/^(0|[1-9][0-9]*)$/", $teacherId->value)){
                    $response_array["error"] = "Invalid input";
                } else{
                    // make sure that the teacher exists and is indeed in a pending state
                    $query = "SELECT id FROM teachers WHERE id = ? AND sign_up_status = ?";
                    if($this->adminModel->TeacherHasStatus($query, array($teacherId->value, 0))){
                        // update the status of the teacher
                        $this->adminModel->ChangeTeacherStatus($teacherId->value, 1);
                        $response_array["alert_text"] = "Inscription acceptée avec succés";
                        $response_array["alert_icon"] = "success";
                        $response_array["danger_mode"] = false;
                        // display the new number of sign ups of each category
                        $response_array["teachers_sign_ups_number_html"] = $this->DisplayTeachersSignUpsCategoriesNumber("return");

                        // get the necessary details of the offer to send an email to the teacher
                        $teacherDetails = $this->adminModel->GetTeacherInfoById($teacherId->value);
                        // send an email to the teacher
                        if(!empty($teacherDetails)){
                            $to = $teacherDetails[0]["email"];
                            $subject = "Validation de votre inscription";
                            $message = "Féliciations Mr/Mme " . $teacherDetails[0]["last_name"] . " " . $teacherDetails[0]["first_name"] . "!\nVotre inscription dans notre site des cours particuliers a été acceptée.\nMerci de nous choisir!";
                            $headers = 'From:emailprogrammingtest@gmail.com' . "\r\n"; 
                            // send the email to the user
                            mail($to, $subject, $message, $headers);
                        }
                    } else{
                        $response_array["alert_text"] = "L'enseignant soit n'existe pas soit il est déja accepté ou refusé";
                        $response_array["alert_icon"] = "warning";
                    }
                }
            }
        }

        echo json_encode($response_array);
    }

    // display the teacher refusal popup
    private function DisplayTeacherRefusalPopup(){
        $response_array["valid_role"] = $response_array["display_refusal_popup"] = false;
        $response_array["danger_mode"] = true;
        $response_array["alert_title"] = "Refuser Annonce";
        $response_array["alert_text"] = $response_array["alert_icon"] = $response_array["error"] = "";
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["teacher_id"])){
            // the user must be logged in and an admin
            if($this->UserHasAdminPriveleges()){
                $response_array["valid_role"] = true;
                // sanitize the user's input
                $teacherId = new Input($_POST["teacher_id"]);
                $teacherId->Sanitize();
                // ensure a valid teacher id
                if(!preg_match("/^(0|[1-9][0-9]*)$/", $teacherId->value)){
                    $response_array["error"] = "Invalid input";
                } else{
                    // make sure that the teacher's sign up exists and is indeed in a pending state
                    $query = "SELECT id FROM teachers WHERE id = ? AND sign_up_status = ?";
                    if($this->adminModel->TeacherHasStatus($query, array($teacherId->value, 0))){
                        $response_array["display_refusal_popup"] = true;
                        $response_array["refusal_popup_id"] = "teacher_refusal_popup_" . $teacherId->value;
                    } else{
                        $response_array["alert_text"] = "L'enseignant soit n'existe pas soit il est déja accepté";
                        $response_array["alert_icon"] = "warning";
                    }
                }
            }
        }

        echo json_encode($response_array);
    }

    // refuse a teacher sign up
    private function RefuseTeacher(){
        $response_array["valid_role"] = false;
        $response_array["danger_mode"] = true;
        $response_array["alert_title"] = "Refuser et supprimer Inscription";
        $response_array["alert_text"] = $response_array["alert_icon"] = $response_array["error"] = "";
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["teacher_id"]) && isset($_POST["refusal_reason"])){
            // the user must be logged in and an admin
            if($this->UserHasAdminPriveleges()){
                $response_array["valid_role"] = true;
                // sanitize the user's input
                $teacherId = new Input($_POST["teacher_id"]);
                $teacherId->Sanitize();
                $refusalReason = new Input($_POST["refusal_reason"]);
                $refusalReason->Sanitize();

                // ensure a valid teacher id
                if(!preg_match("/^(0|[1-9][0-9]*)$/", $teacherId->value)){
                    $response_array["error"] = "Invalid input";
                } 
                // ensure a non-empty reason
                else if($refusalReason->value == ""){
                    $response_array["error"] = "Veuillez spécifier une raison pour le refus";
                }else{
                    // make sure that the teacher exists and is indeed in a pending state
                    $query = "SELECT id FROM teachers WHERE id = ? AND sign_up_status = ?";
                    if($this->adminModel->TeacherHasStatus($query, array($teacherId->value, 0))){
                        // get the details of the teacher
                        $teacherDetails = $this->adminModel->GetTeacherInfoById($teacherId->value);
                        if(!empty($teacherDetails)){
                            // delete the teacher's info
                            $this->adminModel->DeleteTeacher($teacherId->value);
                            // delete both images of the teacher
                            unlink("../Images/IDCards/" . md5($teacherDetails[0]["card_photo"]) . ".jpeg");
                            unlink("../Images/Teachers/" . md5($teacherDetails[0]["teacher_photo"]) . ".jpeg");
                            // delete the teacher's user's info
                            $this->adminModel->auth->admin()->deleteUserById($teacherDetails[0]["user_id"]);

                            $response_array["alert_text"] = "Inscription refusée et supprimée avec succés";
                            $response_array["alert_icon"] = "success";
                            $response_array["danger_mode"] = false;
                            // display the new number of sign ups of each category
                            $response_array["teachers_sign_ups_number_html"] = $this->DisplayTeachersSignUpsCategoriesNumber("return");

                            // send the email to the teacher
                            $to = $teacherDetails[0]["email"];
                            $subject = "Validation de votre inscription";
                            $message = "Mr/Mme " . $teacherDetails[0]["last_name"] . " " . $teacherDetails[0]["first_name"] . ",\nMalheureusement votre inscription dans notre site des cours particuliers a été refusée pour cette raison: " . $refusalReason->value . "\nVotre compte est alors supprimé.\nMerci de nous choisir!";
                            $headers = 'From:emailprogrammingtest@gmail.com' . "\r\n"; 
                            mail($to, $subject, $message, $headers);
                        }
                    } else{
                        $response_array["alert_text"] = "L'enseignant soit n'existe pas soit il est déja accepté";
                        $response_array["alert_icon"] = "warning";
                    }
                }
            }
        }

        echo json_encode($response_array);
    }

    // delete a teacher
    private function DeleteTeacher(){
        $response_array["valid_role"] = false;
        $response_array["danger_mode"] = true;
        $response_array["alert_title"] = "Supprimer Enseignant";
        $response_array["alert_text"] = $response_array["alert_icon"] = $response_array["error"] = "";
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["teacher_id"])){
            // the user must be logged in and an admin
            if($this->UserHasAdminPriveleges()){
                $response_array["valid_role"] = true;
                // sanitize the user's input
                $teacherId = new Input($_POST["teacher_id"]);
                $teacherId->Sanitize();
                // ensure a valid teacher id
                if(!preg_match("/^(0|[1-9][0-9]*)$/", $teacherId->value)){
                    $response_array["error"] = "Invalid input";
                } else{
                    // make sure that the teacher exists and is indeed accepted
                    $query = "SELECT id FROM teachers WHERE id = ? AND sign_up_status = ?";
                    if($this->adminModel->TeacherHasStatus($query, array($teacherId->value, 1))){
                        // get the details of the teacher
                        $teacherDetails = $this->adminModel->GetTeacherInfoById($teacherId->value);
                        if(!empty($teacherDetails)){
                            // delete the teacher's offers (if found)
                            $teacherOffers = $this->adminModel->GetOffersIdsByTeacherId($teacherId->value);
                            if(!empty($teacherOffers)){
                                foreach($teacherOffers as $offer){
                                    // delete the offer's ratings (if found)
                                    $this->adminModel->DeleteOfferRatings($offer["offer_id"]);
                                    // delete the offer's refusal info (if found)
                                    $this->adminModel->DeleteOfferRefusal($offer["offer_id"]);
                                    // delete the offer's info
                                    $this->adminModel->DeleteOffer($offer["offer_id"]);
                                }
                            }

                            // delete the teacher's info
                            $this->adminModel->DeleteTeacher($teacherId->value);
                            // delete both images of the teacher
                            unlink("../Images/IDCards/" . md5($teacherDetails[0]["card_photo"]) . ".jpeg");
                            unlink("../Images/Teachers/" . md5($teacherDetails[0]["teacher_photo"]) . ".jpeg");
                            // delete the teacher's user's info
                            $this->adminModel->auth->admin()->deleteUserById($teacherDetails[0]["user_id"]);

                            $response_array["alert_text"] = "Enseignant supprimé avec succés";
                            $response_array["alert_icon"] = "success";
                            $response_array["danger_mode"] = false;
                            // display the new number of sign ups of each category
                            $response_array["teachers_sign_ups_number_html"] = $this->DisplayTeachersSignUpsCategoriesNumber("return");

                            // send the email to the teacher
                            $to = $teacherDetails[0]["email"];
                            $subject = "Suppression de votre compte";
                            $message = "Mr/Mme " . $teacherDetails[0]["last_name"] . " Un admin a supprimé votre compte.\nMerci de nous choisir";
                            $headers = 'From:emailprogrammingtest@gmail.com' . "\r\n"; 
                            mail($to, $subject, $message, $headers);
                        }
                    } else{
                        $response_array["alert_text"] = "L'enseignant soit n'existe pas soit il n'est pas encore accepté";
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