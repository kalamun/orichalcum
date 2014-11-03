<?php 
/* (c) Kalamun.org - GNU/GPL 3 */
?>
<div class="tab"><dl>
<?php 
$submenu_label=array("Iscrizione singola",$kaTranslate->translate('Newsletter:Mass subscription'));
$submenu_url=array("subscribe.php","subscribe_mass.php");
for($i=0;isset($submenu_label[$i]);$i++) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$submenu_label[$i].'</a>';
	echo '</dt>';
	}
?>
</dl></div>