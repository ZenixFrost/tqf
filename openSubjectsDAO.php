<?php

include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if(isset($_POST["func"] )){
    if($_POST["func"] == "getSubjects"){
        getSubjects();
    }elseif($_POST["func"] == "getTeacher"){
        getTeacher();
    }elseif($_POST["func"] == "addOpenSubjects"){
        addOpenSubjects();
    }elseif($_POST["func"] == "getOpenSubjects"){
        getOpenSubjects();
    }elseif($_POST["func"] == "removeOpenSubjects"){
        removeOpenSubjects();
    }elseif($_POST["func"] == "getResponsible"){
        getResponsible();
    }elseif($_POST["func"] == "getSubjectsTerm"){
        getSubjectsTerm();
    }elseif($_POST["func"] == "updateResponsible"){
        updateResponsible();
    }elseif($_POST["func"] == "updateTeacher"){
        updateTeacher();
    }elseif($_POST["func"] == "getOpenSubjectsTerm"){
        getOpenSubjectsTerm();
    }
}


//subject ที่จะเอามาต้อง Select subject
function getSubjects(){

    $CourseSelected = $_POST["CourseSelected"];

    $sql = "SELECT `subject`.`subject_id`,`subject`.`subject_name`,`subject`.`subject_type` FROM `course_subject`

    INNER JOIN `subject` ON `subject`.`subject_id` = `course_subject`.`subject_id`
    WHERE `course_subject`.`course_id` = %s";

    $result = DB::query($sql,$CourseSelected);

    $json = json_encode($result);
    echo $json;

}

//user_id ทั้งหมดที่จะเอามาโชว์ต้อง Select teacher (dropDown เลือกผู้สอน)
function getTeacher(){

    $sql = "SELECT `user`.`user_id`,`user`.`name`,`user`.`surname`,`user`.`major_id` FROM `user` 
            ORDER BY `name` ASC";

    $results = DB::query($sql);

    $json = json_encode($results);
    echo $json;
}

//add open subjects บันทึกข้อมูลลง subjects table
function addOpenSubjects(){

    $course = $_POST["course"];
    $year = $_POST["year"];
    $term = $_POST["term"];

    $groupIndex = $_POST["groupIndex"];

    $subjectNum = count($_POST["subjects"]);

    $sql = "SELECT `responsible_subject` FROM `open_subjects`
            WHERE  `course_id` = %s
            AND `subject_id` = %s
            AND `year` < %i
            AND `responsible_subject` IS NOT NULL
            
            ORDER BY `year` DESC,`term` DESC";


//    for($i=0;$i<count($groupIndex[0]);$i++){
//        echo $groupIndex[0][$i]." = ".count($_POST["teacher"][0][$groupIndex[0][$i]])."<br>";
//        if(count($_POST["teacher"][0][$groupIndex[0][$i]]) == 0){
//            echo "eiei";
//        }
//    }

    for($i=0;$i<$subjectNum;$i++){
        $check = true;
        $curSubjects = $_POST["subjects"][$i];
        if($curSubjects != NULL){

            for($j = 0;$j < count($_POST["group"][$i]);$j++){
                $curGroup = $_POST["group"][$i][$j];
                $teacherCounter = count($_POST["teacher"][$i][$groupIndex[$i][$j]]);

                if($curGroup != 0 && $teacherCounter != 0){
                    if($check == true) {
                        $resultsSubject = DB::queryFirstRow($sql, $course,$curSubjects,$year);

                        DB::insertIgnore('open_subjects', array(
                            'course_id' => $course,
                            'year' => $year,
                            'term' => $term,
                            'subject_id' => $curSubjects,
                            'responsible_subject' => $resultsSubject["responsible_subject"]
                        ));
                        $check = false;
                    }

                    for($k = 0;$k < $teacherCounter;$k++){
                        $curTeacher = $_POST["teacher"][$i][$groupIndex[$i][$j]][$k];
                        DB::insertIgnore('open_subjects_group', array(
                            'course_id' => $course,
                            'year' => $year,
                            'term' => $term,
                            'subject_id' => $curSubjects,
                            'group' => $curGroup,
                            'teacher_id' => $curTeacher
                        ));
                    }
                }
            }
        }
    }

//    for($i=0;$i<$subjectNum;$i++){
//        $check = true;
//        $curSubjects = $_POST["subjects"][$i];
//        if($curSubjects != NULL){
//
//            for($j = 0;$j < count($_POST["group"][$i]);$j++){
//                $curGroup = $_POST["group"][$i][$j];
//                $curTeacher = $_POST["teacher"][$i][$j];
//
//
//                if($curGroup != 0 && $curTeacher != NULL){
//                    if($check == true) {
//
//                        $resultsSubject = DB::queryFirstRow($sql, $course,$curSubjects,$year);
//
//                        DB::insertIgnore('open_subjects', array(
//                            'course_id' => $course,
//                            'year' => $year,
//                            'term' => $term,
//                            'subject_id' => $curSubjects,
//                            'responsible_subject' => $resultsSubject["responsible_subject"]
//                        ));
//                        $check = false;
//                    }
//
//                    DB::insertUpdate('open_subjects_group', array(
//                        'course_id' => $course,
//                        'year' => $year,
//                        'term' => $term,
//                        'subject_id' => $curSubjects,
//                        'group' => $curGroup,
//                        'teacher_id' => $curTeacher
//                    ));
//                }
//            }
//        }
//
//    }

}

