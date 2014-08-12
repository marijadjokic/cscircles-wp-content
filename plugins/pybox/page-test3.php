<?php
//added by Marija Djokic

session_start();

require_once("include-to-load-wp.php");
$originaldate=$_REQUEST['date'];
$newdate=date("Y-m-d", strtotime($originaldate));

//echo $originaldate."->".$newdate;


$mystudent=array();
$students = getStudents();

foreach ($students as $student) {
        $info = get_userdata($student);
        array_push($mystudent,$info->ID);
       
       }
//print_r($mystudent);

global $wpdb;
$studentsbydate = $wpdb->get_results
    ("SELECT * FROM wp_users WHERE user_registered>'$newdate' ORDER BY id", ARRAY_A);

$result.= "<option value='all'>Prikaz svih mojih studenata</option>";

  foreach ($studentsbydate as $studentbydate) {
    //echo $studentbydate['ID'];
    if(in_array($studentbydate['ID'],$mystudent))
    $result.= "<option value=".$studentbydate['ID'].">".userString($studentbydate['ID'])."</option>";
  }

//echo "<option value=1>Maja</option>";
echo $result;
?>
