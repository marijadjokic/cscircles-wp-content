<?php

/*
require_once("include-to-load-wp.php");
$levelID=$_POST['levelid'];

$options = array();
  
  $options[''] = 'Svi problemi';
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
)", ARRAY_A);

 
  foreach ($problemsbylevel as $problem) {
     $options[$problem['slug']] = $problem['slug'];
  }
echo $options;*/
echo "test";
?>
