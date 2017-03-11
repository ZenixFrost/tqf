<?php

include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";

if(isset($_POST["func"] )) {
    if ($_POST["func"] == "getSummary") {
        getSummary();
    }elseif($_POST["func"] == "getCourseTqf34DataTable"){
        getCourseTqf34DataTable();
    }elseif($_POST["func"] == "getCourseTqf56DataTable"){
        getCourseTqf56DataTable();
    }
}

function getSummary(){
    $faculty_executive_officer = $_POST["faculty_executive_officer"];
    $major_president = $_POST["major_president"];
    $sector_president = $_POST["sector_president"];
    $course_president = $_POST["course_president"];

    $user_id = $_POST["user_id"];
    $faculty_admin = $_POST["faculty_admin"];
    $major_admin = $_POST["major_admin"];
    $course_admin = $_POST["course_admin"];


    $faculty_id = $_POST["faculty_id"];
    $sector_id = $_POST["sector_id"];
    $major_id= $_POST["major_id"];

    $year = $_POST["year"];

    $data = [];
    $results = null;

    if($faculty_executive_officer >= 1 || $faculty_admin == 1){
        $sql = "SELECT `sector`.`faculty_id`,`sector`.`sector_id`,`major`.`major_id`,`course`.`course_id` FROM `course`

                INNER JOIN `major` ON `major`.`major_id` = `course`.`major_id`
                INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`
                
                WHERE `sector`.`faculty_id` = %s
                ORDER BY `sector_id` ASC,`major_id` ASC,`course_id` ASC";
        $results = DB::query($sql,$faculty_id);


    }elseif($sector_president >= 1){
        $sql = "SELECT `sector`.`faculty_id`,`sector`.`sector_id`,`major`.`major_id`,`course`.`course_id` FROM `course`

                INNER JOIN `major` ON `major`.`major_id` = `course`.`major_id`
                INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`
                
                WHERE `sector`.`faculty_id` = %s
                AND `sector`.`sector_id` = %s
                ORDER BY `sector_id` ASC,`major_id` ASC,`course_id` ASC";
        $results = DB::query($sql,$faculty_id,$sector_id);


    }elseif ($major_president >= 1 || $major_admin == 1){
        $sql = "SELECT `sector`.`faculty_id`,`sector`.`sector_id`,`major`.`major_id`,`course`.`course_id` FROM `course`

                INNER JOIN `major` ON `major`.`major_id` = `course`.`major_id`
                INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`
                
                WHERE `sector`.`faculty_id` = %s
                AND `sector`.`sector_id` = %s
                AND `major`.`major_id` = %s
                ORDER BY `sector_id` ASC,`major_id` ASC,`course_id` ASC";
        $results = DB::query($sql,$faculty_id,$sector_id,$major_id);


    }elseif($course_president != null || $course_admin >= 1){
        $courseData = [];
        $checkCourse = true;

        $courseSql = "SELECT `course_id` FROM `course_admin`
                      WHERE `course_admin_id` = %s";
        $courseResults = DB::query($courseSql,$user_id);

        foreach ($courseResults as $rows){
            if($rows["course_id"] ==  $course_president){
                $checkCourse = false;
            }

            array_push($courseData,$rows["course_id"]);
        }

        if($checkCourse){
            array_push($courseData,$course_president);
        }

        $sql = "SELECT `sector`.`faculty_id`,`sector`.`sector_id`,`major`.`major_id`,`course`.`course_id` FROM `course`

                INNER JOIN `major` ON `major`.`major_id` = `course`.`major_id`
                INNER JOIN `sector` ON `sector`.`sector_id` = `major`.`sector_id`
                
                WHERE `sector`.`faculty_id` = %s
                AND `sector`.`sector_id` = %s
                AND `major`.`major_id` = %s
                AND `course`.`course_id` IN %ls
                ORDER BY `sector_id` ASC,`major_id` ASC,`course_id` ASC";
        $results = DB::query($sql,$faculty_id,$sector_id,$major_id,$courseData);

    }


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    //เอาข้อมูลที่ query มาสร้างเป็น array
    foreach ($results as $rows){

        //ข้อมูลของวิชาทั้งหมด

        //term 1
        $sql = "SELECT * FROM `open_subjects`
                    WHERE `year` = %i
                    AND `term` = 1
                    AND `course_id` = %s";
        DB::query($sql,$year,$rows["course_id"]);
        $subjectTerm1Num = DB::count();

        //term 2
        $sql = "SELECT * FROM `open_subjects`
                    WHERE `year` = %i
                    AND `term` = 2
                    AND `course_id` = %s";
        DB::query($sql,$year,$rows["course_id"]);
        $subjectTerm2Num = DB::count();

        //term 3
        $sql = "SELECT * FROM `open_subjects`
                    WHERE `year` = %i
                    AND `term` = 3
                    AND `course_id` = %s";
        DB::query($sql,$year,$rows["course_id"]);
        $subjectTerm3Num = DB::count();


        //////////////////////////////////////////////////TQF FILE NUM///////////////////////////////////////////////////

        //term 1
        $sql = "SELECT `course_id`,`subject_id`,`term`,`year`,`file_id`,`tqf_type` FROM `tqf_file`
                    WHERE `year` = %i
                    AND `term` = 1
                    AND `course_id` = %s
                    AND `tqf_type` IN (3,4)";
        DB::query($sql,$year,$rows["course_id"]);
        $tqf34Term1Num = DB::count();

        $sql = "SELECT `course_id`,`subject_id`,`term`,`year`,`file_id`,`tqf_type` FROM `tqf_file`
                    WHERE `year` = %i
                    AND `term` = 1
                    AND `course_id` = %s
                    AND `tqf_type` IN (5,6)";
        DB::query($sql,$year,$rows["course_id"]);
        $tqf56Term1Num = DB::count();

        //////////////////////////////////////////////////////////////////////////////////////////////////////////////

        //term 2
        $sql = "SELECT `course_id`,`subject_id`,`term`,`year`,`file_id`,`tqf_type` FROM `tqf_file`
                    WHERE `year` = %i
                    AND `term` = 2
                    AND `course_id` = %s
                    AND `tqf_type` IN (3,4)";
        DB::query($sql,$year,$rows["course_id"]);
        $tqf34Term2Num = DB::count();

        $sql = "SELECT `course_id`,`subject_id`,`term`,`year`,`file_id`,`tqf_type` FROM `tqf_file`
                    WHERE `year` = %i
                    AND `term` = 2
                    AND `course_id` = %s
                    AND `tqf_type` IN (5,6)";
        DB::query($sql,$year,$rows["course_id"]);
        $tqf56Term2Num = DB::count();


        /////////////////////////////////////////////////////////////////////////////////////////////////////////////


        //term 3
        $sql = "SELECT `course_id`,`subject_id`,`term`,`year`,`file_id`,`tqf_type` FROM `tqf_file`
                    WHERE `year` = %i
                    AND `term` = 3
                    AND `course_id` = %s
                    AND `tqf_type` IN (3,4)";
        DB::query($sql,$year,$rows["course_id"]);
        $tqf34Term3Num = DB::count();

        $sql = "SELECT `course_id`,`subject_id`,`term`,`year`,`file_id`,`tqf_type` FROM `tqf_file`
                    WHERE `year` = %i
                    AND `term` = 3
                    AND `course_id` = %s
                    AND `tqf_type` IN (5,6)";
        DB::query($sql,$year,$rows["course_id"]);
        $tqf56Term3Num = DB::count();


        /////////////////////////////////////////////////////////////////////////////////////////////////////////////

        if($subjectTerm1Num == 0){
            $tqf34_term1_Num = NULL;
            $tqf56_term1_Num = NULL;
        }else{
            $tqf34_term1_Num = round(($tqf34Term1Num/$subjectTerm1Num)*100);
            $tqf56_term1_Num = round(($tqf56Term1Num/$subjectTerm1Num)*100);
        }

        if($subjectTerm2Num == 0){
            $tqf34_term2_Num= NULL;
            $tqf56_term2_Num = NULL;
        }else{
            $tqf34_term2_Num = round(($tqf34Term2Num/$subjectTerm2Num)*100);
            $tqf56_term2_Num = round(($tqf56Term2Num/$subjectTerm2Num)*100);
        }

        if($subjectTerm3Num == 0){
            $data[] = [
                'faculty_id' => $rows["faculty_id"],
                'sector_id' => $rows["sector_id"],
                'major_id' => $rows["major_id"],
                'course_id' => $rows["course_id"],
                'tqf34_term1_Num' => $tqf34_term1_Num,
                'tqf56_term1_Num' => $tqf56_term1_Num,
                'tqf34_term2_Num' => $tqf34_term2_Num,
                'tqf56_term2_Num' => $tqf56_term2_Num,
            ];
        }else{
            $tqf34_term3_Num = round(($tqf34Term3Num/$subjectTerm3Num)*100);
            $tqf56_term3_Num = round(($tqf56Term3Num/$subjectTerm3Num)*100);

            $data[] = [
                'faculty_id' => $rows["faculty_id"],
                'sector_id' => $rows["sector_id"],
                'major_id' => $rows["major_id"],
                'course_id' => $rows["course_id"],
                'tqf34_term1_Num' => $tqf34_term1_Num,
                'tqf56_term1_Num' => $tqf56_term1_Num,
                'tqf34_term2_Num' => $tqf34_term2_Num,
                'tqf56_term2_Num' => $tqf56_term2_Num,
                'tqf34_term3_Num' => $tqf34_term3_Num,
                'tqf56_term3_Num' => $tqf56_term3_Num,
            ];
        }


    }

    $json = json_encode($data);
    echo $json;


}


//ข้อมูลของ course table tqf3/4
function getCourseTqf34DataTable(){
    $course_id = $_POST["course_id"];
    $year = $_POST["year"];
    $term =  $_POST["term"];

    $sql = "SELECT `open_subjects`.`course_id`,`open_subjects`.`year`,`open_subjects`.`term`,
	                `open_subjects`.`subject_id`,`subject`.`subject_name`,`open_subjects`.`responsible_subject` ,`name`,`surname` ,
	                `tqf_file`.`file_id`,`tqf_file`.`tqf_type`

            FROM `open_subjects`
            INNER JOIN `subject` ON `subject`.`subject_id` = `open_subjects`.`subject_id`
            LEFT JOIN `tqf_file` ON `tqf_file`.`course_id` = `open_subjects`.`course_id` AND
            `tqf_file`.`year` = `open_subjects`.`year` AND
            `tqf_file`.`term` = `open_subjects`.`term` AND
            `tqf_file`.`subject_id` = `open_subjects`.`subject_id` AND
            `tqf_file`.`tqf_type` IN (3,4)
            
            LEFT JOIN `user` ON  `responsible_subject` = `user`.`user_id`
            
            WHERE `open_subjects`.`course_id` = %s
            AND `open_subjects`.`year` = %i
            AND `open_subjects`.`term` = %i
            
            ORDER BY `file_id`,`responsible_subject` ASC,`open_subjects`.`subject_id` ASC";

    $results = DB::query($sql,$course_id,$year,$term);
    $json = json_encode($results);
    echo $json;

}


//ข้อมูลของ course table tqf5/6
function getCourseTqf56DataTable(){
    $course_id = $_POST["course_id"];
    $year = $_POST["year"];
    $term =  $_POST["term"];

    $sql = "SELECT `open_subjects`.`course_id`,`open_subjects`.`year`,`open_subjects`.`term`,
	                `open_subjects`.`subject_id`,`subject`.`subject_name`,`open_subjects`.`responsible_subject` ,`name`,`surname`,
	                `tqf_file`.`file_id`,`tqf_file`.`tqf_type`

            FROM `open_subjects`
            INNER JOIN `subject` ON `subject`.`subject_id` = `open_subjects`.`subject_id`
            LEFT JOIN `tqf_file` ON `tqf_file`.`course_id` = `open_subjects`.`course_id` AND
            `tqf_file`.`year` = `open_subjects`.`year` AND
            `tqf_file`.`term` = `open_subjects`.`term` AND
            `tqf_file`.`subject_id` = `open_subjects`.`subject_id` AND
            `tqf_file`.`tqf_type` IN (5,6)
            
            LEFT JOIN `user` ON  `responsible_subject` = `user`.`user_id`
            
            WHERE `open_subjects`.`course_id` = %s
            AND `open_subjects`.`year` = %i
            AND `open_subjects`.`term` = %i
            
            ORDER BY `file_id`,`responsible_subject` ASC,`open_subjects`.`subject_id` ASC";

    $results = DB::query($sql,$course_id,$year,$term);
    $json = json_encode($results);
    echo $json;

}
