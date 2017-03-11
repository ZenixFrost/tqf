<?php
include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if(isset($_POST["func"] )){
    if($_POST["func"] == "getSector"){
        getSector();
    }elseif($_POST["func"] == "getMajor"){
        getMajor();
    }elseif($_POST["func"] == "getTableData"){
        getTableData();
    }elseif ($_POST["func"] == "updateCourse"){
        updateCourse();
    }elseif($_POST["func"] == "getSectorSelect"){
        getSectorSelect();
    }elseif ($_POST["func"] == "updateSector") {
        updateSector();
    }elseif($_POST["func"] == "addCourse"){
        addCourse();
    }elseif($_POST["func"] == "removeCourse"){
        removeCourse();
    }elseif($_POST["func"] == "updateSectorTable"){
        updateSectorTable();
    }elseif($_POST["func"] == "updateMajorTable"){
        updateMajorTable();
    }elseif($_POST["func"] == "updateCourseTable"){
        updateCourseTable();
    }elseif($_POST["func"] == "getCourse"){
        getCourse();
    }
}


//////////////////////////////////////////////////Auto complete/////////////////////////////////////////////////////////

//Sector Autocomplete
function getSector(){
    $array = [];

    $sector = "SELECT `sector`.`sector_id`  FROM `sector`WHERE `sector`.`faculty_id` = %s";
    $facultyId = $_POST["val"];
    $result = DB::query($sector,$facultyId);
    foreach ($result as $rows) {
        array_push($array,$rows["sector_id"]);

    }

    $json = json_encode($array);
    echo $json;

}

//Major Autocomplete
function getMajor(){
    $array = [];

    $sector = "SELECT `major`.`major_id`  FROM `sector`
                INNER JOIN `major` ON `major`.`sector_id` = `sector`.`sector_id`
                WHERE `sector`.`faculty_id` = %s";
    $facultyId = $_POST["val"];
    $result = DB::query($sector,$facultyId);
    foreach ($result as $rows) {
        array_push($array,$rows["major_id"]);

    }

    $json = json_encode($array);
    echo $json;
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



/////////////////////////////////////////////////////////Course Table///////////////////////////////////////////////////


//ข้อมูล json ที่จะใส่ใน Table
function getTableData(){
    $facultyId = $_POST["val"];
    $data = [];
    $index = 0;

    $sql = "SELECT `sector`.`sector_id`,`major`.`major_id`,`course`.`course_id`,`course`.`initial`,`course`.`course_type`  FROM `sector`

            LEFT JOIN `major` ON `major`.`sector_id` = `sector`.`sector_id`
            LEFT JOIN `course` ON `course`.`major_id` = `major`.`major_id`
            WHERE `sector`.`faculty_id` = %s
            ORDER BY `sector_id`,`major_id`,`course_id`";

    $result = DB::query($sql,$facultyId);

    foreach ($result as $rows){
        $data[] = [
            'index' => $index,
            'sector_id' => $rows["sector_id"],
            'major_id' => $rows["major_id"],
            'course_id' => $rows["course_id"],
            'initial' => $rows["initial"],
            'course_type' => $rows["course_type"],

        ];

        $index++;
    }

    $json = json_encode($data);
    echo $json;
}

//ภาควิชาทั้งหมดจากคณะที่กำหนด Select ตอน edit table
function getSectorSelect(){
    $facultyId = $_POST["val"];
    $data = [];

    $sector = "SELECT `sector`.`sector_id`

                FROM `faculty`
                INNER JOIN `sector` ON `faculty`.`faculty_id` = `sector`.`faculty_id`
                WHERE `faculty`.`faculty_id` = %s";

    $results = DB::query($sector,$facultyId);
    foreach ($results as $row) {
        $data[] =[
            'id' => $row["sector_id"],
            'text' => $row["sector_id"]
        ];
    }

    $json = json_encode($data);
    echo $json;
}

//อัพเดท table หลักสูตร (ย้ายสังกัด สาขา ใน course table)
function updateCourse(){
    $pk = $_POST['pk'];
    $name = $_POST['name'];
    $value = $_POST['value'];


//    if(empty($pk)){
//        header('HTTP/1.0 400 Bad Request', true, 400);
//       echo "!Error Course name's empty.";
//   }elseif(!empty($value) || $value == 0) {
//       DB::update('course', array(
//           $name   => $value,
//       ), "course_id=%s", $pk);
//   } else {
//       header('HTTP/1.0 400 Bad Request', true, 400);
//       echo "!Error Mysql can't update.";
//   }

    if(!empty($value)) {
        DB::update('course', array(
            $name   => $value,
        ), "course_id=%s", $pk);
    } else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!Error Mysql can't update.";
    }

}

