<?php

include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if(isset($_POST["func"] )){
    if($_POST["func"] == "getCourse"){
        getCourse();
    }elseif($_POST["func"] == "getFaculty"){
        getFaculty();
    }elseif($_POST["func"] == "getCourseYear"){
        getCourseYear();
    }elseif($_POST["func"] == "getCourseDataTable"){
        getCourseDataTable();
    }
}

//get ข้อมูลจากคณะนั้นมาทั้งหมดเพื่อเอามาทำ Tree view
function getCourse(){
    $faculty_id = $_POST["faculty_id"];

    $sql = "SELECT `sector`.`faculty_id`,`sector`.`sector_id`,`major`.`major_id`,`course`.`course_id` FROM `course`

            INNER JOIN `major` ON `major`.`major_id` = `course`.`major_id`
            INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`
            
            WHERE `sector`.`faculty_id` = %s
            ORDER BY `faculty_id`,`sector_id`,`major_id`,`course_id`";

    $results = DB::query($sql,$faculty_id);
    $json = json_encode($results);
    echo $json;
}

//get faculty ทั้งหมดสำหรับเอามาทำ select
function getFaculty(){
    $sql = "SELECT `faculty_id` FROM `faculty`
            ORDER BY `faculty_id`";

    $results = DB::query($sql);
    $json = json_encode($results);
    echo $json;
}

//หาจำนวน ปี เทอม ของหลักสูตรนั้นๆ เพื่อนำเอามาแบ่งเป็นเทอม
function getCourseYear(){
    $course_id = $_POST["course_id"];
    $year = $_POST["year"];

    $sql = "SELECT DISTINCT `year`,`term` FROM `open_subjects`
            WHERE `course_id` = %s
            AND `year` = %i
            
            ORDER BY `term` DESC";

    $results = DB::query($sql,$course_id,$year);
    $json = json_encode($results);
    echo $json;

}

//ข้อมูลของ course table
function getCourseDataTable(){
    $course_id = $_POST["course_id"];
    $year = $_POST["year"];

    $sql = "SELECT `open_subjects`.`course_id`,`open_subjects`.`year`,`open_subjects`.`term`,
	        `open_subjects`.`subject_id`,`subject`.`subject_name`,`tqf_file`.`file_id`,`tqf_file`.`tqf_type`

            FROM `open_subjects`
            INNER JOIN `subject` ON `subject`.`subject_id` = `open_subjects`.`subject_id`
            LEFT JOIN `tqf_file` ON `tqf_file`.`course_id` = `open_subjects`.`course_id` AND
            `tqf_file`.`year` = `open_subjects`.`year` AND
            `tqf_file`.`term` = `open_subjects`.`term` AND
            `tqf_file`.`subject_id` = `open_subjects`.`subject_id` AND
            `tqf_file`.`tqf_type` IN (3,4)
            
            WHERE `open_subjects`.`course_id` = %s
            AND `open_subjects`.`year` = %i
            
            ORDER BY `term` DESC ,`open_subjects`.`subject_id`";

    $results = DB::query($sql,$course_id,$year);
    $json = json_encode($results);
    echo $json;

}