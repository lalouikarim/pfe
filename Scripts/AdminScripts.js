function DisplayOffersNumbers(){
    if(document.getElementById("home") == null){
      $.ajax({
        type: "POST",
        url: "../Controllers/AdminController.php",
        data: "&action=viewoffersnumber",
        success: function (data) {
            data = JSON.parse(data);
            if(data["valid_role"] === false){
                window.location.replace("http://localhost/pfe");
            } else{
              $("#tab_content").append(data["offers_number_html"]);
            }
        }
      });
    } else{
        $("#home").addClass("active");
    }

    $("#teachers_sign_ups_section").removeClass("active");
};

function showpopup(params) {
    var popup=document.getElementById(params);
    popup.style.transform = 'translate(-50%, -50%) scale(1)';
}

function hidepopup(params) {
    var popup=document.getElementById(params);       
    popup.style.transform = 'translate(-50%, -50%) scale(0)';
}

function DisplayOfferCategory(status){
    $("#display_offers_" + status + "_form").off("submit");
    $("#display_offers_" + status + "_form").on("submit", function (e) {
        var dataString = $(this).serialize();
        dataString += "&status=" + status + "&action=displayoffercategory";
        
        $.ajax({
            type: "POST",
            url: "../Controllers/AdminController.php",
            data: dataString,
            success: function (data) {
                data = JSON.parse(data);
                if(data["valid_role"] === false){
                    window.location.replace("http://localhost/pfe");
                } else if(data["error"] == ""){
                    $("#offers").html(data["offers_html"]);
                    if(status == 1){
                        DisplayAverageRating(data["avg_ratings"]);
                    }
                } else{
                    alert(data["error"]);
                }
            }
        });
        e.preventDefault();
    });
}

function pagination(page, section){
    var previousPageBtn = document.getElementById("previous_page_btn_"+section);
    var nextPageBtn = document.getElementById("next_page_btn_"+section);

    if(previousPageBtn != null || nextPageBtn != null){
        $(".page-active-" + section).removeClass("page-active-" + section);
        $(".pagination-btn-active").removeClass("active");
        $("#pagination_" + section + "_" + page).addClass("page-active-"+section);

        if(previousPageBtn.getAttribute("hidden") == ""){
        previousPageBtn.removeAttribute("hidden");
        }

        var nextPage = page + 1;
        var previousPage = page - 1;

        nextPageBtn.onclick = function(){
            pagination(nextPage, section);

            if(previousPageBtn.getAttribute("hidden") == ""){
            previousPageBtn.removeAttribute("hidden");
            }
        };
        previousPageBtn.onclick = function(){
            pagination(previousPage, section);

            if(nextPageBtn.getAttribute("hidden") == ""){
            nextPageBtn.removeAttribute("hidden");
            }
        };

        if(!document.getElementById("pagination_" + section + "_" + nextPage)){
        nextPageBtn.setAttribute("hidden", "");
        }
        if(!document.getElementById("pagination_" + section + "_" + previousPage)){
        previousPageBtn.setAttribute("hidden", "");
        }
    }
}

function ValidateOffers(action, offerId, title, text, firstBtn, secondBtn){
    $("#" + action + "_offer_" + offerId + "_form").off("submit");
    $("#" + action + "_offer_" + offerId + "_form").on("submit", function (e) {
        e.preventDefault();

        swal({
            title: title,
            text: text,
            icon: "warning",
            buttons: {
                cancel: firstBtn,
                confirm: {
                    text: secondBtn,
                    value: "proceed"
                }
            },
            dangerMode: true,
            closeOnEsc: false,
            closeOnClickOutside: false
        }).then(results => {
            if(results == "proceed"){
                var dataString = $(this).serialize();
                dataString+= "&offer_id=" + offerId + "&action=" + action;

                $.ajax({
                    type: "POST",
                    url: "../Controllers/AdminController.php",
                    data: dataString,
                    success: function (data) {
                        data = JSON.parse(data);
                        if(data["valid_role"] === false){
                            window.location.replace("http://localhost/pfe");
                        }else if(data["error"] == ""){
                            swal({
                                title: data["alert_title"],
                                text: data["alert_text"],
                                icon: data["alert_icon"],
                                buttons: {
                                    confirm: {
                                        text: "Fermer",
                                    }
                                },
                                dangerMode: data["danger_mode"],
                            });

                            if(data["alert_icon"] == "success"){
                            $("#home").html(data["offers_number_html"]);
                            }
                        } else{
                        alert(data["error"]);
                        }
                    }
                });
            }
        });
    });
}

