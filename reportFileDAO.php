<?php

include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if(isset($_POST["func"] )){
    if($_POST["func"] == "getYear"){
        getYear();
    }elseif($_POST["func"] == "addReport"){
        addReport();
    }elseif($_POST["func"] == "removeReport"){
        removeReport();
    }elseif($_POST["func"] == "getCurYear"){
        getCurYear();
    }elseif($_POST["func"] == "getReportDeadline"){
        getReportDeadline();
    }
}

function getCurYear(){
    $date = new DateTime("now",  new DateTimeZone('ASIA/Bangkok'));
    $year = $date->format("Y")+543;
    echo $year;
}

function getYear(){
    $courseId = $_POST["course_id"];
    $year = $_POST["year"];

    $sql = "SELECT `course_id`,`year`,`report_id`,`report_type`,`date_sent` FROM `report_file`
            WHERE `course_id` = %s
            AND `year` = %i";
    $results = DB::query($sql,$courseId ,$year);

    $counter = DB::count();
    if($counter  == 0){
        $results[] = [
            'course_id' => $courseId,
            'year' => $year,
            'report_id' => NULL,
            'report_type' => NULL
        ];
        $json = json_encode($results);
    }else{
        $json = json_encode($results);
    }

    echo $json;

}


//เพิ่มข้อมูลไฟล์ มคอ ไปที่ tqf_file table (DB)
function addReport(){
    $report_id = $_POST["report_id"];
    $data = $_POST["data"];
    $user = $_POST["user"];
    $course_id = $_POST["course_id"];
    $report_type = $_POST["report_type"];

    DB::insert('report_file', array(
        'course_id' => $course_id,
        'year' => $data["year"],
        'report_id' => $report_id,
        'report_type' => $report_type,
        'sender_id' => $user,
        'date_sent' => date("Y-m-d")
    ));

}

//ลบข้อมูลไฟล์ มคอ ไปที่ tqf_file table (DB) หรือ ลบไฟล์สรุปการสอน จาก teaching_file table
function removeReport(){
    $report_id = $_POST["report_id"];
    $course_id = $_POST["course_id"];
    $year = $_POST["year"];

    DB::delete("`report_file`" ,"`course_id`=%s AND `year`=%i AND`report_id`=%s",$course_id,$year,$report_id);
}



function getReportDeadline(){
    $year = $_POST["year"];
    $major_id = $_POST["major_id"];

    $sql = "SELECT `year`,`report_file_major_deadline`,`major_id` FROM `report_major_deadline`
                        WHERE `year` = %i
                        AND `major_id` = %s";
    $results = DB::query($sql, $year, $major_id);
    $json = json_encode($results);
    echo $json;
}
