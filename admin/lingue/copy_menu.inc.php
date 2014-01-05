<div class="tab">
<dl>
<?
$submenu_label=array("Copia di massa","Copia delle singole pagine");
$submenu_url=array("copy.php","copy_pages.php");
for($i=0;isset($submenu_label[$i]);$i++) {
	if(strpos($submenu_url[$i],"?")!==false) $basename=substr($submenu_url[$i],0,strpos($submenu_url[$i],"?"));
	else $basename=$submenu_url[$i];
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'" ';
	if($basename==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$submenu_label[$i].'</a>';
	echo '</dt>';
	}
?>
</dl>
</div>
