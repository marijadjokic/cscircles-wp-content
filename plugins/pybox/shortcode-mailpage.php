<?php

add_shortcode('pbmailpage', 'pbmailpage');

 function validate() {
 /*   if (!array_key_exists('who', $_GET) || !array_key_exists('what', $_GET))
   return array("error", __t("Mandatory arguments are missing.")); */
  $s = getSoft($_GET, 'who', getUserID());
  if ($s === '') $s = getUserID();
  $p = getSoft($_GET, 'what', '');
  //echo $p;
  if (!is_numeric($s))
    return array("error", __t("ID studenta mora biti broj."));
  $s = (int)$s;
  
  global $wpdb, $mailcond;
  
  $student = get_userdata($s);
  
  if ($student === False)
    return array("error", __t("Ne postoji takav student."));
  
  if (! (getUserID() == $s || in_array($s, getStudents()) || userIsAdmin() || userIsAssistant()) )
    return array("error", __t("Pristup je odbijen. Morate biti prijavljeni."));

  if (! (getUserID() == $s || in_array($s, getStudents()) || userIsAdmin()))
    $mailcond = "(uto = ".getUserID()." OR ufrom = ".getUserID().")";
  else // if viewing as foreign-language assistant, only can view problems to/from self
    $mailcond = "1";
 
  if ($p != '') {
    $problem = $wpdb->get_row($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."pb_problems WHERE slug = %s",
					     $p), ARRAY_A);
    //echo  $problem['publicname'];
    if ($problem === null)
      return array("error", __t("Ne postoji takav problem."));
  }
  
  $f = (int)getSoft($_GET, 'which', -1);
  
  return array("success", array("student"=>$student, "sid"=>$s, "problem"=>($p==''?NULL:$problem), "focus"=>$f));
}

 function pbmailpage($options, $content) {
  if ( !is_user_logged_in() ) 
    return __t("Morate biti logovani da bi ste videli Vašu stranicu za komunikaciju.");
  
  $v = validate();

  
  if ($v[0] != 'success') 
    return $v[1]; // error message
  
  extract($v[1]); // $student, $problem, $focus, $sid
  
  $name = nicefiedUsername($sid, FALSE);
  
  $r = '';
  
  global $wpdb;
  
  $students = getStudents();
  $cstudents = count($students);
 
  $r .= reselector($students, $cstudents);

  $r .= '<hr style="width:80%;align:center;">';
  
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
      $title = __t("Od")." ".nicefiedUsername($message['ufrom'], FALSE). ' '.__t('za').' '.nicefiedUsername($message['uto'], FALSE).', '.$message['time'];
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
      $to .= __t('Poslati e-mail poruku: ');
//modifided by Marija Djokic
      if ($guru !== FALSE) {
	$to .= "<select class='recipient'>
<option value='1'>".__t("Moj mentor")." ($guru_login)</option>
</select></div>";
      } 
      else {
	$to .= "<select class='recipient'>

<option value='0'>".__t("(Nemate definisanog mentora)")."</option>
</select>";
	$to .= '<br/></div>';
      }
    }
//
    
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
      $r .= "<div class='history-note'><a href='".cscurl('progress').'?user='.$sid."'>".sprintf(__t("Progres stranica studenta %s (novi prozor)"), $name)."</a></div>";

$r .= "
<div class='collapseContain hiding'>
<div class='collapseHead'><span class='icon'></span>".__t("Opis problema")." ".$problem['publicname']."</div>
<div class='collapseBody'>".pyBoxHandler(json_decode($problem['shortcodeArgs'], TRUE), $problem['content'])."</div>
</div>";
    
    
    if (getUserID()!=$sid)
      $r .= niceFlex('us', sprintf(__t('Istorija korisnika %1$s za problem %2$s'), $name, $problemname),
		     'problem-history', 'dbProblemHistory', array('user'=>$sid, 'p'=>$problem['slug']));
if(!userIsAdmin())  {
 $r .= niceFlex('ms', sprintf(__t("Moja istorija problema %s"), $problemname),
		   'problem-history', 'dbProblemHistory', array('p'=>$problem['slug']));}
    
    if (getUserID()!=$sid)
      $r .= niceFlex('omp', sprintf(__t("Moje ostale poruke za %s"), $problemname),
		     'mail', 'dbMail', array('what'=>$problem['slug'], 'xwho'=>$sid));
    
    $r .= niceFlex('oms',   (getUserID()==$sid)?__t("Moje poruke za druge probleme"):
		   sprintf(__t("Poruke od/za %s za druge probleme"), $name), 
		   'mail', 'dbMail', array('who'=>$sid, 'xwhat'=>$problem['slug']));
    
  }
  
  if ($cstudents > 0 || userIsAssistant() || userIsAdmin())
   if(array('who'=>$sid) != 'Izaberite studenta'){
    $r .= niceFlex('allstu', sprintf(__t("Sve poruke od studenta %s"), $name),
		   'mail', 'dbMail', array('who'=>$sid));
}
  
  $r .= niceFlex('allme', __t("Sve moje poruke"),
		 'mail', 'dbMail', array());
  
  return $r;
}

function reselector(&$students, $cstudents) {
  
  global $wpdb;
  
  $problem_table = $wpdb->prefix . "pb_problems";
  $problems = $wpdb->get_results
    ("SELECT * FROM $problem_table WHERE facultative = 0 AND lang='sr' AND lesson IS NOT NULL ORDER BY lesson ASC, boxid ASC", ARRAY_A);
  $problemsByNumber = array();
  foreach ($problems as $prow) 
    $problemsByNumber[$prow['slug']] = $prow;
  
  $gp = getSoft($_GET, "what", "");
  if ($gp != "" && $gp != "console" && !array_key_exists($gp, $problemsByNumber)) {
    echo sprintf(__t("Problem %s nije pronađen)"), $gp);
    return;
  }  
  
  $preamble = 
    "<div class='progress-selector'>
       <form method='get'><table style='border:none'>";
  if ($cstudents > 0 || userIsAssistant() || userIsAdmin()) { // slightly leaky but assistants will want to see progress
    $preamble .= "<tr><td>".sprintf(__t("Želite li da pogledate komunikaciju sa nekim Vašim studentom? (imate %s)"), $cstudents).'</td><td>';
    $options = array();
    $options[''] = __t('Izaberite studenta');
    
    if (userIsAdmin()) {
      foreach ($students as $student) {
        $info = get_userdata($student);
        $options[$info->ID] = userString($info->ID);
      }
    }
    
    //if (userIsAdmin()) {
      //$preamble .= '(<a href="'.cscurl('allusers').'">list</a>) <input style = "padding:0px;width:60px" type="text" name="user" value="'.getSoft($_REQUEST, 'user', '').'">';
    //}
    //else {
      $preamble .= optionsHelper($options, 'who');
    
    $preamble .= "</td></tr>";
  }
  
  $preamble .= "<tr><td>".__t("Želite li da pogledate komunikaciju za određeni problem?")."</td><td>";
  $options = array();
  $options[''] = 'Svi problemi';
  foreach ($problems as $problem) {
    if ($problem['type'] == 'code')
      $options[$problem['slug']] = $problem['publicname'];
  }
  $preamble .= optionsHelper($options, 'what') . "</td></tr>";;
  
    $preamble .= "</td></tr><tr><td colspan='2' style='text-align:center'><input style='width: 25%' type='submit' value='".__t('Potvrdi')."'/></tr></td></table></form></div>";
  return $preamble;
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
