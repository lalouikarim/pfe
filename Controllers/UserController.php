<?php

class UserController{
    private $userModel;

    public function __construct(){
        // create a user model
        require "../Models/AccountModel.php";
        $this->userModel = new AccountModel();
    }

    public function performAction($action){
        switch($action){
            case "displayhomepage":
                $this->DisplayOffers("all", "params", "echo", "");
                break;
            case "displayteacherdetailspopup":
                $this->DisplayTeacherDetails();
                break;
            case "rateoffer":
                $this->RateOffer();
                break;
            case "searchoffers":
                $this->SearchOffers();
                break;
            default:
                break;
        }
    }

    // display navbar
    private function DisplayNavbar($dir){
        $navbar = "
        <!-- Brand -->
        <a class='navbar-brand' href='" . $dir . "index.html'>Cours Particuliers</a>
        <!-- Toggler/collapsibe Button -->
        <button class='navbar-toggler' type='button' data-toggle='collapse' data-target='#collapsibleNavbar'>
            <span class='navbar-toggler-icon'></span>
        </button>
        <!-- Navbar links -->
        <div class='collapse navbar-collapse' id='collapsibleNavbar'>
            <ul class='navbar-nav'>";
            
            // display authentication links if the user isn't logged in
            if(!$this->userModel->auth->isLoggedIn()){
                $navbar .= "
                <li class='nav-item'>
                    <a class='nav-link' href='" . $dir . "Views/SignInView.html'>Se Connecter</a>
                </li>
                <li class='nav-item'>
                    <a class='nav-link' href='" . $dir . "Views/SignUpView.html'>S'inscrire</a>
                </li>";
            }

            $navbar .= "
                <li class='nav-item'>
                    <a class='nav-link' href='#'>A propos</a>
                </li>
                <li class='nav-item'>
                    <a class='nav-link' href='#'>Aide</a>
                </li>";

            // display sign out link if the user is logged in
            if($this->userModel->auth->isLoggedIn()){
                $navbar .= "
                <li class='nav-item '>
                    <a class='nav-link' onclick=" . '"' . "LogOut('" . $dir . "')" . '"' . ">Se déconnecter <i class='fas fa-sign-out-alt'></i> </a>
                </li>";  
            }
            $navbar .= "
            </ul>  
        </div>";

        return $navbar;
    }

    // display the footer
    private function DisplayFooter(){
        $footer = "
        <div class='row'>
            <div class='col-sm-4 s-foot-1 txt-center' id='comments_section'>
                <br>
                <div class='form-group'>
                    <h3>Envoyer nous vos commentaires</h3>
                    <br>
                    <form id='send_comment_form' action='' method='post'>
                        <textarea class='form-control form-control-sm' rows='5' id='comment' name='comment_str' placeholder='Commentaire'></textarea>
                        <button class='btn btn-primary btn-block btn-sm'>Envoyer</button>
                    </form>
                </div>
            </div>
            <div class='col-sm-4 s-foot-2 txt-center'>
                <br>
                <h3>Infromations</h3>
                <br>
                <a href='#'>A propos</a> <hr>
                <div class='footer-scm'>
                    <p>Nous suivre</p>
                    <a href='https://www.facebook.com/' target='_blank'><i class='fab fa-facebook'></i></a>
                    <a href='https://www.instagram.com/' target='_blank'><i class='fab fa-instagram'></i></a>
                    <a href='#'><i class='fas fa-envelope'></i></a>
                </div>
                <hr>
                <p>Appelez nous <i class='fas fa-phone-square'></i> <strong> 0123 45 67 89 </strong></p>
            </div>
        </div>";

        return $footer;
    }

