<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Repository:Repository");
define("PAGE_LEVEL",2);
include_once("../inc/head.inc.php");
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<ul class="mainopt">
<li><a href="imgs.php"><?= $kaTranslate->translate('Repository:Images management'); ?></a></li>
<li><a href="docs.php"><?= $kaTranslate->translate('Repository:Documents management'); ?></a></li>
<li><a href="media.php"><?= $kaTranslate->translate('Repository:Multimedias management'); ?></a></li>
</ul>

<?php 
include_once("../inc/foot.inc.php");
