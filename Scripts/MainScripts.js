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
//script for btn products details 
function showbtn(id) {
// var mybtn = document.getElementById("btn");
    var mybtn1 ;
    mybtn1 = document.getElementById("show_teacher_details_popup_" + id );
    mybtn1.style.transform = 'scale(1)'
    //mybtn1 = document.getElementsByClassName("btn-product-details").id;
    //mybtn.style.transform = 'scale(1)'
    //mybtn1[0].style.transform = 'scale(1)'
}

function hidebtn(id) {
var mybtn1 ;
    mybtn1 = document.getElementById("show_teacher_details_popup_" + id );
    mybtn1.style.transform = 'scale(0)';

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