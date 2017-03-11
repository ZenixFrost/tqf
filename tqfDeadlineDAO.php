<?php
include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if(isset($_POST["func"] )){
    if($_POST["func"] == "getTqf36Deadline"){
        getTqf36Deadline();
    }elseif($_POST["func"] == "getTqf7Deadline"){
        getTqf7Deadline();
    }elseif($_POST["func"] == "getMajorTqf36Deadline"){
        getMajorTqf36Deadline();
    }elseif($_POST["func"] == "getMajorTqf7Deadline"){
        getMajorTqf7Deadline();
    }elseif($_POST["func"] == "updateTqfDeadline"){
        updateTqfDeadline();
    }elseif($_POST["func"] == "updateReportDeadline"){
        updateReportDeadline();
    }
}



function getTqf36Deadline(){
    $faculty_id = $_POST["faculty_id"];
    $year = $_POST["year"];

    $sql = "SELECT `year`,`term`,`tqf3-4_deadline`,`tqf5-6_deadline`,`faculty_id` FROM `tqf_deadline`
            WHERE `year` = %i
            AND `faculty_id` = %s
            ORDER BY `term` DESC";

    $results = DB::query($sql, $year,$faculty_id);
    $json = json_encode($results);
    echo $json;

}

function getTqf7Deadline(){
    $faculty_id = $_POST["faculty_id"];
    $year = $_POST["year"];

    $sql = "SELECT `year`,`report_file_deadline`,`faculty_id` FROM `report_deadline`
            WHERE `year` = %i
            AND `faculty_id` = %s";

    $results = DB::query($sql, $year,$faculty_id);
    $json = json_encode($results);
    echo $json;
}

function getMajorTqf36Deadline(){
    $major_id = $_POST["major_id"];
    $year = $_POST["year"];
    $faculty_admin = $_POST["faculty_admin"];
    $faculty_id = $_POST["faculty_id"];

    if($faculty_admin == 0){
        $sql = "SELECT `year`,`term`,`tqf3-4_major_deadline`,`tqf5-6_major_deadline`,`major_id` FROM `tqf_major_deadline`
            WHERE `year` = %i
            AND `major_id` = %s
            ORDER BY `term` DESC";
        $results = DB::query($sql, $year,$major_id);
    }else{
        $sql = "SELECT `year`,`term`,`tqf3-4_major_deadline`,`tqf5-6_major_deadline`,`tqf_major_deadline`.`major_id`,`sector`.`sector_id` FROM `tqf_major_deadline`

                INNER JOIN `major` ON `major`.`major_id` = `tqf_major_deadline`.`major_id`
                INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`
                
                WHERE `year` = %i
                AND `sector`.`faculty_id` = %s
                ORDER BY `sector_id` ASC,`tqf_major_deadline`.`major_id` ASC,`term` DESC ";
        $results = DB::query($sql, $year,$faculty_id);
    }


    $json = json_encode($results);
    echo $json;
}

function getMajorTqf7Deadline(){
    $major_id = $_POST["major_id"];
    $year = $_POST["year"];
    $faculty_admin = $_POST["faculty_admin"];
    $faculty_id = $_POST["faculty_id"];


    if($faculty_admin == 0){
        $sql = "SELECT `year`,`report_file_major_deadline`,`major_id` FROM `report_major_deadline`
                WHERE `year` = %i
                AND `major_id` = %s";
        $results = DB::query($sql, $year,$major_id);
    }else{
        $sql = "SELECT `sector`.`sector_id`,`report_major_deadline`.`major_id`, `year`,`report_file_major_deadline`,`report_major_deadline`.`major_id` FROM `report_major_deadline`
            
                INNER JOIN `major` ON `major`.`major_id` = `report_major_deadline`.`major_id`
                INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`
                
                WHERE `year` = %i
                AND `sector`.`faculty_id` = %s
                ORDER BY `sector_id` ASC,`report_major_deadline`.`major_id` ASC";
        $results = DB::query($sql, $year,$faculty_id);
    }

    $json = json_encode($results);
    echo $json;
}