//get OpenSubjects เอามาใช้เป็นข้อมูล table
function getOpenSubjects(){
    $course = $_POST["course"];
    $year = $_POST["year"];
    $data = [];
    $index = 0;

    $sql = "SELECT `open_subjects_group`.`year`,`open_subjects_group`.`term`,`open_subjects_group`.`subject_id`,`subject`.`subject_name`,
	`open_subjects`.`responsible_subject`,`open_subjects_group`.`group`,`open_subjects_group`.`teacher_id`,`user`.`name`,`user`.`surname`
	
            FROM `open_subjects_group`
                        
            INNER JOIN `subject` ON `subject`.`subject_id` = `open_subjects_group`.`subject_id`
            INNER JOIN `user` ON `user`.`user_id` = `open_subjects_group`.`teacher_id`
            
            INNER JOIN `open_subjects` ON `open_subjects`.`course_id` = `open_subjects_group`.`course_id`
            AND `open_subjects`.`subject_id` = `open_subjects_group`.`subject_id`
            AND `open_subjects`.`year` = `open_subjects_group`.`year`
            AND `open_subjects`.`term` = `open_subjects_group`.`term`
            
            WHERE `open_subjects_group`.`course_id` = %s
            AND `open_subjects_group`.`year` = %i
            ORDER BY `term` DESC,`subject_id` ASC,`group` ASC";

    $nameSql = "SELECT `name`,`surname` FROM `user`
                WHERE `user_id` = %s";

    $results = DB::query($sql,$course,$year);

    foreach($results as $rows){

        $nameResults = DB::queryFirstRow($nameSql,$rows["responsible_subject"]);

        $data[] = [
            'index' => $index,
            'year' => $rows["year"],
            'term' => $rows["term"],
            'subject_id' => $rows["subject_id"],
            'subject_name' => $rows["subject_name"],
            'responsible_subject' => $rows["responsible_subject"],
            'teacher_id' => $rows["teacher_id"],
            'responsible_name' => $nameResults["name"],
            'responsible_surname' => $nameResults["surname"],
            'group' => $rows["group"],
            'name' => $rows["name"],
            'surname' => $rows["surname"],
        ];
        $index++;
    }
    $json = json_encode($data);
    echo $json;

}


