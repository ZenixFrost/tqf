<?php
include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if(isset($_POST["func"] )){
    if($_POST["func"] == "getSelectCourse"){
        getSelectCourse();
    }elseif($_POST["func"] == "getSubjectData"){
        getSubjectData();
    }elseif($_POST["func"] == "updateCourseSubject"){
        updateCourseSubject();
    }elseif($_POST["func"] == "getCourseSelectTable"){
        getCourseSelectTable();
    }elseif($_POST["func"] == "removeCourseSubject"){
        removeCourseSubject();
    }elseif($_POST["func"] == "getSubject"){
        getSubject();
    }elseif($_POST["func"] == "addSubject"){
        addSubject();
    }
}

//subject Autocomplete
function getSubject(){
    $array = [];

    $subject = "SELECT `subject`.`subject_id`,`subject`.`subject_name` FROM `subject`";
    $result = DB::query($subject);
    foreach ($result as $rows) {
//        array_push($array,$rows["subject_name"]);
        $array[] = [
            'value' => $rows["subject_name"],
            'data' => $rows["subject_id"]
        ];
    }

    $json = json_encode($array);
    echo $json;

}

//ข้อมูลตอนที่ Select Major ตอนที่จะ add subject
function getSelectCourse(){

    $userId = $_POST['userId'];
    $sql = "SELECT `user`.`user_id`,`user`.`major_id`,`user`.`major_admin` FROM `user`
            WHERE `user`.`user_id` = %s";

    $user = DB::queryFirstRow($sql, $userId);

    if($user["major_admin"] == 1){
        $course = "SELECT `course`.`course_id` FROM `course`
                  WHERE `course`.`major_id` = %s";
        $result = DB::query($course, $user["major_id"]);
    }else{
        $course = "SELECT `course_admin`.`course_id` FROM `course_admin`
                  WHERE `course_admin`.`course_admin_id` = %s";
        $result = DB::query($course, $user["user_id"]);
    }


    $json = json_encode($result);
    echo $json;
}

//ข้อมูล json ของ course_subject Table
function getSubjectData(){
    $courseId = $_POST['courseId'];
    $data = [];
    $index = 0;

    $sql = "SELECT `course_subject`.`course_id`,`subject`.`subject_id`,`subject`.`subject_name`,`subject`.`subject_type` 
            FROM `course_subject`

            INNER JOIN `subject` ON `subject`.`subject_id` = `course_subject`.`subject_id`

            WHERE `course_subject`.`course_id` = %s
            ORDER BY `course_subject`.`course_id`";

    $results = DB::query($sql, $courseId );

    foreach($results as $rows){
        $data[] = [
            'index' => $index,
            'course_id' => $rows["course_id"],
            'subject_id' => $rows["subject_id"],
            'subject_name' => $rows["subject_name"],
            'subject_type' => $rows["subject_type"],
        ];
        $index++;
    }

    $json = json_encode($data);
    echo $json;

}

//อัพเดท  course_subject Table
function updateCourseSubject(){
    $name = $_POST['name'];
    $value = $_POST['value'];
    $course = $_POST['courseId'];
    $subject = $_POST['subjectId'];
    echo "in";

    if (!empty($value) || $value == 0) {
        if($name == "course_id"){
            DB::update('course_subject', array(
                $name => $value,
            ), "course_id=%s and subject_id=%s", $course,$subject);
        }else{
            DB::update('subject', array(
                $name => $value,
            ), "subject_id=%s", $subject);
        }
    } else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!Error Mysql can't update.";
    }

}

