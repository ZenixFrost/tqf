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
    }elseif($_POST["func"] == "getYearCourse"){
        getYearCourse();
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

//user_id ทั้งหมดที่จะเอามาโชว์ต้อง Select teacher
function getTeacher(){

    $sql = "SELECT `user`.`user_id`,`user`.`name`,`user`.`surname`,`user`.`major_id` FROM `user`";

    $results = DB::query($sql);

    $json = json_encode($results);
    echo $json;
}

//add open subjects
function addOpenSubjects(){

    $course = $_POST["course"];
    $year = $_POST["year"];
    $term = $_POST["term"];


    $subjectNum = count($_POST["subjects"]);

    for($i=0;$i<$subjectNum;$i++){
        $check = true;
        $curSubjects = $_POST["subjects"][$i];
        if($curSubjects != NULL){

            for($j = 0;$j < count($_POST["group"][$i]);$j++){
                $curGroup = $_POST["group"][$i][$j];
                $curTeacher = $_POST["teacher"][$i][$j];

                if($curGroup != 0 && $curTeacher != NULL){
                    if($check == true) {
                        DB::insertIgnore('open_subjects', array(
                            'course_id' => $course,
                            'year' => $year,
                            'term' => $term,
                            'subject_id' => $curSubjects
                        ));
                        $check = false;
                    }
                    DB::insertUpdate('open_subjects_group', array(
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

//get OpenSubjects เอามาใช้เป็นข้อมูล table
function getOpenSubjects(){
    $course = $_POST["course"];
    $year = $_POST["year"];
    $term = $_POST["term"];
    $subjectId = $_POST["subject_id"];

    $sql = "SELECT `open_subjects_group`.`year`,`open_subjects_group`.`term`,`open_subjects_group`.`subject_id`,`subject`.`subject_name`,
	               `open_subjects_group`.`group`,`user`.`name`,`user`.`surname`
	
            FROM `open_subjects_group`
            
            INNER JOIN `subject` ON `subject`.`subject_id` = `open_subjects_group`.`subject_id`
            INNER JOIN `user` ON `user`.`user_id` = `open_subjects_group`.`teacher_id`
            WHERE `course_id` = %s 
            AND `year` = %i
            AND `term` = %i
            AND `open_subjects_group`.`subject_id` = %s
            ORDER BY `year` DESC";
    $results = DB::query($sql,$course,$year,$term,$subjectId);
    $json = json_encode($results);
    echo $json;

}

//get ค่า year term
function getYearCourse(){
    $course = $_POST["course"];
    $year = $_POST["year"];

    $sql = "SELECT DISTINCT `year`,`term` FROM `open_subjects_group`
            WHERE `course_id` = %s
            AND `year` = %i
            ORDER BY `term` DESC";

    $results = DB::query($sql,$course,$year);
    $json = json_encode($results);
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
    $subject_id = $_POST['pk'];
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


        DB::delete('open_subjects_group', "`open_subjects_group`.`course_id`=%s AND 
                    `open_subjects_group`.`year`=%i AND `open_subjects_group`.`term`=%i AND 
                    `open_subjects_group`.`subject_id`=%s AND `open_subjects_group`.`group`=%i",
            $courseId,$year,$term ,$subject_id,$group);

        DB::query($groupSql,$courseId,$year,$term,$subject_id);
        $counter = DB::count();

        if($counter == 0){
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
