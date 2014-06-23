<?php
/* (c) Kalamun.org - GNU/GPL 3 */

error_reporting(0);
require_once('./connect.inc.php');
require_once('kalamun.lib.php');
require_once('./sessionmanager.inc.php');
require_once('./main.lib.php');
$kaTranslate=new kaAdminTranslate();
if(!isset($_SESSION['iduser'])) die('Non hai il permesso di utilizzare questa funzione');

if(!isset($_GET['code'])) $_GET['code']="";

/* set default timezone in PHP and MySQL */
$timezone=kaGetVar('timezone',1);
if($timezone!="") {
	date_default_timezone_set($timezone);
	$query="SET time_zone='".date("P")."'";
	mysql_query($query);
	}

require_once('./log.lib.php');
$kaLog=new kaLog();

define("PAGE_NAME",$kaTranslate->translate('Link:Source Code manager'));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
<title><?php echo ADMIN_NAME." - ".PAGE_NAME; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<style type="text/css">
	@import "<?php echo ADMINDIR; ?>css/screen.css";
	@import "<?php echo ADMINDIR; ?>css/main.lib.css";
	@import "<?php echo ADMINDIR; ?>css/docmanager.css";
	</style>

<script type="text/javascript">var ADMINDIR='<?php echo str_replace("'","\'",ADMINDIR); ?>';</script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/kalamun.js"></script>
</head>

<body>

<div id="docheader">
	<h1><?= $kaTranslate->translate('Editor:Source Code'); ?></h1>
	<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	<div id="doccontents">
		<? if(isset($_GET['update'])) {
			$code=$_GET['code'];
			$code=str_replace("\r",'',$code);
			$code=str_replace("\n","[Bettino:NewLine]",$code);
			?>
			<script type="text/javascript">
				var code="<?= addslashes($code); ?>";
				window.parent.txts.getArea('<?= $_GET['refid']; ?>').insertSourceCode(code);
				window.parent.k_closeIframeWindow();
				</script>
			<? }

		else { ?>
			<form action="" method="get">
				<input type="hidden" name="refid" value="<?= $_GET['refid']; ?>" />
				<textarea name="code" style="width:100%;height:300px;"><?= b3_lmthize($_GET['code'],"textarea"); ?></textarea>
				<br />
				<div class="submit">
					<input type="submit" name="update" value="<?= $kaTranslate->translate('Editor:Insert Source Code'); ?>" class="button" />
					</div>
				</form>
			<? } ?>
		</div>
	</div>
</body>
</html>
