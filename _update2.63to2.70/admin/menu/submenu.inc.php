<dl>
<?php $submenu_label=array($GLOBALS['kaTranslate']->translate('Menu:Navigation menu management'),$GLOBALS['kaTranslate']->translate('Menu:Add a new element'));
$submenu_url=array("index.php?collection=".urlencode($_GET['collection']),"new.php?collection=".urlencode($_GET['collection']));

for($i=0;isset($submenu_label[$i]);$i++) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$submenu_label[$i].'</a>';
	echo '</dt>';
	}
?>
</dl>
