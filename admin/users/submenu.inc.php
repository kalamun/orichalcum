<dl>
<?
$submenu_label=array("Create","Edit","Delete","Public users");
$submenu_url=array("nuovo.php","modifica.php","elimina.php","publicusers.php");
for($i=0;isset($submenu_label[$i]);$i++) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$kaTranslate->translate('Users:'.$submenu_label[$i]).'</a>';
	echo '</dt>';
	}
?>
</dl>
