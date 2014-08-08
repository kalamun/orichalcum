<?php
/* (c) Kalamun.org - GNU/GPL 3 */

require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../inc/main.lib.php');
if(!isset($_SESSION['iduser'])) die('Non hai il permesso di utilizzare questa funzione');
define("PAGE_NAME",'Newsletter:Processing queue');

require_once('../../inc/log.lib.php');
$kaLog=new kaLog();
require_once('../newsletter.lib.php');
$kaNewsletter=new kaNewsletter();
$kaImpostazioni=new kaImpostazioni();

$kaTranslate=new kaAdminTranslate();
$kaTranslate->import('newsletter');

require_once("../../../inc/tplshortcuts.lib.php");
kInitBettino("../../../");

//calculate how many e-mails per second send and how many seconds wait before refresh
$mailLimitHour=$kaImpostazioni->getVar('email-queue-mailperhour',1,"*");
if($mailLimitHour==0) $mailLimitHour=200;
$mailLimitCycle=1;
$refreshTimeout=3600/$mailLimitHour;
if($refreshTimeout==0) {
	$mailLimitCycle=0;
	$refreshTimeout=0;
	}
elseif($refreshTimeout<3) {
	$mailLimitCycle/=$refreshTimeout/3;
	$refreshTimeout=3;
	}
$refreshTimeout=floor($refreshTimeout);
$mailLimitCycle=round($mailLimitCycle);

//passing initial count by GET
if(!isset($_GET['count'])) $_GET['count']=$kaNewsletter->getQueueCount();
$mailInQueue=$kaNewsletter->getQueueCount();
if($_GET['count']<$mailInQueue) $_GET['count']=$mailInQueue;

$percentCompleted=100-round($_GET['count']/100*$mailInQueue);

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
<title><?= $kaTranslate->translate(PAGE_NAME); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />

<? if($refreshTimeout>0&&$mailInQueue>0) { ?><meta http-equiv="refresh" content="<?= $refreshTimeout; ?>;URL='?count=<?= $_GET['count']; ?>'"><? } ?>

<style type="text/css">
	@import "<?= ADMINDIR; ?>css/screen.css";
	@import "<?= ADMINDIR; ?>css/main.lib.css";
	@import "<?= ADMINDIR; ?>css/imgmanager.css";
	@import "../css/substyle.css";
	</style>

<script type="text/javascript">var ADMINDIR='<?= str_replace("'","\'",ADMINDIR); ?>';</script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/main.lib.js"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/kalamun.js"></script>
</head>

<body>

<div id="imgheader">
	<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	</div>

<div id="imgcontents">

	<?
	/***************************/
	/* ACTIONS                 */
	/***************************/
	if($mailInQueue>0) {	
		foreach($kaNewsletter->getQueueList(array("limit"=>$mailLimitCycle)) as $mail) {
			$results=$GLOBALS['__emails']->send($mail['from'],$mail['to'],$mail['subject'],$mail['message'],$mail['template'],$mail['idarch']);
			if($results==true) {
				if($kaNewsletter->removeFromQueueById($mail['idemlq'])) $mailInQueue--;
				$percentCompleted=100-round(100/$_GET['count']*$mailInQueue);
				}
			}
		}
	?>

	<div id="progressBar">
		<div id="completedBar" style="width:<?= $percentCompleted; ?>%"></div>
		</div>

	<?
	if($mailInQueue==0) { ?>
		<div class="MsgSuccess"><?= $kaTranslate->translate('Newsletter:Done! All mail was successfully sent'); ?></div>
		<? } ?>
	<div class="percent"><?= $percentCompleted; ?> %</div>
	<?= $kaTranslate->translate('Newsletter:Sent'); ?>: <strong><?= $_GET['count']-$mailInQueue; ?></strong><br />
	<?= $kaTranslate->translate('Newsletter:Queued'); ?>: <?= $mailInQueue; ?><br />
	<?= $kaTranslate->translate('Newsletter:Total'); ?>: <?= $_GET['count']; ?><br />
	<br />
	<div class="note"><?= $kaTranslate->translate("Newsletter:Yes, it's slow! Be patient, this slowliness is needed to save your e-mails from anti-spam systems"); ?></div>
	</div>

</body>
</html>
