<?php
session_start();
if(!isset($_SESSION['iduser'])) die("You don't have permissions to access this informations");
if(!isset($_GET['idnews'])&&!isset($_GET['delete'])) die('Error selecting news');

require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../users/users.lib.php');
require_once('../../inc/main.lib.php');

/* set default timezone in PHP and MySQL */
$timezone=kaGetVar('timezone',1);
if($timezone!="") {
	date_default_timezone_set($timezone);
	$query="SET time_zone='".date("P")."'";
	mysql_query($query);
	}

$kaUsers=new kaUsers();
$kaTranslate=new kaAdminTranslate();
$kaTranslate->import('news');

if(!$kaUsers->canIUse('news')) die("You don't have permissions to access this informations");

require_once(ADMINRELDIR."inc/config.lib.php");
$kaImpostazioni=new kaImpostazioni();
$pageLayout=$kaImpostazioni->getVar('admin-news-layout',1,"*");

include('../news.lib.php');
$kaNews=new kaNews();

if(isset($_GET['idnews'])) {
	/* baloon in edit mode */
	$row=$kaNews->get($_GET['idnews']);
	?>
	<div style="text-align:center;">
		<a href="?idnews=<?= $row['idnews']; ?>" class="button"><?= $kaTranslate->translate('UI:Edit'); ?></a><br />
		<a href="<?= SITE_URL.BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_news',1).'/'.$row['categorie'][0]['dir'].'/'.$row['dir']; ?>"><?= $kaTranslate->translate('UI:View'); ?></a> | <a href="new.php?copyfrom=<?= $row['idnews']; ?>"><?= $kaTranslate->translate('UI:Copy'); ?></a><br />
	</div>
	<?= ($kaImpostazioni->getVar('news-commenti',1)=='s'?$kaTranslate->translate('News:Comments').': <strong>'.$row['commentiOnline'].'</strong> / '.$row['commentiTot']:'').'<br />'; ?>
	<?= (strpos($pageLayout,",date,")!==false?$kaTranslate->translate('News:Created').' '.strftime("%d %b %Y",mktime(1,0,0,substr($row['data'],5,2),substr($row['data'],8,2),substr($row['data'],0,4))).' '.substr($row['data'],11,5).'<br />':''); ?>
	<?= (strpos($pageLayout,",public,")!==false?$kaTranslate->translate('News:Visible from').' '.strftime("%d %b %Y",mktime(1,0,0,substr($row['pubblica'],5,2),substr($row['pubblica'],8,2),substr($row['pubblica'],0,4))).' '.substr($row['pubblica'],11,5).'<br />':''); ?>
	<?= (strpos($pageLayout,",startingdate,")!==false?$kaTranslate->translate('News:Starting date').' '.strftime("%d %b %Y",mktime(1,0,0,substr($row['starting_date'],5,2),substr($row['starting_date'],8,2),substr($row['starting_date'],0,4))).' '.substr($row['starting_date'],11,5).'<br />':''); ?>
	<?= (strpos($pageLayout,",expiration,")!==false?$kaTranslate->translate('News:Expiration').' '.strftime("%d %b %Y",mktime(1,0,0,substr($row['scadenza'],5,2),substr($row['scadenza'],8,2),substr($row['scadenza'],0,4))).' '.substr($row['scadenza'],11,5).'<br />':''); ?>
	<? }

elseif(isset($_GET['delete'])) {
	/* baloon in delete mode */
	$row=$kaNews->get($_GET['delete']);
	?>
	<div style="text-align:center;">
		<a href="?idnews=<?= $row['idnews']; ?>" class="alertbutton"><?= $kaTranslate->translate('UI:Delete'); ?></a><br />
		<a href="<?= SITE_URL.BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_news',1).'/'.$row['categorie'][0]['dir'].'/'.$row['dir']; ?>"><?= $kaTranslate->translate('UI:View'); ?></a><br />
	</div>
	<?= ($kaImpostazioni->getVar('news-commenti',1)=='s'?$kaTranslate->translate('News:Comments').': <strong>'.$row['commentiOnline'].'</strong> / '.$row['commentiTot']:'').'<br />'; ?>
	<?= (strpos($pageLayout,",date,")!==false?$kaTranslate->translate('News:Created').' '.strftime("%d %b %Y",mktime(1,0,0,substr($row['data'],5,2),substr($row['data'],8,2),substr($row['data'],0,4))).' '.substr($row['data'],11,5).'<br />':''); ?>
	<?= (strpos($pageLayout,",public,")!==false?$kaTranslate->translate('News:Visible from').' '.strftime("%d %b %Y",mktime(1,0,0,substr($row['pubblica'],5,2),substr($row['pubblica'],8,2),substr($row['pubblica'],0,4))).' '.substr($row['pubblica'],11,5).'<br />':''); ?>
	<?= (strpos($pageLayout,",startingdate,")!==false?$kaTranslate->translate('News:Starting date').' '.strftime("%d %b %Y",mktime(1,0,0,substr($row['starting_date'],5,2),substr($row['starting_date'],8,2),substr($row['starting_date'],0,4))).' '.substr($row['starting_date'],11,5).'<br />':''); ?>
	<?= (strpos($pageLayout,",expiration,")!==false?$kaTranslate->translate('News:Expiration').' '.strftime("%d %b %Y",mktime(1,0,0,substr($row['scadenza'],5,2),substr($row['scadenza'],8,2),substr($row['scadenza'],0,4))).' '.substr($row['scadenza'],11,5).'<br />':''); ?>
	<? }

?>