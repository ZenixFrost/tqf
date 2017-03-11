<?php
include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if(isset($_POST["func"] )){
    if($_POST["func"] == "getReport"){
        getReport();
    }
}


//get ข้อมูลของ report table
function getReport(){
    $faculty_executive_officer = $_POST["faculty_executive_officer"];
    $major_president = $_POST["major_president"];
    $sector_president = $_POST["sector_president"];
    $course_president = $_POST["course_president"];

    $user_id = $_POST["user_id"];
    $faculty_admin = $_POST["faculty_admin"];
    $major_admin = $_POST["major_admin"];
    $course_admin = $_POST["course_admin"];


    $faculty_id = $_POST["faculty_id"];
    $sector_id = $_POST["sector_id"];
    $major_id= $_POST["major_id"];

    $year = $_POST["year"];

    if($faculty_executive_officer >= 1 || $faculty_admin == 1){
        $sql = "SELECT `sector`.`faculty_id`,`sector`.`sector_id`,`major`.`major_id`,`course`.`course_id`,`year`,`report_file`.`report_id`,`report_file`.`report_type` FROM `course`

                LEFT JOIN `report_file` ON `report_file`.`course_id` = `course`.`course_id` AND `report_file`.`year` = %i
                INNER JOIN `major` ON `major`.`major_id` = `course`.`major_id`
                INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`
                
                WHERE `sector`.`faculty_id` = %s
                ORDER BY `sector_id` ASC,`major_id` ASC,`course_id` ASC";
        $results = DB::query($sql,$year,$faculty_id);
        $json = json_encode($results);
        echo $json;

    }elseif($sector_president >= 1){
        $sql = "SELECT `sector`.`faculty_id`,`sector`.`sector_id`,`major`.`major_id`,`course`.`course_id`,`year`,`report_file`.`report_id`,`report_file`.`report_type` FROM `course`

                LEFT JOIN `report_file` ON `report_file`.`course_id` = `course`.`course_id` AND `report_file`.`year` = %i
                INNER JOIN `major` ON `major`.`major_id` = `course`.`major_id`
                INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`
                
                WHERE `sector`.`faculty_id` = %s
                AND `sector`.`sector_id` = %s
                ORDER BY `sector_id` ASC,`major_id` ASC,`course_id` ASC";
        $results = DB::query($sql,$year,$faculty_id,$sector_id);
        $json = json_encode($results);
        echo $json;

    }elseif ($major_president >= 1 || $major_admin == 1){
        $sql = "SELECT `sector`.`faculty_id`,`sector`.`sector_id`,`major`.`major_id`,`course`.`course_id`,`year`,`report_file`.`report_id`,`report_file`.`report_type` FROM `course`

                LEFT JOIN `report_file` ON `report_file`.`course_id` = `course`.`course_id` AND `report_file`.`year` = %i
                INNER JOIN `major` ON `major`.`major_id` = `course`.`major_id`
                INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`
                
                WHERE `sector`.`faculty_id` = %s
                AND `sector`.`sector_id` = %s
                AND `major`.`major_id` = %s
                ORDER BY `sector_id` ASC,`major_id` ASC,`course_id` ASC";
        $results = DB::query($sql,$year,$faculty_id,$sector_id,$major_id);
        $json = json_encode($results);
        echo $json;

    }elseif($course_president != null || $course_admin >= 1){
        $courseData = [];
        $checkCourse = true;

        $courseSql = "SELECT `course_id` FROM `course_admin`
                      WHERE `course_admin_id` = %s";
        $courseResults = DB::query($courseSql,$user_id);

        foreach ($courseResults as $rows){
            if($rows["course_id"] ==  $course_president){
                $checkCourse = false;
            }

            array_push($courseData,$rows["course_id"]);
        }

        if($checkCourse){
            array_push($courseData,$course_president);
        }


        $sql = "SELECT `sector`.`faculty_id`,`sector`.`sector_id`,`major`.`major_id`,`course`.`course_id`,`year`,`report_file`.`report_id`,`report_file`.`report_type` FROM `course`

                LEFT JOIN `report_file` ON `report_file`.`course_id` = `course`.`course_id` AND `report_file`.`year` = %i
                INNER JOIN `major` ON `major`.`major_id` = `course`.`major_id`
                INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`
                
                WHERE `sector`.`faculty_id` = %s
                AND `sector`.`sector_id` = %s
                AND `major`.`major_id` = %s
                AND `course`.`course_id` IN %ls
                ORDER BY `sector_id` ASC,`major_id` ASC,`course_id` ASC";
        $results = DB::query($sql,$year,$faculty_id,$sector_id,$major_id,$courseData);
        $json = json_encode($results);
        echo $json;
    }





}