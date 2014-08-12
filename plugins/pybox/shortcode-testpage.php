<?php



add_shortcode('pyTest', 'pyTest');


function pyTest($options, $content) {
  if ( !is_user_logged_in() ) 
    return __t("Morate biti prijavljeni da bi ste videli stranicu sa testovima.");
  
 

  echo '<script type="text/javascript">
$(document).ready(function(){ 
var level_id = $("select#level option:selected").attr("value");
var test = $("select#test option:selected").attr("value");
if(level_id != "")changeLevel(level_id,test);
$("select#level").change(function(){
var level_id = $("select#level option:selected").attr("value");
var test = $("select#test option:selected").attr("value");
changeLevel(level_id,test);
});
var test = $("select#test option:selected").attr("value");
if(test != "")changeTestsProblems(test,problems);
$("select#test").change(function(){
var test = $("select#test option:selected").attr("value");
var problems = $("select#problem option:selected").attr("value");
changeTestsProblems(test,problems);
});
});

function changeLevel(level_id,test)
{
$.ajax({
type: "GET",
url: "../wp-content/plugins/pybox/page-test1.php",
data: {"levelid":level_id,"test":test},
success: function(html){
$("select#test").html(html);
},
error: function(jxhr){alert("Sacekajte učitavanje zadataka")}
});
}

function changeTestsProblems(test,problems)
{
$.ajax({
type: "GET",
url: "../wp-content/plugins/pybox/page-test2.php",
data: {"test":test,"problems":problems},
success: function(html){
$("select#problem").html(html);
},
error: function(jxhr){alert("Sacekajte učitavanje zadataka")}
});
}
</script>';

  global $wpdb;  
  $gl = getSoft($_GET, 'level', '');
  

  $students = getStudents();
  $cstudents = count($students);
  
  $problem_table = $wpdb->prefix . "pb_problems";
  
 

if(isset($gl)){
$t="SELECT * FROM wp_pb_problems
WHERE facultative = 0 
AND lesson IS NOT NULL 
AND lang='sr'
AND postid IN
(SELECT id 
FROM wp_pb_lessons
WHERE wp_pb_problems.postid=";
if($_GET['test']!='')$t.=$_GET['test'];
else $t.="wp_pb_lessons.id";
$t.=" AND wp_pb_lessons.level_id=$gl
AND wp_pb_lessons.is_test=1)
ORDER BY lesson ASC";
//echo $t;
  $problems = $wpdb->get_results
    ($t, ARRAY_A);
  
 $tests=$wpdb->get_results
    ("SELECT * FROM wp_pb_lessons
WHERE level_id=$gl
AND is_test=1", ARRAY_A);
}
 
  //proverava da li postoje testovi za selektovani nivo
  $testByNumber=array();
  foreach ($tests as $test) 
    $testByNumber[$test['id']] = $test;

  $gt = getSoft($_GET, "test", "");

  if ($gt != "" && !array_key_exists($gt, $testByNumber)) {
    echo sprintf(__t("Test %s nije pronađen."), $gt);
    return;
  }
   //proverava da li postoje zadaci za selektovani nivo
  $problemsByNumber = array();
  foreach ($problems as $prow) 
    $problemsByNumber[$prow['slug']] = $prow;

  $gp = getSoft($_GET, "problem", "");

  if ($gp != "" && $gp != "console" && !array_key_exists($gp, $problemsByNumber)) {
    echo sprintf(__t("Problem %s nije pronađen."), $gp);
    return;
  }

  if ($cstudents>0) {
    
    $preamble = 
      "<div class='progress-selector'>
       <form method='get'><table style='border:none'><tr><td>".__t("Želite li da pratite rad svog studenta?").'</td><td>';
    $options = array();
    $options['all'] = __t('Prikaz svih mojih studenata');
      foreach ($students as $student) {
        $info = get_userdata($student);
        $options[$info->ID] = userString($info->ID);
      }
    $preamble .= optionsHelper($options, 'user');
    $preamble .="</td><td>Datum: <input type='text' id='datepicker'style='width:80px;'/></td></tr>";

    $levels = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."pb_lesson_level ORDER BY id", ARRAY_A);
    if ($levels === null)
      return array("error", __t("Ne postoji takav nivo."));
   
    $preamble .= "<tr><td>".__t("Želite li da pogledate testove i zadatke za određeni nivo?")."</td><td>";
    $options = array();
    $options[''] = 'Izaberite nivo';
  
    foreach ($levels as $level) {
      $options[$level['id']] = $level['level'];
    }
    $preamble .= optionsHelper($options, 'level') . "</td></tr><tr><td>";

    $preamble .= __t("Želite li prikaz nekog testa?")."</td><td>";
    $options = array();
    $options[''] = __t('Svi testovi');
    $preamble .= optionsHelper($options, 'test')."</td></tr><tr><td>";

    $preamble .= __t("Želite li da pogledate zadatke testova?")."</td><td>";
    $options = array();
    $options[''] = __t('Svi zadaci'); 
    $preamble .= optionsHelper($options, 'problem')."</td></tr>";
    
    $preamble .= "</td></tr><tr><td colspan='2' style='text-align:center'><input style='width: 25%' type='submit' value='".__t('Potvrdi')."'/></tr></td></table></form></div>";
    echo $preamble;
 
  
  $allStudents = isSoft($_GET, 'user', 'all');

  //proverava da li je dobar ID korisnika
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
    echo "<div class='history-prenote'>".sprintf(__t("Pronašli ste istoriju Vaših studenata"), $problem_html) ."</div>";
  }


  /***************** end of header ***************/


  $flexigrids = "";

  $completed_table = $wpdb->prefix . "pb_completed";
  

  if ($allStudents && !$allProblems && $gp != "console") {
     
   
    if($_GET['problem']!="")
    $flexigrids .= niceFlex('perstudent',  sprintf(__t("Rešenja studenta za zadatak %s"), 
                                                   $problemsByNumber[$_GET['problem']]['publicname']),
                            'problem-summary', 'dbProblemSummary', array('p'=>$_GET['problem']));
  }
 


  $dbparams = array();
  if (getSoft($_GET, 'user', '')!='')
    $dbparams['user'] = $_GET['user'];
if (getSoft($_GET, 'test', '')!='')
    $dbparams['test'] = $_GET['test'];
  if (getSoft($_GET, 'problem', '')!='')
    $dbparams['problemhash'] = $_GET['problem'];
 if (getSoft($_GET, 'level', '')!='')
    $dbparams['level'] = $_GET['level'];
 

if(isset($_GET['level']) && $_GET['problem']!="")
  $flexigrids .= niceFlex('submittedcode', 
                          $allProblems ? __t("Istorija svih problema") 
                          : sprintf(__t("Istorija problema zadatka %s"),

                                    $problemsByNumber[$_GET['problem']]['publicname']),
                          'entire-testhistory', 
                          'dbEntireTestHistory', 
                          $dbparams); 



  $recent = "";

  if (!$allStudents) {
    // queries more than 6 in order to fill out progress table of all problems

if(isset($_REQUEST['level'])){
$latest_completed_problems="SELECT * FROM $completed_table WHERE userid = $uid AND problem
IN (
SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = ";
if($_GET['test']!=""){$latest_completed_problems.=$_GET['test'];}
else{$latest_completed_problems.="wp_pb_lessons.id";}
$latest_completed_problems.= " AND wp_pb_lessons.level_id = ". $_REQUEST['level']."
AND wp_pb_lessons.is_test=1
)
ORDER BY time DESC";
$completed = $wpdb->get_results
     ($latest_completed_problems, ARRAY_A);


 $recent .= '<div class="recent"><span class="latest-title">'.__t("Poslednji izvršeni zadaci").":</span>";
    // but for now we only use 6 entries for "most recently completed" section
    for ($i=0; $i<count($completed) && $i < 6; $i++) {
      $p = getSoft($problemsByNumber, $completed[$i]['problem'], FALSE);
      if ($p !== FALSE) {

        if (getSoft($_GET, 'user', '')=='') {
           
            $url = '.?user='.$_GET['user'].'&problem='.$p['slug']; // if viewing someone else, link to problem-specific pagef
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

}
  $submissions_table = $wpdb->prefix . "pb_submissions";

  $studentTable = '';
 if ($allStudents) {
   
    $studentList = getStudentList();
    $where = "WHERE userid in $studentList";
    if (!$allProblems)
      $where .= $wpdb->prepare("and problem LIKE %s", $gp);

    // show number of problems each student completed
    $scompleted = $wpdb->get_results
      ("SELECT userid, count(1) as comps from $completed_table $where AND problem IN 
(SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.id=".$_GET['test']."
AND wp_pb_lessons.level_id = ".$_REQUEST['level']."
AND wp_pb_lessons.is_test=1
)GROUP BY userid", OBJECT_K);
    // show number of submissions by each student for this problem
    $ssubmissions = $wpdb->get_results
      ("SELECT userid, count(1) as subs from $submissions_table $where AND problem IN
(SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.id=".$_GET['test']."
AND wp_pb_lessons.level_id = ".$_REQUEST['level']."
AND wp_pb_lessons.is_test=1
)GROUP BY userid", OBJECT_K);

    $studentTable .= '<div class="history-note">Lista studenata (kliknite na ime)</div>';
    $studentTable .= '<table>';
    if($_GET['problem']!=""){
    foreach (getStudents() as $stu) {
      $studentTable .= '<tr>';
      $studentTable .= '<td>';

      $studentTable .= '<a class="open-same-window" href="?user=' . $stu .'&level='.$_GET['level'].'&test='.$_GET['test'].'&problem=' . $gp . '">';
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
}
else{
  $testbylevel = $wpdb->get_results
      ("SELECT * FROM wp_pb_lessons WHERE level_id=".$_GET['level']." AND is_test=1", ARRAY_A);
    
foreach (getStudents() as $stu) {
      $studentTable .= '<tr>';
      $studentTable .= '<td>';
      if($_GET['test']==""){
      $studentTable .= '<a class="open-same-window" href="?user=' . $stu .'&level='.$_GET['level'].'&test=&problem=">';
      $studentTable .= userString($stu);
      $studentTable .= '</a></td>';}
else{
  $studentTable .= '<a class="open-same-window" href="?user=' . $stu .'&level='.$_GET['level'].'&test='.$_GET['test'].'&problem=">';
      $studentTable .= userString($stu);
      $studentTable .= '</a></td>';
}
      $studentTable .= '<td style="padding:10px;">';
     if($_GET['test']==""){
      foreach($testbylevel as $test)
      {

          $studentTable .="<a href=".get_page_link($test['id'])." target='_blank'>".$test['ordering'].":".$test['title']."</a> &#8658 <b>Poeni/18</b> &nbsp; &nbsp;";
      }
}
else 
     {
 foreach($testbylevel as $test)
      {
      if($test['id']==$_GET['test'])
          $studentTable .="<a href=".get_page_link($test['id'])." target='_blank'>".$test['ordering'].":".$test['title']."</a> &#8658 <b>Poeni/18</b> &nbsp; &nbsp;";
      }

}

      $studentTable .= '</td>';
      $studentTable .= '</tr>';
    }
}
    $studentTable .= '</table>';

  }


  $lessons_table = $wpdb->prefix . "pb_lessons";
  $lessons = $wpdb->get_results
    ("SELECT * FROM $lessons_table WHERE is_test=1", ARRAY_A);

  $lessonsByNumber = array();
  foreach ($lessons as $lrow) 
    $lessonsByNumber[$lrow['ordering']] = $lrow;

  $overview = '';
  if ($allProblems || !$allStudents) {
    
   
if(isset($_REQUEST['level'])){
 $overview = '<h2 style="margin-top:5px;text-align:center">'.__t('Lista svih problema').
      (!$allStudents ? ' (sa brojem pokušaja)' : ' (sa brojem urađenih zadataka)').
      '</h2>';
   }
 
   
    $checkIt = array(); //array from slug to boolean, whether to check the icon
    $showNum = array(); //array from slug to number, number to display beside each
    
    
    if ($allStudents) {
   
        $completed = $wpdb->get_results
          ("SELECT count(userid), problem from $completed_table WHERE userid in $studentList GROUP BY problem", ARRAY_A);
      

      foreach ($completed as $crow) 
        $showNum[$crow['problem']] = $crow['count(userid)'];
    }
    else {
     
      if(isset($_REQUEST['level'])){
           
      $studentList = getStudentList();
$completed = $wpdb->get_results
            ("SELECT count(userid), problem from $completed_table WHERE userid=$uid AND problem IN(
SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.level_id =". $_REQUEST['level']."
AND wp_pb_lessons.is_test=1
) GROUP BY problem", ARRAY_A);



      foreach ($completed as $crow) 
        $showNum[$crow['problem']] = $crow['count(userid)'];
    
         $submissions = $wpdb->get_results
        ("SELECT count(1), problem from $submissions_table WHERE userid = $uid GROUP BY problem", ARRAY_A);
      foreach ($submissions as $srow)
        $showNum[$srow['problem']] = $srow['count(1)'];
      
      foreach ($completed as $crow)  // this was queried earlier
        $checkIt[$crow['problem']] = TRUE;
    }

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
      
    
      else
        $url = $prow['url'];
      
      $overview .= '<a class="open-same-window" ';
      if ($url != null) $overview .= ' href="' . $url . '" ';
      $overview .= '>';

      $overview .= '<table class="history-tablette"><tr class="history-tablette-top"><td>';
      
      $overview .= '<img style="margin:-10px 0px" title="' . $prow['publicname'] . '" src="' . UFILES .
        (isSoft($checkIt, $prow['slug'], TRUE) ? 'checked' : 'icon') . '.png"/>';


      $overview .= '</a></td></tr><tr class="history-tablette-bottom"><td>';
      
      $overview .= (array_key_exists($prow['slug'], $showNum) ? 
                    $showNum[$prow['slug']]
                    : '&nbsp;'
                    );

      $overview .= '</td></tr></table></a>';
    }
    
    $overview .= '</table>';

   }
  }

  return "<div class='userpage'>$flexigrids $recent $studentTable $overview</div>";

}
}


// end of file
