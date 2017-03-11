<?php
include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if ($_POST["func"] == "checklogin") {
    checklogin();
}elseif($_POST["func"] == "getUserData"){
    getUserData();
}

function checklogin(){

    $userinfo = 'https://www.googleapis.com/oauth2/v1/tokeninfo?id_token=' . $_POST["tokenId"];
    $json = file_get_contents($userinfo);
    $userInfoArray = json_decode($json, true);
    $googleEmail = $userInfoArray['email'];
//$tokenUserId = $userInfoArray['user_id'];
    $tokenAudience = $userInfoArray['audience'];
    $tokenIssuer = $userInfoArray['issuer'];

//    $results = DB::queryFirstRow("SELECT * FROM user WHERE user_id=%s", $googleEmail);
    $results = DB::queryFirstRow("SELECT `user_id`,`name`,`surname`,`faculty`.`faculty_id`,`sector`.`sector_id`,
                                         `major`.`major_id`,`faculty_admin`,`major_admin`

                                        FROM USER 
                                        
                                        INNER JOIN `major` ON `major`.`major_id` = `user`.`major_id`
                                        INNER JOIN `sector` ON `sector`.`sector_id` = major.`sector_id`
                                        INNER JOIN `faculty` ON `faculty`.`faculty_id` = `sector`.`faculty_id`
                                        
                                        WHERE `user`.user_id =%s ", $googleEmail);

    $counter = DB::count();

//$stmt = $conn->prepare("SELECT * FROM user_accounts WHERE user=:user");
//$stmt->execute(array(':user' => $googleEmail));
//$count = $stmt->rowCount();


    if ($counter == 1 && $tokenIssuer == "accounts.google.com" && $tokenAudience == "495548064612-h9gvpqiumbfue4tpkmh34uqnnthdljuc.apps.googleusercontent.com") {
//    $row = $stmt->fetch();
//    $_SESSION["name"] = $row["name"];
//    $_SESSION["surname"] = $row["surname"];

        $sql = "SELECT `course_id` FROM `course`
            WHERE `course_president_id` = %s";

        $coursePre = DB::queryFirstRow($sql,$googleEmail);

        DB::query("SELECT `position` FROM `faculty_executive_officer`WHERE `user_id` = %s", $googleEmail);
        $counterFac = DB::count();

        DB::query("SELECT sector_president_id FROM `sector` WHERE `sector_president_id` = %s", $googleEmail);
        $counterSector = DB::count();

        DB::query("SELECT `major_president_id` FROM `major` WHERE `major_president_id` = %s", $googleEmail);
        $counterMajor = DB::count();

        DB::query("SELECT `course_admin_id`,`course_id` FROM `course_admin` WHERE `course_admin_id` = %s", $googleEmail);
        $counterCourse = DB::count();


        $results = [
            "user_id" => $results["user_id"],
            "name" => $results["name"],
            "surname" => $results["surname"],
            "faculty_id" => $results["faculty_id"],
            "sector_id" => $results["sector_id"],
            "major_id" => $results["major_id"],
            "faculty_admin" => $results["faculty_admin"],
            "major_admin" => $results["major_admin"],
            "course_president" => $coursePre["course_id"],
            'course_admin' => $counterCourse,
            'faculty_executive_officer' => $counterFac,
            'sector_president' => $counterSector,
            'major_president' => $counterMajor
        ];

        $json = json_encode($results);
        echo $json;
    }
}


function getUserData(){
    $userEmail = $_POST["email"];
    $results = DB::queryFirstRow("SELECT `user_id`,`name`,`surname`,`faculty`.`faculty_id`,`sector`.`sector_id`,
                                         `major`.`major_id`,`faculty_admin`,`major_admin`

                                        FROM USER 
                                        
                                        INNER JOIN `major` ON `major`.`major_id` = `user`.`major_id`
                                        INNER JOIN `sector` ON `sector`.`sector_id` = major.`sector_id`
                                        INNER JOIN `faculty` ON `faculty`.`faculty_id` = `sector`.`faculty_id`
                                        
                                        WHERE `user`.user_id =%s ", $userEmail);

    $sql = "SELECT `course_id` FROM `course`
            WHERE `course_president_id` = %s";

    $coursePre = DB::queryFirstRow($sql,$userEmail);


    DB::query("SELECT `position` FROM `faculty_executive_officer`WHERE `user_id` = %s", $userEmail);
    $counterFac = DB::count();

    DB::query("SELECT sector_president_id FROM `sector` WHERE `sector_president_id` = %s", $userEmail);
    $counterSector = DB::count();

    DB::query("SELECT `major_president_id` FROM `major` WHERE `major_president_id` = %s", $userEmail);
    $counterMajor = DB::count();

    DB::query("SELECT `course_admin_id`,`course_id` FROM `course_admin` WHERE `course_admin_id` = %s", $userEmail);
    $counterCourse = DB::count();

    $results = [
        "user_id" => $results["user_id"],
        "name" => $results["name"],
        "surname" => $results["surname"],
        "faculty_id" => $results["faculty_id"],
        "sector_id" => $results["sector_id"],
        "major_id" => $results["major_id"],
        "faculty_admin" => $results["faculty_admin"],
        "major_admin" => $results["major_admin"],
        "course_president" => $coursePre["course_id"],
        'course_admin' => $counterCourse,
        'faculty_executive_officer' => $counterFac,
        'sector_president' => $counterSector,
        'major_president' => $counterMajor
    ];

    $counter = DB::count();
    if($counter == 1) {
        $json = json_encode($results);
        echo $json;
    }

}
