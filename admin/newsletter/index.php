<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Newsletter:Newsletter");
include_once("../inc/head.inc.php");
include_once("./newsletter.lib.php");

?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>

<?
$kaNewsletter=new kaNewsletter();
$queueCount=$kaNewsletter->getQueueCount();
if($queueCount>0) { ?>
	<br />
	<script type="text/javascript" src="js/newsletter.js"></script>
	<div class="box">
		<div style="float:left;margin-right:20px;"><h3><?= $kaTranslate->translate('Newsletter:E-mail queue'); ?></h3>
			<?= $kaTranslate->translate('Newsletter:There are %d e-mails in queue',$queueCount); ?></div>
		<div style="float:left;margin-right:20px;"><input type="button" onclick="processQueue();" value="<?= $kaTranslate->translate('Newsletter:Process queue'); ?>" class="button" /></div>
		<div style="clear:both;"></div>
		</div>
	<? }
?>

	<ul class="mainopt">
	<li><a href="write.php"><?= $kaTranslate->translate('Newsletter:Write an e-mail'); ?></a></li>
	<li><a href="subscribe.php"><?= $kaTranslate->translate('Newsletter:Subscribe to a list'); ?></a></li>
	<li><a href="lists.php"><?= $kaTranslate->translate('Newsletter:Lists management'); ?></a></li>
	<li><a href="archive.php"><?= $kaTranslate->translate('Newsletter:Archive'); ?></a></li>
	</ul>

<?	
include_once("../inc/foot.inc.php");
?>
