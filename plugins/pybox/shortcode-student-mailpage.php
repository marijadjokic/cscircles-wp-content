<?php

session_start();
$_SESSION['problemslug']=$_GET['what'];


add_shortcode('pbstudentmailpage', 'pbstudentmailpage');

 function validate() {



 /*   if (!array_key_exists('who', $_GET) || !array_key_exists('what', $_GET))
   return array("error", __t("Mandatory arguments are missing.")); */
  $s = getSoft($_GET, 'who', getUserID());
  if ($s === '') $s = getUserID();
  $p = getSoft($_GET, 'what', '');
  $l = getSoft($_GET, 'level', '');
  $date =$_GET['datepicker'];
  if (!is_numeric($s))
    return array("error", __t("ID studenta mora biti broj."));
  $s = (int)$s;
  
  global $wpdb, $mailcond;
  
  
  echo '<script type="text/javascript">
function validatemail(){
var level=document.getElementById("level").value;
if(level=="")alert("Morate odabrati nivo!");
else if(document.getElementById("who").value=="")alert("Morate odabrati studenta!");
else document.studentmail.submit();
}
$(document).ready(function(){ 
var level_id = $("select#level option:selected").attr("value");
if(level_id != "")changeLevel(level_id);
$("select#level").change(function(){
var level_id = $("select#level option:selected").attr("value");
changeLevel(level_id);
});
});

function changeLevel(level_id)
{
$.ajax({
type: "GET",
url: "../wp-content/plugins/pybox/page-test.php",
data: {"levelid":level_id},
success: function(html){
$("select#what").html(html);
},
error: function(jxhr){alert(jxhr.responseText)}
});
}
$(function(){
$("#datepicker").datepicker(
{
    onSelect: function()
    { 
        var date = $("#datepicker").val(); 
        //alert(date);
       if(date != "")selectStudentsByDate(date);
    }
});
});

function selectStudentsByDate(date)
{
$.ajax({
type: "GET",
url: "../wp-content/plugins/pybox/page-test4.php",
data: {"date":date},
success: function(html){
$("select#who").html(html);
},
error: function(jxhr){alert("Sacekajte učitavanje studenata")}
});
}
</script>';
  
  
  $student = get_userdata((int)$s);
  
  if ($student === False)
    return array("error", __t("Ne postoji takav student."));

  
  if (! (getUserID() == $s || in_array($s, getStudentsByDate(FALSE, $date)) || userIsAdmin() || userIsAssistant()) )
    return array("error", __t("Pristup je odbijen. Morate biti prijavljeni."));

  if (! (getUserID() == $s || in_array($s, getStudentsByDate(FALSE, $date))) || userIsAdmin())
    $mailcond = "(uto = ".getUserID()." OR ufrom = ".getUserID().")";
  else // if viewing as foreign-language assistant, only can view problems to/from self
    $mailcond = "1";
  //echo $mailcond;
 
  if ($p != '') {
    $problem = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."pb_problems WHERE slug = %s AND slug in
(SELECT slug 
FROM wp_pb_problems, wp_pb_lessons
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.is_test=0
)", $p), ARRAY_A);
    //echo  $problem['publicname'];
    if ($problem === null)
      return array("error", __t("<b>Ne postoji takav problem!</b>"));
  }
  

  
  $f = (int)getSoft($_GET, 'which', -1);
  
  return array("success", array("student"=>$student, "sid"=>$s, "problem"=>($p==''?NULL:$problem), "focus"=>$f));
}

 function pbstudentmailpage($options, $content) {
  if ( !is_user_logged_in() ) 
    return __t("Morate biti logovani da bi ste videli stranicu za komunikaciju sa studentima.");
 
 if ( !userIsAdmin()) 
    return __t("<br/><br/>Nemate pravo pristupa stranici <b>Komunikacija</b>.");
  
  $v = validate();

  
  if ($v[0] != 'success') 
    return $v[1]; // error message
  
  extract($v[1]); // $student, $problem, $focus, $sid
  
  $name = nicefiedUsername($sid, FALSE);
  
  $r = '';
  
  global $wpdb;
  
  $date=$_GET['datepicker'];
  $students = getStudentsByDate(FALSE, $date);
  $cstudents = count($students);
  if($cstudents>0){
  $r .= reselector($students, $cstudents);
  }
else {
return __t("<br/><br/>Trenutno nemate dodeljenih studenta.");
}
  
  if ($problem !== NULL) {
    $finished = $wpdb->get_var($wpdb->prepare("SELECT time FROM ".$wpdb->prefix."pb_completed WHERE userid = %d AND problem = %s",
				      $sid, $problem['slug']));


    $r .= '<div class="history-note">'.
      sprintf(__t('Komunikacija za problem %1$s [%3$s] sa studentom %2$s'),
              $problem['publicname'],
	      userString($sid),
              '<a href="' . $problem['url'] . '">' . 
              __t('link ka originalnoj stranici') . '</a>'
              );
    $r .= '</div>';
if ($finished !== NULL)
      $r .= "<img title='".$student->user_login.__t(" je završio ovaj problem.")."' src='".UFILES."checked.png' class='pycheck'/>";
    else
      $r .= "<img title='".$student->user_login.__t(" nije završio ovaj problem.")."' src='".UFILES."icon.png' class='pycheck'/>";
    
    if ($finished !== NULL)
      $r .= "<div class='history-prenote'>".sprintf(__t('Napomena: student je završio problem u %s'), $finished)."</div>";    
    
    $r .= '<i>'.__t('Kliknite na naslov poruke da biste otvorili ili zatvorili poruku.').'</i>';
    
    global $mailcond;
    $messages = $wpdb->get_results($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."pb_mail WHERE ustudent = %d AND problem = %s AND $mailcond ORDER BY ID desc",
						  $sid, $problem['slug']), ARRAY_A);
    
    foreach ($messages as $i=>$message) {
      $c =  ($message['ID']==$focus) ?  " showing" : " hiding";
      $idp = ($message['ID']==$focus) ?  " id='m' " : '';
      $r .= "<div $idp class='collapseContain$c' style='border-radius: 5px;'>";
      if(nicefiedUsername($message['ufrom'], FALSE)=="ja")
      $title = __t("ja").__t(' / ').' '.nicefiedUsername($message['uto'], FALSE).', '.$message['time'];
       if(nicefiedUsername($message['uto'], FALSE)=="ja")
 $title =nicefiedUsername($message['ufrom'], FALSE). ' '.__t(' / ja').', '.$message['time'];
      if (count($messages)>1 && $i==0) $title .= " ".__t("(najnovija poruka)");
      if (count($messages)>1 && $i==count($messages)-1) $title .= " " .__t("(najstarija poruka)");
      $r .= "<div class='collapseHead'><span class='icon'></span>$title</div>";
      $r .= "<div class='collapseBody'><span class='quoth'>".__t("Citiraj/<br/>Odgovori")."</span>".preBox($message['body'], -1,10000,"font-size:12px; line-height:14px; white-space: pre-wrap;").'</div>';
      $r .= '</div>';
    }
    
    $to = "";
    if (getUserID() == $sid) {
      $guru_login = get_the_author_meta('pbguru', get_current_user_id());
      $guru = get_user_by('login', $guru_login);                          // FALSE if does not exist
      $to .= '<div style="text-align: center">';
      $to .= __t('Pošaljite poruku: ');
      if ($guru !== FALSE) {
	$to .= "<select class='recipient'>
<option value='$guru->id'>".__t("Moj mentor")." ($guru_login)</option>
</select></div>";
      } 
      else {
	$to .= "<select class='recipient'>

<option value='0'>".__t("(Nemate definisanog mentora)")."</option>
</select>";
	$to .= '<br/></div>';
      }
    }

    
    $r .= '<div class="pybox fixsresize mailform" id="mailform">
<div id="bodyarea" class="pyboxTextwrap">
<textarea name="body" class="resizy" placeholder="'.__t('Ovde otkucajte i pošaljite odogvor').'" style="width:100%; white-space: pre-wrap; font-size: 11px; line-height:13px" rows=12></textarea>
</div>
'.$to;
    
    if (getUserID() != $sid) 

      $r .= '<input style="position:relative; top:2px" type="checkbox" id="noreply" onclick="toggleVisibility(\'bodyarea\')">'.
	' <label style="font-size:75%" for="noreply">'.__t('Označite poruku kao nepročitanu').'</label><br>';
    
    $r .= '<button onclick="mailReply('.$sid.',\''.$problem['slug'].'\');">'.__t('Pošaljite poruku!').'</button>
</div>';
    
    $problemname = $problem['publicname'];

    $r .= '<hr style="width:80%;align:center;">';
    
    if (getUserID() != $sid) 
    

$r .= "
<div class='collapseContain hiding'>
<div class='collapseHead'><span class='icon'></span>".__t("Opis problema")." ".$problem['publicname']."</div>
<div class='collapseBody'>".pyBoxHandler(json_decode($problem['shortcodeArgs'], TRUE), $problem['content'])."</div>
</div>";
    
    
    if (getUserID()!=$sid)
      $r .= niceFlex('us', sprintf(__t('Istorija korisnika %1$s za problem %2$s'), $name, $problemname),
		     'problem-history', 'dbProblemHistory', array('user'=>$sid, 'p'=>$problem['slug']));

    
    if (getUserID()!=$sid)
      $r .= niceFlex('omp', sprintf(__t("Moje ostale poruke za %s"), $problemname),
		     'mail', 'dbMail', array('what'=>$problem['slug'], 'xwho'=>$sid,'level'=>$_GET['level']));
    
    $r .= niceFlex('oms',   (getUserID()==$sid)?__t("Moje poruke za druge probleme"):
		   sprintf(__t("Poruke od/za %s za druge probleme"), $name), 
		   'mail', 'dbMail', array('who'=>$sid, 'xwhat'=>$problem['slug'],'level'=>$_GET['level']));
    
  }
  
  if ($cstudents > 0)
   if(array('who'=>$sid) != 'Izaberite studenta' && $_GET['level']!=""){
    if($name!="ja"){
    $r .= niceFlex('allstu', sprintf(__t("Sve poruke od studenta %s"), $name),
		   'mail', 'dbMail', array('who'=>$sid, 'level'=>$_REQUEST['level']));
$r .= niceFlex('allme', __t("Sve moje poruke"),
		 'mail', 'dbMail', array('level'=>$_REQUEST['level']));
   }
 }
  return $r;
}

