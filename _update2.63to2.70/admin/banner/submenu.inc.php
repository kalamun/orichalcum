<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

?>
<dl>
<?php 
$submenu_label=array("Inserisci","Modifica","Elimina","Categorie");
$submenu_url=array("inserisci.php","modifica.php","elimina.php","categorie.php");
$submenu_title=array("Inserisci un nuovo banner","Modifica Banner","Elimina un banner","Categorie");
foreach($submenu_label as $i=>$label) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'" title="'.$submenu_title[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$label.'</a>';
	echo '</dt>';
	}
?>
</dl>

