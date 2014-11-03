<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Maintenance:Maintenance");
include_once("../inc/head.inc.php");

/* todo */
/*
- check of unused metadata
- check of unused documents
- check of unused videos
- check of contents from removed languages
*/

?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<ul class="mainopt">
<li><a href="backup.php"><?= $kaTranslate->translate('Maintenance:Backup'); ?></a></li>
<li><a href="mailserver.php"><?= $kaTranslate->translate('Maintenance:Check mailserver'); ?></a></li>
<li><a href="permalink.php"><?= $kaTranslate->translate('Maintenance:Fix duplicated or invalid permalinks'); ?></a></li>
<li><a href="images.php"><?= $kaTranslate->translate('Maintenance:Search for orphan images'); ?></a></li>
<li><a href="utf8.php"><?= $kaTranslate->translate('Maintenance:Set UTF-8 as charset on database tables'); ?></a></li>
<li><a href="emails.php"><?= $kaTranslate->translate('Maintenance:Check if e-mails exists'); ?></a></li>
</ul>

<?php 
include_once("../inc/foot.inc.php");
