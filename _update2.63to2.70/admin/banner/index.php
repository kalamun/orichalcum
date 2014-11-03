<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Banner:Banner");
include_once("../inc/head.inc.php");
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<ul class="mainopt">
<li><a href="inserisci.php"><?= $kaTranslate->translate('Banner:Add a new banner'); ?></a></li>
<li><a href="modifica.php"><?= $kaTranslate->translate('Banner:Edit a banner'); ?></a></li>
<li><a href="elimina.php"><?= $kaTranslate->translate('Banner:Delete a banner'); ?></a></li>
<li><a href="categorie.php"><?= $kaTranslate->translate('Banner:Category'); ?></a></li>
</ul>

<?php 
include_once("../inc/foot.inc.php");
