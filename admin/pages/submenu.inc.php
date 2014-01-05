<dl>
<?php
$pageLayout=$GLOBALS['kaImpostazioni']->getVar('admin-page-layout',1,"*");
$submenu_label=array($GLOBALS['kaTranslate']->translate('Pages:Add a page'),$GLOBALS['kaTranslate']->translate('Pages:Edit a page'),$GLOBALS['kaTranslate']->translate('Pages:Delete a page'),$GLOBALS['kaTranslate']->translate('Pages:Categories'));
$submenu_url=array("new.php","edit.php","delete.php","categories.php");
if(strpos($pageLayout,",categories,")===false) {
	array_pop($submenu_label);
	array_pop($submenu_url);
	}

for($i=0;isset($submenu_label[$i]);$i++) {
	echo '<dt>';
	echo '<a href="'.$submenu_url[$i].'"';
	if($submenu_url[$i]==basename($_SERVER['PHP_SELF'])) { echo ' class="sel"'; }
	echo '>'.$submenu_label[$i].'</a>';
	echo '</dt>';
	}
?>
</dl>
