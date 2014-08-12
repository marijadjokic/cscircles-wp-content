<?php
/*
	Code for uninstalling
	Menu Maker plugin.
	
	We hate to go, but we
	have to, because you 
	are forcing us to.
*/

delete_option('id_before_menu');
delete_option('id_before_item');
delete_option('id_after_item');
delete_option('id_before_active_item')
delete_option('id_after_active_item')
delete_option('id_after_menu');
delete_option('id_menumaker_pullpage');
$num = get_option('id_menumaker_number');
for($i=1;$i<=$num;$i++) {
	$menu = "id_menumaker_menu_$i";
	delete_option($menu);
}
delete_option('id_menumaker_number');
delete_option('id_menumaker_maxnum');

?>