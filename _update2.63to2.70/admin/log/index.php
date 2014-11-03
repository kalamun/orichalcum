<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Log:Activity log");
include_once("../inc/head.inc.php");
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<ul class="mainopt">
<li><a href="email.php"><?= $kaTranslate->translate('Log:E-mail archive'); ?></a></li>
<li><a href="controlpanel.php"><?= $kaTranslate->translate('Log:Control-panel activity'); ?></a></li>
</ul>

<?php 
include_once("../inc/foot.inc.php");
