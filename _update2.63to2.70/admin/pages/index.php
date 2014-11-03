<?php /* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Pages:Pages");
include_once("../inc/head.inc.php");
$pageLayout=$kaImpostazioni->getVar('admin-page-layout',1,"*");
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />

	<ul class="mainopt">
	<li><a href="new.php"><?= $kaTranslate->translate('Pages:Add a page'); ?></a></li>
	<li><a href="edit.php"><?= $kaTranslate->translate('Pages:Edit a page'); ?></a></li>
	<li><a href="delete.php"><?= $kaTranslate->translate('Pages:Delete a page'); ?></a></li>
	<?php  if(strpos($pageLayout,",categories,")!==false) { ?><li><a href="categories.php"><?= $kaTranslate->translate('Pages:Pages categories'); ?></a></li><?php  } ?>
	</ul>

<?php 
include_once("../inc/foot.inc.php");
