<?php
//added by Marija Djokic

session_start();
$levelTest=$_SESSION['test'];

require_once("include-to-load-wp.php");
$levelID=$_REQUEST['levelid'];

$result.= "<option value=''>Svi testovi</option>";
global $wpdb;

$testsbylevel = $wpdb->get_results
    ("SELECT * FROM wp_pb_lessons WHERE level_id=$levelID AND is_test=1 ORDER BY ordering ASC", ARRAY_A);


 
  foreach ($testsbylevel as $tests) {
     if($levelTest == $tests["id"])
	$result.= "<option value=".$tests["id"]." selected='selected'>".$tests["ordering"].":".$tests["title"]."</option>"; 
     else
       $result.= "<option value=".$tests["id"].">".$tests["ordering"].":".$tests["title"]."</option>";
  }

//echo "<option value=1>Maja</option>";
echo $result;
?>
