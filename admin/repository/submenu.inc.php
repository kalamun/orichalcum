<?
/* (c) Kalamun.org - GNU/GPL 3 */

?>
<dl>
<?
$submenu_label=array("Immagini","Documenti","Multimedia");
$submenu_url=array("imgs.php","docs.php","media.php");
foreach($submenu_label as $i=>$label) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$label.'</a>';
	echo '</dt>';
	}
?>
</dl>

