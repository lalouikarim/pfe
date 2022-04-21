function LogOut(){
    var dataString = "&action=logout";
    $.ajax({
        type: "POST",
        url: "Controllers/AccountController.php",
        data: dataString,
        success: function (data) {
            window.location.replace("");
        }
    });
}


function showbtn(id) {
    var mybtn1 ;
    mybtn1 = document.getElementById("show_teacher_details_popup_" + id );
    mybtn1.style.transform = 'scale(1)';
    var btn2 = document.getElementById("rate_offer_popup_" + id);
    btn2.style.transform = 'scale(1)';
}

function hidebtn(id) {
var mybtn1 ;
    mybtn1 = document.getElementById("show_teacher_details_popup_" + id );
    mybtn1.style.transform = 'scale(0)';
    var btn2 = document.getElementById("rate_offer_popup_" + id);
    btn2.style.transform = 'scale(0)';
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

function showpopup(params) {
    var popup=document.getElementById(params);
    popup.style.transform = 'translate(-50%, -50%) scale(1)';
}

function hidepopup(params) {
    var popup=document.getElementById(params);       
    popup.style.transform = 'translate(-50%, -50%) scale(0)';
}

function DisplayTeacherDetailsPopup(offerId){
    var dataString = "&action=displayteacherdetailspopup&offer_id=" + offerId;
    $.ajax({
        type: "POST",
        url: "Controllers/UserController.php",
        data: dataString,
        success: function (data) {
            data = JSON.parse(data);
            if(data["logged_in"] === false){
                window.open('Views/SignInView.html?req=viewteacher', '_blank', 'width=1000, height=400, top=200, left=300');
            } else if(data["error"] != ""){
                alert(data["error"]);
            } else{
                showpopup("teacher_details_popup_" + offerId);
                $("#teacher_details_popup_" + offerId).html(data["teacher_details_html"]);
            }
        }
    });
}

function RateOffers(){
    $(".user-rating").each(function(index, element){
        var id = this.id;
        $("#" + id).barrating({
            theme: 'fontawesome-stars', 
            onSelect: function(value, text, event){
                if (typeof(event) !== 'undefined'){
                    var offerId = id.split('_')[3];
                    var dataString = "&action=rateoffer&offer_id=" + offerId + "&offer_rating=" + value;
                    $.ajax({
                        url:"Controllers/UserController.php",
                        type: 'post',
                        data: dataString,
                        success: function(data){
                            data = JSON.parse(data);
                            if(data["error"] != ""){
                                $('#user_offer_rating_' + offerId).barrating('set', 0);
                                alert(data["error"]);
                            } else if(data["logged_in"] === false){
                                $('#user_offer_rating_' + offerId).barrating('set', 0);
                                window.open('Views/SignInView.html?req=rateoffer', '_blank', 'width=1000, height=400, top=200, left=300');
                            } else{
                                $("#offer_rating_" + offerId).barrating('set', Math.floor(data["avg_rating"]));
                                $("#offer_rates_number_" + offerId).html(data["rates_number"]);
                            }
                        }
                    });
                }
            }
        });
    });
}