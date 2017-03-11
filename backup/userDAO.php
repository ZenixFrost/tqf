<?php
include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if(isset($_POST["func"] )){
    if($_POST["func"] == "getUser"){
        getUser();
    }elseif($_POST["func"] == "addUser"){
        addUser();
    }elseif($_POST["func"] == "removeUser"){
        removeUser();
    }elseif($_POST["func"] == "getSelect"){
        getSelect();
    }elseif ($_POST["func"] == "updateUser") {
        updateUser();
    }elseif ($_POST["func"] == "getSelectMajor") {
        getSelectMajor();
    }elseif ($_POST["func"] == "getFaculty") {
        getFaculty();
    }elseif ($_POST["func"] ==  "getFacultyUser"){
        getFacultyUser();
    }elseif($_POST["func"] == "getSector"){
        getSector();
    }elseif($_POST["func"] == "getSectorUser") {
        getSectorUser();
    }elseif ($_POST["func"] == "updateSectorUser") {
        updateSectorUser();
    }elseif ($_POST["func"] == 'setNullSectorUser'){
        setNullSectorUser();
    } elseif($_POST["func"] == "getMajor"){
        getMajor();
    }elseif($_POST["func"] == "getMajorUser"){
        getMajorUser();
    }elseif($_POST["func"] == "updateMajorUser"){
        updateMajorUser();
    }elseif($_POST["func"] == "setNullMajorUser"){
        setNullMajorUser();
    }elseif($_POST["func"] == "getPosition"){
        getPosition();
    }elseif($_POST["func"] == "updateFacultyPosition"){
        updateFacultyPosition();
    }elseif($_POST["func"] == "addPositionUser"){
        addPositionUser();
    }elseif($_POST["func"] == "removePositionUser"){
        removePositionUser();
    }
}

//----------------------------------------Faculty Admin-----------------------------------------------------------------

////////////////////////////////////////User Table//////////////////////////////////////////////////////////////////////

//user ของ Table
function getUser(){
    $user = "SELECT `user`.`user_id`,`name`,`surname`,`faculty`.`faculty_id`,`sector`.`sector_id`,
                    `major`.`major_id`,`faculty_admin`,`sector_admin`,`major_admin`
                    
            FROM `user`
            INNER JOIN `major` ON `major`.`major_id` = `user`.`major_id`
            INNER JOIN `sector` ON `sector`.`sector_id` = major.`sector_id`
            INNER JOIN `faculty` ON `faculty`.`faculty_id` = `sector`.`faculty_id`
            
            WHERE `faculty`.`faculty_id` = %s
            ORDER BY `sector`.`sector_id`";

//    $result = DB::query("SELECT * FROM user where major_id = %s", $_POST["val"]);
    $result = DB::query($user, $_POST["val"]);

    $json = json_encode($result);
    echo $json;
}

