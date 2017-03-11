<?php
include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

?>
<!doctype html>
<html>
<head>
    <title>tqf</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="google-signin-client_id"
          content="177754446863-u8ge7u3siaslfvimanh56it6ailpaddv.apps.googleusercontent.com">


    <link rel="stylesheet" type="text/css" href="./lib/bootstrap-3.3.6-dist/css/bootstrap.css"/>
    <link rel="stylesheet" type="text/css" href="./lib/jasny-bootstrap/css/jasny-bootstrap.css"/>
    <link rel="stylesheet" type="text/css" href="./lib/fuelux/css/fuelux.css"/>
    <!--    <link rel="stylesheet" type="text/css" href="./lib/EasyAutocomplete-1.3.5/easy-autocomplete.css">-->

    <script src="./lib/jquery/jquery-2.1.4.js"></script>
    <script src="./lib/bootstrap-3.3.6-dist/js/bootstrap.js"></script>
    <script src="./lib/fuelux/js/fuelux.js"></script>
    <script src="./lib/jasny-bootstrap/js/jasny-bootstrap.js"></script>


    <script type="text/javascript" src="./js/login.js"></script>
    <script type="text/javascript" src="./js/state.js"></script>
    <script src="https://apis.google.com/js/platform.js?onload=renderButton" async defer></script>

    <!--    <script src="./lib/bootstrap-filestyle-1.2.1/bootstrap-filestyle.js"></script>-->
    <!--    <script src="./lib/EasyAutocomplete-1.3.5/jquery.easy-autocomplete.js"></script>-->

    <!--    <script src="./lib/jQuery-File-Upload-master/js/vendor/jquery.ui.widget.js"></script>-->
    <!--    <script src="./lib/jQuery-File-Upload-master/js/jquery.iframe-transport.js"></script>-->
    <!--    <script src="./lib/jQuery-File-Upload-master/js/jquery.fileupload.js"></script>-->
    <!--    <script src="./lib/jQuery-File-Upload-master/js/jquery.fileupload-process.js"></script>-->
    <!--    <script src="./lib/jQuery-File-Upload-master/js/jquery.fileupload-validate.js"></script>-->

    <!--//////////////////////////////-->
    <style type="text/css">

        @media (min-width: 992px) {
            body {
                padding: 0 0 0 300px;
            }

            .navmenu {

                width: 250px;
                padding-top: 50px;
            }

            .navbar {
                height: 50px;
            }

        }

    </style>

    <script>

//
//        var me = {name:'myname',age:99,gender:'myGender',address : 'world'};
//        console.log(me);
//        localStorage.setItem("user",JSON.stringify(me));
//        console.log(localStorage.getItem("user"));
//        var me  = JSON.parse(localStorage.getItem("user")); // a javascript object
//        console.log(me);



        //        $(function () {
        //            var options = {
        //                data: ["blue", "green", "pink", "red", "yellow"]
        //            };
        //
        //            $("#basics").easyAutocomplete(options);
        //
        //        });
        //
        //
        //        $(function () {
        //            $(":file").filestyle({
        //                'placeholder': 'Placeholder test',
        //                buttonName: "btn-primary",
        //
        //            });
        //        });
        //
        //        //        $(function () {
        //        //            $(":file").change(function () {
        //        //                var file = $("#upload").attr("name");
        //        //                $.post("upload.php", {file: file}, function (data) {
        //        //                    $("#test11").html(data);
        //        //                });
        //        //            });
        //        //        });
        //
        //        $(function () {
        //            $('#fileupload').fileupload({
        //                dataType: 'json',
        //                acceptFileTypes: /(\.|\/)(gif|jpe?g|png|msword)$/i,
        //
        //                progressall: function (e, data) {
        //                    var progress = parseInt(data.loaded / data.total * 100, 10);
        //                    $('#progress .progress-bar').css(
        //                        'width',
        //                        progress + '%'
        //                    );
        //                }
        //
        //            }).bind('fileuploadprocessdone', function (e, data) {
        //                $.each(data.result.files, function (index, file) {
        //                    $('<p/>').text(file.name).appendTo(document.body);
        //                });
        //            }).bind("fileuploadprocessfail", function (e, data) {
        //                var file = data.files[data.index];
        //                alert(file.error);
        //            });
        //        });
        //

        jQuery.fn.center = function () {
            this.css("position","absolute");
            this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) +
                    $(window).scrollTop()) + "px");
            this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 1.75) +
                    $(window).scrollLeft()) + "px");
            return this;
        }

