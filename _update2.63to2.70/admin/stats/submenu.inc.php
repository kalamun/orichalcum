<dl>
<?php $submenu_label=array($kaTranslate->translate('Statistics:Realtime'),$kaTranslate->translate('Statistics:Visitors'),$kaTranslate->translate('Statistics:Pages'),$kaTranslate->translate('Statistics:Systems and Browsers'),$kaTranslate->translate('Statistics:Referer'));
$submenu_url=array("realtime.php","visitatori.php","pagine.php","sistemi.php","referer.php");
for($i=0;isset($submenu_label[$i]);$i++) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$submenu_label[$i].'</a>';
	echo '</dt>';
	}
?>
</dl>