//เพิ่ม user
function addUser(){
    DB::$error_handler = false;
    DB::$throw_exception_on_error = true;

    if (empty($_POST["sector_admin"])) {
        $_POST["sector_admin"] = 0;
    }
    if (empty($_POST["major_admin"])) {
        $_POST["major_admin"] = 0;
    }

    try {
        DB::insert('user', array(
            'user_id' => $_POST["user_id"],
            'name' => $_POST["name"],
            'surname' => $_POST["surname"],
            'major_id' => $_POST["selected"],
            'sector_admin' => $_POST["sector_admin"],
            'major_admin' => $_POST["major_admin"],
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

//dropdown เลือกสาขาตอน add user
function getSelect(){
    $major = "SELECT `faculty`.`faculty_id`,`sector`.`sector_id`,`major`.`major_id`

            FROM `major`
            INNER JOIN `sector` ON `sector`.`sector_id` = major.`sector_id`
            INNER JOIN `faculty` ON `faculty`.`faculty_id` = `sector`.`faculty_id`
            
            WHERE `faculty`.`faculty_id` = %s
            ORDER BY `sector`.`sector_id`";

    $results = DB::query($major,$_POST["val"]);

    $data = [];
    $major_id = [];
    $oldSector = "";

    array_push($major_id, $results[0]['major_id']);
    for ($i = 0; $i < count($results); $i++) {

        $curSector = $results[$i]['sector_id'];
        $curMajor = $results[$i]['major_id'];

        if (isset($results[$i - 1]['sector_id'])) {
            $oldSector = $results[$i - 1]['sector_id'];
            if ($oldSector == $curSector) {
                array_push($major_id, $curMajor);
            } else {
                $data[] = [
                    'sector_id' => $oldSector,
                    'major_id' => $major_id
                ];
                $major_id = [];
                array_push($major_id, $results[$i]['major_id']);
            }
        }
    }
    $data[] = [
        'sector_id' => $oldSector,
        'major_id' => $major_id
    ];

    $json = json_encode($data);

    echo $json;
}

//dropdown แก้ major ใน table
function getSelectMajor(){
    $major = "SELECT `major`.`major_id`

            FROM `major`
            INNER JOIN `sector` ON `sector`.`sector_id` = major.`sector_id`
            INNER JOIN `faculty` ON `faculty`.`faculty_id` = `sector`.`faculty_id`
            
            WHERE `faculty`.`faculty_id` = %s
            ORDER BY `sector`.`sector_id`";

    $data = [];
    $results = DB::query($major,$_POST["val"]);
    foreach ($results as $row) {
        $data[] =[
            'id' => $row["major_id"],
            'text' => $row["major_id"]
        ];
    }

    $json = json_encode($data);
    echo $json;
}

//update user
function updateUser(){
    $pk = $_POST['pk'];
    $name = $_POST['name'];
    $value = $_POST['value'];

    if(!empty($value) || (($name == "faculty_admin" || $name == "sector_admin" || $name == "major_admin" ) && $value == 0 )) {
        DB::update('user', array(
            $name   => $value,
        ), "user_id=%s", $pk);
    } else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!Error Mysql can't update.";
    }

    //เช็ค admin หลักสูตร ว่าหลักสูตรที่ตัวเองเป็น admin ตรงกับ สาขาของที่ตัวเองอยู่ โดยส่งค่า major_id มาจาก admin
    if ($name == "major_id") {

        $curMajor = $_POST['majorIdAdmin'];

        $checkAdmin = "SELECT DISTINCT `course_admin_id`,`user`.`major_id` FROM `course_admin`

                  INNER JOIN `user` ON `user`.`user_id` = `course_admin`.`course_admin_id`
                  INNER JOIN `course` ON `course`.`course_id` = `course_admin`.`course_id`
                  WHERE `course`.`major_id` = %s";
        $AdminUserResults = DB::query($checkAdmin,$curMajor);

        foreach ($AdminUserResults as  $AdminUser){
            if ($AdminUser["major_id"] != $curMajor) {
                DB::delete('course_admin', "course_admin_id=%s",$AdminUser["course_admin_id"]);
            }
        }



        $checkPre = "SELECT `course`.`course_id`,`course`.`course_president_id`,`user`.`major_id` FROM `course` 

                     INNER JOIN `user` ON `user`.`user_id` = `course`.`course_president_id`
                     WHERE `course`.`major_id` = %s";

        $preUserResults = DB::query($checkPre,$curMajor);

        foreach ($preUserResults as  $perUser){
            if ($perUser["major_id"] != $curMajor) {
                DB::update('course', array(
                    'course_president_id' => NULL
                ), "course_president_id=%s", $perUser["course_president_id"]);

            }
        }

    }

}

///////////////////////////////////////////////END//////////////////////////////////////////////////////////////////////




////////////////////////////////////////////ผู้บริหารคณะ///////////////////////////////////////////////////////////////////

//คณะ จากค่าคณะของ admin ที่ส่งมา
function getFaculty(){
    $data = [];
    $index = 0;

    $faculty = "SELECT `faculty_id`,`user`.`user_id`,`name`,`surname`,`position` FROM `faculty_executive_officer`
                INNER JOIN `user` ON `user`.`user_id` = `faculty_executive_officer`.`user_id`
                WHERE `faculty_id` = %s
                ORDER BY `name`";

    $result = DB::query($faculty, $_POST["val"]);

    foreach ($result as $row){
        $data[] = [
            'index' => $index,
            'faculty_id' => $row["faculty_id"],
            'user_id' => $row["user_id"],
            'name' => $row["name"],
            'surname' => $row["surname"],
            'position' => $row["position"],
        ];
        $index++;
    }

    $json = json_encode($data);
    echo $json;
}

//get ต่ำแหน่งของผู้บริหาร
function getPosition(){
    $sql = "SELECT `position_id` FROM `position`";

    $data = [];
    $results = DB::query($sql);
    foreach ($results as $row) {
        $data[] =[
            'id' => $row["position_id"],
            'text' => $row["position_id"]
        ];
    }

    $json = json_encode($data);
    echo $json;

}

//dropdown ของประธานคณะ
function getFacultyUser(){
    $facultyUser = "SELECT `user`.`user_id`,`name`,`surname`,`faculty`.`faculty_id`,`sector`.`sector_id`,
                    `major`.`major_id`,`faculty_admin`,`sector_admin`,`major_admin`
            FROM `user`
            INNER JOIN `major` ON `major`.`major_id` = `user`.`major_id`
            INNER JOIN `sector` ON `sector`.`sector_id` = major.`sector_id`
            INNER JOIN `faculty` ON `faculty`.`faculty_id` = `sector`.`faculty_id`
            
            WHERE `faculty`.`faculty_id` = %s
            ORDER BY `sector`.`sector_id`";

    $data = [];
    $results = DB::query($facultyUser, $_POST["val"]);
    foreach ($results as $row) {
        $data[] =[
            'id' => $row["user_id"],
            'text' => $row["name"] . " " . $row["surname"]

        ];
    }

    $json = json_encode($data);
    echo $json;

}

//แก้ไขประธานคณะ
//function updateFacultyUser(){
//    $pk = $_POST['pk'];
//    $name = $_POST['name'];
//    $value = $_POST['value'];
//
//    if(!empty($value)) {
//        DB::update('faculty', array(
//            $name   => $value,
//        ), "faculty_id=%s", $pk);
//    } else {
//        header('HTTP/1.0 400 Bad Request', true, 400);
//        echo "!Error Mysql can't update.";
//    }
//
//}

//แก้ไขผู้บริหารคณะ
function updateFacultyPosition(){
    $pk = $_POST['pk'];
    $name = $_POST['name'];
    $value = $_POST['value'];
    $facultyId = $_POST['facultyId'];
    $userId = $_POST['userId'];
    $position = $_POST['position'];

    if(!empty($value)) {
        DB::update('faculty_executive_officer', array(
            $name   => $value,
        ), "faculty_id=%s and user_id=%s and position = %s", $facultyId,$userId,$position);
    } else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!Error Mysql can't update.";
    }

}

//เพิ่มผู้บริหารคณะ ไปืที่ faculty_executive_officer table
function addPositionUser(){
    DB::$error_handler = false;
    DB::$throw_exception_on_error = true;

    try {
        DB::insert('faculty_executive_officer', array(
            'faculty_id' => $_POST["faculty_id"],
            'user_id' => $_POST["user_id"],
            'position' => $_POST["position"],
        ));
        echo "success";
    }catch (MeekroDBException $e){
        echo "Duplicate position, This user has already this position.";
    }
}

//ลบผู้บริหารคณะ จาก faculty_executive_officer table
function removePositionUser(){
    $data = $_POST["data"];
    foreach($data as $row){
        DB::delete('faculty_executive_officer', "`faculty_id`=%s AND `user_id`=%s AND `position`=%s",
            $row["faculty_id"],$row["user_id"],$row["position"]);
    }

}

//////////////////////////////////////////////END///////////////////////////////////////////////////////////////////////




//////////////////////////////////////////ประธานภาควิชา///////////////////////////////////////////////////////////////////

//ภาควิชาทั้งหมดจากคณะที่กำหนด
function getSector(){
    $sector = "SELECT `faculty`.`faculty_id`,`sector`.`sector_id`,`sector`.`sector_president_id`

                FROM `faculty`
                INNER JOIN `sector` ON `faculty`.`faculty_id` = `sector`.`faculty_id`
                WHERE `faculty`.`faculty_id` = %s";

    $result = DB::query($sector, $_POST["val"]);

    $json = json_encode($result);
    echo $json;

}

//sector user ทั้งหมดจากคณะ
function getSectorUser(){
    $SectorUser = "SELECT `user`.`user_id`,`name`,`surname`,`sector`.`sector_id`
                    
            FROM `user`
            INNER JOIN `major` ON `major`.`major_id` = `user`.`major_id`
            INNER JOIN `sector` ON `sector`.`sector_id` = major.`sector_id`
            INNER JOIN `faculty` ON `faculty`.`faculty_id` = `sector`.`faculty_id`
            
            WHERE `faculty`.`faculty_id` = %s
            ORDER BY `sector`.`sector_id`";

    $results = DB::query($SectorUser, $_POST["val"]);

    $json = json_encode($results);
    echo $json;

}

//แก้ไขประธานภาควิชา
function updateSectorUser(){
    $pk = $_POST['pk'];
    $name = $_POST['name'];
    $value = $_POST['value'];

    if(!empty($value)) {
        DB::update('sector', array(
            $name   => $value,
        ), "sector_id=%s", $pk);
    } else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!Error Mysql can't update.";
    }

}

//แก้ไขประธานภาค null
function setNullSectorUser(){
    $pk = $_POST['pk'];
    echo $pk;

    if(!empty($pk)) {
        DB::update('sector', array(
            "sector_president_id"  => NULL,
        ), "sector_id=%s", $pk);
    } else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!Error Mysql can't update.";
    }

}
//////////////////////////////////////////////END///////////////////////////////////////////////////////////////////////