//        $(function () {
//            if (sessionStorage.getItem('page') != null) {
//                $('#view').load(sessionStorage.getItem('page'));
//            } else {
//                sessionStorage.setItem('page', 'templates/home.html');
//                $('#view').load('templates/home.html');
//            }
//
//            $('a[href*="#home"]').click(function () {
//                sessionStorage.setItem('page', 'templates/home.html');
//                $('#view').load('templates/home.html');
//                $('ul li').removeClass("active");
//                $('ul li:eq(0)').addClass("active");
//            });
//
//            $('a[href*="#subject"]').click(function () {
//                sessionStorage.setItem('page', 'templates/subject.html');
//                $('#view').load('templates/subject.html');
//                $('ul li').removeClass("active");
//                $('ul li ul li:eq(0)').addClass("active");
//            });
//        });

        $(document).ready(function(){
            $("<div id='loading' class='loader' data-initialize='loader'></div>").insertAfter("#view").center();
            $('#loading').loader();
            $(document).ajaxStart(function(){
                $("#loading").show();
            }).ajaxStop(function(){
                $("#loading").hide();
            });
        });

    </script>


</head>
<body class="fuelux">
<div class="container-fluid">
    <nav class="navmenu navmenu-default  navmenu-fixed-left offcanvas-sm" role="navigation">
        <!--    <a class="navmenu-brand" href="#">ระบบส่งเอกสาร มคอ.</a>-->
        <ul class="nav navmenu-nav">

            <li class="active"><a href="#home">หน้าแรก</a></li>
            <li><a href="#">ส่งเอกสาร มคอ</a></li>
            <li><a href="#contact">ส่งเอกสาร รายงานสรุป</a></li>
            <li><a href="#contact">รายงานสรุปผล การส่ง มคอ.</a></li>
            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">หลักสูตรและรายวิชา <b class="caret"></b></a>
                <ul class="dropdown-menu navmenu-nav" role="menu">
                    <li><a href="#subject">กำหนดวิชา</a></li>
                    <li><a href="#">กำหนดวิชาของหลักสูตร</a></li>
                    <li role="separator" class="divider"></li>
                    <li><a href="#about">กำหนดกลุ่มเรียน</a></li>
                    <li><a href="#about">กำหนดวันส่ง มคอ.</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle" data-toggle="dropdown">ค่าพื้นฐานของระบบ <b class="caret"></b></a>
                <ul class="dropdown-menu navmenu-nav" role="menu">
                    <li><a href="#">กำหนดหลักสูตร</a></li>
                    <li role="separator" class="divider"></li>
                    <li><a href="#">กำหนดปฏิทินวันหยุดราชการ</a></li>
                    <li><a href="#">กำหนดวัน เปิด-ปิด ภาคเรียน</a></li>
                </ul>
            </li>

            <li><a href="#about">กำหนดบัญชีผุ้ใช้</a></li>
        </ul>
    </nav>

    <div class="row" style="margin-top: 20px">
        <nav class="col-md-offset-0 navbar navbar-default navbar-fixed-top">
            <div class="container-fluid">
                <div class="navbar-header">
                <span class="navbar-brand">
                    <span class="glyphicon glyphicon-file" aria-hidden="true"></span>
                    ระบบส่งเอกสาร มคอ. <small>TQF Management System</small>
                </span>
                </div>
                <ul id="user" class="nav navbar-nav navbar-right">
<!--                    --><?php //if (isset($_SESSION["name"])) { ?>
<!--                        <li>-->
<!--                            <p class="navbar-text">Signed in as --><?//= $_SESSION["name"] . " " . $_SESSION["surname"] ?><!--</p>-->
<!--                        </li>-->
<!--                        <li>-->
<!--                            <a href="#signOut" onclick="location.assign('logout.php');"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Sign out</a>-->
<!--                        </li>-->
<!--                    --><?php //} else { ?>
                        <div id="SignIn" class="g-signin2" data-onsuccess="onSignIn" style="margin: 5px 5px"></div>
<!--                    --><?php //} ?>
                </ul>
            </div>
        </nav>

    </div>

    <div class="row">
        <div id="view" style="margin-top: 60px"></div>
    </div>
