<?php

/* 
inner function returns either a string in case of error,
or an array-pair (total, array of (id, cell) array-pairs),
where each cell is an array representing a row.
*/

function dbProblemHistory($limit, $sortname, $sortorder, $req = NULL) {
  global $db_query_info;
  $db_query_info = array();
  if ($req == NULL) $req = $_REQUEST;
   $db_query_info['type'] = 'problem-history';

   $problemname = getSoft($req, "p", ""); //which problem?
   $user = getSoft($req, "user", "");   
   if ($problemname=="")
     return __t("Morate uneti naziv zadatka.");
   $db_query_info['problem'] = $problemname;

   $resultdesc = array('y'=> __t('Did not crash.'), 
		       'Y'=> __t('Tačno!'), 
		       'N'=> __t('Netačno.'), 
		       'E'=> __t('Unutrašnja greška.'), 
		       'S'=> __t('Sačuvano.'),
		       's'=> __t('Sačuvano.'));

   if ( !is_user_logged_in() )
     return __t("MOrate biti prijavljeni da bi ste videli istoriju komunikacije.");
   //modified by Marija Djokic
   if ( (userIsAdmin() || userIsAssistant()) && $user != "") {
   //
     $u = get_userdata($user);
     if ($u === false) 
       return sprintf(__t("Korisnički broj %s nije pronađen."), $u);
     $db_query_info['viewuser'] = $user;
   }
   else
     $u = wp_get_current_user();
   
   $uid = $u->ID;
   $uname = $u->user_login;
   
   global $wpdb;   
   $table_name = $wpdb->prefix . "pb_submissions";

   $counts = $wpdb->get_results
     ($wpdb->prepare("SELECT COUNT(1), COUNT(userinput) from $table_name
WHERE userid = %d AND problem = %s AND problem IN 
(SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.is_test=0
)", $uid, $problemname), ARRAY_N);
   
   $count = $counts[0][0];
   $showInputColumn = $counts[0][1] > 0;
   
   if ($count==0) 
     return sprintf(__t('Ne postoji istorija korisnika %1$s za problem %2$s.'),
		    $uname . ' (#'.$uid.')',
		    $problemname);
   

   $knownFields = array(__t("time &amp; ID")=>"beginstamp", __t("user code")=>"usercode", 
			__t("user input")=>"userinput", __t("result")=>"result");

   if (array_key_exists($sortname, $knownFields)) {
     $sortString = $knownFields[$sortname] . " " . $sortorder . ", ";
   }
   else $sortString = "";

   $prep = $wpdb->prepare("SELECT ID, beginstamp, usercode, userinput, result from $table_name
WHERE userid = %d AND problem = %s AND problem IN 
(SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.is_test=0
) ORDER BY $sortString ID DESC" . $limit, $uid, $problemname);
  
   $flexirows = array();
   foreach ($wpdb->get_results( $prep, ARRAY_A ) as $r) {
     $cell = array();
     $cell[__t('korisnički kod')] = preBox($r['usercode'], -1, -1);
     if ($showInputColumn) 
       $cell[__t('korisnički unos')] = $r['userinput'] == NULL ? '<i>'.__t('ne').'</i>' : preBox($r['userinput'], -1, 100000);
     if ($problemname != "visualizer")
       $cell[__t('rezultat')] = getSoft($resultdesc, $r['result'], $r['result']);
     $cell[__t('vreme')] = str_replace(' ', '<br/>', $r['beginstamp']) ;
     $flexirows[] = array('id'=>$r['ID'], 'cell'=>$cell);
   }
   return array('total' => $count, 'rows' => $flexirows);
}

// only do this if calld directly
if(strpos($_SERVER["SCRIPT_FILENAME"], '/db-problem-history.php')!=FALSE) {
  require_once("db-include.php");
  echo dbFlexigrid('dbProblemHistory');
 }

// paranoid against newline error
