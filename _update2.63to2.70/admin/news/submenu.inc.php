<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

?>
<dl>
<?php 
$get="";
if(isset($_GET['m'])) $get.="&m=".urlencode($_GET['m']);
if(isset($_GET['y'])) $get.="&y=".urlencode($_GET['y']);
if($get!="") $get="?".trim($get,"&");

$submenu_label=array($kaTranslate->translate('News:Write'),$kaTranslate->translate('News:Edit'),$kaTranslate->translate('News:Delete'),$kaTranslate->translate('News:Categories'));
$submenu_url=array("new.php","edit.php","delete.php","categorie.php");
foreach($submenu_label as $i=>$label) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].$get.'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$label.'</a>';
	echo '</dt>';
	}
?>
</dl>

