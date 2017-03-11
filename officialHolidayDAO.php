<?php
include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if(isset($_POST["func"] )){
    if($_POST["func"] == "getHoliday"){
        getHoliday();
    }elseif($_POST["func"] == "addHoliday"){
        addHoliday();
    }elseif($_POST["func"] == "removeHoliday"){
        removeHoliday();
    }elseif($_POST["func"] == "updateHoliday"){
        updateHoliday();
    }
}

function getHoliday(){
    $year = $_POST["year"]-543;
    $faculty_id = $_POST["faculty_id"];

    $sql = "SELECT `date_id`,`date_name`,`faculty_id` FROM `official_holiday`
            WHERE YEAR(`date_id`) = %i
            AND `faculty_id` = %s";

    $results = DB::query($sql,$year,$faculty_id);
    $json = json_encode($results);
    echo $json;
}


function addHoliday(){
    DB::$error_handler = false;
    DB::$throw_exception_on_error = true;

    $holiday = $_POST["holiday"];
    $holidayName = $_POST["holidayName"];
    $faculty_id = $_POST["faculty_id"];

    $count = count($holiday);
    for($i=0;$i<$count;$i++){

        if($holiday[$i] != null && $holidayName[$i] != null) {
            $date = DateTime::createFromFormat('d/m/Y', $holiday[$i]);
            $holiday[$i] = $date->sub(new DateInterval("P543Y"))->format('Y-m-d');

            DB::insertIgnore('official_holiday', array(
                'date_id' => $holiday[$i],
                'date_name' => $holidayName[$i],
                'faculty_id' => $faculty_id,
            ));

            //แก้ไขวันเปิดปิดเทอม กรณีที่ วันหยุดตรงกับวันเปิดเทอม
            $deadline = getDeadline($holiday[$i],$faculty_id);
            checkDeadline($deadline,$holiday[$i],$faculty_id);
        }

    }
}

function removeHoliday(){
    $data = $_POST["data"];

    foreach($data as $row){
        DB::delete('official_holiday',"`date_id`=%s AND faculty_id=%s", $row["date_id"],$row["faculty_id"]);
    }
}

function updateHoliday(){
    $name = $_POST["name"];
    $faculty_id = $_POST["faculty_id"];
    $dateId = null;
    $value = null;

    if($name == "date_id"){
        $date = DateTime::createFromFormat('d/m/Y', $_POST["pk"]);
        $dateId = $date->sub(new DateInterval("P543Y"))->format('Y-m-d');

        $date1 = DateTime::createFromFormat('d/m/Y', $_POST["value"]);
        $value = $date1->sub(new DateInterval("P543Y"))->format('Y-m-d');


    }elseif($name == "date_name"){
        $dateId = $_POST["pk"];
        $value = $_POST["value"];
    }

    DB::update('official_holiday', array(
        $name => $value
    ), "date_id=%s and faculty_id=%s", $dateId,$faculty_id);


    //แก้ไขวันเปิดปิดเทอม กรณีที่ วันหยุดตรงกับวันเปิดเทอม
    $deadline = getDeadline($value,$faculty_id);
    checkDeadline($deadline,$value,$faculty_id);

}

//ฟังก์ชั่นกำหนด เวลาส่ง มคอ. โดยการเช็ควันหยุด(ส/อ) หรือวันหยุดราชการ
function getDeadline($deadlineTime,$faculty_id){
    $sqlHoliday = "SELECT `date_id` FROM `official_holiday`
                  WHERE `date_id` = %s
                  AND `faculty_id` = %s";
    $day = new DateTime($deadlineTime);
    $day = $day->format('N');

    DB::query($sqlHoliday,$deadlineTime,$faculty_id);
    $countHoliday = DB::count();

    while($day == 6 || $day == 7 || $countHoliday == 1){

        $deadlineTime = new DateTime($deadlineTime);
        $deadlineTime = $deadlineTime->add(new DateInterval("P1D"))->format('Y-m-d');

        $day = new DateTime($deadlineTime);
        $day = $day->format('N');

        DB::query($sqlHoliday,$deadlineTime,$faculty_id);
        $countHoliday = DB::count();

    }
    return $deadlineTime;
}


