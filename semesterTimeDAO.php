<?php
include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if(isset($_POST["func"] )){
    if($_POST["func"] == "addSemesterTime"){
        addSemesterTime();
    }elseif($_POST["func"] == "getSemesterTime"){
        getSemesterTime();
    }elseif($_POST["func"] == "removeSemesterTime"){
        removeSemesterTime();
    }elseif($_POST["func"] == "updateSemesterTime"){
        updateSemesterTime();
    }
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

//เพิ่มวัน เปิด ปิด ภาคเรียน
function addSemesterTime(){
    DB::$error_handler = false; // since we're catching errors, don't need error handler
    DB::$throw_exception_on_error = true;

    $year = $_POST["year"];
    $term = $_POST["term"];
    $faculty_id = $_POST["faculty_id"];
    $start_semester = $_POST["start_semester"];
    $end_semester = $_POST["end_semester"];

    $date = DateTime::createFromFormat('d/m/Y', $start_semester);
    $start_semester = $date->sub(new DateInterval("P543Y"))->format('Y-m-d');

    $date2 = DateTime::createFromFormat('d/m/Y', $end_semester);
    $end_semester = $date2->sub(new DateInterval("P543Y"))->format('Y-m-d');

    try{
        DB::insert('semester_time', array(
            'year' => $year,
            'term' => $term,
            'faculty_id' => $faculty_id,
            'start_semester' => $start_semester,
            'end_semester' => $end_semester
        ));
    } catch(MeekroDBException $e) {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!Error Mysql can't update.";
        exit();
    }

    $sql = "SELECT `faculty_id`,`tqf34_period`,`tqf56_period`,`report_period` FROM `tqf_period`
            WHERE `faculty_id` = %s";
    $periodResults = DB::queryFirstRow($sql,$faculty_id);
    $counterPeriod = DB::count();

    if($counterPeriod == 0){
        DB::insert('tqf_period', array(
            'faculty_id' => $faculty_id,
            'tqf34_period' => 7,
            'tqf56_period' => 30,
            'report_period' => 60,
        ));

        $tqf34Period = 7;
        $tqf56Period = 30;
        $reportPeriod = 60;

    }else{
        $tqf34Period = $periodResults["tqf34_period"];
        $tqf56Period = $periodResults["tqf56_period"];
        $reportPeriod = $periodResults["report_period"];
    }


    //ตั้งวันส่ง มคอ. 3-4
    $start_semester_date = new DateTime($start_semester);
    $start_semester_date = $start_semester_date->sub(new DateInterval("P".$tqf34Period."D"))->format('Y-m-d');
    $tqf34Deadline = getDeadline($start_semester_date,$faculty_id);


    //ตั้งวันส่ง มคอ. 5-6
    $end_semester_date = new DateTime($end_semester);
    $end_semester_date = $end_semester_date->add(new DateInterval("P".$tqf56Period."D"))->format('Y-m-d');
    $tqf56Deadline = getDeadline($end_semester_date,$faculty_id);


    //เพิ่มวันส่ง มคอ. 3-6 ลงใน tqf_deadline table db
    DB::insert('tqf_deadline', array(
        'year' => $year,
        'term' => $term,
        'faculty_id' => $faculty_id,
        'tqf3-4_deadline' => $tqf34Deadline,
        'tqf5-6_deadline' => $tqf56Deadline
    ));

    $sqlMajor = "SELECT `major_id` FROM `major`

                INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`

                WHERE `sector`.`faculty_id` = %s";
    $majorId = DB::query($sqlMajor,$faculty_id);


    //เอา tqf Deadline ใส่ในของ table  major tqf Deadline
    foreach ($majorId as $row){

        $resultsMajorTqf = DB::queryFirstRow("SELECT `year`,`term`,`tqf3-4_major_deadline`,`tqf5-6_major_deadline`,`major_id` FROM `tqf_major_deadline`
                    WHERE `year` = %i
                    AND `term` = %i
                    AND `major_id` = %s", $year,$term,$row["major_id"] );

        //นับว่ามีข้อมูลใน table ไหม
        $countMajorTqf = DB::count();

        //กรณีไม่มีก็คือ == 0 ให้เพิ่มใหม่
        if($countMajorTqf == 0){
            DB::insert('tqf_major_deadline', array(
                'year' => $year,
                'term' => $term,
                'major_id' => $row["major_id"],
                'tqf3-4_major_deadline' => $tqf34Deadline,
                'tqf5-6_major_deadline' => $tqf56Deadline
            ));
        }else{
            //กรณีมีอยู่แล้ว

            //เช็คว่า tqf 3 - 4 dead line มากกว่าของที่กำหนดมาเองไหม ถ้ามากกว่าให้อัพเดทแก้ไข
            if($resultsMajorTqf["tqf3-4_major_deadline"] > $tqf34Deadline){
                DB::update('tqf_major_deadline', array(
                    "tqf3-4_major_deadline" => $tqf34Deadline
                ), "year=%i AND term=%i AND major_id=%s",$year, $term, $row["major_id"]);
            }

            //เช็คว่า tqf 5 - 6 dead line มากกว่าของที่กำหนดมาเองไหม ถ้ามากกว่าให้อัพเดทแก้ไข
            if($resultsMajorTqf["tqf5-6_major_deadline"] > $tqf56Deadline){
                DB::update('tqf_major_deadline', array(
                    "tqf3-4_major_deadline" => $tqf56Deadline
                ), "year=%i AND term=%i AND major_id=%s",$year, $term, $row["major_id"]);
            }

        }

    }

    //ตั้งวันส่งไฟล์รายงาน (มคอ. 7)
    if($term == 2){
        $end_semester_date = new DateTime($end_semester);
        $end_semester_date = $end_semester_date->add(new DateInterval("P".$reportPeriod."D"))->format('Y-m-d');
        $reportDeadline = getDeadline($end_semester_date,$faculty_id);

        //เพิ่มวันส่ง report file (มคอ.7) ลงใน report_deadline table db
        DB::insert('report_deadline', array(
            'year' => $year,
            'faculty_id' => $faculty_id,
            'report_file_deadline' => $reportDeadline,
        ));

        //เอา report Deadline ใส่ในของ table major report Deadline
        foreach ($majorId as $row){

            $resultsMajorTqf7 = DB::queryFirstRow("SELECT `year`,`report_file_major_deadline`,`major_id` FROM `report_major_deadline`
                    WHERE `year` = %i
                    AND `major_id` = %s", $year,$row["major_id"] );
            //นับว่ามีข้อมูลใน table ไหม
            $countMajorTqf7 = DB::count();

            //กรณีไม่มีก็คือ == 0 ให้เพิ่มใหม่
            if($countMajorTqf7 == 0){
                DB::insert('report_major_deadline', array(
                    'year' => $year,
                    'report_file_major_deadline' => $reportDeadline,
                    'major_id' => $row["major_id"],
                ));
            }else{
                //กรณีมีอยู่แล้ว

                //เช็คว่า tqf 7 dead line มากกว่าของที่กำหนดมาเองไหม ถ้ามากกว่าให้อัพเดทแก้ไข
                if($resultsMajorTqf7["report_major_deadline"] > $reportDeadline){
                    DB::update('report_major_deadline', array(
                        "report_file_major_deadline" => $reportDeadline
                    ), "year=%i AND major_id=%s",$year, $row["major_id"]);
                }
            }
        }

    }

}

//ลบข้อมูล วันเปิด-ปิด ภาคเรียนนั้น
function removeSemesterTime(){
    $data = $_POST["data"];

    foreach($data as $row){
        DB::delete('semester_time',"`year`=%i AND `term`=%i AND faculty_id=%s", $row["year"],$row["term"],$row["faculty_id"]);
        DB::delete('tqf_deadline',"`year`=%i AND `term`=%i AND faculty_id=%s", $row["year"],$row["term"],$row["faculty_id"]);

        //ลบของสาขา
        DB::delete('tqf_major_deadline',"`year`=%i AND `term`=%i AND 
                    `major_id` IN (SELECT `major_id` FROM `major` 
                    INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id` 
                    WHERE `sector`.`faculty_id` = %s)",
                    $row["year"],$row["term"],$row["faculty_id"]);


        if($row["term"] == 2){
            DB::delete('report_deadline',"`year`=%i AND faculty_id=%s", $row["year"],$row["faculty_id"]);

            //ลบของสาขา
            DB::delete('report_major_deadline',"`year`=%i AND 
                        `major_id` IN (SELECT `major_id` FROM `major` 
                        INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id` 
                        WHERE `sector`.`faculty_id` = %s)",
                        $row["year"],$row["faculty_id"]);

        }
    }
}

//get ข้อมูล วันเปิด-ปิด ภาคเรียนจาก database
function getSemesterTime(){
    $year = $_POST["year"];
    $faculty_id = $_POST["faculty_id"];

    $sql = "SELECT `year`,`term`,`faculty_id`,`start_semester`,`end_semester` FROM `semester_time`
            WHERE `year` = %i
            AND `faculty_id` = %s
            ORDER BY `term` DESC";
    $results = DB::query($sql,$year,$faculty_id);
    $json = json_encode($results);
    echo $json;

}


//อัพเดทข้อมูล เปิด-ปิด ภาคเรียน โดยจะทำการอัพเดท วันส่ง มคอ. ของคณะไปด้วย
function updateSemesterTime(){
    $year = $_POST["year"];
    $term = $_POST["term"];
    $name = $_POST["name"];
    $value = $_POST["value"];
    $faculty_id = $_POST["faculty_id"];

    //แก้ไขวันโดย ลบ ปี 543 และแปลงเป็นแบบ Y-m-d
    $date = DateTime::createFromFormat('d/m/Y', $value);
    $dateData = $date->sub(new DateInterval("P543Y"))->format('Y-m-d');

    //อัพเดทข้อมูลลง semester_time table
    DB::update('semester_time', array(
        $name => $dateData
    ), "faculty_id=%s AND year=%i AND term=%i",$faculty_id, $year,$term);

    $sql = "SELECT `faculty_id`,`tqf34_period`,`tqf56_period`,`report_period` FROM `tqf_period`
            WHERE `faculty_id` = %s";
    $periodResults = DB::queryFirstRow($sql,$faculty_id);
    $counterPeriod = DB::count();

    //ในกรณีที่ไม่ได้ตั้งระยะเวลาของ มคอ. จะทำการเพิ่มให้เอง
    if($counterPeriod == 0){
        DB::insert('tqf_period', array(
            'faculty_id' => $faculty_id,
            'tqf34_period' => 7,
            'tqf56_period' => 30,
            'report_period' => 60,
        ));

        $tqf34Period = 7;
        $tqf56Period = 30;
        $reportPeriod = 60;

    }else{
        $tqf34Period = $periodResults["tqf34_period"];
        $tqf56Period = $periodResults["tqf56_period"];
        $reportPeriod = $periodResults["report_period"];
    }

    if($name == "start_semester"){

        //ตั้งวันส่ง มคอ. 3-4
        $start_semester_date = new DateTime($dateData);
        $start_semester_date = $start_semester_date->sub(new DateInterval("P".$tqf34Period."D"))->format('Y-m-d');
        $tqf34Deadline = getDeadline($start_semester_date,$faculty_id);

        //เปลี่ยนวันส่ง มคอ.3-4 ของคณะ
        DB::update('tqf_deadline', array(
            "tqf3-4_deadline" => $tqf34Deadline
        ), "faculty_id=%s AND year=%i AND term=%i",$faculty_id, $year, $term);

        //เอา tqf34 Deadline ใส่ในของ  major tqf Deadline table (แก้ไขกรณีที่ วันกำหนดเวลาของ สาขา มากกว่าของ คณะ)
        DB::update('tqf_major_deadline', array(
            'tqf3-4_major_deadline' => $tqf34Deadline

        ), "`major_id` IN (SELECT `major_id` FROM `major` 
            INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id` 
            WHERE `sector`.`faculty_id` = %s)

            AND `year` = %i
            AND `term` = %i
            AND `tqf3-4_major_deadline` > %s", $faculty_id,$year,$term,$tqf34Deadline);

    }elseif($name == "end_semester"){

        //ตั้งวันส่ง มคอ. 5-6
        $end_semester_date = new DateTime($dateData);
        $end_semester_date = $end_semester_date->add(new DateInterval("P".$tqf56Period."D"))->format('Y-m-d');
        $tqf56Deadline = getDeadline($end_semester_date,$faculty_id);

        //เปลี่ยนวันส่ง มคอ.5-6 ของคณะ
        DB::update('tqf_deadline', array(
            "tqf5-6_deadline" => $tqf56Deadline
        ), "faculty_id=%s AND year=%i AND term=%i",$faculty_id, $year, $term);

        //เอา tqf56 Deadline ใส่ในของ table  major tqf Deadline (แก้ไขกรณีที่ วันกำหนดเวลาของ สาขา มากกว่าของ คณะ)
        DB::update('tqf_major_deadline', array(
            'tqf5-6_major_deadline' => $tqf56Deadline

        ), "`major_id` IN (SELECT `major_id` FROM `major` 
            INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id` 
            WHERE `sector`.`faculty_id` = %s)

            AND `year` = %i
            AND `term` = %i
            AND `tqf5-6_major_deadline` > %s", $faculty_id,$year,$term,$tqf56Deadline);


        //ตั้งวันส่งไฟล์รายงาน (มคอ. 7) ถ้าเป็นเทอม 2
        if($term == 2){
            $end_semester_date = new DateTime($dateData);
            $end_semester_date = $end_semester_date->add(new DateInterval("P".$reportPeriod."D"))->format('Y-m-d');
            $reportDeadline = getDeadline($end_semester_date,$faculty_id);

            //เพิ่มวันส่ง report file (มคอ.7) ลงใน report_deadline table db
            DB::update('report_deadline', array(
                "`report_file_deadline`" => $reportDeadline
            ), "`faculty_id`=%s AND `year`=%i",$faculty_id, $year);

            //เอา report Deadline ใส่ในของ table major report Deadline (แก้ไขกรณีที่ วันกำหนดเวลาของ สาขา มากกว่าของ คณะ)
            DB::update('report_major_deadline', array(
                "`report_file_major_deadline`" => $reportDeadline

            ), "`major_id` IN (SELECT `major_id` FROM `major` 
            INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id` 
            WHERE `sector`.`faculty_id` = %s)

            AND `year` = %i
            AND `report_file_major_deadline` > %s", $faculty_id,$year,$reportDeadline);
        }

    }


}