function DisplayRefusalPopup(section, id){
    $("#display_" + section + "_refusal_popup_" + id + "_form").off("submit");
    $("#display_" + section + "_refusal_popup_" + id + "_form").on("submit", function (e) {
        var dataString = $(this).serialize();
        dataString+= "&" + section +"_id=" + id + "&action=display" + section + "refusalpopup";
        
        $.ajax({
            type: "POST",
            url: "../Controllers/AdminController.php",
            data: dataString,
            success: function (data) {
                data = JSON.parse(data);
                if(data["valid_role"] === false){
                    window.location.replace("http://localhost/pfe");
                }else if(data["error"] == ""){
                    if(data["display_refusal_popup"] === false){
                        swal({
                            title: data["alert_title"],
                            text: data["alert_text"],
                            icon: data["alert_icon"],
                            buttons: {
                                confirm: {
                                    text: "Fermer",
                                }
                            },
                            dangerMode: data["danger_mode"],
                        });
                    } else{
                        showpopup(data["refusal_popup_id"]);
                    }

                } else{
                    alert(data["error"]);
                }
            }
        });
        e.preventDefault();
    });
}

function RedirectAdminToCollapse(section, id){
    var $root = $("html, body");
    $root.animate({
        scrollTop: $("#" + section + "_title_" + id).offset().top
    }, 500, function () {
        currentUrl = window.location.href;
        currentUrl = currentUrl.substring(0, location.href.indexOf("#"));
        window.location.href = currentUrl + "#" + section + "_title_" + id;
    });
}

function DisplayTeachersSignUpsCategories(){
    if(document.getElementById("teachers_sign_ups_section") == null){
        $.ajax({
            type: "POST",
            url: "../Controllers/AdminController.php",
            data: "&action=viewteacherssignupsnumber",
            success: function (data) {
                data = JSON.parse(data);
                if(data["valid_role"] === false){
                    window.location.replace("http://localhost/pfe");
                } else{
                    $("#tab_content").append(data["teachers_sign_ups_number_html"]);
                }
            }
        });
    } else{
        $("#teachers_sign_ups_section").addClass("active");
    }

    $("#home").removeClass("active");
}

function DisplayTeachersSignUpsDetails(status){
    $("#display_teachers_sign_ups_" + status + "_form").off("submit");
    $("#display_teachers_sign_ups_" + status + "_form").on("submit", function (e) {
        var dataString = $(this).serialize();
        dataString += "&status=" + status + "&action=displayteacherssignupsdetails";
        
        $.ajax({
            type: "POST",
            url: "../Controllers/AdminController.php",
            data: dataString,
            success: function (data) {
                data = JSON.parse(data);
                if(data["valid_role"] === false){
                    window.location.replace("http://localhost/pfe");
                } else if(data["error"] == ""){
                    $("#sign_ups_details").html(data["sign_ups_details_html"]);
                } else{
                    alert(data["error"]);
                }
            }
        });
        e.preventDefault();
    });
}

function UpdateTeachers(action, signUpId, title, text, firstBtn, secondBtn){
    $("#" + action + "_" + signUpId + "_form").off("submit");
    $("#" + action + "_" + signUpId + "_form").on("submit", function (e) {
        e.preventDefault();

        swal({
            title: title,
            text: text,
            icon: "warning",
            buttons: {
                cancel: firstBtn,
                confirm: {
                    text: secondBtn,
                    value: "proceed"
                }
            },
            dangerMode: true,
            closeOnEsc: false,
            closeOnClickOutside: false
        }).then(results => {
            if(results == "proceed"){
                var dataString = $(this).serialize();
                dataString+= "&teacher_id=" + signUpId + "&action=" + action;

                $.ajax({
                    type: "POST",
                    url: "../Controllers/AdminController.php",
                    data: dataString,
                    success: function (data) {
                        data = JSON.parse(data);
                        if(data["valid_role"] === false){
                            window.location.replace("http://localhost/pfe");
                        }else if(data["error"] == ""){
                            swal({
                                title: data["alert_title"],
                                text: data["alert_text"],
                                icon: data["alert_icon"],
                                buttons: {
                                    confirm: {
                                        text: "Fermer",
                                    }
                                },
                                dangerMode: data["danger_mode"],
                            });

                            if(data["alert_icon"] == "success"){
                            $("#teachers_sign_ups_section").html(data["teachers_sign_ups_number_html"]);
                            }
                        } else{
                        alert(data["error"]);
                        }
                    }
                });
            }
        });
    });
}

function DisplayAverageRating(avgRatings){
    $('.avg-rating').each(function(index, element){
        var id = this.id;
        var offerId = id.split('_')[2];
        $("#" + id).barrating({ theme: 'fontawesome-stars'});
        $('#' + id).barrating('set', Math.floor(avgRatings[offerId]));
        $('#' + id).barrating('readonly', true);
    });
}