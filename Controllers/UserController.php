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
                $this->DisplayOffers();
                break;
            default:
                break;
        }
    }

    // display navbar
    private function DisplayNavbar(){
        $navbar = "
        <!-- Brand -->
        <a class='navbar-brand' href='index.html'>Cours Particuliers</a>
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
                    <a class='nav-link' href='Views/SignInView.html'>Se Connecter</a>
                </li>
                <li class='nav-item'>
                    <a class='nav-link' href='Views/SignUpView.html'>S'inscrire</a>
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
                    <a class='nav-link' onclick='LogOut()'>Se déconnecter <i class='fas fa-sign-out-alt'></i> </a>
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
    private function DisplayOffers(){
        // send this array to the client
        $responseArray = [];

        // use these variables for pagination
        $offersPerPage = 0;
        $pageNumber = 0;

        // retrieve offers
        $query = "SELECT teachers.id AS teacher_id, teachers.first_name, teachers.last_name, offers.id AS offer_id, offers.state, offers.commune, offers.level, offers.subject, offers.price FROM offers INNER JOIN teachers ON teachers.id = offers.teacher_id WHERE status = ?";
        $offersDetails = $this->userModel->RetrieveOffers($query, array(1));

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
                            <div class='d-none d-lg-block card-img-overlay' id='btn-card-" . $offer["offer_id"] . "'>
                                <button class='btn-offer btn-offer-details btn btn-block btn-primary btn-sm' id='show_teacher_details_popup_" . $offer["offer_id"] . "'>Détails du prof</button>
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
                                <h6>" . $offer["subject"] . " - " . $offer["level"] . "</h6>
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
                                    <button class='btn btn-block btn-outline-primary btn-sm' id='show_teacher_details_popup_phone_" . $offer["offer_id"] . "'>Détails du prof</button>
                                </div>
                            </div>
                            <div class='popup-hide' id='teacher_details_popup_" . $offer["offer_id"] . "'></div>
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
        $responseArray["navbar"] = $this->DisplayNavbar();
        $responseArray["footer"] = $this->DisplayFooter();

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