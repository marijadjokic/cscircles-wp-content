<?php

require_once("include-to-load-wp.php");

$page = NULL;
if (array_key_exists("page", $_GET)) {
  $page = get_post($_GET["page"]);
 }
 else if (array_key_exists("slug", $_GET)) {
   $page = get_page_by_path($_GET["slug"]);
 }

if ($page == NULL)
  { echo "Stranica nije pronadjena"; return; }

$content = $page->post_content;

$content = 
  preg_replace("_(solver|answer|right|wrong)\\s*=\\s*" 
               ."(" . '"'.'(\\\\.|""|[^\\\\"])*'.'"(?!")' 
               ."|" . "'"."(\\\\.|''|[^\\\\'])*"."'(?!')"
               .")_s",
               "$1=REDACTED",
               $content);

?>
<html>
U nastavku je dat izvorni kod za stranicu <b><a href="<?php 
echo get_permalink($page->ID); 
?>"><?php echo $page->post_title; ?></a></b> na koju ste kliknuli.
<br>
<?php echo open_source_preamble(); ?>
<hr>
<pre style="white-space:pre-wrap"><?php
echo embed_atfile_links(htmlspecialchars($content));
?></pre>
</html>
