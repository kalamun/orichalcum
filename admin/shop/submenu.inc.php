<?
/* (c) Kalamun.org - GNU/GPL 3 */

?>
<dl>
<?
$submenu_label=array($kaTranslate->translate('Shop:Add'),$kaTranslate->translate('Shop:Edit'),$kaTranslate->translate('Shop:Delete'),$kaTranslate->translate('Shop:Categories'));
$submenu_url=array("add.php","edit.php","delete.php","categories.php");
foreach($submenu_label as $i=>$label) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'" ';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$label.'</a>';
	echo '</dt>';
	}
?>
</dl>
<?
$pageLayout=$kaImpostazioni->getVar('admin-shop-layout',1,"*");
if(strpos($pageLayout,",ordersummary,")!==false) { ?>
<br />
<h2>Ordini</h2>
<dl>
<?
$submenu_label=array($kaTranslate->translate('Shop:Opened orders'),$kaTranslate->translate('Shop:Closed orders'),$kaTranslate->translate('Shop:Canceled orders'));
$submenu_url=array("orders-open.php","orders-closed.php","orders-canceled.php");
foreach($submenu_label as $i=>$label) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'" ';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$label.'</a>';
	echo '</dt>';
	}
?>
</dl>
<? } ?>
