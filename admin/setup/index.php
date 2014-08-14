<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Setup:Setup");
include_once("../inc/head.inc.php");
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<ul class="mainopt">
<li><a href="general.php"><?= $kaTranslate->translate('Setup:General settings'); ?></a></li>
<li><a href="directories.php"><?= $kaTranslate->translate('Setup:Reserved URLs'); ?></a></li>
<li><a href="pages.php"><?= $kaTranslate->translate('Setup:Page settings'); ?></a></li>
<li><a href="news.php"><?= $kaTranslate->translate('Setup:News settings'); ?></a></li>
<li><a href="photogallery.php"><?= $kaTranslate->translate('Setup:Photogallery settings'); ?></a></li>
<li><a href="shop.php"><?= $kaTranslate->translate('Setup:Shop settings'); ?></a></li>
<li><a href="welcomepage.php"><?= $kaTranslate->translate('Setup:Welcome page setup'); ?></a></li>
</ul>

<h1><?= $kaTranslate->translate('Setup:Advanced settings'); ?></h1>
<br />
<ul class="mainopt">
<li><a href="template.php">Template</a></li>
<li><a href="imgresize.php">Impostazioni per le immagini</a></li>
<li><a href="emails.php"><?= $kaTranslate->translate('Setup:E-mails and newsletters'); ?></a></li>
<li><a href="statistiche-esterne.php">Statistiche</a></li>
<li><a href="config.php">Config.inc.php</a></li>
</ul>

<?
include_once("../inc/foot.inc.php");
?>
