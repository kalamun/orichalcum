<?
/* (c) Kalamun.org - GNU/GPL 3 */

?>
<div class="tab"><dl>
<?
$submenu_label=array("Members:Single user","Members:Batch creation");
$submenu_url=array("nuovo.php","nuovo_mass.php");
foreach($submenu_label as $i=>$label) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) echo ' class="sel"';
	echo '>'.$kaTranslate->translate($label).'</a>';
	echo '</dt>';
	}
?>
</dl></div>

