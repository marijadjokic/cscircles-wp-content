<?php

/*

This file exposes part of the mail database to the user via flexigrid.

The present file accepts POST queries with the following arguments:

- who : a user id number, and we seek all messages about this user.
- xwho : we seek all messages not about this user. cannot be used with who
- what : a problem slug/name, and we seek all messages about this problem.
- xwhat : we seek all messages not about this problem. cannot be used with what
- unans : find only unanswered messages (1) or answered messages (0)

Additionally, we filter by security: the presently logged-in user can
only view messages that are to/from themselves and their students
(admins can view everything).

*/

function dbMail($limit, $sortname, $sortorder, $req = NULL) {
  global $db_query_info;
  $db_query_info = array();
 
  $who = getSoft(($req===NULL?$_REQUEST:$req), "who", "");
  $xwho = getSoft(($req===NULL?$_REQUEST:$req), "xwho", "");
  $what = getSoft(($req===NULL?$_REQUEST:$req), "what", "");
  $level = getSoft(($req===NULL?$_REQUEST:$req), "level", "");
  $xwhat = getSoft(($req===NULL?$_REQUEST:$req), "xwhat", "");
  $unans = getSoft(($req===NULL?$_REQUEST:$req), "unans", "");

   $db_query_info['type'] = 'mail-history';
   $db_query_info['who'] = $who;
   $db_query_info['level'] = $level;
   $db_query_info['xwho'] = $xwho;
   $db_query_info['what'] = $what;
   $db_query_info['xwhat'] = $xwhat;
   $db_query_info['unans'] = $unans;

   
   if ( !is_user_logged_in() )
     return __t("Morate biti logovani da bi ste videli raniju komunikaciju.");

  
   
   $where = 'WHERE 1';

   if (userIsAdmin()) {
     $where .= ' AND (uto = '. getUserID() . ' OR uto = 0 OR ufrom = '. getUserID() . ' OR ufrom = 0)';
   }
   else {
     $students = getStudents();
     $students[] = getUserID();
     $where .= ' AND (ustudent IN ('.implode(',', $students).') OR uto = '. getUserID() . ' OR ufrom = '. getUserID() .' )';
   }

   if ($who != '') {
     if (!is_numeric($who))
       return sprintf(__t("%s mora biti broj."), "'who'");
     $who = (int)$who;
     if (userIsAdmin() || getUserID() == $who || getUserID() == guruIDID($who) || userIsAssistant())
       $where .= ' AND ustudent = '.$who;
     else
       return __t("Pristup je odbijen.");
   }
   else if ($xwho != '') {
     if (!is_numeric($xwho))
       return sprintf(__t("%s mora biti broj."), "'xwho'");
     $xwho = (int)$xwho;
     $where .= ' AND ustudent != '.$xwho;
   }

   if ($unans != '') {
     if (!is_numeric($unans))
       return sprintf(__t("%s mora biti broj."), "'unans'");
     $unans = (int)$unans;
     $where .= ' AND unanswered = '.$unans;
   }

   global $wpdb;   

   if ($what != '') 
     $where .= $wpdb->prepare(' AND problem = %s', $what);
   if ($xwhat != '') 
     $where .= $wpdb->prepare(' AND problem != %s', $xwhat);

   
   $table_name = $wpdb->prefix . "pb_mail";

   $knownFields = array(__t("from")=>"ufrom", __t("to")=>"uto", 
			__t("when")=>"time", __t("message")=>"body",
			__t("problem")=>"problem", __t("replied?")=>"unanswered");

   $sortString = (array_key_exists($sortname, $knownFields)) ?
     ($knownFields[$sortname] . " " . $sortorder . ", ") : "";
//modified by Marija Djokic


if(userIsAdmin())
{
 $count = $wpdb->get_var
("SELECT COUNT(1) 
from $table_name $where 
AND problem IN(
SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.level_id =$level
AND wp_pb_lessons.is_test=0
)");


$prep = "SELECT * from $table_name $where AND problem IN(
SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.level_id =$level
AND wp_pb_lessons.is_test=0
)
ORDER BY $sortString ID DESC" . $limit;
}


else
{
$count = $wpdb->get_var
("SELECT COUNT(1) 
from $table_name $where 
AND problem IN(
SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.level_id = wp_users.current_lesson_level_id
AND wp_pb_lessons.is_test=0
AND wp_users.ID =".getUserID()."
)");

   $prep = "SELECT * from $table_name $where AND problem IN(
SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.level_id = wp_users.current_lesson_level_id
AND wp_pb_lessons.is_test=0
AND wp_users.ID =".getUserID()."
)
ORDER BY $sortString ID DESC" . $limit;
}

   
   $flexirows = array();
   foreach ($wpdb->get_results( $prep, ARRAY_A ) as $r) {
     $cell = array();
     $cell[__t('od koga')] = nicefiedUsername($r['ufrom']);
     $cell[__t('kome')] = nicefiedUsername($r['uto']);
     $url =  cscurl('mail') . "?who=".$r['ustudent']."&level=".$level."&what=".$r['problem']."&which=".$r['ID']."#m\n";
     $cell[__t('kada')] = str_replace(' ', '<br>', $r['time']);
     if ($what=='')
       $cell[__t('problem')] = $r['problem'];
     if ($unans == '')
       $cell[__t('odgovor na poruku')] = ($r['unanswered'] == 1) ? __t('ne') : __t('da');
     $cell[__t('sadržaj poruke')] = "<a href='$url'>".preBox($r['body'])."</a>";
     $flexirows[] = array('id'=>$r['ID'], 'cell'=>$cell);
   }
   return array('total' => $count, 'rows' => $flexirows);
}


// only do this if calld directly
if(strpos($_SERVER["SCRIPT_FILENAME"], '/db-mail.php')!=FALSE) {
  require_once("db-include.php");
  echo dbFlexigrid('dbMail');
 }

// paranoid against newline error
