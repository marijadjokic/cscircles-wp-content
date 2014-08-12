<?php

session_start();
$_SESSION['problem']=$_GET['problem'];

//require_once("db-include.php");

add_shortcode('pyStudent', 'pyStudent');


function pyStudent($options, $content) {
  if ( !is_user_logged_in() ) 
    return __t("Morate biti prijavljeni da bi ste videli stranicu za progres studenata.");
  
  if ( !userIsAdmin()) 
    return __t("<br/><br/>Nemate pravo pristupa stranici <b>Progres studenata</b>.");
  
  global $wpdb; 
  
  
  $allStudents = isSoft($_GET, 'user', 'all');
  $gl = getSoft($_GET, 'level', '');
  $gp = getSoft($_GET, "problem", "");
  $date = $_GET['datepicker'];
  
  $user = wp_get_current_user();
  $uid = $user->ID;
  $students = getStudentsByDate(FALSE, $date);     
  $cstudents = count($students);
  
 

  echo '<script type="text/javascript">
function validate(){
var level=document.getElementById("level").value;
if(level=="")alert("Morate odabrati nivo!");
else document.progress.submit();
}

$(document).ready(function(){ 
var level_id = $("select#level option:selected").attr("value");;
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
$("select#problem").html(html);
},
error: function(jxhr){alert("Sacekajte učitavanje zadataka")}
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
url: "../wp-content/plugins/pybox/page-test3.php",
data: {"date":date},
success: function(html){
$("select#user").html(html);
},
error: function(jxhr){alert("Sacekajte učitavanje studenata")}
});
}

</script>';


//proveravamo da li postoji dati korisnik, nivo ili problem

  $level_table=$wpdb->prefix."pb_lesson_level";
  $levels = $wpdb->get_results("SELECT * FROM $level_table ORDER BY id", ARRAY_A);
  $levelsByNumber = array();
  foreach ($levels as $lrow) {
    //echo $lrow['id'];
    $levelsByNumber[$lrow['id']] = $lrow;
    }
  

  if ($gl!= "" && !array_key_exists($gl, $levelsByNumber)) {
    echo sprintf(__t("<b>Nivo %s ne postoji!</b>"), $gl);
    return;
  } 


 
  $problem_table = $wpdb->prefix . "pb_problems";
