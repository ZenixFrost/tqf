<?php

include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if(isset($_POST["func"] )){
    if($_POST["func"] == "getTqfFileTable"){
        getTqfFileTable();
    }elseif($_POST["func"] == "getCourseTqfTable"){
        getCourseTqfTable();
    }elseif($_POST["func"] == "addTqfFile"){
        addTqfFile();
    }elseif($_POST["func"] == "removeTqfFile"){
        removeTqfFile();
    }elseif($_POST["func"] == "getTeachingFileTable"){
        getTeachingFileTable();
    }elseif($_POST["func"] == "addTeachingFile"){
        addTeachingFile();
    }elseif($_POST["func"] == "getTqfDeadline"){
        getTqfDeadline();
    }elseif($_POST["func"] == "getCourseId"){
        getCourseId();
    }
}

function getCourseId(){
    $year = $_POST["year"];
    $user_id = $_POST["user_id"];

    $sql = "SELECT DISTINCT `course_id` FROM `open_subjects_group`
            WHERE `year` = %i
            AND `teacher_id` = %s
            ORDER BY `course_id` ASC";

    $results = DB::query($sql, $year, $user_id);
    $json = json_encode($results);
    echo $json;
}

//ข้อมูลของ Table ไฟล์ มคอ.
function getTqfFileTable(){
    $year = $_POST["year"];
    $user_id = $_POST["user_id"];
    $course = $_POST["course"];
    $data = [];
    $preTqf_type = null;
    $preFile_id = null;
    $preDate_sent = null;
    $preSender_id = null;
    $perSender_name = null;
    $perSender_surname = null;
    $check = false;

    $sql = "SELECT DISTINCT `course`.`major_id`,`open_subjects_group`.`course_id`,`open_subjects_group`.`year`,`open_subjects_group`.`term`,
		    `open_subjects_group`.`subject_id`,`subject`.`subject_name`,`subject`.`subject_type`,
	        `tqf_file`.`file_id`,`tqf_file`.`tqf_type`,`responsible_subject`,`name`,`surname`,`sender_id`,`date_sent`
	
            FROM `open_subjects_group`
            
            INNER JOIN `subject` ON `subject`.`subject_id` = `open_subjects_group`.`subject_id`
            
            INNER JOIN `open_subjects`ON `open_subjects`.`course_id` = `open_subjects_group`.`course_id`
            AND `open_subjects`.`subject_id` = `open_subjects_group`.`subject_id`
            AND `open_subjects`.`term` = `open_subjects_group`.`term`
            AND `open_subjects`.`year` = `open_subjects_group`.`year`
            
            LEFT JOIN `user` ON  `responsible_subject` = `user`.`user_id`
            
            LEFT JOIN `tqf_file`ON `tqf_file`.`course_id` = `open_subjects_group`.`course_id`
            AND `tqf_file`.`subject_id` = `open_subjects_group`.`subject_id`
            AND `tqf_file`.`term` = `open_subjects_group`.`term`
            AND `tqf_file`.`year` = `open_subjects_group`.`year`
            
            INNER JOIN `course` ON `course`.`course_id` = `open_subjects_group`.`course_id`
                        
            WHERE `open_subjects_group`.`year` = %i
            AND `teacher_id` = %s
            AND `open_subjects_group`.`course_id` IN %ls
            
            ORDER BY `open_subjects_group`.`course_id` ASC,`open_subjects_group`.`term` DESC ,`open_subjects_group`.`subject_id` ASC";
    $results = DB::query($sql,$year,$user_id,$course);

    for($i = 0;$i < count($results);$i++){

        $major_id = $results[$i]["major_id"];
        $course_id = $results[$i]["course_id"];
        $year = $results[$i]["year"];
        $term = $results[$i]["term"];
        $subject_id = $results[$i]["subject_id"];
        $subject_name = $results[$i]["subject_name"];
        $subject_type = $results[$i]["subject_type"];
        $file_id = $results[$i]["file_id"];
        $tqf_type = $results[$i]["tqf_type"];
        $name = $results[$i]["name"];
        $surname = $results[$i]["surname"];
        $sender_id = $results[$i]["sender_id"];
        $date_sent = $results[$i]["date_sent"];

        //ชื่อผู้ส่งไฟล์
        $sender = DB::queryFirstRow("SELECT `name`,`surname` FROM `user` WHERE `user_id` = %s", $sender_id);
        $sender_name = $sender["name"];
        $sender_surname = $sender["surname"];


        //เช็ดตัวต่อไปเพื่อนำเอามาเป็น มคอ 3,4 หรือ 5,6
        if($i < count($results)-1){
            $nextCourse_id = $results[$i+1]["course_id"];
            $nextYear= $results[$i+1]["year"];
            $nextTerm = $results[$i+1]["term"];
            $nextSubject_id = $results[$i+1]["subject_id"];

            if($course_id == $nextCourse_id && $year == $nextYear && $term == $nextTerm && $nextSubject_id == $subject_id){
                $preFile_id = $results[$i]["file_id"];
                $preTqf_type = $results[$i]["tqf_type"];
                $preDate_sent = $results[$i]["date_sent"];
                $preSender_id = $results[$i]["sender_id"];

                //ชื่อผู้ส่งไฟล์
                $sender = DB::queryFirstRow("SELECT `name`,`surname` FROM `user` WHERE `user_id` = %s", $preSender_id);
                $perSender_name = $sender["name"];
                $perSender_surname = $sender["surname"];

                $check = true;
                continue;
            }
        }

        if($tqf_type == 3 || $tqf_type == 4){
            $data[] = [
                'major_id' => $major_id,
                'course_id' => $course_id,
                'year' => $year,
                'term' => $term,
                'subject_id' => $subject_id,
                'subject_name' => $subject_name,
                'subject_type' => $subject_type,

                //มคอ 3,4
                'pre_file_id' => $file_id,
                'pre_tqf_type' => $tqf_type,
                'pre_sender_id' => $sender_id,
                'pre_sender_name' => $sender_name,
                'pre_sender_surname' => $sender_surname,
                'pre_date_send' => $date_sent,

                //มคอ 5,6
                'post_file_id' => $preFile_id,
                'post_tqf_type' => $preTqf_type,
                'post_sender_id' => $preSender_id,
                'post_sender_name' => $perSender_name,
                'post_sender_surname' => $perSender_surname,
                'post_date_send' => $preDate_sent,

                //ผู้รับผิดชอบรายวิชา
                'name' => $name,
                'surname' => $surname,
            ];
        }elseif($tqf_type == 5 || $tqf_type == 6){
            $data[] = [
                'major_id' => $major_id,
                'course_id' => $course_id,
                'year' => $year,
                'term' => $term,
                'subject_id' => $subject_id,
                'subject_name' => $subject_name,
                'subject_type' => $subject_type,

                //มคอ 3,4
                'pre_file_id' => $preFile_id,
                'pre_tqf_type' => $preTqf_type,
                'pre_sender_id' => $preSender_id,
                'pre_sender_name' => $perSender_name,
                'pre_sender_surname' => $perSender_surname,
                'pre_date_send' => $preDate_sent,

                //มคอ 5,6
                'post_file_id' => $file_id,
                'post_tqf_type' => $tqf_type,
                'post_sender_id' => $sender_id,
                'post_sender_name' => $sender_name,
                'post_sender_surname' => $sender_surname,
                'post_date_send' => $date_sent,

                //ผู้รับผิดชอบรายวิชา
                'name' => $name,
                'surname' => $surname,
            ];

        }elseif($tqf_type == null){
            $data[] = [
                'major_id' => $major_id,
                'course_id' => $course_id,
                'year' => $year,
                'term' => $term,
                'subject_id' => $subject_id,
                'subject_name' => $subject_name,
                'subject_type' => $subject_type,

                //มคอ 3,4
                'pre_file_id' => $file_id,
                'pre_tqf_type' => $tqf_type,
                'pre_sender_id' => $sender_id,
                'pre_sender_name' => $sender_name,
                'pre_sender_surname' => $sender_surname,
                'pre_date_send' => $date_sent,

                //มคอ 5,6
                'post_file_id' => $file_id,
                'post_tqf_type' => $tqf_type,
                'post_sender_id' => $sender_id,
                'post_sender_name' => $sender_name,
                'post_sender_surname' => $sender_surname,
                'post_date_send' => $date_sent,

                //ผู้รับผิดชอบรายวิชา
                'name' => $name,
                'surname' => $surname,
            ];
        }

        if($check == true){
            $preFile_id = null;
            $preTqf_type = null;
            $preSender_id = null;
            $perSender_name = null;
            $perSender_surname = null;
            $date_sent = null;
            $check = false;
        }

    }

    $json = json_encode($data);
    echo $json;

}

