<?php
include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if(isset($_POST["func"] )){
    if($_POST["func"] == "getPeriodData"){
        getPeriodData();
    }elseif($_POST["func"] == "updatePeriod"){
        updatePeriod();
    }
}

function getPeriodData(){
    $faculty_id = $_POST["faculty_id"];
    $data = [];

    $sql = "SELECT `faculty_id`,`tqf34_period`,`tqf56_period`,`report_period` FROM `tqf_period`
            WHERE `faculty_id` = %s";
    $results = DB::query($sql,$faculty_id);
    $counter = DB::count();


    if($counter == 0){
        $data[] = [
            'faculty_id' => $faculty_id,
            'tqf34_period' => null,
            'tqf56_period' => null,
            'report_period' => null,
        ];
        $json = json_encode($data);
    }else{
        $json = json_encode($results);
    }

    echo $json;
}

function updatePeriod(){
    DB::$error_handler = false;
    DB::$throw_exception_on_error = true;

    $faculty_id = $_POST["pk"];
    $name = $_POST["name"];
    $value = $_POST['value'];


    if(is_numeric($value)){

        DB::update('tqf_period', array(
            $name => $value
        ), "faculty_id=%s", $faculty_id);

    }else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "เฉพาะตัวเลข";

    }
}