//ฟังก์ชั่นเช็คว่า วันหยุด ตรงกับวันส่ง มคอ. ไหมถ้าตรงให้เปลี่ยน
function checkDeadline($deadline , $value ,$faculty_id){

    //ของ มคอ 3 - 6
    DB::query("SELECT * FROM `tqf_deadline`
                WHERE `faculty_id` = %s
                AND `tqf3-4_deadline` = %s
                OR `tqf5-6_deadline` = %s",$faculty_id, $value,$value);
    $tqf36DeadlineCounter = DB::count();


    if($tqf36DeadlineCounter >= 1){
        //เปลี่ยนวันส่ง มคอ.3-4 ของคณะ
        DB::update('tqf_deadline', array(
            "`tqf3-4_deadline`" => $deadline
        ), "`faculty_id`=%s AND `tqf3-4_deadline`=%s",$faculty_id,$value);

        //เปลี่ยนวันส่ง มคอ.5-6 ของคณะ
        DB::update('tqf_deadline', array(
            "`tqf5-6_deadline`" => $deadline
        ), "`faculty_id`=%s AND `tqf5-6_deadline`=%s",$faculty_id,$value);

    }

    DB::query("SELECT * FROM `tqf_major_deadline`
            WHERE `major_id` IN (SELECT `major_id` FROM `major`
            INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`
            WHERE `sector`.`faculty_id` = %s)

            AND `tqf3-4_major_deadline` = %s
            OR `tqf5-6_major_deadline` = %s",$faculty_id, $value,$value);
    $majorTqf36DeadlineCounter = DB::count();

    if($majorTqf36DeadlineCounter >= 1){

        DB::update('tqf_major_deadline', array(
            'tqf3-4_major_deadline' => $deadline

        ), "`major_id` IN (SELECT `major_id` FROM `major`
            INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`
            WHERE `sector`.`faculty_id` = %s)

            AND `tqf3-4_major_deadline` = %s", $faculty_id,$value);

        DB::update('tqf_major_deadline', array(
            'tqf5-6_major_deadline' => $deadline

        ), "`major_id` IN (SELECT `major_id` FROM `major`
            INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`
            WHERE `sector`.`faculty_id` = %s)

            AND `tqf5-6_major_deadline` = %s", $faculty_id,$value);
    }


    //ของ มคอ.7
    DB::query("SELECT * FROM `report_deadline`
                WHERE `faculty_id` = %s
                AND `report_file_deadline` = %s",$faculty_id, $value);
    $tqf7DeadlineCounter = DB::count();

    if($tqf7DeadlineCounter >= 1){
        //เพิ่มวันส่ง report file (มคอ.7) ลงใน report_deadline table db
        DB::update('report_deadline', array(
            "`report_file_deadline`" => $deadline
        ), "`faculty_id`=%s AND `report_file_deadline`",$faculty_id,$value);

    }

    DB::query("SELECT * FROM `report_major_deadline`
                WHERE `major_id` IN (SELECT `major_id` FROM `major`
                                     INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`
                                     WHERE `sector`.`faculty_id` = %s)
                            
                AND `report_file_major_deadline` = %s",$faculty_id, $value);
    $majorTqf7DeadlineCounter = DB::count();

    if($majorTqf7DeadlineCounter >= 1){

        //เอา report Deadline ใส่ในของ table major report Deadline (แก้ไขกรณีที่ วันกำหนดเวลาของ สาขา มากกว่าของ คณะ)
        DB::update('report_major_deadline', array(
            "`report_file_major_deadline`" => $deadline

        ), "`major_id` IN (SELECT `major_id` FROM `major` 
            INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id` 
            WHERE `sector`.`faculty_id` = %s)

            AND `report_file_major_deadline` = %s", $faculty_id,$value);
    }

}