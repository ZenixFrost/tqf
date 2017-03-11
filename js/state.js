$(function () {
    //เอา caching จาก load ออกเพื่อให้เวลาอัพเดทข้อมูล
    $.ajaxSetup ({
        // Disable caching of AJAX responses
        cache: false
    });

    if(user == null){
        $('#view').load("templates/guest.html");
    } else if (JSON.parse(sessionStorage.getItem("page")) != null) {
        var page = JSON.parse(sessionStorage.getItem("page"));
        active(page.id,page.url);
    } else {
        state("home");
    }

    function state(id){
        var page = {'id':"#"+id,'url': 'templates/'+id+".html"};
        sessionStorage.setItem("page", JSON.stringify(page));
        active(page.id,page.url);
    }

    function active(id,url) {
        // $("#view").hide().load(url, function(){ $(this).fadeIn(); });
        $("#view").load(url);
        $('ul li').removeClass("activated");
        $(id).addClass("activated");

    }

    $(".page").click(function () {
        var id = $(this).attr("id");
        if(id == "home"){
            $('.side-nav .collapse').collapse('hide');
        }

        //ปิด tap เมนู ขณะย่อ (responsive)
        $('.navbar-ex1-collapse').collapse('hide');
        state(id);
        return false;
    });


    $(".collapse").click(function () {
        $('.side-nav .collapse').collapse('hide');
        setTimeout(function () {
            $('.side-nav .collapse').collapse('hide');
        },150);

    });


    $("#header").click(function () {
        $('.side-nav .collapse').collapse('hide');
        var page = {'id':"#home",'url': 'templates/home.html'};
        sessionStorage.setItem("page", JSON.stringify(page));
        $('ul li').removeClass("activated");
        $("#home").addClass("activated");
        $('#view').load("templates/home.html");
        return false;
    });

});