function reselector(&$students, $cstudents) {
  
  global $wpdb;
  
  $problem_table = $wpdb->prefix . "pb_problems";
  $problems = $wpdb->get_results
    ("SELECT * FROM wp_pb_problems WHERE facultative = 0 AND lang='sr' AND lesson IS NOT NULL
AND slug IN(
SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.level_id = ".$_REQUEST['level']."
AND wp_pb_lessons.is_test=0
AND wp_users.ID=".getUserID()."
)
ORDER BY lesson ASC, boxid ASC", ARRAY_A);


  $problemsByNumber = array();
  foreach ($problems as $prow) 
    $problemsByNumber[$prow['slug']] = $prow;
  
  $gp = getSoft($_GET, "what", "");
  if ($gp != "" && $gp == "console" && !array_key_exists($gp, $problemsByNumber)) {
    echo sprintf(__t("Problem %s nije pronađen)"), $gp);
    return;
  }  
  
if ($cstudents > 0 || userIsAssistant() || userIsAdmin()) {
  $preamble = 
    "<div class='progress-selector'>
       <form method='get' id='studentmail'><table style='border:none'><tr><td>".__t("Želite li komunikaciju sa nekim Vašim studentom?").'</td><td>';

    $options = array();
    $options[''] = __t('Izaberite studenta');
    
   
      foreach ($students as $student) {
        $info = get_userdata($student);
        $options[$info->ID] = userString($info->ID);
      }
    
   
    $preamble .= optionsHelper($options, 'who');
     $preamble .="</td><td>Datum: <input type='text' name='datepicker'id='datepicker'style='width:80px;' value='".$_GET['datepicker']."'/></td></tr>";
    


if ($l == '') {
    $levels = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."pb_lesson_level ORDER BY id", ARRAY_A);
    if ($levels === null)
      return array("error", __t("Ne postoji takav nivo."));
  }

$preamble .= "<tr><td>".__t("Želite li određeni nivo?")."</td><td>";
  $options = array();
  $options[''] = 'Izaberite nivo';
  
  foreach ($levels as $level) {
      $options[$level['id']] = $level['level'];
  }
  $preamble .= optionsHelper($options, 'level') . "</td></tr>";
  
  $preamble .= "<tr><td>".__t("Želite li određeni problem?")."</td><td>";
  $options = array();
  
  $options[''] = 'Svi problemi';
  
  foreach ($problems as $problem) {
    //if ($problem['type'] == 'code')

 }
  $preamble .= optionsHelper($options, 'what') . "</td></tr>";

  
    $preamble .= "<tr><td colspan='3' style='text-align:center'><input style='width: 25%' type='submit' value='".__t('Potvrdi')."' onclick='validatemail();'/></tr></td></table></form></div>";
  return $preamble;
 }
}

function niceFlex($id, $title, $fileSuffix, $functionName, $dbparams) {
  
  include_once("db-$fileSuffix.php");
  $url = UDBPREFIX . $fileSuffix . ".php";
  $query_result = call_user_func($functionName," limit 0,0", '', '', $dbparams);
  if (is_string($query_result))
    $rows = __t("n/a");
  else
    $rows = $query_result['total'];

  return "<div class='collapseContain hiding' id='cc$id'>
<div class='collapseHead' id='ch$id'><span class='icon'></span>$title ($rows)</div>
<div class='collapseBody' id='cb$id'></div></div>
<script type='text/javascript'>
jQuery('#ch$id').click(function(e) {
  if (0==jQuery('#cb$id .flexigrid').size()) pyflex({'id':'cb$id', 'url':'$url', 'dbparams':".json_encode($dbparams)."});
});
</script>";

}


// end of file
