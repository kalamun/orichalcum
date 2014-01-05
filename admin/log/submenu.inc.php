<dl>
<?php
$submenu_label=array($kaTranslate->translate('Log:E-mail archive'),$kaTranslate->translate('Log:Control-panel activity'));
$submenu_url=array("email.php","controlpanel.php");

for($i=0;isset($submenu_label[$i]);$i++) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$submenu_label[$i].'</a>';
	echo '</dt>';
	}
?>
</dl>
