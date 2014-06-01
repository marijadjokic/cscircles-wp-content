<?php

  //require_once("db-include.php");


add_shortcode('pyUser', 'pyUser');


function pyUser($options, $content) {
  if ( !is_user_logged_in() ) 
    return __t("Morate biti prijavljeni da bi ste videli Vašu korisničku stranu.");
  
  global $wpdb;
  
  $pagetitle=get_the_title();
  
  
  $user = wp_get_current_user();
  $uid = $user->ID;
  //added by Marija Djokic  
  $useradminid=$user->ID;
  //
  $students = getStudents();
  $cstudents = count($students);

  $problem_table = $wpdb->prefix . "pb_problems";
  //modifided by Marija Djokic
  $problems = $wpdb->get_results
    ("SELECT * FROM $problem_table WHERE facultative = 0 AND lang='sr' AND lesson IS NOT NULL ORDER BY lesson ASC, boxid ASC", ARRAY_A);
  //
 
  $problemsByNumber = array();
  foreach ($problems as $prow) 
    //echo $prow['publicname'];
    $problemsByNumber[$prow['slug']] = $prow;

  $gp = getSoft($_GET, "problem", "");
  if ($gp != "" && $gp != "console" && !array_key_exists($gp, $problemsByNumber)) {
    echo sprintf(__t("Problem %s nije pronađen"), $gp);
    return;
  }
//modifided by Marija Djokic
  if ((userIsAdmin() || userIsAssistant() || $cstudents>0) && $pagetitle=="Progres studenata") {
   
    $preamble = 
      "<div class='progress-selector'>
       <form method='get'><table style='border:none'><tr><td>".sprintf(__t("Želite li da pratite rad svog studenta? (imate %s)"), $cstudents).'</td><td>';
    $options = array();
    //$options[''] = __t('Prikaži svoje vežbe');
    $options['all'] = __t('Prikaz svih mojih studenata');
    
    if (userIsAdmin()) {
      foreach ($students as $student) {
        $info = get_userdata($student);
        $options[$info->ID] = userString($info->ID);
      }
    }
    
   // if (userIsAdmin()) {
      //$preamble .= 'blank: you; "all": all; id#: user (<a href="'.cscurl('allusers').'">list</a>) <input style = "padding:0px;width:60px" type="text" name="user" value="'.getSoft($_REQUEST, 'user', '').'">';
    //}
    //else {
      $preamble .= optionsHelper($options, 'user');
    //}
    $preamble .= '</td></tr><tr><td>';
    $preamble .= __t("Zelite li prikaz rešenja nekog problema?");
    $options = array();
    $options[''] = __t('Prikaži sve');
    //$options['console'] = __t('Konzola');
    foreach ($problems as $problem) {
      //if ($problem['type'] == 'code')
	$options[$problem['slug']] = $problem['publicname'];
    }
    $preamble .= '</td><td>';
    $preamble .= optionsHelper($options, 'problem');
    
    $preamble .= "</td></tr><tr><td colspan='2' style='text-align:center'><input style='width: 25%' type='submit' value='".__t('Potvrdi')."'/></tr></td></table></form></div>";
    echo $preamble;
  }
  
  $allStudents = isSoft($_GET, 'user', 'all');

  $viewingAsStudent = ('' == getSoft($_GET, 'user', ''));

  $allProblems = ($gp == "");

  if (!$viewingAsStudent) {
    if ($allProblems)
      $problem_html = "svih problema";
    else if ($gp=='console') 
      $problem_html = "Konzola";
    else 
      $problem_html = 
        "<a href='".$problemsByNumber[$gp]['url']."'>".
        $problemsByNumber[$gp]['publicname'] ."</a>";
  }

  if (!$allStudents && array_key_exists('user', $_GET) && $_GET['user'] != '') {
    if (!is_numeric($_GET['user']))
      return __t("ID korisnika mora biti broj.");
    $getuid = (int)$_GET['user'];
    if (userIsAdmin() || userIsAssistant()) {
      if (get_userdata($getuid) === FALSE)
	return __t("Pogrešan ID korisnika.");
    }
    else {
      if (!in_array($getuid, $students))
	return __t("Pogrešan ID korisnika.");
    }
    $uid = $getuid;
    $user = get_userdata($uid);
    echo "<div class='history-prenote'>".sprintf(__t("Pronašli ste istoriju %s studenta "), $problem_html) . userString($uid) . '</div>';
  }
  if ($allStudents) {
    echo "<div class='history-prenote'>".sprintf(__t("Pronašli ste istoriju %s Vaših studenata"), $problem_html) ."</div>";
  }


  /***************** end of header ***************/


  $flexigrids = "";

  $completed_table = $wpdb->prefix . "pb_completed";
  

  if ($allStudents && !$allProblems && $gp != "console") {
     
  

    $flexigrids .= niceFlex('perstudent',  sprintf(__t("Rešenja studenta za zadatak %s"), 
                                                   $problemsByNumber[$_GET['problem']]['publicname']),
                            'problem-summary', 'dbProblemSummary', array('p'=>$_GET['problem']));
  }
 



  $dbparams = array();
  if (getSoft($_GET, 'user', '')!='')
    $dbparams['user'] = $_GET['user'];
  if (getSoft($_GET, 'problem', '')!='')
    $dbparams['problemhash'] = $_GET['problem'];
  
  $flexigrids .= niceFlex('submittedcode', 
                          $allProblems ? __t("Istorija svih problema") 
                          : sprintf(__t("Istorija problema zadatka %s"),
                                    $_GET['problem']=='console'?'Konzolu':
                                    $problemsByNumber[$_GET['problem']]['publicname']),
                          'entire-history', 
                          'dbEntireHistory', 
                          $dbparams); 
//modifided by Marija Djokic  
  $recent = "";
  if (!$allStudents) {
    // queries more than 6 in order to fill out progress table of all problems
if($pagetitle=="Progres studenata")
{
if(userIsAdmin()){
if($uid==$useradminid){
$studentList=getStudentList();
$completed = $wpdb->get_results
      ("SELECT * FROM $completed_table WHERE userid in $studentList ORDER BY time DESC", ARRAY_A);
}
else{
$completed = $wpdb->get_results
      ("SELECT * FROM $completed_table WHERE userid=$uid ORDER BY time DESC", ARRAY_A);
}
}
}
else
{
 $completed = $wpdb->get_results
      ("SELECT * FROM $completed_table WHERE userid=$uid ORDER BY time DESC", ARRAY_A);
}
   $recent .= '<div class="recent"><span class="latest-title">'.__t("Poslednji izvršeni zadaci").":</span>";
    // but for now we only use 6 entries for "most recently completed" section
    for ($i=0; $i<count($completed) && $i < 6; $i++) {
      $p = getSoft($problemsByNumber, $completed[$i]['problem'], FALSE);
      if ($p !== FALSE) {
        if (getSoft($_GET, 'user', '')!='') {
          if ($problemsByNumber[$p['slug']]['type'] == 'code')
            $url = '.?user='.$_GET['user'].'&problem='.$p['slug']; // if viewing someone else, link to problem-specific page
          else
            $url = null;
        }

        else
          $url = $p['url'];

        $recent .= ' <a class="open-same-window problem-completed" ';
        if ($url != null)
          
          $recent .= ' href="' . $url . '" ';
        $recent .= ' title="'. $completed[$i]['time'] .'">' 
            . $p['publicname'] . '</a>';
      }
      else
	$recent .= '['.$completed[$i]['problem'].']';
    }
    $recent .= '</div>';
  }


  $submissions_table = $wpdb->prefix . "pb_submissions";

  $studentTable = '';

  
   

 if ($allStudents && userIsAdmin()) {
   
    $studentList = getStudentList();
    $where = "WHERE userid in $studentList";
    if (!$allProblems)
      $where .= $wpdb->prepare("and problem LIKE %s", $gp);

    // show number of problems each student completed
    $scompleted = $wpdb->get_results
      ("SELECT userid, count(1) as comps from $completed_table $where GROUP BY userid", OBJECT_K);
    
    // show number of submissions by each student for this problem
    $ssubmissions = $wpdb->get_results
      ("SELECT userid, count(1) as subs from $submissions_table $where GROUP BY userid", OBJECT_K);

    $studentTable .= '<div class="history-note">Lista studenata (kliknite na ime)</div>';
    $studentTable .= '<table>';

    foreach (getStudents() as $stu) {
      $studentTable .= '<tr>';
      $studentTable .= '<td>';
      $studentTable .= '<a class="open-same-window" href="?user=' . $stu .'&problem=' . $gp . '">';
      $studentTable .= userString($stu);
      $studentTable .= '</a></td>';
      $studentTable .= '<td>';
      if ($allProblems)
        $studentTable .= (array_key_exists($stu, $scompleted) ? ($scompleted[$stu]->comps) : 0) . ' uradjenih';
      else
        $studentTable .= '<img src="' . UFILES .
 (array_key_exists($stu, $scompleted) ? 'checked' : 'icon') . '.png"/>';

      $studentTable .= '</td>';
      $studentTable .= '<td>';
      $studentTable .= (array_key_exists($stu, $ssubmissions) ? ($ssubmissions[$stu]->subs) : 0) . ' pokušaja';
      $studentTable .= '</td>';
      $studentTable .= '</tr>';
    }
    $studentTable .= '</table>';
  }


  $lessons_table = $wpdb->prefix . "pb_lessons";
  $lessons = $wpdb->get_results
    ("SELECT * FROM $lessons_table", ARRAY_A);

  $lessonsByNumber = array();
  foreach ($lessons as $lrow) 
    $lessonsByNumber[$lrow['ordering']] = $lrow;

  $overview = '';
  if ($allProblems || !$allStudents) {
    
    $overview = '<h2 style="margin-top:5px;text-align:center">'.__t('Lista svih problema').
      (!$allStudents ? ' (sa brojem urađenih zadataka)' : ' (sa pokušajima)').
      '</h2>';
    
    if (!$viewingAsStudent) {
      $overview .= "<div style='text-align:center'>Kliknite na <img style='height:1em,width:1em' src='".UFILES."/icon.png'></div>";
    }


    
    $checkIt = array(); //array from slug to boolean, whether to check the icon
    $showNum = array(); //array from slug to number, number to display beside each
    
    
    if ($allStudents) {
      if (userIsAdmin() || userIsAssistant())  
        $completed = $wpdb->get_results
          ("SELECT count(userid), problem from $completed_table WHERE userid in $studentList GROUP BY problem", ARRAY_A);
   
      else {
       
        $studentList = getStudentList();
        $completed = $wpdb->get_results
          ("SELECT count(userid), problem from $completed_table WHERE userid in $studentList GROUP BY problem", ARRAY_A);
      }

      foreach ($completed as $crow) 
        $showNum[$crow['problem']] = $crow['count(userid)'];
    }
    else {
      if($pagetitle=="Progres studenata"){
            if($useradminid==$uid){
    
      $studentList = getStudentList();
$completed = $wpdb->get_results
            ("SELECT count(userid), problem from $completed_table WHERE userid in $studentList GROUP BY problem", ARRAY_A);
      

      foreach ($completed as $crow) 
        $showNum[$crow['problem']] = $crow['count(userid)'];
            }
            else{
                  $submissions = $wpdb->get_results
        ("SELECT count(1), problem from $submissions_table WHERE userid = $uid GROUP BY problem", ARRAY_A);
      foreach ($submissions as $srow)
        $showNum[$srow['problem']] = $srow['count(1)'];
       foreach ($completed as $crow)  // this was queried earlier
        $checkIt[$crow['problem']] = TRUE;
            }
}
else{
      $submissions = $wpdb->get_results
        ("SELECT count(1), problem from $submissions_table WHERE userid = $uid GROUP BY problem", ARRAY_A);
      foreach ($submissions as $srow)
        $showNum[$srow['problem']] = $srow['count(1)'];
      
      foreach ($completed as $crow)  // this was queried earlier
        $checkIt[$crow['problem']] = TRUE;
    }
}
//
    
    $overview .= '<table style="width:auto;border:none;margin:0px auto;">';
    
    $lesson = -1;
    $lrow = NULL;
    $llink = "";
    $firstloop = true;
    foreach ($problems as $prow) {
      if ($prow['lesson'] != $lesson) {
        if (!$firstloop)
          $overview .= "</td></tr>\n";
        $firstloop = false;
        $overview .= "<tr><td class='lessoninfo'>";
        $lesson = $prow['lesson'];
        $lrow = $lessonsByNumber[$lesson];
        $overview .= '<a class="open-same-window" href="';
        $llink = get_page_link($lrow['id']);
        $overview .= $llink;
        $overview .= '">';
        $overview .= $lrow['number'] . ": " . $lrow['title'];
        $overview .= '</a></td><td>';
      }
      
      if (!$viewingAsStudent) {
  
        // drill-down link
        $url = '.?user='.$_GET['user'].'&problem='.$prow['slug']; 
      }
      else
        $url = $prow['url'];
      
      $overview .= '<a class="open-same-window" ';
      if ($url != null) $overview .= ' href="' . $url . '" ';
      $overview .= '>';

      $overview .= '<table class="history-tablette"><tr class="history-tablette-top"><td>';
      
      $overview .= '<img style="margin:-10px 0px" title="' . $prow['publicname'] . '" src="' . UFILES .
        (isSoft($checkIt, $prow['slug'], TRUE) ? 'checked' : 'icon') . '.png"/>';


      $overview .= '</a></td></tr><tr class="history-tablette-bottom"><td>';

      /*      $overview .= '<a class="open-same-window" ';
      if ($url != null) $overview .= ' href="' . $url . '" ';
      $overview .= '>';*/
      
      $overview .= (array_key_exists($prow['slug'], $showNum) ? 
                    $showNum[$prow['slug']]
                    : '&nbsp;'
                    );

      $overview .= '</td></tr></table></a>';
    }
    
    $overview .= '</table>';
  }

  return "<div class='userpage'>$flexigrids $recent $studentTable $overview</div>";

}

// end of file