//update sector (ย้ายสังกัด)
function updateSector(){

    $pk = $_POST['pk'];
    $name = $_POST['name'];
    $value = $_POST['value'];


    if(empty($pk)){
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!Error Course name's empty.";
    }else {
        $majorId = "SELECT `major`.`major_id` FROM `major`

    INNER JOIN `course` ON `course`.`major_id` = `major`.`major_id`

    WHERE `course`.`course_id` = %s";

        $result = DB::query($majorId, $pk);

        $curMajorId = $result[0]["major_id"];


        if (!empty($value) || $value == 0) {
            DB::update('major', array(
                $name => $value,
            ), "major_id=%s", $curMajorId);
        } else {
            header('HTTP/1.0 400 Bad Request', true, 400);
            echo "!Error Mysql can't update.";
        }
    }
}

//add course
function addCourse(){
    DB::$error_handler = false;
    DB::$throw_exception_on_error = true;

    $faculty = $_POST["faculty"];
    $sector = $_POST["sector"];
    $major = $_POST["major"];
    $courses = $_POST["courses"];
    $initial = $_POST["initial"];
    $coursesType = $_POST["coursesType"];

    DB::insertIgnore('sector', array(
        'sector_id' => $sector,
        'faculty_id' => $faculty,
    ));

    DB::insertIgnore('major', array(
        'major_id' => $major,
        'sector_id' => $sector,
    ));

    try {
        DB::insert('course', array(
            'course_id' => $courses,
            'initial' => $initial,
            'major_id' => $major,
            'course_type' => $coursesType
        ));
        echo "success";
    }catch (MeekroDBException $e){
        echo "Duplicate courses, This courses is already used.";

    }
}

//remove course
function removeCourse(){
    $data = $_POST["data"];

    foreach($data as $row){
        DB::delete('course', "course_id=%s", $row["course_id"]);


        //ลบ major เมื่อเช็คแล้วไม่มีการใช้
        $majorSql = "SELECT `course_id` FROM `course`
                INNER JOIN `major` ON `major`.`major_id` = `course`.`major_id`
                WHERE  `major`.`major_id` = %s";

        DB::query($majorSql, $row["major_id"]);
        $majorCounter = DB::count();

        if($majorCounter == 0){
            DB::delete('major', "`major_id`=%s", $row["major_id"]);
        }


        //ลบ sector เมื่อเช็คแล้วไม่มีการใช้
        $sectorSql = "SELECT `sector`.`sector_id`  FROM `sector`

                      INNER JOIN `major` ON `major`.`sector_id` = `sector`.`sector_id`
                      WHERE  `sector`.`sector_id` = %s";

        DB::query($sectorSql, $row["sector_id"]);
        $sectorCounter = DB::count();

        if($sectorCounter == 0){
            DB::delete('sector', "`sector_id`=%s", $row["sector_id"]);
        }



    }
}


//เปลี่ยนค่า sector_id (เปลี่ยนชื่อ)
function updateSectorTable(){
    $sector_id = $_POST['sector_id'];
    $name = $_POST['name'];
    $value = $_POST['value'];

    if(!empty($value)) {
        DB::update('sector', array(
            $name   => $value,
        ), "sector_id=%s", $sector_id);
    } else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!ค่าที่กรอกมาห้ามว่าง";
    }
}

//เปลี่ยนค่า major_id (เปลี่ยนชื่อ)
function updateMajorTable(){
    $major_id = $_POST['major_id'];
    $name = $_POST['name'];
    $value = $_POST['value'];


    if(!empty($value)) {
        DB::update('major', array(
            $name   => $value,
        ), "major_id=%s", $major_id);
    } else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!ค่าที่กรอกมาห้ามว่าง";
    }

}

function updateCourseTable(){
    $course_id = $_POST['course_id'];
    $name = $_POST['name'];
    $value = $_POST['value'];

    if(!empty($value) || $name == "course_type") {
        DB::update('course', array(
            $name   => $value,
        ), "course_id=%s", $course_id);
    } else {
        header('HTTP/1.0 400 Bad Request', true, 400);
        echo "!ค่าที่กรอกมาห้ามว่าง";
    }
}


function getCourse(){
    $sql = "SELECT `course_id` FROM `course`";
    $result = DB::query($sql);

    $json = json_encode($result);
    echo $json;
}


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////