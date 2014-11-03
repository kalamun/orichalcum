<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Profile:Personal Profile Management");
include_once("../inc/head.inc.php");

?><h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	<ul class="mainopt">
	<li><a href="modifica.php"><?= $kaTranslate->translate('Profile:Change your Personal Profile'); ?></a></li>
	<li><a href="password.php"><?= $kaTranslate->translate('Profile:Change your Password'); ?></a></li>
	</ul>

<?php 	
include_once("../inc/foot.inc.php");
