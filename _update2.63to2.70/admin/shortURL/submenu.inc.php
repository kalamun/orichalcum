<dl>
<?php $submenu_label=array("Nuovo URL","Modifica URL","Elimina URL");
$submenu_url=array("new.php","edit.php","delete.php");
for($i=0;isset($submenu_label[$i]);$i++) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$submenu_label[$i].'</a>';
	echo '</dt>';
	}
?>
</dl>