//หลักสูตร Select ตอน edit table
function getCourseSelectTable(){

    $data = [];
    $userId = $_POST['userId'];
    $sql = "SELECT `user`.`user_id`,`user`.`major_id`,`user`.`major_admin` FROM `user`
            WHERE `user`.`user_id` = %s";

    $user = DB::queryFirstRow($sql, $userId);

    if($user["major_admin"] == 1){
        $course = "SELECT `course`.`course_id` FROM `course`
                  WHERE `course`.`major_id` = %s";
        $result = DB::query($course, $user["major_id"]);
    }else{
        $course = "SELECT `course_admin`.`course_id` FROM `course_admin`
                  WHERE `course_admin`.`course_admin_id` = %s";
        $result = DB::query($course, $user["user_id"]);
    }

    foreach ($result as $row) {
        $data[] =[
            'id' => $row["course_id"],
            'text' => $row["course_id"]
        ];
    }

    $json = json_encode($data);
    echo $json;
}

//remove course subject data Table
function removeCourseSubject(){
    $data = $_POST["data"];

    foreach($data as $row){
        //เช็คไฟล์สรุปการสอน ถ้ามีจะทำการลบไฟล์สรุปการสอน และข้อมูลใน teaching_file table
        $teachingFileResult =  DB::query("SELECT `file_id` FROM `teaching_file` WHERE `course_id` = %s AND `subject_id` = %s", $row["course_id"],$row["subject_id"]);
        $teachingFileCounter = DB::count();
        if($teachingFileCounter >= 1){
            foreach($teachingFileResult as $fileRows){
                $path = '.\server\php\files\\'.$fileRows["file_id"];
                unlink($path);
            }
            DB::delete('teaching_file', "`course_id` = %s AND `subject_id` = %s", $row["course_id"],$row["subject_id"]);
        }

        //เช็คไฟล์ มคอ. 3-6 ถ้ามีจะทำการลบไฟล์ มคอ. และข้อมูลใน tqf_file table
        $tqfFileResult =  DB::query("SELECT `file_id` FROM `tqf_file` WHERE `course_id` = %s AND `subject_id` = %s", $row["course_id"],$row["subject_id"]);
        $tqfFileCounter = DB::count();
        if($tqfFileCounter >= 1){
            foreach($tqfFileResult as $fileRows){
                $path = '.\server\php\files\\'.$fileRows["file_id"];
                unlink($path);
            }
            DB::delete('tqf_file', "`course_id` = %s AND `subject_id` = %s", $row["course_id"],$row["subject_id"]);
        }

        //ลบข้อมูลใน open_subjects_group table
        DB::delete('open_subjects_group', "`course_id` = %s AND `subject_id` = %s", $row["course_id"],$row["subject_id"]);

        //ลบข้อมูลใน open_subjects table
        DB::delete('open_subjects', "`course_id` = %s AND `subject_id` = %s", $row["course_id"],$row["subject_id"]);

        //ลบข้อมูลใน course_subject table
        DB::delete('course_subject', "course_id=%s and subject_id=%s", $row["course_id"],$row["subject_id"]);

        //มาเช็คมา มีคนใช้วิชานี้ใน course_subject table ถ้าไม่มีคนใช้ก็ให้ไปลบใน subject table
        DB::query("SELECT `subject_id` FROM `course_subject` WHERE `subject_id` = %s", $row["subject_id"]);
        $subjectCounter = DB::count();
        if($subjectCounter == 0){
            DB::delete('subject', "`subject_id` = %s", $row["subject_id"]);
        }

    }
}

//add course_subject
function addSubject(){
    DB::$error_handler = false;
    DB::$throw_exception_on_error = true;

    $subjectId = $_POST["subjectId"];
    $subjectName = $_POST["subjectName"];
    $subjectType = $_POST["subjectType"];
    $course = $_POST["course"];

    $count = count($subjectId);
    for($i=0;$i<$count;$i++){

        if($subjectId[$i] != NULL && $subjectName[$i] != NULL){
            DB::insertIgnore('subject', array(
                'subject_id' => $subjectId[$i],
                'subject_name' => $subjectName[$i],
                'subject_type' => $subjectType[$i],
            ));

            DB::insertIgnore('course_subject', array(
                'course_id' => $course,
                'subject_id' => $subjectId[$i],
            ));

        }
    }
}
