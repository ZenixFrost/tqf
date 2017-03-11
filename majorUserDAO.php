<?php

include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if(isset($_POST["func"] )){
    if($_POST["func"] == "getMajorUser"){
        getMajorUser();
    }elseif($_POST["func"] == "addUser"){
        addUser();
    }elseif($_POST["func"] == "removeUser"){
        removeUser();
    }elseif($_POST["func"] == "updateUser"){
        updateUser();
    }elseif($_POST["func"] == "getCourse"){
        getCourse();
    }elseif($_POST["func"] == "getCourseUser"){
        getCourseUser();
    }elseif ($_POST["func"] == "updateCourseUser") {
        updateCourseUser();
    }elseif ($_POST["func"] == "getAllCourse") {
        getAllCourse();
    }elseif ($_POST["func"] == "updateCourseAdmin"){
        updateCourseAdmin();
    }
}

////////////////////////////////////////USER TABLE//////////////////////////////////////////////////////////////////////

//ข้อมูล user ของ major และ course จะเอา course table มาสร้าง key กับ value
function getMajorUser(){
    $curMajor = $_POST["val"];

    //เช็คประธานหลักสูตรว่าหลักสูตรที่ตัวเองเป็น admin ตรงกับ สาขาที่ตัวเองอยู่ไหม
//    $checkPre = "SELECT DISTINCT `course_admin_id`,`user`.`major_id` FROM `course_admin`
//
//                  INNER JOIN `user` ON `user`.`user_id` = `course_admin`.`course_admin_id`
//                  INNER JOIN `course` ON `course`.`course_id` = `course_admin`.`course_id`
//                  WHERE `course`.`major_id` = %s";
//    $preUserResults = DB::query($checkPre,$_POST["val"]);
//
//    foreach ($preUserResults as  $perUser){
//        if ($perUser["major_id"] != $curMajor) {
//            DB::delete('course_admin', "course_admin_id=%s",$perUser["course_admin_id"]);
//        }
//    }

    $user = "SELECT `user_id`,`name`,`surname`,`major_id` FROM USER

              WHERE `user`.`major_id` = %s
              ORDER BY `name` ASC";

    $courseId = "SELECT `course_id` FROM `course`
    WHERE `major_id` = %s";

    $courseResults = DB::query($courseId,$curMajor);
    $results = DB::query($user,$curMajor);

    $majorUser = [];

    for($i = 0;$i < count($results);$i++) {
        $curUserId = $results[$i]["user_id"];
        $curUserName = $results[$i]["name"];
        $curUserSurname = $results[$i]["surname"];
        $curUserMajor = $results[$i]["major_id"];


        $majorUser[$i] = [
            'user_id' =>  $curUserId,
            'name' => $curUserName,
            'surname' => $curUserSurname,
            'major_id' => $curUserMajor
        ];
        ///เอา couese id และ admin มาสร้างเป็น key และ value วนลูป select ถ้ามี ค่าเป็น 1 ไม่มีค่าเป็น 0
        for ($j = 0; $j < count($courseResults); $j++) {
            $curCourseId = $courseResults[$j]["course_id"];
            //Select หาค่าโดยที่ตามที่ course_admin_id และ course_id ตามที่กำหนดถ้าเจอให้เป็น 1 ไม่เจอเป็น 0
            DB::query("SELECT `course_admin_id`,`course_id` FROM `course_admin`

                    WHERE `course_admin_id` = %s
                    AND `course_id` =%s",  $curUserId , $curCourseId);
            $counter = DB::count();
            if($counter == 1) {
                $majorUser[$i][$curCourseId] = 1;
            }else{
                $majorUser[$i][$curCourseId] = 0;
            }
        }
    }
    $json = json_encode($majorUser);

    echo $json;
}

//เพิ่ม user
function addUser(){
    DB::$error_handler = false;
    DB::$throw_exception_on_error = true;

    try {
        DB::insert('user', array(
            'user_id' => $_POST["user_id"],
            'name' => $_POST["name"],
            'surname' => $_POST["surname"],
            'major_id' => $_POST["major_id"],
            'major_admin' => 0,
        ));
        echo "success";
    }catch (MeekroDBException $e){
        if(empty($_POST["selected"])){
            echo "You didn't choose major.";
        }else{
            echo "Duplicate email, This email is already used.";
        }
    }
}

//ลบ user
function removeUser(){
    $data = $_POST["data"];

    foreach($data as $row){
        DB::delete('user', "user_id=%s", $row["user_id"]);
    }

}

//update user
function updateUser(){
    $pk = $_POST['pk'];
    $name = $_POST['name'];
    $value = $_POST['value'];

    if(!empty($value)) {
        DB::update('user', array(
            $name   => $value,
        ), "user_id=%s", $pk);
    } else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!Error Mysql can't update.";
    }

}