</div>



</body>
</html>
<!--    --><?php
//    $results = DB::query("SELECT * FROM user_accounts");
//    foreach ($results as $row) {
//        echo "Name: " . $row['name'] . "<br>";
//
//
//    }
//
//    $json = json_encode($results);
//    //echo $json;
//    $a = json_decode($json);
//
//    echo $a[1]->name;
//    ?>

<!--/////////////////////////////////////////////NAV////////////////////////////////////////////////////////////////////////-->

<!--<div class="row" style="margin-top: 20px">-->
<!--    <nav class="navbar navbar-default navbar-fixed-top">-->
<!--        <div class="container-fluid">-->
<!---->
<!--            <div class="navbar-header">-->
<!--                <span class="navbar-brand">ระบบส่งเอกสาร มคอ.</span>-->
<!--            </div>-->
<!--            <ul class="nav navbar-nav">-->
<!--                <li class="active"><a href="#home">หน้าแรก</a></li>-->
<!--                <li><a href="#">ส่งเอกสาร มคอ</a></li>-->
<!--                <li><a href="#contact">ส่งเอกสาร รายงานสรุป</a></li>-->
<!--                <li><a href="#contact">รายงานสรุปผล การส่ง มคอ.</a></li>-->
<!---->
<!--                <li class="dropdown">-->
<!--                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"-->
<!--                       aria-expanded="false">หลักสูตรและรายวิชา<span class="caret"></span></a>-->
<!--                    <ul class="dropdown-menu">-->
<!--                        <li><a href="#subject">กำหนดวิชา</a></li>-->
<!--                        <li><a href="#">กำหนดวิชาของหลักสูตร</a></li>-->
<!--                        <li role="separator" class="divider"></li>-->
<!--                        <li><a href="#about">กำหนดกลุ่มเรียน</a></li>-->
<!--                        <li><a href="#about">กำหนดวันส่ง มคอ.</a></li>-->
<!--                    </ul>-->
<!--                </li>-->
<!---->
<!--                <li class="dropdown">-->
<!--                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true"-->
<!--                       aria-expanded="false">ค่าพื้นฐานของระบบ<span class="caret"></span></a>-->
<!--                    <ul class="dropdown-menu">-->
<!--                        <li><a href="#">กำหนดหลักสูตร</a></li>-->
<!--                        <li role="separator" class="divider"></li>-->
<!--                        <li><a href="#">กำหนดปฏิทินวันหยุดราชการ</a></li>-->
<!--                        <li><a href="#">กำหนดวัน เปิด-ปิด ภาคเรียน</a></li>-->
<!--                    </ul>-->
<!--                </li>-->
<!---->
<!--                <li><a href="#about">กำหนดบัญชีผุ้ใช้</a></li>-->
<!---->
<!---->
<!--            </ul>-->
<!---->
<!--            <ul class="nav navbar-nav navbar-right">-->
<!--                --><?php //if (isset($_SESSION["name"])) { ?>
<!--                    <li><p class="navbar-text">Signed in-->
<!--                            as --><? //= $_SESSION["name"] . " " . $_SESSION["surname"] ?><!--</p>-->
<!--                    <li>-->
<!--                    <li>-->
<!--                        <a href="#" onclick="location.assign('logout.php');">-->
<!--                            <span class="glyphicon glyphicon-log-out" aria-hidden="true"></span> Sign out-->
<!--                        </a>-->
<!--                    </li>-->
<!--                --><?php //} else { ?>
<!--                    <div class="g-signin2" data-onsuccess="onSignIn" style="margin: 5px 5px"></div>-->
<!--                --><?php //} ?>
<!--            </ul>-->
<!---->
<!--        </div>-->
<!--    </nav>-->
<!---->
<!--</div>-->


<!--////////////////////////////////FILE UPLOAD//////////////////////////////////////////////////-->

<!--    <h3>Testing Upload</h3>-->
<!--    <hr>-->
<!--    <div class="row">-->
<!--        <div class="col-xs-4">-->
<!--            <div class="form-group">-->
<!--                <input id="fileupload" type="file" name="files[]" data-url="server/php/" multiple>-->
<!--            </div>-->
<!--            <div id="progress" class="progress">-->
<!--                <div class="progress-bar progress-bar-success"></div>-->
<!--            </div>-->
<!--        </div>-->
<!--    </div>-->