//ข้อมูลของ Table ไฟล์สรุปการสอน
function getTeachingFileTable(){
    $year = $_POST["year"];
    $user_id = $_POST["user_id"];
    $course = $_POST["course"];
    $subjectData = [];
    $data = [];

    $oldTerm = null;
    $oldCourseId = null;
    $oldSubjectId = null;

    $groupCounter = 0;
    $teacherCounter = 0;


    $subjectSql = "SELECT `course_id`,`year`,`term`,`subject_id` FROM `open_subjects_group`
                    WHERE `teacher_id` = %s
                    AND `year` = %i
                    ORDER BY `course_id`,`term` DESC ,`subject_id` ASC";
    $subjectResults = DB::query($subjectSql,$user_id,$year);

    foreach ($subjectResults as $rows){
        array_push($subjectData,$rows["subject_id"]);
    }


    if(count($subjectData) >= 1) {
        $sql = "SELECT `open_subjects_group`.`course_id`,`open_subjects_group`.`year`,`open_subjects_group`.`term`,
		`open_subjects_group`.`subject_id`,`subject`.`subject_name`,`subject`.`subject_type`,
	        `open_subjects_group`.`group`,`teaching_file`.`file_id`,`teaching_file`.`tqf_type`,`teacher_id`,`name`,`surname`
	
          FROM `open_subjects_group`
                      
          INNER JOIN `subject` ON `subject`.`subject_id` = `open_subjects_group`.`subject_id`
          
          LEFT JOIN `user` ON  `open_subjects_group`.`teacher_id` = `user`.`user_id`
          
          LEFT JOIN `teaching_file`ON `teaching_file`.`course_id` = `open_subjects_group`.`course_id`
          AND `teaching_file`.`subject_id` = `open_subjects_group`.`subject_id`
          AND `teaching_file`.`term` = `open_subjects_group`.`term`
          AND `teaching_file`.`year` = `open_subjects_group`.`year`
          AND `teaching_file`.`group` = `open_subjects_group`.`group`
          
          WHERE `open_subjects_group`.`year` = %i
          AND `open_subjects_group`.`subject_id` IN %ls
          AND `open_subjects_group`.`course_id` IN %ls
      
          ORDER BY `open_subjects_group`.`course_id`,`open_subjects_group`.`term` DESC ,`subject_id` ASC,`group` ASC,`name` ASC,`surname` ASC";
        $results = DB::query($sql, $year, $subjectData, $course);

        foreach ($results as $rows) {

            //ถ้ามีค่าอะไรเปลี่ยนก็ให้เข้ามาเช็คอีกรอบ
            if($oldTerm != $rows["term"] || $oldCourseId != $rows["course_id"] || $oldSubjectId != $rows["subject_id"]){
                //เช็คว่า เราเป็นคนสอนในกลุ่มนี้ด้วยไหม
                DB::query("SELECT `teacher_id` FROM `open_subjects_group`
                    WHERE `year` = %i
                    AND `term` = %i
                    AND `course_id` = %s
                    AND `subject_id` = %s
                    AND `teacher_id` = %s", $rows["year"], $rows["term"], $rows["course_id"], $rows["subject_id"],$user_id);
                $groupCounter = DB::count();

                //เช็คว่า มีคนสอนคนเดียวรึเปล่าใน กลุ่มนั้น
                DB::query("SELECT DISTINCT `teacher_id` FROM `open_subjects_group`
                    WHERE `year` = %i
                    AND `term` = %i
                    AND `course_id` = %s
                    AND `subject_id` = %s", $rows["year"], $rows["term"], $rows["course_id"], $rows["subject_id"]);
                $teacherCounter = DB::count();

            }


            if ($teacherCounter > 1 && $groupCounter >= 1) {
                $data[] = [
                    'course_id' => $rows["course_id"],
                    'year' => $rows["year"],
                    'term' => $rows["term"],
                    'subject_id' => $rows["subject_id"],
                    'subject_name' => $rows["subject_name"],
                    'subject_type' => $rows["subject_type"],
                    'group' => $rows["group"],
                    'file_id' => $rows["file_id"],
                    'tqf_type' => $rows["tqf_type"],
                    'teacher_id' => $rows["teacher_id"],
                    'name' => $rows["name"],
                    'surname' => $rows["surname"],
                ];
            }

            $oldTerm = $rows["term"];
            $oldCourseId = $rows["course_id"];
            $oldSubjectId = $rows["subject_id"];
        }
    }else{
        $data = [];
    }

    $json = json_encode($data);
    echo $json;
}

//course_id ดูว่าในแต่ละปีเทอม user นั้นของผุ้สอนมีหลักสูตรอะไรมั่ง โดยแยกเป็นโครง table มคอ และ ไฟล์สรุปการสอน
function getCourseTqfTable(){
    $year = $_POST["year"];
    $term = $_POST["term"];
    $user_id = $_POST["user_id"];

    $sql = "SELECT DISTINCT `open_subjects_group`.`course_id` FROM `open_subjects_group`
            WHERE `teacher_id` = %s
            AND  `open_subjects_group`.`year` = %i
            AND `open_subjects_group`.`term` = %i";
    $results = DB::query($sql,$user_id,$year,$term);
    $json = json_encode($results);
    echo $json;

}

//เพิ่มข้อมูลไฟล์ มคอ ไปที่ tqf_file table (DB)
function addTqfFile(){
    $date = new DateTime("now",  new DateTimeZone('ASIA/Bangkok'));

    $file_id = $_POST["file_id"];
    $tqf_index = $_POST["tqf_index"];
    $data = $_POST["data"];
    $user = $_POST["user"];
    $tqfType = null;

    if($tqf_index == 0){
        if($data["subject_type"] == 0){
            $tqfType = 3;
        }elseif($data["subject_type"] == 1){
            $tqfType = 4;
        }
    }elseif($tqf_index == 1){
        if($data["subject_type"] == 0){
            $tqfType = 5;
        }elseif($data["subject_type"] == 1){
            $tqfType = 6;
        }
    }


    DB::insertIgnore('tqf_file', array(
        'course_id' => $data["course_id"],
        'subject_id' => $data["subject_id"],
        'term' => $data["term"],
        'year' => $data["year"],
        'file_id' => $file_id,
        'sender_id' => $user,
        'tqf_type' => $tqfType,
        'date_sent' => $date->format("Y-m-d")
    ));
}

//ลบข้อมูลไฟล์ มคอ ไปที่ tqf_file table (DB) หรือ ลบไฟล์สรุปการสอน จาก teaching_file table
function removeTqfFile(){
    $file_id = $_POST["file_id"];
    $table = $_POST["table"];

    DB::delete($table ,"`file_id`= %s",$file_id);
}

//เพิ่มข้อมูลไฟล์ สรุปผลการสอน ไปที่ teaching_file table (DB)
function addTeachingFile(){
    $date = new DateTime("now",  new DateTimeZone('ASIA/Bangkok'));

    $file_id = $_POST["file_id"];
    $data = $_POST["data"];
    $user = $_POST["user"];
    $tqfType = null;

    if($data["subject_type"] == 0){
        $tqfType = 5;
    }elseif($data["subject_type"] == 1){
        $tqfType = 6;
    }

    DB::insertIgnore('teaching_file', array(
        'course_id' => $data["course_id"],
        'subject_id' => $data["subject_id"],
        'term' => $data["term"],
        'year' => $data["year"],
        'group' => $data["group"],
        'file_id' => $file_id,
        'sender_id' => $user,
        'tqf_type' => $tqfType,
        'date_sent' => $date->format("Y-m-d")
    ));
}

function getTqfDeadline(){
    $year = $_POST["year"];
    $user = $_POST["user"];
    $majorData = [];

    $sql = "SELECT DISTINCT `major_id` FROM `open_subjects_group`
            INNER JOIN `course` ON `open_subjects_group`.`course_id` = `course`.`course_id`
            WHERE `year` = %i
            AND `teacher_id` = %s";
    $MajorResults = DB::query($sql,$year,$user);
    $majorCounter = DB::count();

    if($majorCounter == 0){
        array_push($majorData,null);
    }else{
        foreach ($MajorResults as $rows){
            array_push($majorData,$rows["major_id"]);
        }
    }


    $tqfDeadlineSql = "SELECT `year`,`term`,`tqf3-4_major_deadline`,`tqf5-6_major_deadline`,`major_id` FROM `tqf_major_deadline`
                        WHERE `year` = %i
                        AND `major_id` IN %ls
                        ORDER BY `major_id` ASC ,`term` DESC";
    $results = DB::query($tqfDeadlineSql, $year, $majorData);
    $json = json_encode($results);
    echo $json;
}

