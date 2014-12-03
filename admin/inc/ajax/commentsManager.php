<?php 
session_start();
if(!isset($_SESSION['iduser'])) die();
if(!isset($_POST['t'])) die();
if(!isset($_POST['id'])) die();

require_once("../../inc/config.inc.php");
if(!isset($db['id'])) require_once("../../inc/connect.inc.php");
require_once("../../inc/main.lib.php");
require_once("../../inc/kalamun.lib.php");
require_once("../../inc/comments.lib.php");

/* set default timezone in PHP and MySQL */
$timezone=kaGetVar('timezone',1);
if($timezone!="") {
	date_default_timezone_set($timezone);
	$query="SET time_zone='".date("P")."'";
	ksql_query($query);
	}


$kaTranslate=new kaAdminTranslate();
$kaComments=new kaComments();
?>

<div id="iPopUpHeader">
	<h1><?= $kaTranslate->translate('Comments:Comments management'); ?></h1>
	<a href="javascript:kCloseIPopUp();" class="closeWindow"><img src="<?= ADMINDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	</div>

<div style="padding:20px;"><?php 
	$i=0;
	foreach($kaComments->getList($_POST['t'],$_POST['id']) as $c) { ?>
		<table id="comment<?= $c['idcomm']; ?>" class="<?= ($c['public']=='n'?'disapproved':'approved'); ?>">
		<tr><td>
		<small><?= preg_replace("/(\d{4})-(\d{2})-(\d{2}).*/","$3-$2-$1",$c['data']); ?> - <?= $c['autore']; ?></small><br />
		<?= $c['testo']; ?><br />
		<small class="actions" id="commentOptions<?= $c['idcomm']; ?>">
			<a href="javascript:deleteComment(<?= $c['idcomm']; ?>,'<?= ADMINDIR; ?>')" class="warning" onclick="return confirm('<?= $kaTranslate->translate('UI:Are you sure?'); ?>');"><?= $kaTranslate->translate('Comments:Delete'); ?></a> |
			<a href="javascript:approveComment(<?= $c['idcomm']; ?>,'<?= ADMINDIR; ?>')" id="commentApprove<?= $c['idcomm']; ?>" style="display:<?= ($c['public']=='n'?'inline':'none'); ?>"><?= $kaTranslate->translate('Comments:Approve'); ?></a>
			<a href="javascript:approveComment(<?= $c['idcomm']; ?>,'<?= ADMINDIR; ?>')" id="commentHide<?= $c['idcomm']; ?>" style="display:<?= ($c['public']=='s'?'inline':'none'); ?>"><?= $kaTranslate->translate('Comments:Hide'); ?></a>
			</small>
		</td></tr></table>
		<hr />
		<?php 
		$i++;
		}

	if($i==0) { ?>
		<?= $kaTranslate->translate('Comments:No comment'); ?>
		<?php  } ?>
	</div>
