<?
/* (c) Kalamun.org - GNU/GPL 3 */

?>
<dl>
<?
$submenu_label=array(
	$kaTranslate->translate('Maintenance:Backup'),
	$kaTranslate->translate('Maintenance:Check mailserver'),
	$kaTranslate->translate('Maintenance:Check Permalinks'),
	$kaTranslate->translate('Maintenance:Check Images'),
	$kaTranslate->translate('Maintenance:UTF-8 charset'),
	$kaTranslate->translate('Maintenance:E-mails existence')
	);
$submenu_url=array("backup.php","mailserver.php","permalink.php","images.php","utf8.php","emails.php");
foreach($submenu_label as $i=>$label) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$label.'</a>';
	echo '</dt>';
	}
?>
</dl>

