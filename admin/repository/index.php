<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Repository");
define("PAGE_LEVEL",2);
include_once("../inc/head.inc.php");
?>

<h1><? echo PAGE_NAME; ?></h1>
<br />
<ul class="mainopt">
<li><a href="imgs.php">Gestione Immagini</a></li>
<li><a href="docs.php">Gestione Documenti</a></li>
<li><a href="media.php">Gestione Oggetti Multimediali (video, animazioni, ...)</a></li>
</ul>

<?
include_once("../inc/foot.inc.php");
?>
