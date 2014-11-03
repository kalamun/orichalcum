<dl>
<?php 
$submenu_label=array($kaTranslate->translate('Members:New member'),$kaTranslate->translate('Members:Edit'),$kaTranslate->translate('Members:Delete'),$kaTranslate->translate('Members:Export'));
$submenu_url=array("nuovo.php","modifica.php","elimina.php","export.php");
for($i=0;isset($submenu_label[$i]);$i++) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$submenu_label[$i].'</a>';
	echo '</dt>';
	}
?>
</dl>
