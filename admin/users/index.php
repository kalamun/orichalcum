<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Users:Users management");
include_once("../inc/head.inc.php");

?><h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	<ul class="mainopt">
	<li><a href="nuovo.php"><?= $kaTranslate->translate('Users:Create a new user'); ?></a></li>
	<li><a href="modifica.php"><?= $kaTranslate->translate('Users:Edit user'); ?></a></li>
	<li><a href="elimina.php"><?= $kaTranslate->translate('Users:Delete user'); ?></a></li>
	<li><a href="publicusers.php"><?= $kaTranslate->translate('Users:Public users'); ?></a></li>
	</ul>

<?	
include_once("../inc/foot.inc.php");
?>
