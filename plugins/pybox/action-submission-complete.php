<?php

//created by Marija Djokic
require_once("include-to-load-wp.php");

$problem = $_POST["problem"];
$answer = $_POST["usercode"];
$result = $_POST["result"];

global $current_user;
get_currentuserinfo();
global $wpdb;


if ( is_user_logged_in() ) {

  $uid = $current_user->ID;
  $table_name = $wpdb->prefix . "pb_submissions";


$logRow = array(
                  'beginstamp' => date( 'Y-m-d H:i:s', time() ),
                  'usercode' => $answer,
                  'userinput' => NULL,
                  'result' =>  isSoft($_REQUEST, "result", "true") ? 'Y' : 'N',
                  'problem' => $_POST["problem"], 
                  'ipaddress' => ($_SERVER['REMOTE_ADDR']),
                  'referer' => ($_SERVER['HTTP_REFERER']),
                  'userid' => is_user_logged_in() ? wp_get_current_user()->ID : -1);
 
  $wpdb->insert( $table_name, $logRow);



  
}

// end of file!
