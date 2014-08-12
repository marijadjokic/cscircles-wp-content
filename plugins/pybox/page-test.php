<?php

session_start();
$problemSlug=$_SESSION['problem'];

require_once("include-to-load-wp.php");

$levelID=$_REQUEST['levelid'];


$result.= "<option value=''>Svi problemi</option>";
global $wpdb;

$problemsbylevel = $wpdb->get_results
    ("SELECT * FROM wp_pb_problems
 WHERE facultative = 0 AND lang='sr' AND lesson IS NOT NULL
AND slug IN(
SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.level_id = $levelID
AND wp_pb_lessons.is_test=0)", ARRAY_A);


 
  foreach ($problemsbylevel as $problem) {
        if($problem["slug"]==$problemSlug) $result.= "<option value=".$problem["slug"]." selected='selected'>".$problem["publicname"]."</option>";
        else $result.= "<option value=".$problem["slug"].">".$problem["publicname"]."</option>";
  }


//echo "<option value=1>Maja</option>";
echo $result;
?>
