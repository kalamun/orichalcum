<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Statistics:Statistics");
include_once("../inc/head.inc.php");
include_once("stats.lib.php");
$kaStats=new kaStats(array("visits"=>true, "newsletter"=>true));
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<ul class="mainopt">
<li><a href="realtime.php"><?= $kaTranslate->translate('Statistics:Realtime'); ?></a></li>
<li><a href="visitatori.php"><?= $kaTranslate->translate('Statistics:Visitors'); ?></a></li>
<li><a href="pagine.php"><?= $kaTranslate->translate('Statistics:Pages'); ?></a></li>
<li><a href="sistemi.php"><?= $kaTranslate->translate('Statistics:Systems and Browsers'); ?></a></li>
<li><a href="referer.php"><?= $kaTranslate->translate('Statistics:Referer'); ?></a></li>
</ul>

<?php 
include_once("../inc/foot.inc.php");
