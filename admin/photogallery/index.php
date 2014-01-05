<?php
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Photogalleries:Photogalleries");
include_once("../inc/head.inc.php");

?><h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />

	<ul class="mainopt">
	<li><a href="new.php"><?= $kaTranslate->translate('Photogalleries:Add a gallery'); ?></a></li>
	<li><a href="edit.php"><?= $kaTranslate->translate('Photogalleries:Edit a gallery'); ?></a></li>
	<li><a href="delete.php"><?= $kaTranslate->translate('Photogalleries:Delete a gallery'); ?></a></li>
	</ul>

<?php	
include_once("../inc/foot.inc.php");
?>
