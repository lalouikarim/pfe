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