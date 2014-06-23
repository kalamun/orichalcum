<?php
/* (c) Kalamun.org - GNU/GPL 3 */
require_once("main.lib.php");
$orichalcum = new kaOrichalcum();
$orichalcum->init();
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
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/kzeneditor.css?<?= SW_VERSION; ?>" type="text/css" />

<?
/* if current module contains a substyle, include it */
$filename='css/substyle.css';
if(file_exists($filename)) echo '<link rel="stylesheet" href="'.ADMINDIR.PAGE_ID.'/'.$filename.'?'.SW_VERSION.'" type="text/css" />';
?>

<script type="text/javascript" src="<?= ADMINDIR; ?>js/dictionary.js.php?<?= SW_VERSION; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/kalamun.js?<?= SW_VERSION; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/main.lib.js?<?= SW_VERSION; ?>" charset="utf-8"></script>
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
