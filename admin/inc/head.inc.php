<?php
/* (c) Kalamun.org - GNU/GPL 3 */

/* init default session */
if((!isset($_SESSION['exists'])||$_SESSION['exists']!=true)||!isset($_SESSION['ll'])) {
	session_start();
	$_SESSION['exists']=true;
	}

/* prevent XSS */
header('X-Frame-Options: deny');

/* connect to db and set default constants */
require_once("config.inc.php");
if(!isset($db['id'])) require_once("connect.inc.php");
require_once("main.lib.php");
require_once("kalamun.lib.php");

/* set default timezone in PHP and MySQL */
$timezone=kaGetVar('timezone',1);
if($timezone=="") $timezone='Europe/Rome';
date_default_timezone_set($timezone);
$query="SET time_zone='".date("P")."'";
mysql_query($query);
mysql_query("SET NAMES utf8");

/* load setup variables */
require_once(ADMINRELDIR."inc/log.lib.php");
$kaLog=new kaLog();
$kaImpostazioni=new kaImpostazioni();

/* generate PAGE_ID and additional constants */
if(!defined("PAGE_ID")) define("PAGE_ID",substr(dirname($_SERVER['PHP_SELF']),strpos(dirname($_SERVER['PHP_SELF']),"admin/")+6));
if(!defined("RICH_EDITOR")) {
	if($kaImpostazioni->getVar('admin-editor',1,"*")=="true") define("RICH_EDITOR",true);
	else define("RICH_EDITOR",true);
	}

/* load generic purpose classes */
require_once(ADMINRELDIR."users/users.lib.php");
$kaUsers=new kaUsers();
require_once(ADMINRELDIR."inc/metadata.lib.php");
$kaMetadata=new kaMetadata();
$kaTranslate=new kaAdminTranslate();

/* manage language changes */
if(isset($_GET['chg_lang'])) {
	$_SESSION['ll']=$_GET['chg_lang'];
	$kaImpostazioni->kaImpostazioni();
	}

/* manage session and access based on user permissions */
include_once("sessionmanager.inc.php");

/* if user is not logged in, display login page */
if(!isset($_SESSION['username'])||$_SESSION['username']=="") {
	include_once("login.inc.php");
	die();
	}

/* if user are not allowed to access this page, display error */
if(!$kaUsers->canIUse()) {
	?>
	<div class="alert"><h1><?= $kaTranslate->translate('UI:Forbidden'); ?></h1>
	<a href="<?= ADMINRELDIR; ?>"><?= $kaTranslate->translate('UI:Back to home'); ?></a></div>
	<?
	include_once(ADMINRELDIR."inc/foot.inc.php");
	die();
	}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $_SESSION['ll']; ?>" lang="<?= $_SESSION['ll']; ?>">
<head>
<title><?= $kaImpostazioni->getVar("sitename",1)." &gt; ".$kaTranslate->translate(PAGE_NAME); ?></title>
<meta name="description" content="<?= $kaImpostazioni->getVar("sitename",1)." Admin"; ?>" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<link rel="shortcut icon" href="<?= ADMINDIR; ?>img/favicon.png" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/init.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/screen.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/main.lib.css?<?= SW_VERSION; ?>" type="text/css" />

<?
/* if current module contains a substyle, include it */
$filename='css/substyle.css';
if(file_exists($filename)) echo '<link rel="stylesheet" href="'.ADMINDIR.PAGE_ID.'/'.$filename.'?'.SW_VERSION.'" type="text/css" />';
?>

<script type="text/javascript" src="<?= ADMINDIR; ?>js/kalamun.js?<?= SW_VERSION; ?>"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/main.lib.js?<?= SW_VERSION; ?>"></script>
<script type="text/javascript">
	var ADMINDIR='<?= addslashes(ADMINDIR); ?>';
	var BASEDIR='<?= addslashes(BASEDIR); ?>';
	document.addEventListener("DOMContentLoaded",kCheckMessages,false);
	document.addEventListener("DOMContentLoaded",onScrollHandler,false);
	document.addEventListener("scroll",onScrollHandler,false);
	</script>
</head>

<body>

<div id="header">
	<h1><?= $kaImpostazioni->getVar("sitename",1); ?></h1>&nbsp;&nbsp;<?= $kaTranslate->translate('Menu:Control Panel'); ?>
	<div class="logout">
		<a href="javascript:k_openIframeWindow('http://help.bettino.it/<?= $_SESSION['ll']; ?>/<?= trim(PAGE_ID.'/'.basename($_SERVER['PHP_SELF']),"./"); ?>',Math.round(kWindow.clientWidth()*.9)+'px',Math.round(kWindow.clientHeight()*.9)+'px',true);"><?= $kaTranslate->translate('Menu:Help'); ?></a> | 
		<? if(strpos($_SESSION['permissions'],',upgrade,')!==false) { ?><a href="<?= ADMINDIR; ?>upgrade/" title="Aggiorna Bettino"><?= $kaTranslate->translate('Menu:Upgrade'); ?></a> | <? } ?>
		<a href="?logout" title="Esci"><?= $kaTranslate->translate('Menu:Logout'); ?><img src="<?php echo ADMINDIR; ?>img/logout.png" height="24" width="24" alt="X" /></a>
		</div>
	</div>

<?
/* print top menu */
$kaAdminMenu=new kaAdminMenu();
echo $kaAdminMenu->get();
?>

<div id="container">

<div id="contents">
	<? if($kaAdminMenu->getSelected('id')!="") { ?>
	<div id="submenu">
		<div class="submenu">
		<ul><li>
			<h2><?= $kaAdminMenu->getSelected('title'); ?></h2>
			<?
			/* print left menu */
			$filename='submenu.inc.php';
			if(file_exists($filename)) include($filename);
			?></li>
			</ul>
		</div>

		</div>
	<? } ?>
