<?php
/**
 * Created by PhpStorm.
 * User: Indyn
 * Date: 14/9/2559
 * Time: 10:18
 */

include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if(isset($_POST["func"] )){
    if($_POST["func"] == "getYearTerm"){
        getYearTerm();
    }elseif($_POST["func"] == "getTqfFileTable"){
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
    }
}

//get ค่า year term
function getYearTerm(){
    $user_id = $_POST["user_id"];
    $sql = "SELECT DISTINCT `year`,`term` FROM `open_subjects_group`
            WHERE `teacher_id` = %s
            ORDER BY `year` DESC";

    $results = DB::query($sql,$user_id);
    $json = json_encode($results);
    echo $json;
}

//ข้อมูลของ Table ไฟล์ มคอ.
function getTqfFileTable(){
    $year = $_POST["year"];
    $term = $_POST["term"];
    $user_id = $_POST["user_id"];
    $course_id = $_POST["course_id"];
    $data = [];
    $preTqf_type = null;
    $preFile_id = null;
    $check = false;

    $sql = "SELECT DISTINCT `open_subjects_group`.`course_id`,`open_subjects_group`.`year`,`open_subjects_group`.`term`,
		    `open_subjects_group`.`subject_id`,`subject`.`subject_name`,`subject`.`subject_type`,
	        `tqf_file`.`file_id`,`tqf_file`.`tqf_type`,`responsible_subject`,`name`,`surname`
	
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
                        
            WHERE `open_subjects_group`.`year` = %i
            AND `open_subjects_group`.`term` = %i
            AND `open_subjects_group`.`course_id` = %s
            AND `teacher_id` = %s
            ORDER BY `open_subjects_group`.`subject_id` DESC";
    $results = DB::query($sql,$year,$term,$course_id,$user_id);

    for($i = 0;$i < count($results);$i++){

        $course_id = $results[$i]["course_id"];
        $year = $results[$i]["year"];
        $term = $results[$i]["term"];
        $subject_id = $results[$i]["subject_id"];
        $subject_name = $results[$i]["subject_name"];
        $file_id = $results[$i]["file_id"];
        $tqf_type = $results[$i]["tqf_type"];
        $name = $results[$i]["name"];
        $surname = $results[$i]["surname"];

        if($i < count($results)-1){
            $nextCourse_id = $results[$i+1]["course_id"];
            $nextYear= $results[$i+1]["year"];
            $nextTerm = $results[$i+1]["term"];
            $nextSubject_id = $results[$i+1]["subject_id"];

            if($course_id == $nextCourse_id && $year == $nextYear && $term == $nextTerm && $nextSubject_id == $subject_id){
                $preFile_id = $results[$i]["file_id"];
                $preTqf_type = $results[$i]["tqf_type"];
                $check = true;
                continue;
            }

        }

        if($tqf_type == 3 || $tqf_type == 4){
            $data[] = [
                'course_id' => $course_id,
                'year' => $year,
                'term' => $term,
                'subject_id' => $subject_id,
                'subject_name' => $subject_name,
                'pre_file_id' => $file_id,
                'pre_tqf_type' => $tqf_type,
                'post_file_id' => $preFile_id,
                'post_tqf_type' => $preTqf_type,
                'name' => $name,
                'surname' => $surname,
            ];
        }elseif($tqf_type == 5 || $tqf_type == 6){
            $data[] = [
                'course_id' => $course_id,
                'year' => $year,
                'term' => $term,
                'subject_id' => $subject_id,
                'subject_name' => $subject_name,
                'pre_file_id' => $preFile_id,
                'pre_tqf_type' => $preTqf_type,
                'post_file_id' => $file_id,
                'post_tqf_type' => $tqf_type,
                'name' => $name,
                'surname' => $surname,
            ];

        }elseif($tqf_type == null){
            $data[] = [
                'course_id' => $course_id,
                'year' => $year,
                'term' => $term,
                'subject_id' => $subject_id,
                'subject_name' => $subject_name,
                'pre_file_id' => $file_id,
                'pre_tqf_type' => $tqf_type,
                'post_file_id' => $file_id,
                'post_tqf_type' => $tqf_type,
                'name' => $name,
                'surname' => $surname,
            ];
        }

        if($check == true){
            $preFile_id = null;
            $preTqf_type = null;
            $check = false;
        }

    }

    $json = json_encode($data);
    echo $json;

}

//ข้อมูลของ Table ไฟล์สรุปการสอน
function getTeachingFileTable(){
    $year = $_POST["year"];
    $term = $_POST["term"];
    $course_id = $_POST["course_id"];
    $user_id = $_POST["user_id"];
    $subjectData = [];
    $data = [];

    $subjectSql = "SELECT `subject_id` FROM `open_subjects_group`
                    WHERE `teacher_id` = %s
                    AND `course_id` = %s
                    AND `year` = %i
                    AND `term` = %i";
    $subjectResults = DB::query($subjectSql,$user_id,$course_id,$year,$term);

    foreach ($subjectResults as $rows){
        array_push($subjectData,$rows["subject_id"]);
    }


    $sql= "SELECT `open_subjects_group`.`course_id`,`open_subjects_group`.`year`,`open_subjects_group`.`term`,
		`open_subjects_group`.`subject_id`,`subject`.`subject_name`,`subject`.`subject_type`,
	        `open_subjects_group`.`group`,`teaching_file`.`file_id`,`teaching_file`.`tqf_type`,`teacher_id`,`name`,`surname`
	
          FROM `open_subjects_group`
                      
          INNER JOIN `subject` ON `subject`.`subject_id` = `open_subjects_group`.`subject_id`
          
          INNER JOIN `open_subjects`ON `open_subjects`.`course_id` = `open_subjects_group`.`course_id`
          AND `open_subjects`.`subject_id` = `open_subjects_group`.`subject_id`
          AND `open_subjects`.`term` = `open_subjects_group`.`term`
          AND `open_subjects`.`year` = `open_subjects_group`.`year`
          
          LEFT JOIN `user` ON  `open_subjects_group`.`teacher_id` = `user`.`user_id`
          
          LEFT JOIN `teaching_file`ON `teaching_file`.`course_id` = `open_subjects_group`.`course_id`
          AND `teaching_file`.`subject_id` = `open_subjects_group`.`subject_id`
          AND `teaching_file`.`term` = `open_subjects_group`.`term`
          AND `teaching_file`.`year` = `open_subjects_group`.`year`
          AND `teaching_file`.`group` = `open_subjects_group`.`group`
          
          WHERE `open_subjects_group`.`year` = %i
          AND `open_subjects_group`.`term` = %i
          AND `open_subjects_group`.`course_id` = %s
          AND `open_subjects_group`.`subject_id` IN %ls
      
          ORDER BY `subject_id` DESC,`group` ASC";
    $results = DB::query($sql,$year,$term,$course_id,$subjectData);

    foreach ($results as $rows){
        DB::query("SELECT DISTINCT `teacher_id` FROM `open_subjects_group`
                    WHERE `year` = %i
                    AND `term` = %i
                    AND `course_id` = %s
                    AND `subject_id` = %s", $rows["year"],$rows["term"],$rows["course_id"],$rows["subject_id"]);
        $counter = DB::count();
        if($counter <= 1){
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
                'isOneGroup' => true
            ];
        }elseif($counter > 1){
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
                'isOneGroup' => false
            ];
        }
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
        'date_sent' => date("Y-m-d")
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
        'date_sent' => date("Y-m-d")
    ));
}

