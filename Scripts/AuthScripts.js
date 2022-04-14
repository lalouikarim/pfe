function SignIn(){
    $("#sign_in_form").off("submit");
    $("#sign_in_form").on("submit", function (e) {
        var dataString = $(this).serialize();
        dataString += "&action=signin";
        
        $.ajax({
            type: "POST",
            url: "../Controllers/AccountController.php",
            data: dataString,
            success: function (data) {
                data = JSON.parse(data);
                if(data["signed_in"] === true){
                    window.location.replace(data["redirect_url"]);
                } else{
                    $("#sign_in_error").html(data["error"]);
                }
            }
        });
        e.preventDefault();
    });
}

function ShowCamera(divId) {
    var cameraPopups = document.getElementsByClassName("popup-hide");
    for(var i=0; i<cameraPopups.length; i++){
        HideCamera(cameraPopups[i].id);
    }
    var popup=document.getElementById(divId);
    popup.style.transform = 'translate(-50%, -50%) scale(1)';   
}

function HideCamera(divId) {
    var popup=document.getElementById(divId);       
    popup.style.transform = 'translate(-50%, -50%) scale(0)';
    Webcam.reset();
}

function LoadCamera(cameraId){
    ShowCamera(cameraId + "_popup");
    Webcam.set({
        width: 490,
        height: 390,
        image_format: 'jpeg',
        jpeg_quality: 90
    });
    Webcam.attach("#" + cameraId);
}

function TakeSnapshot(imgInput) {
    Webcam.snap( function(data_uri) {
        $("#" + imgInput).val(data_uri);
        document.getElementById('saved_' + imgInput).innerHTML = '<br><img src="'+data_uri+'" style="height:200px;width:300px">';
    });
}

function SignUp(accountType){
    $("#" + accountType + "_sign_up_form").off("submit");
    $("#" + accountType + "_sign_up_form").on("submit", function (e) {
        var dataString = new FormData(this);
        dataString.append("action", "signup");
        
        $.ajax({
            type: "POST",
            url: "../Controllers/AccountController.php",
            data: dataString,
            contentType: false,
            processData: false,
            success: function (data) {
                data = JSON.parse(data);
                if(data["signed_up"] === true || data["already_loggedin"] === true){
                    window.location.replace(data["redirect_url"]);
                } else{
                    for(section in data){
                        $("#"+section).html(data[section]);
                    }
                }
            }
        });
        e.preventDefault();
    });
}