<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

?>
<div class="tab"><dl>
<?php 
$submenu_label=array("Setup:Template","Setup:E-mail template");
$submenu_url=array("template.php","template-email.php");
foreach($submenu_label as $i=>$label) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$kaTranslate->translate($label).'</a>';
	echo '</dt>';
	}
?>
</dl></div>

