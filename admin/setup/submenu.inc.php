<dl>
<?
$submenu_label=array("Setup:General settings","Setup:Reserved URLs","Setup:Page settings","Setup:News settings","Setup:Photogallery settings","Setup:Shop settings");
$submenu_url=array("general.php","directories.php","pages.php","news.php","photogallery.php","shop.php");
for($i=0;isset($submenu_label[$i]);$i++) {
	if($submenu_url[$i]!="") {
		echo '<dt>';
		echo '<a href="'.$submenu_url[$i].'"';
		if(preg_match("/^".substr($submenu_url[$i],0,strpos($submenu_url[$i],"."))."[\._-].*/",basename($_SERVER['PHP_SELF']))) { echo ' class="sel"'; }
		echo '>'.$kaTranslate->translate($submenu_label[$i]).'</a>';
		echo '</dt>';
		}
	}
?>
</dl>
<h2><?= $kaTranslate->translate('Setup:Advanced settings'); ?></h2>
<dl>
<?
$submenu_label=array("Setup:Template","Setup:Images","Setup:E-mails and newsletters","Setup:Statistics","Setup:Config.inc.php");
$submenu_url=array("template.php","imgresize.php","emails.php","statistiche.php","config.php");
for($i=0;isset($submenu_label[$i]);$i++) {
	if($submenu_url[$i]!="") {
		echo '<dt>';
		echo '<a href="'.$submenu_url[$i].'"';
		if(preg_match("/^".substr($submenu_url[$i],0,strpos($submenu_url[$i],"."))."[\._-].*/",basename($_SERVER['PHP_SELF']))) { echo ' class="sel"'; }
		echo '>'.$kaTranslate->translate($submenu_label[$i]).'</a>';
		echo '</dt>';
		}
	}
?>
</dl>
