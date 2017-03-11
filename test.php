<script src="./lib/jquery/jquery-2.1.4.js"></script>
<script>
    $(function () {
        var test = [
            {
                "faculty_id": "science",
                "sector_id": "math and computer science",
                "major": ["cs", "math"]
            },
            {
                "faculty_id": "science",
                "sector_id": "sci",
                "major": "เคมี"
            },
        ]
        console.log(test);

//        var test = [
//            {"faculty_id": "science", "sector_id": "math and computer science", "major": "cs"},
//            {"faculty_id": "science", "sector_id": "math and computer science", "major": "math"},
//            {"faculty_id": "science", "sector_id": "sci", "major": "เคมี"},
//        ]
//        console.log(test);

        $.each(test, function (i, a) {
            console.log(a.faculty_id);
            if (Array.isArray(a.major) == true) {
                $.each(a.major, function (j, b) {
                    console.log(b);
                });

            } else {
                console.log(a.major);
            }
            console.log("==============================");
        });
    });


    $(function () {
        $.ajax({
            type: 'GET',
            url: 'test.php',
            dataType: 'json',
            success: function (response) {
                console.log(response);
            }
        });
    });

</script>
<?php
include_once "./lib/composer/vendor/autoload.php";
include_once "connect.php";
/**
 * Created by PhpStorm.
 * User: Indyn
 * Date: 18/7/2559
 * Time: 15:03
 */


$email = "indynormal@gmail.com";
$f = "วิทยาการคอมพิวเตอร์";

//echo hash("crc32b","สาขาวิชาเทคโนโลยีการถ่ายภาพและภาพยนตร์",FALSE);

//echo time()."<br>";

//for($i = 0;$i <= 10;$i++){
//    echo md5(time() . mt_rand())."<br>";
//}


//echo md5(time() . mt_rand(1,1000000));

$rows[] = [
    'username' => 'Frankie',
    'password' => 'abc'
];
$rows[] = [
    'username' => 'Bob',
    'password' => 'def'
];

//echo $rows[0]['username'];

//$major = [
//    0 => [
//        'major_name' => 'css',
//        'course' => ['it', 'cs'],
//    ]
//];
//
//
//echo $major[0]['major_name']."<br>";
//echo $major[0]['course'][0]."<br>";
//echo $major[0]['course'][1];
//


//$json = json_encode($major);
//
//echo $json . "<br>";


//var_dump($major);


//$major = array(
//    0 => array('sector_id' => "science", 'major' => 'cs'),
//    1 => array('sector_id' => "science", 'major' => 'math'),
//    2 => array('sector_id' => "aa", 'major' => 'gg'),
//    3 => array('sector_id' => "aa", 'major' => 'hh'),
//);

//foreach ($majors as $major){
//    echo $major["major"]."<br>";
//    echo $major["major"][0];
//}

//$results = DB::query("SELECT `faculty`.`faculty_id`,`sector`.`sector_id`,`major`.`major_id`
//
//FROM `major`
//INNER JOIN `sector` ON `sector`.`sector_id` = major.`sector_id`
//INNER JOIN `faculty` ON `faculty`.`faculty_id` = `sector`.`faculty_id`
//
//
//WHERE `faculty`.`faculty_id` = %s
//ORDER BY `sector`.`sector_id`", "คณะวิทยาศาสตร์และเทคโนโลยี");

//echo count($results);
//foreach ($results as $result) {
//    echo   $result["faculty_id"]." | ". $result["sector_id"]." | ".$result["major_id"]."<br>";
//}


//$major = $results;
//$test = [];
//$eiei = [];
//
//$oldSector = "";
//
//
//array_push($eiei, $major[0]['major_id']);
//for ($i = 0; $i < count($major); $i++) {
//
//    $curSector = $major[$i]['sector_id'];
//    $curMajor = $major[$i]['major_id'];
//
//    if (isset($major[$i - 1]['sector_id'])) {
//        $oldSector = $major[$i - 1]['sector_id'];
//        if ($oldSector == $curSector) {
//            array_push($eiei, $curMajor);
//        } else {
//            $test[] = [
//                'sector_id' => $oldSector,
//                'major' => $eiei,
//            ];
//            $eiei = [];
//            array_push($eiei, $major[$i]['major_id']);
//        }
//    }
//
//}
//$test[] = [
//    'sector_id' => $oldSector,
//    'major' => $eiei
//];
//
//$json = json_encode($test);
//
////echo $json . "<br>";
//
//$num = 0;
//
//if(!empty($num) || $num == 0) {
//    echo "in";
//}else{
//    echo "out";
//}

