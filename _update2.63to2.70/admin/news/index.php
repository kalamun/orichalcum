<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","News:News");
include_once("../inc/head.inc.php");
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<ul class="mainopt">
<li><a href="new.php"><?= $kaTranslate->translate('News:Write a new News'); ?></a></li>
<li><a href="edit.php"><?= $kaTranslate->translate('News:Edit a News'); ?></a></li>
<li><a href="delete.php"><?= $kaTranslate->translate('News:Delete a News'); ?></a></li>
<li><a href="categorie.php"><?= $kaTranslate->translate('News:Manage News categories'); ?></a></li>
</ul>

<?php 
include_once("../inc/foot.inc.php");
