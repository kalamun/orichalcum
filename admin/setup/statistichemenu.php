<?
/* (c) Kalamun.org - GNU/GPL 3 */

?>
<div class="tab"><dl>
<?
$submenu_label=array("Statistiche","Statistiche esterne");
$submenu_url=array("statistiche.php","statistiche-esterne.php");
foreach($submenu_label as $i=>$label) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$label.'</a>';
	echo '</dt>';
	}
?>
</dl></div>