//get OpenSubjects เอามาใช้เป็นข้อมูล table + term
function getOpenSubjectsTerm(){
    $course = $_POST["course"];
    $year = $_POST["year"];
    $term = $_POST["term"];
    $data = [];
    $index = 0;

    $sql = "SELECT `open_subjects_group`.`course_id`,`open_subjects_group`.`year`,`open_subjects_group`.`term`,`open_subjects_group`.`subject_id`,`subject`.`subject_name`,
	`open_subjects`.`responsible_subject`,`open_subjects_group`.`group`,`open_subjects_group`.`teacher_id`,`user`.`name`,`user`.`surname`
	
            FROM `open_subjects_group`
                        
            INNER JOIN `subject` ON `subject`.`subject_id` = `open_subjects_group`.`subject_id`
            INNER JOIN `user` ON `user`.`user_id` = `open_subjects_group`.`teacher_id`
            
            INNER JOIN `open_subjects` ON `open_subjects`.`course_id` = `open_subjects_group`.`course_id`
            AND `open_subjects`.`subject_id` = `open_subjects_group`.`subject_id`
            AND `open_subjects`.`year` = `open_subjects_group`.`year`
            AND `open_subjects`.`term` = `open_subjects_group`.`term`
            
            WHERE `open_subjects_group`.`course_id` = %s
            AND `open_subjects_group`.`year` = %i
            AND `open_subjects_group`.`term` = %i
            ORDER BY `term` DESC,`subject_id` ASC,`group` ASC";

    $nameSql = "SELECT `name`,`surname` FROM `user`
                WHERE `user_id` = %s";

    $results = DB::query($sql,$course,$year,$term);

    foreach($results as $rows){

        $nameResults = DB::queryFirstRow($nameSql,$rows["responsible_subject"]);

        $data[] = [
            'index' => $index,
            'course_id' => $rows["course_id"],
            'year' => $rows["year"],
            'term' => $rows["term"],
            'subject_id' => $rows["subject_id"],
            'subject_name' => $rows["subject_name"],
            'responsible_subject' => $rows["responsible_subject"],
            'teacher_id' => $rows["teacher_id"],
            'responsible_name' => $nameResults["name"],
            'responsible_surname' => $nameResults["surname"],
            'group' => $rows["group"],
            'name' => $rows["name"],
            'surname' => $rows["surname"],
        ];
        $index++;
    }
    $json = json_encode($data);
    echo $json;
}

//get ค่า Responsible (ผู้รับผิดชอบรายวิชา) จาก open_subjects table (x-editable select2)
function getResponsible(){

    $course = $_POST["course"];
    $year = $_POST["year"];
    $term = $_POST["term"];
    $subjectId = $_POST["subject_id"];
    $data = [];

    $sql = "SELECT DISTINCT `teacher_id`,`user`.`name`,`user`.`surname`
	
            FROM `open_subjects_group`
            
            INNER JOIN `subject` ON `subject`.`subject_id` = `open_subjects_group`.`subject_id`
            INNER JOIN `user` ON `user`.`user_id` = `open_subjects_group`.`teacher_id`
            WHERE `course_id` = %s 
            AND `year` = %i
            AND `term` = %i
            AND `open_subjects_group`.`subject_id` = %s
            ORDER BY `teacher_id` DESC";
    $results = DB::query($sql,$course,$year,$term,$subjectId);
    foreach($results as $row){
        $data[] = [
            'id' => $row["teacher_id"],
            'text' => $row["name"]." ".$row["surname"]
        ];
    }

    $json = json_encode($data);
    echo $json;
}

//update Responsible อัพเดทผู้รับผิดชอบรายวิชา
function updateResponsible(){
    $subject_id = $_POST['subject_id'];
    $name = $_POST['name'];
    $value = $_POST['value'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $term = $_POST['term'];


    if(!empty($value)) {
        DB::update('open_subjects', array(
            $name   => $value,
        ), "course_id=%s AND year=%i AND term=%i AND subject_id=%s ", $course,$year,$term,$subject_id);
    } else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!Error Mysql can't update.";
    }

}

//update Responsible อัพเดทผู้สอน
function updateTeacher(){
    $subject_id = $_POST['subject_id'];
    $name = $_POST['name'];
    $value = $_POST['value'];
    $course = $_POST['course'];
    $year = $_POST['year'];
    $term = $_POST['term'];
    $group = $_POST['group'];
    $teacher_id = $_POST['teacher_id'];


    if(!empty($value)) {
        DB::update('open_subjects_group', array(
            $name   => $value,
        ), "`course_id`=%s AND `year`=%i AND `term`=%i AND `subject_id`=%s AND `group`=%i AND `teacher_id`=%s", $course,$year,$term,$subject_id,$group,$teacher_id);
    } else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!Error Mysql can't update.";
    }

}

