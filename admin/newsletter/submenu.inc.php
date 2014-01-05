<?
/* (c) Kalamun.org - GNU/GPL 3 */
?>
<dl>
<?
$submenu_label=array($kaTranslate->translate('Newsletter:Write an e-mail'),$kaTranslate->translate('Newsletter:Subscribe to a list'),$kaTranslate->translate('Newsletter:Lists management'),$kaTranslate->translate('Newsletter:Archive'));
$submenu_url=array("write.php","subscribe.php","lists.php","archive.php");
for($i=0;isset($submenu_label[$i]);$i++) {
	if($submenu_url[$i]!="") {
		echo '<dt>';
		echo '<a href="'.$submenu_url[$i].'"';
		if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
		echo '>'.$submenu_label[$i].'</a>';
		echo '</dt>';
		}
	}
?>
</dl>