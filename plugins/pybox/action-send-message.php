<?php

require_once("include-to-load-wp.php");

function send($problem_info, $from, $to, $student, $slug, $body, $noreply) {

  global $wpdb, $current_user;

  $unanswered = (getUserID() == $student) ? 1 : 0;

  if (getUserID() != $student) 
    $wpdb->update($wpdb->prefix.'pb_mail',
		  array('unanswered' => 0),
		  array('unanswered' => 1, 'ustudent' => $student, 'problem' => $slug));

  if ($noreply != 'false') // don't redirect
    return "#";   

  $insert_to = $to;
  if ($to == 0 && pll_current_language()=='de')
    $insert_to = CSCIRCLES_ASST_ID_DE;

  $wpdb->insert($wpdb->prefix.'pb_mail', 
		array('ufrom' => $from, 'uto' => $insert_to, 'ustudent' => $student, 'problem' => $slug, 'body' => $body, 
		      'unanswered' => $unanswered), 
		array('%d','%d','%d','%s','%s', '%d'));
  $mailref = $wpdb->insert_id;

  if (userIsAdmin() || userIsAssistant())
    $mFrom = '"'. __t("IMI Python elearning") . '"<m.djokic@kg.ac.rs>';
  else 
    $mFrom = '"' . $current_user->user_login . '" <' . $current_user->user_email . '>';

  $subject = __t('IMI Python elearning') .' - '. __t('poruka o problemu') . ' ' . $problem_info['publicname'];
  
  $contents = $body."\n===\n";
  $contents .= __t("Da biste odgovroili na poruku molimo Vas posetite")."\n";
  $contents .= cscurl('mail') . "?who=$student&level=$level&what=$slug&which=$mailref#m\n";
  $contents .= __t("Problem URL:")." " . $problem_info['url'] . "\n";
  $contents .= "[".__t("IMI Python elearning")." ".cscurl("homepage")."]";

  if ($to == 0) {
    // same fallback as admin-options.php
    $to_emailaddr = get_option('cscircles_asst_email', get_userdata(1)->user_email);
    if (pll_current_language()=='de')
      $to_emailaddr = get_user_by('id', CSCIRCLES_ASST_ID_DE)->user_email;
  }
  else {
    $to_emailaddr = get_user_by('id', $to)->user_email;
  }

  //pyboxlog($mFrom . " " . $to_emailaddr . " " . $subject . " " . $contents);
  pb_mail($mFrom, $to_emailaddr, $subject, $contents);

  if (get_the_author_meta('pbnocc', getUserID())!='true') {
    $to_desc = ($to == 0) ? "the CS Circles Assistant" : get_user_by('id',$to)->user_login;
    pb_mail($mFrom, 
	    $current_user->user_email, 
	    __t("SENT:")." " . $subject, 
	    (sprintf(__t("OVO JE KOPIJA PORUKE koju ste poslali studentu %s."), $to_desc) .
	     "\n\n" . $contents)
	    );
  }

  return $mailref;
}


$slug = $_POST["slug"];
$source = $_POST["source"];

$user = getUserID();

if ($user < 0) {
  header('HTTP/1.1 401 Unauthorized');
  return;
 }

global $current_user;
get_currentuserinfo();
$user_email = $current_user->user_email;

global $wpdb;
//modified by Marija Djokic
$problem_info = $wpdb->get_row($wpdb->prepare('SELECT * from '.$wpdb->prefix.'pb_problems where slug = %s', 
					      $slug), ARRAY_A);
//

if ($problem_info === NULL) {
  header('HTTP/1.1 404 Not Found');
  return;  
 }

$message = stripcslashes($_POST["message"]);
$noreply = getSoft($_POST, 'noreply', 'false');

if ($source == 1) { //inline help form
  $guru_login = get_the_author_meta('pbguru', get_current_user_id()); // '' if does not exist
  $guru = get_user_by('login', $guru_login);                          // FALSE if does not exist

  $code = stripcslashes($_POST["code"]);
  
  $message .= "\n===\n".__t("Korisnik je poslao sledeći kod sa porukom:")."\n===\n" . $code;

  echo send($problem_info, getUserID(), isSoft($_POST, 'recipient', '1') ? $guru->ID : 0, getUserID(), $slug, $message, $noreply);
 }
elseif ($source == 2) { //mail page
  $id = $_POST['id'];  
  $guru_login = get_the_author_meta('pbguru', $id); // '' if does not exist
  $guru = get_user_by('login', $guru_login);        // FALSE if does not exist
  if (userIsAdmin() || userIsAssistant() || getUserID() == $guru->ID) {
    // from {guru or CSC Asst.} to student
//modified by Marija Djokic
    echo send($problem_info, userIsAdmin()?getUserID():0, $id, $id, $slug, $message, $noreply);
//
  }
  elseif ($id == getUserID()) {
    // from student to {guru or CSC Asst.}
    echo send($problem_info, $id, isSoft($_POST, 'recipient', '1') ? $guru->ID : 0, $id, $slug, $message, $noreply);
  }
  
}
// end of file!