//var_dump($test);

//$json = json_encode($majors);
//
//echo $json;

//$courseResults = [
//    ['course_id' => 'cs'],
//    ['course_id' => 'it'],
//    ['course_id' => 'sa'],
//];


///////////////////////////////////////////////////////////////////////////////////////

$user = "SELECT `user_id`,`name`,`surname` FROM USER

WHERE `user`.`major_id` = \"สาขาวิชาวิทยาการคอมพิวเตอร์\"";

$courseId = "SELECT `course_id` FROM `course`
WHERE `major_id` = \"สาขาวิชาวิทยาการคอมพิวเตอร์\"";

$courseResults = DB::query($courseId);
$results = DB::query($user);

$majorUser = [];

for($i = 0;$i < count($results);$i++) {
    $curUserId = $results[$i]["user_id"];
    $majorUser[$i] = [
        'user_id' =>  $curUserId,
    ];
    for ($j = 0; $j < count($courseResults); $j++) {
        $curCourseId = $courseResults[$j]["course_id"];
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



/////////////////////////////////////////////////////////////////////////////////////////
echo "---------------------------------------------------------------------------------------<br>";

$num = 21;
for($i=11;$i>=0;$i--){
    for($j=0;$j<$num;$j++){
        if($j>=$i && $j < ($num-$i)){
            echo ":bacon:";
        }else{
            echo ":cheese:";
        }
    }
    echo "<br>";
}

echo "---------------------------------------------------------------------------------------<br>";

//
//$OpenSubjectsSql = "SELECT `course_id`,`year`,`term`,`subject_id` FROM `open_subjects`";
//$groupSql = "SELECT DISTINCT`course_id`,`year`,`term`,`subject_id` FROM `open_subjects_group`";
//
//$OpenSubjectsResults = DB::query($OpenSubjectsSql);
//$groupResults = DB::query($groupSql);
//$check = false;
//
//$openCourseId = "";
//$openYear = "";
//$openTerm = "";
//$openSubjectId = "";
//
//$groupCourseId = "";
//$groupYear = "";
//$groupTerm = "";
//$groupSubjectId = "";
//
//foreach ($OpenSubjectsResults as $OpenSubjectsRows){
//    $check = false;
//    foreach ($groupResults as $groupRows){
//        $openCourseId = $OpenSubjectsRows["course_id"];
//        $openYear = $OpenSubjectsRows["year"];
//        $openTerm = $OpenSubjectsRows["term"];
//        $openSubjectId = $OpenSubjectsRows["subject_id"];
//
//        $groupCourseId = $groupRows["course_id"];
//        $groupYear = $groupRows["year"];
//        $groupTerm = $groupRows["term"];
//        $groupSubjectId = $groupRows["subject_id"];
//
//
//        if($openCourseId == $groupCourseId && $openYear == $groupYear && $openTerm == $groupTerm && $openSubjectId == $groupSubjectId) {
//            $check = true;
//        }
//    }
//
//    if($check == false){
//        DB::delete('open_subjects', "`course_id`=%s AND `year`=%i AND `term`=%i AND `subject_id`=%s", $openCourseId,$openYear, $openTerm , $openSubjectId);
//    }
//
//}

$date = date("Y-m-d");

$time = strtotime($date);
$time = strtotime('2016-09-24');
$time2 = strtotime('2016-09-25');

if($time2 > $time){

}
//echo date("d",$time);



$rows[] = [
    [
        'username' => 'Frankie',
        'password' => 'abc'
    ],
    [
        'username' => 'Bob',
        'password' => 'def'
    ]
];


$json = json_encode($rows);

$obj = json_decode($json,true);


;


$test = '[{"id":"21","RFID":"11111","stdid":"565021000312","stdname":"ธนธรณ์ กีรติมงคลกุล","credit":"18","email":"565021000312@mail.rmutk.ac.th"},
{"id":"26","RFID":"11111","stdid":"565021000403","stdname":"มรุพล เดชอุดม","credit":"1","email":"565021000403@mail.rmutk.ac.th"},{"id":"27","RFID":"66666","stdid":"565021000304","stdname":"ธณเดช เลิศวุฒิการย์","credit":"7","email":"565021000304@mail.rmutk.ac.th"},
{"id":"28","RFID":"11111","stdid":"565021000189","stdname":"ณัฐพล รัตนกร","credit":"5","email":"565021000189@mail.rmutk.ac.th"},{"id":"31","RFID":"11111","stdid":"565021000395","stdname":"ชัชวาล จีนมหันต์","credit":"1","email":"565021000395@mail.rmutk.ac.th"},
{"id":"33","RFID":"11111","stdid":"565021000775","stdname":"ชมพู่ เป๊ดเป็ด","credit":"0","email":"565021000775@mail.rmutk.ac.th"},{"id":"34","RFID":"11111","stdid":"565021000300","stdname":"ธนธรณ์ อิ่มอิ่ม","credit":"5.5","email":"rollingpork@gmail.com"}]';
$eiei = json_decode($test,true);
var_dump($eiei);

$today = date("Y")+543;

echo $today;
echo "<br><br><br>";


$tree = [
            'text' => "คณะวิท",
            'node' => [
                [
                    'text' => "วิทคอม"
                ],[
                    'text' => "เทคโนโลยีสารสนเทศ"
                ],[
                    'text' => "ศึกษาทั่วไป"
                ],
            ]

        ];

$json = json_encode($tree);
echo $json;

echo "<br>";
$test = "2560/10/11";



$date = str_replace('/','-',$test);

echo $date;
echo "<br>";


$date ='12-12-2559';
$dateTime = new DateTime($date);
$formatted_date = date_format( $dateTime, 'Y-m-d' );
echo $formatted_date;

echo "<br>";


$test1 = "12/10/2560";
$result_date = DateTime::createFromFormat('d/m/Y', $test1);
$t = $result_date->format('Y/m/d');


echo ($result_date->format('Y')-543)."-".$result_date->format('m')."-".$result_date->format('d')."<br>";


$test1 = "12-10-2016";
$date = strtotime("+7769 days",strtotime ( $test1 )); 
$newdate = date ( 'Y-m-d' , $date );

echo $newdate."<br>";

echo date('Y-m-d', strtotime("+7744 days"))."<br>";


$date = new DateTime('1980-02-15');
$day = 2000;
$date->sub(new DateInterval("P".$day."Y"));
echo $date->format('Y-m-d')."<br>";


$jd =  new DateTime('2016-11-8');
echo $jd->format('N')."<br>";

///////////////////////////////
$faculty_id = "วิทยาศาสตร์และเทคโนโลยี";
$checkTqf34Deadline = false;


$start_semester = new DateTime('2016-11-7');
$tqf34Deadline = $start_semester->sub(new DateInterval("P7D"))->format('Y-m-d');


$day = new DateTime($tqf34Deadline);
$day = $day->format('N');



$sqlHoliday = "SELECT `date_id` FROM `official_holiday`

        WHERE `date_id` = %i
        AND `faculty_id` = %s";

DB::query($sqlHoliday,$tqf34Deadline,$faculty_id);
$countHoliday = DB::count();


while($day == 6 || $day == 7 || $countHoliday == 1){

    $tqf34Deadline = new DateTime($tqf34Deadline);
    $tqf34Deadline = $tqf34Deadline->sub(new DateInterval("P1D"))->format('Y-m-d');

    $day = new DateTime($tqf34Deadline);
    $day = $day->format('N');

    DB::query($sqlHoliday,$tqf34Deadline,$faculty_id);
    $countHoliday = DB::count();
}

echo $tqf34Deadline;


$pig = ":pig:";

for($i=0;$i<25;$i++){
    for($j=0;$j<$i;$j++){
        echo $pig;
    }
    echo "<br>";
}

if("2016-11-11" < "2016-11-12"){
    echo "in";
}

echo "<br>";
$year = 2559;

$sql = "SELECT `year`,`term`,`tqf3-4_deadline`,`tqf5-6_deadline`,`faculty_id` FROM `tqf_deadline`
            WHERE `year` = %i
            AND `term` = 3
            AND `faculty_id` = %s
            ORDER BY `term` DESC";
$term3Results = DB::queryFirstRow($sql, $year,$faculty_id);

echo $term3Results["tqf3-4_deadline"];

if($term3Results["tqf3-4_deadline"] == null){
    echo "test";
}
echo "<br>";

$dt = new DateTime("now",  new DateTimeZone('ASIA/Bangkok'));
echo $dt->format("N");


$sql = "SELECT * FROM `teaching_file`
WHERE `subject_id` = '1957325'";

$results = DB::query($sql);



foreach($results as $rows){
    echo $rows["file_id"]."<br>";
}


//unlink('.\server\php\files\test.docx');
$file = "test.docx";
echo $path = '.\server\php\files\\'.$file;

echo "<br>";
//unlink($path);

$deadlineTime = "2016-12-24";
$deadlineTime = new DateTime($deadlineTime);

echo $deadlineTime->add(new DateInterval("P1D"))->format('Y-m-d');

