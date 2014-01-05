<? /* (c) Kalamun.org - GNU/GPL 3 */ ?>
<dl>
<?
$submenu_label=array($kaTranslate->translate('Languages:Management'),$kaTranslate->translate('Languages:Copy content'),$kaTranslate->translate('Languages:Map of translations'));
$submenu_url=array("index.php","copy.php","map.php");
foreach($submenu_label as $i=>$label) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$label.'</a>';
	echo '</dt>';
	}
?>
</dl>

