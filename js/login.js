$(function () {
    login();
    $("#loading").hide();
    // console.log(user);
});

var user;

// function user() {
//     user = JSON.parse(localStorage.getItem("user"));
// }

function getUserData(){

    if(user != null) {
        $.ajax({
            type: 'POST',
            url: 'checkLogin.php',
            data: {func: 'getUserData', email: user.user_id},
            dataType: 'json',
            success: function (responser) {
                var newUserData = responser;
                localStorage.setItem("user", JSON.stringify(newUserData));
            }
        });
    }
}


function login() {
    user = JSON.parse(localStorage.getItem("user"));
    console.log(user);
    if (user != null) {
        getUserData();
        var Signed =
            // "<li>" +
            //     "<p class=\"navbar-text\"><span class=\"glyphicon glyphicon-user\" aria-hidden=\"true\"></span> \u00A0" + user.name + " " + user.surname + "</p>" +
            // "</li>" +
            // "<li id='singOut'>" +
            //     "<a href=\"\">" +
            //         "<span class=\"glyphicon glyphicon-log-out\" aria-hidden=\"true\"></span> ออกจากระบบ" +
            //     "</a>" +
            // "</li>"
            "<li class=\"dropdown\">"+
                "<a href=\"\" class=\"dropdown-toggle\" data-toggle=\"dropdown\"><i class=\"fa fa-user\"></i> " + user.name + " " + user.surname + " <b class=\"caret\"></b></a>"+
                "<ul class=\"dropdown-menu\">"+
                    "<li id='singOut'>"+
                        "<a href=\"\"><i class=\"glyphicon glyphicon-log-out\"></i> ออกจากระบบ</a>"+
                    "</li>"+
                "</ul>"+
            "</li>";



        $("#SignIn").hide();
        $("#login").html(Signed);
    } else {
        $("#SignIn").show();
    }


}

function onSignIn(googleUser) {

//    var profile = googleUser.getBasicProfile();
//    console.log("ID: " + profile.getId()); // Don't send this directly to your server!
//    console.log('Full Name: ' + profile.getName());
//    console.log('Given Name: ' + profile.getGivenName());
//    console.log('Family Name: ' + profile.getFamilyName());
//    console.log("Image URL: " + profile.getImageUrl());
//    console.log("Email: " + profile.getEmail());
    // The ID token you need to pass to your backend:


    var id_token = googleUser.getAuthResponse().id_token;
    var auth2 = gapi.auth2.getAuthInstance();
    auth2.signOut();
    $("#SignIn").hide();
    $("#loading").show();

    $.ajax({
        type: 'POST',
        url: 'checkLogin.php',
        data: {func: 'checklogin', tokenId: id_token},
        dataType: 'json',
        success: function (response) {
            localStorage.setItem("user", JSON.stringify(response));
            window.location.reload();
        },error: function () {
            $("#SignIn").show();
            $("#loading").hide();

            var errorLogin = "<div class=\"alert alert-danger alert-dismissible\"  role=\"alert\"> " +
                "<span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span> " +
                "<button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-label=\"Close\"><span aria-hidden=\"true\">&times;</span></button>"+
                "<strong>ผิดพลาด!</strong> อีเมล์ของคุณไม่ได้ลงทะเบียน" +
                "</div>";

            $("#showError").append(errorLogin);

            // setTimeout(function () {
            //     window.location.reload();
            // }, 1650);

        }
    });


    // $.post('checkLogin.php', {func: 'checklogin', tokenId: id_token}, function (response) {
    //     localStorage.setItem("user", JSON.stringify(response));
    //     window.location.reload();
    // }, 'json').fail(function () {
    //     // window.location.assign("index.php");
    //
    //     $("<div class=\"alert alert-danger alert-fixed-top\"> " +
    //         "<span class=\"glyphicon glyphicon-exclamation-sign\" aria-hidden=\"true\"></span> " +
    //         "<strong>Error!</strong> อีเมล์ของคุณไม่ได้ลงทะเบียน" +
    //         "</div>").insertAfter("#view");
    //     setTimeout(function () {
    //         window.location.reload();
    //     }, 1650);
    //
    // });
}

$(function () {
    $("#singOut").on('click',function () {
        localStorage.removeItem("user");
        var page = {'id': "#home", 'url': "templates/home.html"};
        sessionStorage.setItem("page", JSON.stringify(page));
        window.location.reload();
    });
});



