<?php

  //require_once("db-include.php");

add_shortcode('pyUser', 'pyUser');


function pyUser($options, $content) {
  if ( !is_user_logged_in() ) 
    return __t("Morate biti prijavljeni da bi ste videli Vašu korisničku stranu.");
  
  global $wpdb;

  $l = getSoft($_GET, 'level', '');
  $user = wp_get_current_user();
  $uid = $user->ID;
  $students = getStudents();
  $cstudents = count($students);

  $problem_table = $wpdb->prefix . "pb_problems";


$problems = $wpdb->get_results
    ("SELECT * FROM wp_pb_problems, wp_pb_lessons,wp_users 
WHERE facultative = 0 
AND lesson IS NOT NULL 
AND wp_pb_problems.lang='sr'
AND wp_pb_problems.postid=wp_pb_lessons.id
AND wp_pb_lessons.level_id=wp_users.current_lesson_level_id
AND wp_pb_lessons.is_test=0
AND wp_users.ID= $uid
ORDER BY lesson ASC", ARRAY_A);

  $problemsByNumber = array();
  foreach ($problems as $prow) {
    //echo $prow['publicname'];
    $problemsByNumber[$prow['slug']] = $prow;
  }

  
  $allStudents = isSoft($_GET, 'user', 'all');

  $viewingAsStudent = ('' == getSoft($_GET, 'user', ''));

  $allProblems = ($gp == "");
  
 



  /***************** end of header ***************/


  $flexigrids = "";

  $completed_table = $wpdb->prefix . "pb_completed";
  

  $dbparams = array();
  if (getSoft($_GET, 'user', '')!='')
    $dbparams['user'] = $_GET['user'];
 
 
 
//istorija korisnikovog trenutnog nivoa
    $flexigrids .= niceFlex('submittedcode', 
                          $allProblems ? __t("Istorija svih problema") 
                          : sprintf(__t("Istorija problema zadatka %s"),
                                    $_GET['problem']=='console'?'Konzolu':
                                    $problemsByNumber[$_GET['problem']]['publicname']),
                          'entire-my-progress-history', 
                          'dbEntireMyProgressHistory', 
                          $dbparams); 

//prikaz poslednjih sest uradjenih zadataka iz nivoa na kome se korisnik trenutno nalazi  
$recent = "";
$completed = $wpdb->get_results
      ("SELECT * 
FROM wp_pb_completed
WHERE userid=$uid
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
)
ORDER BY time DESC", ARRAY_A);


   $recent .= '<div class="recent"><span class="latest-title">'.__t("Poslednji izvršeni zadaci").":</span>";
    for ($i=0; $i<count($completed) && $i < 6; $i++) {
      $p = getSoft($problemsByNumber, $completed[$i]['problem'], FALSE);
      if ($p !== FALSE) {
         
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


   //prikaz svih lekcija nivoa na kome se korisnik trenutno nalazi 
    
   $overview = '<h2 style="margin-top:5px;text-align:center">'.__t('Lista svih problema').
      (!$allStudents ? ' (sa brojem pokušaja)' : ' (sa pokušajima)').
      '</h2>';
    

    $submissions_table = $wpdb->prefix . "pb_submissions";
    $checkIt = array(); //array from slug to boolean, whether to check the icon
    $showNum = array(); //array from slug to number, number to display beside each
    
    
      $submissions = $wpdb->get_results
        ("SELECT count(1), problem from $submissions_table WHERE userid = $uid GROUP BY problem", ARRAY_A);
      foreach ($submissions as $srow)
        $showNum[$srow['problem']] = $srow['count(1)'];
      
      foreach ($completed as $crow)  // ovo je vec ranije definisano
        $checkIt[$crow['problem']] = TRUE;
    



  
  //prikaz lekcija i izvrsenih zadataka
   $lessons_table = $wpdb->prefix . "pb_lessons";
  $lessons = $wpdb->get_results
    ("SELECT * FROM $lessons_table, wp_users WHERE level_id=current_lesson_level_id AND is_test=0 AND wp_users.id=$uid", ARRAY_A);

  $lessonsByNumber = array();
  foreach ($lessons as $lrow) 
    $lessonsByNumber[$lrow['ordering']] = $lrow;
   

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
        $overview .= "<tr><td class='lessoninfo'>";        $lesson = $prow['lesson'];
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
  

  return "<div class='userpage'>$flexigrids $recent $studentTable $overview</div>";

}

// end of file
