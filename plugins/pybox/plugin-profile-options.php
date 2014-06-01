<?php

function user_pb_options_fields( $user ) { 

  /*  $checkd = "";
  if (get_the_author_meta( 'pbplain', $user->ID )=='true') $checkd = ' checked="yes"  ';
  $oh = "";
  if (get_the_author_meta( 'pboldhistory', $user->ID )=='true') $oh = ' checked="yes"  ';*/
  $nocc = "";
  if (get_the_author_meta( 'pbnocc', $user->ID )=='true') $nocc = ' checked="yes"  ';
  $optout = "";
  if (get_the_author_meta( 'pboptout', $user->ID )=='true') $optout = ' checked="yes"  ';
  $guru_login = get_the_author_meta( 'pbguru', $user->ID ); 

  //echo $guru_login;
global $wpdb;
  $gurucheck = '';
  if ($guru_login != '') {
   
    $guruid = $wpdb->get_var($wpdb->prepare('SELECT ID from '.$wpdb->prefix.'users WHERE user_login = %s', $guru_login));
    if ($guruid === NULL) 
      $gurucheck = 
	"<b>".sprintf(__t("The username %s does not exist."), "<code>" . htmlspecialchars($guru_login) . "</code>")."</b>";
    else
      $gurucheck = 
	sprintf(__t("%s exists! They are your guru."), "<code>" . htmlspecialchars($guru_login) . "</code> ");
  }
  else 
    $gurucheck = __t("Enter the username of your guru. After you press <i>Update profile</i> we'll see if they exist.");

$gurulist=array();
$mygur="";


$gurulist = $wpdb->get_results('SELECT user_id, display_name FROM wp_user2role2object_rs, wp_users WHERE role_name="administrator" AND user_id=id', ARRAY_A);

//echo $gurulist;
//echo "admin count".count($gurulist);

//modifided by Marija Djokic

$myguru = $wpdb->get_var($wpdb->prepare('SELECT meta_value FROM wp_usermeta WHERE meta_key="pbguru" AND user_ID="'.$user->ID.'"'));
//echo "my guru: ".$myguru;


if($myguru!=''){// ako postoji mentor
  ?>
<h3>Computer Science Circles Options</h3>
     <table class="form-table">
     <tr><th><label for="pbguru"><?php echo __t('Vaš mentor'); ?></label></th>
				<td>
<?php
   echo '<select name="pbguru">';
foreach($gurulist as $guru){
    
     if($myguru==$guru['display_name']) echo '<option value="'.$guru['display_name'].'"selected="selected">'.$guru['display_name'].'</option>';
    else echo '<option value="'.$guru['display_name'].'">'.$guru['display_name'].'</option>';
   }
   echo '<option value="">Samostalno učenje</option>';
echo '</select>';
  echo $guruinput . __t(" Mentoru možete postavljati direktna pitanja, a on će moći da prati Vaš progres. Odabir opcije <i>Samostalno učenje</i> označava da ne želite mentora.");
}

else {// ako ne postoji mentor
?>
<h3>Computer Science Circles Options</h3>
     <table class="form-table">
     <tr><th><label for="pbguru"><?php echo __t('Vaš mentor'); ?></label></th>
				<td>
<?php
echo '<select name="pbguru">';
   echo '<option value="">Samostalno učenje</option>';
foreach($gurulist as $guru){
  echo '<option value="'.$guru['display_name'].'">'.$guru['display_name'].'</option>';
}
echo '</select>';
echo $guruinput . __t(" Mentoru možete postavljati direktna pitanja, a on će moći da prati Vaš progres. Odabir opcije <i>Samostalno učenje</i> označava da ne želite mentora.");
}


 //$guruinput = '<input type="text" name="pbguru" id="pbguru" value="'. htmlspecialchars($guru_login) . '"> ' . $gurucheck .'<br/>';

//
  

		    
?>
	 </input>
       </td>
       </tr>
       <tr>
	     <th><label for="pbnocc"><?php echo __t("Ne šalji kopiju poruka");?></label></th>
       <td>
     <input type="checkbox" name="pbnocc" id="pbnocc"<?php echo $nocc ." > ".
     __t("Čekiranjem ovog polja kopije Vaših poslatih poruka neće biće prosleđene na Vašu elektronsku adresu."); ?></input>
       </td>
       </tr>
      <!-- <tr>
														  <th><label for="pboptout"><?php echo __t("Opt Out of Mass Emails"); ?></label></th>

       <td>
     <input type="checkbox" name="pboptout" id="pboptout"<?php echo $optout . " > ".
 __t("(default: unchecked) If checked, you will not receive announcements from CS Circles. They are pretty infrequent, about once per year.");?></input>
       </td>
       </tr> -->
       </table>
    <?php }

add_action( 'show_user_profile', 'user_pb_options_fields' );
add_action( 'edit_user_profile', 'user_pb_options_fields' );

// store pb_options
function user_pb_options_fields_save( $user_id ) {
  //pyboxlog('save' . print_r($_POST, TRUE));
  if ( !current_user_can( 'edit_user', $user_id ) )
    return false;

  /*  update_user_meta( $user_id, 'pbplain', ($_POST['pbplain']=='on')?'true':'false' );
   update_user_meta( $user_id, 'pboldhistory', ($_POST['pboldhistory']=='on')?'true':'false' );*/
  update_user_meta( $user_id, 'pbguru', ($_POST['pbguru']));
  update_user_meta( $user_id, 'pbnocc', ($_POST['pbnocc']=='on')?'true':'false' );
  update_user_meta( $user_id, 'pboptout', ($_POST['pboptout']=='on')?'true':'false' );
}
add_action( 'personal_options_update', 'user_pb_options_fields_save' );
add_action( 'edit_user_profile_update', 'user_pb_options_fields_save' );

// end of file