//////////////////////////////////////////ประธานสาขา///////////////////////////////////////////////////////////////////

//สาขาทั้งหมดจากคณะที่กำหนด
function getMajor(){
    $major = "SELECT `sector`.`sector_id`,`major`.`major_id`,`major`.`major_president_id`

                FROM `major`
                INNER JOIN `sector` ON `sector`.`sector_id` = major.`sector_id`
                INNER JOIN `faculty` ON `faculty`.`faculty_id` = `sector`.`faculty_id`
                
                WHERE `faculty`.`faculty_id` = %s
                ORDER BY `sector`.`sector_id`";

    $result = DB::query($major, $_POST["val"]);

    $json = json_encode($result);
    echo $json;
}

//major user ทั้งหมดจากคณะ
function getMajorUser(){
    $majorUser = "SELECT `user`.`user_id`,`name`,`surname`,`major`.`major_id`
                    
            FROM `user`
            INNER JOIN `major` ON `major`.`major_id` = `user`.`major_id`
            INNER JOIN `sector` ON `sector`.`sector_id` = major.`sector_id`
            INNER JOIN `faculty` ON `faculty`.`faculty_id` = `sector`.`faculty_id`
            
            WHERE `faculty`.`faculty_id` = %s
            ORDER BY `sector`.`sector_id`";

    $results = DB::query($majorUser, $_POST["val"]);

    $json = json_encode($results);
    echo $json;
}

//แก้ไขประธานสาขา
function updateMajorUser(){
    $pk = $_POST['pk'];
    $name = $_POST['name'];
    $value = $_POST['value'];

    if(!empty($value)) {
        DB::update('major', array(
            $name   => $value,
        ), "major_id=%s", $pk);
    } else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!Error Mysql can't update.";
    }

}

//แก้ไขประธานสาขา null
function setNullMajorUser(){
    $pk = $_POST['pk'];

    if(!empty($pk)) {
        DB::update('major', array(
            "major_president_id"  => NULL,
        ), "major_id=%s", $pk);
    } else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!Error Mysql can't update.";
    }

}

//////////////////////////////////////////////END///////////////////////////////////////////////////////////////////////


//------------------------------------------------END-------------------------------------------------------------------