function updateTqfDeadline(){
    $year = $_POST["year"];
    $term = $_POST["term"];
    $name = $_POST["name"];
    $value = $_POST["value"];
    $faculty_id = $_POST["faculty_id"];
    $major_id = $_POST["major_id"];


    //วันที่รับค่าเข้ามา
    $date = DateTime::createFromFormat('d/m/Y', $value);
    $date = $date->sub(new DateInterval("P543Y"));

    $dateId = $date->format('Y-m-d');
    $day = $date->format("N");


    //เช็ควันหยุด
    $holidaySql = "SELECT `date_id`,`date_name` FROM `official_holiday`
                       WHERE `date_id` = %s
                       AND `faculty_id` = %s";
    $holidayResult = DB::queryFirstRow($holidaySql, $dateId,$faculty_id);
    $checkHoliday = DB::count();



    if($checkHoliday != 0){
        echo "วันที่กำหนดต้องไม่ใช่ วัน".$holidayResult["date_name"];
        header('HTTP/1.0 400 Bad Request', true, 400);
    }elseif($day == 6){
        echo "วันที่กำหนดต้องไม่ใช่วันเสาร์";
        header('HTTP/1.0 400 Bad Request', true, 400);
    }elseif($day == 7){
        echo "วันที่กำหนดต้องไม่ใช่วันอาทิตย์";
        header('HTTP/1.0 400 Bad Request', true, 400);
    }elseif($major_id == "null"){

        //อัพเดทของ กำหนดวันส่ง มคอ. ของคณะ
        DB::update('tqf_deadline', array(
            $name => $dateId
        ), "year=%i AND term=%i AND faculty_id=%s", $year,$term,$faculty_id);


        //เช็คว่า เวลาที่กำหนด มคอ. ของสาขา เกินของคณะไหม
        if($name == "tqf3-4_deadline"){

            DB::update('tqf_major_deadline', array(
                'tqf3-4_major_deadline' => $dateId

            ), "`major_id` IN (SELECT `major_id` FROM `major` 
            INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id` 
            WHERE `sector`.`faculty_id` = %s)

            AND `year` = %i
            AND `term` = %i
            AND `tqf3-4_major_deadline` > %s", $faculty_id,$year,$term,$dateId);
        }elseif($name == "tqf5-6_deadline"){

            DB::update('tqf_major_deadline', array(
                'tqf5-6_major_deadline' => $dateId

            ), "`major_id` IN (SELECT `major_id` FROM `major` 
            INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id` 
            WHERE `sector`.`faculty_id` = %s)

            AND `year` = %i
            AND `term` = %i
            AND `tqf5-6_major_deadline` > %s", $faculty_id,$year,$term,$dateId);
        }

    }else{


        //เช็ควัน dead line ของ มคอ.
        $sql = "SELECT `year`,`term`,`tqf3-4_deadline`,`tqf5-6_deadline`,`faculty_id` FROM `tqf_deadline`
                WHERE `year` = %i
                AND `term` = %i
                AND `faculty_id` = %s";
        $tqfDeadline = DB::queryFirstRow($sql,$year,$term,$faculty_id);

        if($name == "tqf3-4_major_deadline"){

            if($dateId > $tqfDeadline["tqf3-4_deadline"]){
                echo "วันที่กำหนดต้องไม่เกินของคณะ";
                header('HTTP/1.0 400 Bad Request', true, 400);
            }else{
                DB::update('tqf_major_deadline', array(
                    $name => $dateId
                ), "year=%i AND term=%i AND major_id=%s", $year,$term,$major_id);
            }

        }elseif($name == "tqf5-6_major_deadline"){

            if($dateId > $tqfDeadline["tqf5-6_deadline"]){
                echo "วันที่กำหนดต้องไม่เกินของคณะ";
                header('HTTP/1.0 400 Bad Request', true, 400);
            }else{
                DB::update('tqf_major_deadline', array(
                    $name => $dateId
                ), "year=%i AND term=%i AND major_id=%s", $year,$term,$major_id);
            }

        }

    }

}


function updateReportDeadline(){
    $year = $_POST["year"];
    $value = $_POST["value"];
    $faculty_id = $_POST["faculty_id"];
    $major_id = $_POST["major_id"];

    //วันที่รับค่าเข้ามา
    $date = DateTime::createFromFormat('d/m/Y', $value);
    $date = $date->sub(new DateInterval("P543Y"));

    $dateId = $date->format('Y-m-d');
    $day = $date->format("N");


    //เช็ควันหยุด
    $holidaySql = "SELECT `date_id`,`date_name` FROM `official_holiday`
                       WHERE `date_id` = %s
                       AND `faculty_id` = %s";
    $holidayResult = DB::queryFirstRow($holidaySql, $dateId,$faculty_id);
    $checkHoliday = DB::count();


    if($checkHoliday != 0){
        echo "วันที่กำหนดต้องไม่ใช่ วัน".$holidayResult["date_name"];
        header('HTTP/1.0 400 Bad Request', true, 400);
    }elseif($day == 6){
        echo "วันที่กำหนดต้องไม่ใช่วันเสาร์";
        header('HTTP/1.0 400 Bad Request', true, 400);
    }elseif($day == 7){
        echo "วันที่กำหนดต้องไม่ใช่วันอาทิตย์";
        header('HTTP/1.0 400 Bad Request', true, 400);
    }elseif($major_id == "null"){
        DB::update('report_deadline', array(
            "report_file_deadline" => $dateId
        ), "year=%i AND faculty_id=%s", $year,$faculty_id);

        DB::update('report_major_deadline', array(
            'report_file_major_deadline' => $dateId

        ), "`major_id` IN (SELECT `major_id` FROM `major` 
            INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id` 
            WHERE `sector`.`faculty_id` = %s)

            AND `year` = %i
            AND `report_file_major_deadline` > %s", $faculty_id,$year,$dateId);

    }else{

        $sql = "SELECT `year`,`report_file_deadline`,`faculty_id` FROM `report_deadline`
                WHERE `year` = %i
                AND `faculty_id` = %s";
        $reportDeadline = DB::queryFirstRow($sql,$year,$faculty_id);


        if($dateId <= $reportDeadline["report_file_deadline"]){
            DB::update('report_major_deadline', array(
                "report_file_major_deadline" => $dateId
            ), "year=%i AND major_id=%s", $year,$major_id);
        }else{
            echo "วันที่กำหนดต้องไม่เกินของคณะ";
            header('HTTP/1.0 400 Bad Request', true, 400);
        }

    }

}