<dl>
<?php $submenu_label=array("Photogalleries:Add a gallery","Photogalleries:Edit a gallery","Photogalleries:Delete a gallery","Photogalleries:Categories");
$submenu_url=array("new.php","edit.php","delete.php","categories.php");
for($i=0;isset($submenu_label[$i]);$i++) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) echo ' class="sel"';
	echo '>'.$kaTranslate->translate($submenu_label[$i]).'</a>';
	echo '</dt>';
	}
?>
</dl>