//course ทั้งหมดรวม หมวด วิชาศึกษาทั่วไป
function getAllCourse(){
    $course = "SELECT `course_id`,`initial`
                FROM `course`

                WHERE `course`.`major_id` = %s";

    $result = DB::query($course, $_POST["val"]);
    $json = json_encode($result);
    echo $json;
}

//update Admin คณะ
function updateCourseAdmin(){

    $pk = $_POST['pk'];
    $name = $_POST['name'];
    $value = $_POST['value'];

    if($value == 1){
        DB::insert('course_admin', array(
            'course_admin_id' => $pk,
            'course_id' => $name
        ));
    }elseif($value == 0){
            DB::delete('course_admin', "course_admin_id=%s AND course_id=%s",$pk,$name);
    }else{
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!Error Mysql have something wrong.";
    }
}



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////ประธานหลักสูตร///////////////////////////////////////////////////////////////////

//หลักสูตร จากค่า major_id ของ admin ที่ส่งมา ไม่รวม วิชาศึกษาทั่วไป
function getCourse(){
    $course = "SELECT `course_id` , `major_id`,`course_type`,`course_president_id` 
                FROM `course`

                WHERE `course`.`major_id` = %s
                AND `course_type` = 0";

    $result = DB::query($course, $_POST["val"]);

    $json = json_encode($result);
    echo $json;
}

//dropdown ของประธานหลักสูตร
function getCourseUser(){
    $courseUser = "SELECT `user`.`user_id`,`name`,`surname`,`major_id`
                    
            FROM `user`
            
            WHERE `major_id` = %s";

    $data = [];
    $results = DB::query($courseUser, $_POST["val"]);
    foreach ($results as $row) {
        $data[] =[
            'id' => $row["user_id"],
            'text' => $row["name"] . " " . $row["surname"]

        ];
    }

    $json = json_encode($data);
    echo $json;

}

//แก้ไขประธานหลักสูตร
function updateCourseUser(){
    $pk = $_POST['pk'];
    $name = $_POST['name'];
    $value = $_POST['value'];
    $majorId = $_POST['major_id'];

    DB::query("SELECT `course_president_id` FROM course WHERE `major_id`=%s AND `course_president_id`=%s", $majorId,$value);
    $counter = DB::count();

    //เช็คว่าถ้ามีชื่อประธานซ้ำก็จะให้ ประธานหลักสูตรของสาขาที่มีชื่อซ้ำของเก่าเป็น null
    if($counter >= 1){
        DB::update('course', array(
            $name   => NULL,
        ), "`major_id`=%s", $majorId);
    }


    //แล้วแทนประธานหลัหสูตรเข้าไปในต่ำแหน่งใหม่
    if(!empty($value)) {
        DB::update('course', array(
            $name   => $value,
        ), "course_id=%s", $pk);
    } else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!Error Mysql can't update.";
    }

}

//////////////////////////////////////////////END///////////////////////////////////////////////////////////////////////