    // display offers
    private function DisplayOffers($query, $queryParams, $returnOrEcho, $dir){
        // send this array to the client
        $responseArray = [];
        // this array holds the average rating of each offer
        $offersRatings = [];
        // this array holds a user's rating of each offer
        $userOfferRatings = [];

        // use these variables for pagination
        $offersPerPage = 0;
        $pageNumber = 0;

        // all offers
        if($query === "all"){
            $query = "SELECT teachers.id AS teacher_id, teachers.first_name, teachers.last_name, offers.id AS offer_id, offers.state, offers.commune, offers.level, offers.subject, offers.price FROM offers INNER JOIN teachers ON teachers.id = offers.teacher_id WHERE status = ?";
            // the 1 represents the accepted status of an offer
            $queryParams = array(1);
        }

        // retrieve offers
        $offersDetails = $this->userModel->RetrieveOffers($query, $queryParams);

        if(empty($offersDetails)){

        } else{
            $responseArray["offers_html"] = "";
            // display the previous and next btns only if there are more than 12 offers
            if(count($offersDetails) > 12){
                $responseArray["offers_html"] .= "
                <div class='centring pagination'>
                    <a id='previous_page_btn_offers' class='active btn btn-sm btn-link' onclick=" . '"' . "pagination(0, 'offers')" . '"' . " hidden> <i class='fas fa-chevron-circle-left'></i> Précédent </a>
                    <a id='next_page_btn_offers' class='active btn btn-sm btn-link' onclick=" . '"' . "pagination(1, 'offers')" . '"' . "> Suivant <i class='fas fa-chevron-circle-right'></i> </a>
                </div>";
            }

            foreach($offersDetails as $offer){
                // initialze the user's rating of the offer
                $userOfferRatings[$offer["offer_id"]] = 0;
                
                // if it's the first offer of the page
                if($offersPerPage === 0){
                    $responseArray["offers_html"] .= "
                    <div id='pagination_offers_" . $pageNumber . "' class='page";

                    // the first page should be automatically visible
                    if($pageNumber === 0)
                    {
                        $responseArray["offers_html"] .= " page-active-offers";
                    }

                    $responseArray["offers_html"] .= "'>";

                    // increment the page number
                    $pageNumber += 1;
                }
                $responseArray["offers_html"] .= "
                        <div class='card offer-card' style='width:250px' onmouseover='showbtn( " . $offer["offer_id"] . ")' onmouseout='hidebtn( " . $offer["offer_id"] . ")'>
                            <div class='d-none d-lg-block' id='btn-card-" . $offer["offer_id"] . "'>
                                <button class='btn-offer btn-offer-details btn btn-block btn-primary btn-sm' id='show_teacher_details_popup_" . $offer["offer_id"] . "' onclick=" . '"' . "DisplayTeacherDetailsPopup(" . $offer["offer_id"] . ", '" . $dir . "')" . '"' . ">Détails du prof</button>
                            </div>
                            <div class='card-body'>";
                if($offer["level"] === "primary"){
                    $offer["level"] = "Primaire";
                } else if($offer["level"] === "middle"){
                    $offer["level"] = "Moyenne";
                } else if($offer["level"] === "high"){
                    $offer["level"] = "Secondaire";
                } else if($offer["level"] === "college"){
                    $offer["level"] = "Universitaire";
                }
                $responseArray["offers_html"] .= "
                                <h6>" . $offer["subject"] . " - " . $offer["level"] . "</h6>";


                // retrieve the rating details of the offer
                $offerRatings = $this->userModel->RetrieveOfferRatings($offer["offer_id"]);
                if(empty($offerRatings)){
                    $offersRatings[$offer["offer_id"]] = 0;
                    $ratesNumber = 0;
                } else{
                    $offersRatings[$offer["offer_id"]] = $offerRatings[0]["avg_rating"];
                    $ratesNumber = $offerRatings[0]["rates_number"];
                }

                // if the user id logged in then retrieve their rating of the offer (if found)
                if($this->userModel->auth->isLoggedIn()){
                    $userOfferRating = $this->userModel->RetrieveUserOfferRating($this->userModel->auth->getUserId(), $offer["offer_id"]);
                    if(!empty($userOfferRating)){
                        $userOfferRatings[$offer["offer_id"]] = $userOfferRating[0]["rating"];
                    }
                }
                $responseArray["offers_html"] .= "
                                <div class='post-action'>
                                    <div class='d-inline-flex'>
                                        <select class='avg-rating' id='offer_rating_" . $offer["offer_id"] . "' data-id='offer_rating_" . $offer["offer_id"] . "'>
                                            <option value='0'>0</option>
                                            <option value='1' >1</option>
                                            <option value='2' >2</option>
                                            <option value='3' >3</option>
                                            <option value='4' >4</option>
                                            <option value='5' >5</option>
                                        </select>
                                        <small class='card-text' id='offer_rates_number_" . $offer["offer_id"] . "'><i class='fas fa-users'></i> " . $ratesNumber . "</small>
                                        <div style='clear: both;'></div>
                                    </div>
                                </div>
                                <span class='card-offer-details'>
                                    <small>" . $offer["last_name"] . " </small>
                                    <small>" . $offer["first_name"] . "</small>
                                </span>
                                <br>
                                <span class='card-offer-details'>
                                    <small>" . $offer["commune"] . ", </small>
                                    <small>" . $offer["state"] . "</small>
                                </span>
                                <br>
                                <span class='card-product-prices'>
                                    <small>" . $offer["price"] . " DA</small>
                                </span>
                                <!-- this btn is only visible on small screens -->
                                <div class ='d-sm-block d-md-block d-lg-none' id='btn-card-phone-" . $offer["offer_id"] . "'>
                                    <button class='btn btn-block btn-outline-primary btn-sm' id='show_teacher_details_popup_phone_" . $offer["offer_id"] . "' onclick='DisplayTeacherDetailsPopup(" . $offer["offer_id"] . ")'>Détails du prof</button>
                                </div>
                                <div class ='d-sm-block d-md-block d-lg-none' id='rate-btn-card-phone-" . $offer["offer_id"] . "'>
                                    <button class='btn btn-block btn-outline-primary btn-sm' id='rate_offer_popup_phone_" . $offer["offer_id"] . "' onclick=" . '"' . "showpopup('rate_offer_" . $offer["offer_id"] . "_popup')" . '"' . ">Noter annonce</button>
                                </div>
                            </div>
                            <div class='d-none d-lg-block' id='rate-btn-card-" . $offer["offer_id"] . "'>
                                <button class='btn-offer btn-offer-details btn btn-block btn-primary btn-sm' id='rate_offer_popup_" . $offer["offer_id"] . "' onclick=" . '"' . "showpopup('rate_offer_" . $offer["offer_id"] . "_popup')" . '"' . ">Noter annonce</button>
                            </div>
                            <div class='popup-hide' id='teacher_details_popup_" . $offer["offer_id"] . "'></div>
                            <div class='popup-hide' id='rate_offer_" . $offer["offer_id"] . "_popup'>
                                <button class='btn btn-primary btn-sm btn-block sticky-top' id='hide_rate_offer_popup_" . $offer["offer_id"] . "' onclick=" . '"' . "hidepopup('rate_offer_" . $offer["offer_id"] . "_popup')" . '"' . ">&times;</button>
                                <hr>
                                <div class='post-action'>
                                    <select class='user-rating' id='user_offer_rating_" . $offer["offer_id"] . "' data-id='user_offer_rating_" . $offer["offer_id"] . "'>
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
                        </div>";

                // indicate that an offer has been echoed
                $offersPerPage += 1;
                // if it's the last product of the page
                if($offersPerPage === 12){
                    $responseArray["offers_html"] .= "
                    </div>";

                    // reinitialize the number of offers echoed in the page
                    $offersPerPage = 0;
                }
            }

            // this is in case the last page has less than 12 offers
            if($offersPerPage > 0 && $offersPerPage < 12){
                $responseArray["offers_html"] .= "
                    </div>";
            }
        }

        // display the navbar and the footer
        $responseArray["navbar"] = $this->DisplayNavbar($dir);
        $responseArray["footer"] = $this->DisplayFooter();

        // assign the ratings arrays
        $responseArray["avg_ratings"] = $offersRatings;
        $responseArray["user_ratings"] = $userOfferRatings;

        // either display the offers html or return it
        if($returnOrEcho === "echo"){
            echo json_encode($responseArray);
        } else{
            return $responseArray;
        }
    }