$p="SELECT * FROM $problem_table, wp_pb_lessons 
WHERE facultative = 0 
AND lesson IS NOT NULL 
AND wp_pb_problems.lang='sr'
AND wp_pb_problems.postid=wp_pb_lessons.id";
if($_GET['level']!=""){$p.=" AND wp_pb_lessons.level_id=".$_GET['level']." AND wp_pb_lessons.is_test=0
ORDER BY lesson ASC";}
else{$p.=" AND wp_pb_lessons.is_test=0
ORDER BY lesson ASC";}

  //echo $p;
  $problems = $wpdb->get_results
    ($p, ARRAY_A);
  


  $problemsByNumber = array();
  foreach ($problems as $prow) {
    //echo $prow['publicname'];
    $problemsByNumber[$prow['slug']] = $prow;
    }
  

  if ($gp != "" && !array_key_exists($gp, $problemsByNumber)) {
    echo sprintf(__t("<b>Problem %s nije pronađen!</b>"), $gp);
    return;
  }

   
  if ($cstudents>0){
    
    $preamble = 
      "<div class='progress-selector'>
       <form method='get' id='progress'><table style='border:none'><tr><td>".__t("Želite li da pratite rad svog studenta?").'</td><td>';
    $options = array();
    $options['all'] = __t('Prikaz svih mojih studenata');
    
  
    foreach ($students as $student) {
        $info = get_userdata($student);
        $options[$info->ID] = userString($info->ID);
       }
     
     $preamble .= optionsHelper($options, 'user');
     $preamble .="</td><td>Datum: <input type='text' name='datepicker' id='datepicker'style='width:80px;' value='".$_GET['datepicker']."'/></td></tr>";

 
     $preamble .= "<tr><td>".__t("Želite li da pogledate komunikaciju za određeni nivo?")."</td><td>";
     $options = array();
     $options[''] = 'Izaberite nivo';
  
     foreach ($levels as $level) {
        $options[$level['id']] = $level['level'];
     }
     $preamble .= optionsHelper($options, 'level') . "</td></tr><tr><td>";

     $preamble .= __t("Zelite li prikaz rešenja nekog problema?");
     $options = array();
     $options[''] = __t('Svi problemi');
    
     foreach ($problems as $problem) {
      //popunjava ajax
      }
     $preamble .= '</td><td>';
     $preamble .= optionsHelper($options, 'problem').'</td></tr>';
    
     $preamble .= "<tr><td colspan='3' style='text-align:center'><input style='width: 25%' type='submit' value='".__t('Potvrdi')."' onclick='validate();'/></tr></td></table></form></div>";
    
     echo $preamble;
  
  

  $viewingAsStudent = ('' == getSoft($_GET, 'user', ''));//mozda se brise

  $allProblems = ($gp == "");
    if ($allProblems)
      $problem_html = "svih problema";
    else 
      $problem_html = 
        "<a href='".$problemsByNumber[$gp]['url']."'>".
        $problemsByNumber[$gp]['publicname'] ."</a>";
  

  if (!$allStudents && array_key_exists('user', $_GET) && $_GET['user'] != '' && $_GET['level']!='') {
    if (!is_numeric($_GET['user']))
      return __t("<b>ID korisnika mora biti broj.</b>");
    $getuid = (int)$_GET['user'];
    if (userIsAdmin() || userIsAssistant()) {
      if (get_userdata($getuid) === FALSE)
	return __t("<b>Pogrešan ID korisnika.</b>");
    }
    else {
      if (!in_array($getuid, $students))
	return __t("<b>Pogrešan ID korisnika.</b>");
    }
    $uid = $getuid;
    $user = get_userdata($uid);
    echo "<div class='history-prenote'>".sprintf(__t("Pronašli ste istoriju %s studenta "), $problem_html) . userString($uid) . '</div>';
  }
  if ($allStudents && $_GET['level']!='') {
    echo "<div class='history-prenote'>".sprintf(__t("Pronašli ste istoriju %s Vaših studenata"), $problem_html) ."</div>";
  }


  /***************** end of header ***************/


  $flexigrids = "";

  if ($allStudents && !$allProblems && $gp != "console") {
    $flexigrids .= niceFlex('perstudent',  sprintf(__t("Rešenja studenta za zadatak %s"), 
                                                   $problemsByNumber[$_GET['problem']]['publicname']),
                            'problem-summary', 'dbProblemSummary', array('p'=>$_GET['problem'],'d'=>$_GET['datepicker']));
  }
 



  $dbparams = array();
  if (getSoft($_GET, 'user', '')!='')
    $dbparams['user'] = $_GET['user'];
  if (getSoft($_GET, 'problem', '')!='')
    $dbparams['problemhash'] = $_GET['problem'];
 if (getSoft($_GET, 'level', '')!='')
    $dbparams['level'] = $_GET['level'];
  $dbparams['datepicker']=$_GET['datepicker'];

//prikaz istorije
if($dbparams['level']!="")
    $flexigrids .= niceFlex('submittedcode', 
                          $allProblems ? __t("Istorija svih problema") 
                          : sprintf(__t("Istorija problema zadatka %s"),
                                    $_GET['problem']=='console'?'Konzolu':
                                    $problemsByNumber[$_GET['problem']]['publicname']),
                          'entire-students-history', 
                          'dbEntireStudentHistory', 
                          $dbparams);   
  

  //prikaz poslednjih izvršenih zadataka datog nivoa i korisnika
  $recent = "";
  $completed_table = $wpdb->prefix . "pb_completed";


  if (!$allStudents && $gl!="") {
    // queries more than 6 in order to fill out progress table of all problems


$completed = $wpdb->get_results
     ("SELECT * FROM $completed_table WHERE userid = $uid AND problem
IN (
SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.level_id = ". $_REQUEST['level']."
AND wp_pb_lessons.is_test=0
)
ORDER BY time DESC", ARRAY_A);


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



  $submissions_table = $wpdb->prefix . "pb_submissions";
  $studentTable = '';

  if ($allStudents && $_GET['level']!='') {
   
    $studentList = getStudentListByDate(false, $date);
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
AND wp_pb_lessons.level_id = ".$_REQUEST['level']."
AND wp_pb_lessons.is_test=0
)GROUP BY userid", OBJECT_K);
    // show number of submissions by each student for this problem
    $submissions = $wpdb->get_results
      ("SELECT userid, count(1) as subs from $submissions_table $where AND problem IN
(SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.level_id = ".$_REQUEST['level']."
AND wp_pb_lessons.is_test=0
)GROUP BY userid", OBJECT_K);

    $studentTable .= '<div class="history-note">Lista studenata (kliknite na ime)</div>';
    $studentTable .= '<table>';

    foreach (getStudentsByDate(FALSE, $date) as $stu) {
      $studentTable .= '<tr>';
      $studentTable .= '<td>';

      $studentTable .= '<a class="open-same-window" href="?user=' . $stu .'&datepicker='.$date.'&level='.$gl.'&problem=' . $gp . '">';
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
      $studentTable .= (array_key_exists($stu, $submissions) ? ($submissions[$stu]->subs) : 0) . ' pokušaja';
      $studentTable .= '</td>';
      $studentTable .= '</tr>';
    }
    $studentTable .= '</table>';
  }


  $lessons_table = $wpdb->prefix . "pb_lessons";
  $lessons = $wpdb->get_results
    ("SELECT * FROM $lessons_table WHERE level_id=$gl AND is_test=0", ARRAY_A);

  $lessonsByNumber = array();
  foreach ($lessons as $lrow) 
    $lessonsByNumber[$lrow['ordering']] = $lrow;

  $overview = '';
//prikaz lekcija sa brojem urađenih zadataka/pokušaja
  if (($allProblems || !$allStudents) && $_GET['level']!="") {
    
 $overview = '<h2 style="margin-top:5px;text-align:center">'.__t('Lista svih problema').
      (!$allStudents ? ' (sa brojem pokušaja)' : ' (sa brojem urađenih zadataka)').
      '</h2>';
  
    if (!$viewingAsStudent) {
      $overview .= "<div style='text-align:center'>Kliknite na <img style='height:1em,width:1em' src='".UFILES."/icon.png'></div>";
    }

    
    $checkIt = array(); //array from slug to boolean, whether to check the icon
    $showNum = array(); //array from slug to number, number to display beside each
    
    
    if ($allStudents) {
        $studentList = getStudentListByDate(false, $date);
        $completed = $wpdb->get_results
          ("SELECT count(userid), problem from $completed_table WHERE userid in $studentList AND problem IN(
SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.level_id =". $_REQUEST['level']."
AND wp_pb_lessons.is_test=0
) GROUP BY problem", ARRAY_A);
      

      foreach ($completed as $crow) 
        $showNum[$crow['problem']] = $crow['count(userid)'];
    }
    else {
    
    
         
$completed = $wpdb->get_results
            ("SELECT count(userid), problem from $completed_table WHERE userid= $uid AND problem IN(
SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.level_id =". $_REQUEST['level']."
AND wp_pb_lessons.is_test=0
) GROUP BY problem", ARRAY_A);
      

      foreach ($completed as $crow) 
        $showNum[$crow['problem']] = $crow['count(userid)'];
      
      $submissions = $wpdb->get_results
        ("SELECT count(1), problem from $submissions_table WHERE userid = $uid AND problem IN(
SELECT slug 
FROM wp_pb_problems, wp_pb_lessons, wp_users
WHERE facultative =0
AND lesson IS NOT NULL 
AND wp_pb_problems.lang = 'sr'
AND wp_pb_problems.postid = wp_pb_lessons.id
AND wp_pb_lessons.level_id =". $_REQUEST['level']."
AND wp_pb_lessons.is_test=0
) GROUP BY problem", ARRAY_A);

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
      //echo $prow['slug'];
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
        $url = '.?user='.$_GET['user'].'&datepicker='.$_GET['datepicker'].'&level='.$_GET['level'].'&problem='.$prow['slug']; 
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

  return "<div class='userpage'>$flexigrids $recent $studentTable $overview</div>";

}
else 
 {
   return __t("<br/><br/>Trenutno nemate dodeljenih studenta.");
 }
}

// end of file
