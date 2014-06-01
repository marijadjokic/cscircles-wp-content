<?php  
/* 
Plugin Name: My first wordpress plugin 
Plugin URI: http://147.91.205.71/wordpress/
Version: 1.1 
Author: Marija Djokic 
Description: This is my first wordpress plugin.
*/
  
function filter_pagetitle($title)
{
//echo "Python elearning|IMI";
if(!is_single()){
return $title;
}
}
add_filter("wp_title","filter_pagetitle");
?>
