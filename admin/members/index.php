<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Menu:Members");
include_once("../inc/head.inc.php");

?><h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	<ul class="mainopt">
	<li><a href="nuovo.php"><?= $kaTranslate->translate('Members:Create member'); ?></a></li>
	<li><a href="modifica.php"><?= $kaTranslate->translate('Members:Edit member'); ?></a></li>
	<li><a href="elimina.php"><?= $kaTranslate->translate('Members:Delete'); ?></a></li>
	<li><a href="export.php"><?= $kaTranslate->translate('Members:Export'); ?></a></li>
	</ul>

<?	
include_once("../inc/foot.inc.php");
?>
