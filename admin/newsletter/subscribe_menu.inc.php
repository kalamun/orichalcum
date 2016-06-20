<?php 
/* (c) Kalamun.org - GNU/GPL 3 */
?>
<div class="tab"><dl>
<?php 
$submenu_label=array( $kaTranslate->translate('Newsletter:Single subscription'), $kaTranslate->translate('Newsletter:Mass subscription'), $kaTranslate->translate('Newsletter:Import'));
$submenu_url=array("subscribe.php", "subscribe_mass.php", "subscribe_import.php");
for($i=0;isset($submenu_label[$i]);$i++) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$submenu_label[$i].'</a>';
	echo '</dt>';
	}
?>
</dl></div>