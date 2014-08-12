<?php
//added by Marija Djokic

session_start();
$problem=$_SESSION['newproblem'];
require_once("include-to-load-wp.php");
$test=$_REQUEST['test'];

//echo $test;
$result = "<option value=''>Svi zadaci</option>";
global $wpdb;

$testsproblemsbylevel = $wpdb->get_results
    ("SELECT * FROM wp_pb_problems
 WHERE facultative = 0 AND lang='sr' AND lesson IS NOT NULL
AND slug IN(
SELECT slug 
FROM wp_pb_problems, wp_pb_lessons
WHERE wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_problems.postid=$test)", ARRAY_A);


 
  foreach ($testsproblemsbylevel as $testsproblems) {
   //echo strcmp($testsproblems["slug"],$problem);
   if(strcmp($testsproblems["slug"],$problem)==0) $result.= "<option value=".$testsproblems["slug"]." selected=selected>".$testsproblems["publicname"]."</option>";
        else $result.= "<option value=".$testsproblems["slug"].">".$testsproblems["publicname"]."</option>";
  }


//echo "<option value=1>Maja</option>";
echo $result;
?>
