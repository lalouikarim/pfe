function DisplayOfferNumber(){
    if(document.getElementById("home") == null){
        $.ajax({
            type: "POST",
            url: "../Controllers/TeacherController.php",
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

    $("#add_offer_section").removeClass("active");
}

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
            url: "../Controllers/TeacherController.php",
            data: dataString,
            success: function (data) {
                data = JSON.parse(data);
                if(data["valid_role"] === false){
                    window.location.replace("http://localhost/pfe");
                } else if(data["error"] == ""){
                    $("#offers").html(data["offers_html"]);
                } else{
                    alert(data["error"]);
                }
            }
        });
        e.preventDefault();
    });
}

function DisplayAddOfferMenu(){
    if(document.getElementById("add_offer_section") == null){
        $.ajax({
            type: "POST",
            url: "../Controllers/TeacherController.php",
            data: "&action=addoffermenu",
            success: function (data) {
                data = JSON.parse(data);
                if(data["valid_role"] === false){
                    window.location.replace("http://localhost/pfe");
                } else{
                    $("#tab_content").append(data["section"]);
                }
            }
        });
    } else{
        $("#add_offer_section").addClass("active");
    }

    $("#home").removeClass("active");
}

function Offer(action, offerId){
    $("#" + action + "_offer_" + offerId + "_form").off("submit");
    $("#" + action + "_offer_" + offerId + "_form").on("submit", function (e) {
        var dataString = $(this).serialize();
        dataString += "&action=" + action;
        if(offerId != ""){
            dataString += "&offer_id=" + offerId;
        }
        
        $.ajax({
            type: "POST",
            url: "../Controllers/TeacherController.php",
            data: dataString,
            success: function (data) {
                data = JSON.parse(data);
                if(data["valid_role"] === false){
                    window.location.replace("http://localhost/pfe");
                } else if(data["display_alert"] === true){
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

                    if(data["action_completed"]){
                        $("#home").html(data["offers_number_html"]);
                    }
                } else if(data["action_completed"] === false){ 
                    for(section in data["errors"]){
                        $("#"+section).html(data["errors"][section]);
                    }
                }
            }
        });
        e.preventDefault();
    });
}

function ConfirmClick(action, offerId, title, text, firstBtn, secondBtn){
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
            Offer(action, offerId);
            $("#" + action + "_offer_" + offerId + "_form").submit();
        }
    });
}

function RedirectTeacherToOffer(offerId){
    var $root = $("html, body");
    $root.animate({
        scrollTop: $("#offer_title_" + offerId).offset().top
    }, 500, function () {
        currentUrl = window.location.href;
        currentUrl = currentUrl.substring(0, location.href.indexOf("#"));
        window.location.href = currentUrl + "#offer_title_" + offerId;
    });
}

function DisplayModifyOfferPopup(offerId){
    $("#display_modify_offer_popup_" + offerId + "_form").off("submit");
    $("#display_modify_offer_popup_" + offerId + "_form").on("submit", function (e) {
        var dataString = $(this).serialize();
        dataString+= "&offer_id=" + offerId + "&action=displaymodifyofferpopup";
        
        $.ajax({
            type: "POST",
            url: "../Controllers/TeacherController.php",
            data: dataString,
            success: function (data) {
                data = JSON.parse(data);
                if(data["valid_role"] === false){
                    window.location.replace("http://localhost/pfe");
                }else if(data["error"] == ""){
                    if(data["display_modify_offer_popup"] === false){
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
                    showpopup(data["modify_offer_popup_id"]);
                    }

                } else{
                    alert(data["error"]);
                }
            }
        });
        e.preventDefault();
    });
}