    // display an offer's teacher details
    private function DisplayTeacherDetails(){
        $responseArray["logged_in"] = false;
        $responseArray["error"] = "";
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["offer_id"]) && isset($_POST["dir"])){
            // sanitize the user's input
            $offerId = new Input($_POST["offer_id"]);
            $offerId->Sanitize();
            $dir = new Input($_POST["dir"]);
            $dir->Sanitize();

            // ensure a valid id format
            if(!preg_match("/^(0|[1-9][0-9]*)$/", $offerId->value)){
                $responseArray["error"] = "Invalid input";
            } 
            // ensure a valid dir 
            else if(!preg_match("/^(|\.\.\/)$/", $dir->value)){
                $responseArray["error"] = "Invalid input";
            }
            else{
                if($this->userModel->auth->isLoggedIn()){
                    // indicate that the user is logged in
                    $responseArray["logged_in"] = true;

                    // retrieve the teacher's details
                    $teacherDetails = $this->userModel->RetrieveOfferTeacherInfo($offerId->value);
                    if(empty($teacherDetails)){
                        $responseArray["error"] = "L'enseignant n'existe pas";
                    } else{
                        $responseArray["teacher_details_html"] = "
                        <button class='btn btn-primary' id='teacher_details_hidepopup_" . $offerId->value . "' onclick=" . '"' . "hidepopup('teacher_details_popup_" .  $offerId->value . "')" . '"' . ">&times;</button>
                        <p>" . $teacherDetails[0]["last_name"] . " " . $teacherDetails[0]["first_name"] . "</p>
                        <p>" . $teacherDetails[0]["email"] . "</p>
                        <p>" . $teacherDetails[0]["phone"] . "</p>
                        <p><a href='" . $teacherDetails[0]["cv_link"] . "' target='_blank'>Cliquez ici pour voir le cv</a></p>
                        <img src='" . $dir->value . "Images/Teachers/" . md5($teacherDetails[0]["teacher_photo"]) . ".jpeg' alt='Teacher Photo' style='height:200px; width:300px;'>";
                    }
                }
            }
        }

        echo json_encode($responseArray);
    }

    // rate an offer
    private function RateOffer(){
        $responseArray["logged_in"] = false;
        $responseArray["error"] = "";
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["offer_id"]) && isset($_POST["offer_rating"])){
            // sanitize the user's input
            $offerId = new Input($_POST["offer_id"]);
            $offerId->Sanitize();
            $rating = new Input($_POST["offer_rating"]);
            $rating->Sanitize();

            // ensure a valid id format
            if(!preg_match("/^(0|[1-9][0-9]*)$/", $offerId->value)){
                $responseArray["error"] = "Invalid input";
            } 
            // ensure a valid rating format
            else if(!preg_match("/^(1|2|3|4|5)$/", $rating->value)){
                $responseArray["error"] = "Veuillez choisir une note valide";
            } else{
                if($this->userModel->auth->isLoggedIn()){
                    // indicate that the user is logged in
                    $responseArray["logged_in"] = true;
                    $userCanRate = true;

                    // set the account role
                    $this->userModel->SetAccountRole($this->userModel->auth->getEmail());

                    // a teacher can't rate their own offers
                    if($this->userModel->role === "teacher"){
                        if($this->userModel->TeacherOwnsOffer($offerId->value, $this->userModel->auth->getUserId())){
                            $responseArray["error"] = "Vous ne pouvez pas noter vos propres annonces";
                            $userCanRate = false;
                        }
                    }

                    if($userCanRate){
                        // the offer must be validated
                        if($this->userModel->OfferHasStatus($offerId->value, 1)){
                            // insert the rating
                            $this->userModel->InsertOfferRating($this->userModel->auth->getUserId(), $offerId->value, $rating->value);

                            // retrieve the new rating details of the offer
                            $offerRatings = $this->userModel->RetrieveOfferRatings($offerId->value);
                            if(empty($offerRatings)){
                                $responseArray["avg_rating"] = 0;
                                $responseArray["rates_number"] = "<small class='card-text' id='offer_rates_number_" . $offerId->value . "'><i class='fas fa-users'></i> 0</small>";
                            } else{
                                $responseArray["avg_rating"] = $offerRatings[0]["avg_rating"];
                                $responseArray["rates_number"] = "<small class='card-text' id='offer_rates_number_" . $offerId->value . "'><i class='fas fa-users'></i> " . $offerRatings[0]["rates_number"] . "</small>";
                            }
                        } else{
                            $responseArray["error"] = "L'annonce n'existe pas";
                        }
                    }
                }
            }
        }

        echo json_encode($responseArray);
    }

    // filter offers
    private function SearchOffers(){
        $responseArray["error"] = "";
        // this array holds the sql query parameters; the 1 represents the accepted status of an offer
        $queryParams = array(1);
        if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["subject"]) && isset($_POST["max_price"]) && isset($_POST["state"]) && isset($_POST["commune"]) && isset($_POST["first_name"]) && isset($_POST["last_name"])){
            // sanitize the user's input
            $subject = new Input($_POST["subject"]);
            $subject->Sanitize();
            $maxPrice = new Input($_POST["max_price"]);
            $maxPrice->Sanitize();
            $state = new Input($_POST["state"]);
            $state->Sanitize();
            $commune = new Input($_POST["commune"]);
            $commune->Sanitize();
            $firstName = new Input($_POST["first_name"]);
            $firstName->Sanitize();
            $lastName = new Input($_POST["last_name"]);
            $lastName->Sanitize();

            // ensure a valid price format
            if(!preg_match("/^([1-9][0-9]+|)$/", $maxPrice->value)){
                $responseArray["error"] .= "Veuillez spécifier un prix valide<br>";
            }

            // the rating direction won't be set unless the user chose one (default is DESC)
            $ratingDirection = new Input("DESC");
            if(isset($_POST["rating_direction"])){
                $ratingDirection->value = $_POST["rating_direction"];
                $ratingDirection->Sanitize();

                // ensure a valid rating
                if(!preg_match("/^(ASC|DESC)$/", $ratingDirection->value)){
                    $responseArray["error"] .= "Veuillez spécifier une note valide<br>";
                }
            }

            // the level(s) won't be set unless the user chose them (default is all levels)
            $levels = ["primary", "middle", "high", "college"];
            if(isset($_POST["level"])){
                $levels = $_POST["level"];
            }
            foreach($levels as $level){
                // ensure a valid level format
                if(!preg_match("/^(primary|middle|high|college)$/", $level)){
                    $responseArray["error"].= "Veuillez choisir un(des) palier(s) valide(s)<br>";
                    break;
                }

                // add the level to the query parameters
                array_push($queryParams, $level);
            }

            // retrieve offers only if the inputs are valid
            if($responseArray["error"] === ""){
                // this is to select all offers
                $query = "SELECT DISTINCT teachers.id AS teacher_id, teachers.first_name, teachers.last_name, offers.id AS offer_id, offers.state, offers.commune, offers.level, offers.subject, offers.price FROM offers INNER JOIN teachers ON teachers.id = offers.teacher_id LEFT JOIN ratings ON offers.id = ratings.offer_id WHERE status = ?";

                // bind the levels parameters
                $levelsQuestionMarks = str_repeat('?,', count($levels) - 1) . '?';
                $query .= " AND offers.level IN ($levelsQuestionMarks)";

                // bind each non-empty user input
                if($subject->value !== ""){
                    // filter offers by subject
                    $query .= " AND offers.subject = ?";
                    // add the subject to the query params
                    array_push($queryParams, $subject->value);
                }
                if($maxPrice->value !== ""){
                    // filter offers by price
                    $query .= " AND offers.price <= ?";
                    // add the price to the query params
                    array_push($queryParams, $maxPrice->value);
                } 
                if($state->value){
                    // filter offers by state
                    $query .= " AND offers.state = ?";
                    // add the state to the query params
                    array_push($queryParams, $state->value);
                } 
                if($commune->value !== ""){
                    // filter offers by commune
                    $query .= " AND offers.commune = ?";
                    // add the commune to the query params
                    array_push($queryParams, $commune->value);
                }
                if($firstName->value !== ""){
                    // filter offers by teacher's first name
                    $query .= " AND teachers.first_name = ?";
                    // add the teacher's first name to the query params
                    array_push($queryParams, $firstName->value);
                }
                if($lastName->value !== ""){
                    // filter offers by teacher's last name
                    $query .= " AND teachers.last_name = ?";
                    // add the teacher's last name to the query params
                    array_push($queryParams, $lastName->value);
                }

                // order by the average rating
                $query .= "ORDER BY IFNULL((SELECT AVG(rating) FROM ratings WHERE offer_id = offers.id), 0) " . $ratingDirection->value;

                // display the offers
                $responseArray = $this->DisplayOffers($query, $queryParams, "return", "../");
                $responseArray["error"] = "";
            }
        }

        echo json_encode($responseArray);
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
    $user = new UserController();
    $user->performAction($action->value);
}

?>