//ลบ data ใน open_subjects_group table
function removeOpenSubjects(){
    $courseId = $_POST["courseId"];
    $data = $_POST["data"];

    $groupSql = "SELECT DISTINCT`course_id`,`year`,`term`,`subject_id` FROM `open_subjects_group` 
                WHERE `course_id` = %s 
                AND `year` = %i
                AND `term` = %i
                AND `subject_id` = %s";

    foreach($data as $row){
        $year = $row["year"];
        $term = $row["term"];
        $subject_id = $row["subject_id"];
        $group = $row["group"];
        $teacher_id = $row["teacher_id"];

        //เช็คไฟล์สรุปการสอน ถ้ามีจะทำการลบไฟล์สรุปการสอน และข้อมูลใน teaching_file table
        $teachingFileResult =  DB::queryFirstRow("SELECT `file_id` FROM `teaching_file` 
                                          WHERE `course_id`= %s 
                                          AND `year`= %i
                                          AND `term`= %i
                                          AND `subject_id`= %s
                                          AND `group`= %i",$courseId,$year,$term ,$subject_id,$group);
        $teachingFileCounter = DB::count();
        if($teachingFileCounter == 1){
            $path = '.\server\php\files\\'.$teachingFileResult["file_id"];
            unlink($path);

            DB::delete('teaching_file', "`course_id`= %s
                                          AND `year`= %i
                                          AND `term`= %i
                                          AND `subject_id`= %s
                                          AND `group`= %i", $courseId,$year,$term ,$subject_id,$group);
        }


        DB::delete('open_subjects_group', "`open_subjects_group`.`course_id`=%s AND 
                    `open_subjects_group`.`year`=%i AND `open_subjects_group`.`term`=%i AND 
                    `open_subjects_group`.`subject_id`=%s AND `open_subjects_group`.`group`=%i AND `teacher_id`=%s",
            $courseId,$year,$term ,$subject_id,$group,$teacher_id);

        DB::query($groupSql,$courseId,$year,$term,$subject_id);
        $counter = DB::count();
        if($counter == 0){
            //เช็คไฟล์ มคอ. 3-6 ถ้ามีจะทำการลบไฟล์ มคอ. และข้อมูลใน tqf_file table
            $tqfFileResult =  DB::query("SELECT `file_id` FROM `tqf_file` 
                                WHERE `course_id`=%s 
                                AND `year`=%i 
                                AND `term`=%i 
                                AND `subject_id`=%s", $courseId,$year,$term ,$subject_id
            );

            $tqfFileCounter = DB::count();
            if($tqfFileCounter >= 1){
                foreach($tqfFileResult as $fileRows){
                    $path = '.\server\php\files\\'.$fileRows["file_id"];
                    unlink($path);
                }
                DB::delete('tqf_file', "`course_id`=%s 
                                AND `year`=%i 
                                AND `term`=%i 
                                AND `subject_id`=%s", $courseId,$year,$term ,$subject_id);
            }


            DB::delete('open_subjects', "`course_id`=%s AND `year`=%i AND `term`=%i AND `subject_id`=%s",
                $courseId,$year,$term ,$subject_id);
        }

    }

}

//get Subjects จากข้อมูล course_id, year, term ที่ input เข้ามา (ใช้วนลูปโชว์ใน table)
function getSubjectsTerm(){
    $course = $_POST["course"];
    $year = $_POST["year"];
    $term = $_POST["term"];

    $sql = "SELECT `subject`.`subject_id`,`subject_name`,`responsible_subject`,`name`,`surname` FROM `open_subjects`
            INNER JOIN `subject` ON `subject`.`subject_id` = `open_subjects`.`subject_id`
            LEFT JOIN `user` ON `user`.`user_id` = `open_subjects`.`responsible_subject`
            WHERE `course_id` = %s 
            AND `year` = %i
            AND `term` = %i";

    $results = DB::query($sql,$course,$year,$term);
    $json = json_encode($results);
    echo $json;
}
