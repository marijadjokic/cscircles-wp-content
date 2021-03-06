<?php

/* 
inner function returns either a string in case of error,
or an array-pair (total, array of (id, cell) array-pairs),
where each cell is an array representing a row.
*/

//added by Marija Djokic
function dbEntireMyProgressHistory($limit, $sortname, $sortorder, $req=NULL) {
   global $db_query_info;
   $db_query_info = array();
   if ($req == NULL) $req = $_REQUEST;
   $db_query_info['type'] = 'entire-my-progress-history';

   $user = getSoft($req, "user", "");   
   
   $resultdesc = array('y'=> __t('Program je izvršen bez grešaka.'), 
		       'Y'=> __t('Tačno!'), 
		       'N'=> __t('Netačno.'), 
		       'E'=> __t('Unutrašnja greška.'), 
		       'S'=> __t('Sačuvano.'),
		       's'=> __t('Sačuvano.'));
   
   global $current_user;
   $currentuser=get_current_user();
   $currentuserid= $current_user->ID;

   
   get_currentuserinfo();
   global $wpdb;
   
   if ( !is_user_logged_in() ) 
     return __t("Morate biti logovani da bi ste videli Vašu istoriju.");

   if ($user == "all") {
     $u = "all";
   }
   elseif ($user == "") {
     $u = $current_user;
   }
   else {
     $u = get_userdata($user);
     if ($u === false)
       return __t("Korisnički broj nije pronađen.");
   }

   if ($user != "")
     $db_query_info['viewuser'] = $user;


$problemTableQuery="SELECT slug, publicname, url FROM ".$wpdb->prefix."pb_problems, wp_pb_lessons, wp_users WHERE slug IS NOT NULL AND postid=wp_pb_lessons.id AND wp_pb_lessons.is_test=0 AND wp_pb_lessons.level_id=wp_users.current_lesson_level_id AND wp_users.id=$currentuserid";
//echo $problemTableQuery;
   
$problemTable = $wpdb->get_results($problemTableQuery, 
				      OBJECT_K);
  
$knownFields = array(__t("userid")=>"userid", __t("time &amp; ID")=>"beginstamp", __t("problem")=>"problem",
			__t("user code")=>"usercode", __t("user input")=>"userinput", __t("result")=>"result");
   
   if (array_key_exists($sortname, $knownFields)) {
     $sortString = $knownFields[$sortname] . " " . $sortorder . ", ";
   }
   else $sortString = "";

   $whereStudent = NULL;

   if ($u == "all") {
     $whereStudent = !userIsAdmin() ? "1" : ("userid in " . getStudentList());
   }
   else {
     $uid = $u->ID;
     $whereStudent = $wpdb->prepare("userid = %d", $uid);

   }     


//upiti za prikaz istorije 
$countsubmissions="SELECT COUNT(1)
FROM wp_pb_submissions
WHERE $whereStudent
AND problem
IN (
SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.level_id = wp_users.current_lesson_level_id
AND wp_pb_lessons.is_test=0
AND wp_users.ID =$uid
)";
//echo $countsubmissions;
$prep = "
SELECT userid, ID, beginstamp, usercode, userinput, result, problem
FROM ".$wpdb->prefix."pb_submissions
WHERE $whereStudent
AND problem IN (SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.level_id = wp_users.current_lesson_level_id
AND wp_pb_lessons.is_test=0
AND wp_users.ID =$uid
)
ORDER BY $sorting ID DESC";

//echo $prep;
$count = $wpdb->get_var($countsubmissions);
//echo $count;


   $flexirows = array();
   foreach ($wpdb->get_results( $prep, ARRAY_A ) as $r) {
     
     $cell = array();
     if ($u == "all") {
       $cell[__t('userid')] = str_replace(' ', "<br>", userString($r['userid'], true));
     }
     
     $p = $r['problem'];
    
     if (array_key_exists($p, $problemTable)) 
     
       $cell[__t('problem')] = '<a class="open-same-window" href="' . $problemTable[$p]->url . '">'
	 . $problemTable[$p]->publicname . '</a>';


     else

       $cell[__t('problem')] = $p;

       
     $cell[__t('korisnički kod')] = preBox($r['usercode'], -1, -1);
     $cell[__t('korisnički unos')] = $r['userinput'] == NULL ? '<i>'.__t('n/a').'</i>' : preBox($r['userinput'], -1, 100000);
     if ($p != 'visualizer' && $p != 'visualizer-iframe')
       $cell[__t('rezultat')] = getSoft($resultdesc, $r['result'], '???');
     else
       $cell[__t('rezultat')] = '<i>ne/da</i>';       
     $cell[__t('Vreme &amp; ID')] = str_replace(' ', '<br/>', $r['beginstamp']) . '<br/>#' . $r['ID'];
    
     $flexirows[] = array('id'=>$r['ID'], 'cell'=>$cell);

   }

   return array('total' => $count, 'rows' => $flexirows);

 };


// only do this if calld directly
if(strpos($_SERVER["SCRIPT_FILENAME"], '/db-entire-my-progress-history.php')!=FALSE) {
  require_once("db-include.php");
  echo dbFlexigrid('dbEntireMyProgressHistory');
 }

// paranoid against newline error
