<?php 
session_start();
if(!isset($_SESSION['iduser'])) die();

require_once("../../inc/config.inc.php");
if(!isset($db['id'])) require_once("../../inc/connect.inc.php");
require_once("../../inc/main.lib.php");
require_once("../../inc/kalamun.lib.php");

/* set default timezone in PHP and MySQL */
$timezone=kaGetVar('timezone',1);
if($timezone!="") {
	date_default_timezone_set($timezone);
	$query="SET time_zone='".date("P")."'";
	mysql_query($query);
	}


$kaTranslate=new kaAdminTranslate();
$kaTranslate->import('menu');
?>

<div id="iPopUpHeader">
	<h1><?= $kaTranslate->translate('Menu:Add new menu'); ?></h1>
	<a href="javascript:kCloseIPopUp();" class="closeWindow"><img src="<?= ADMINDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	</div>

<div style="padding:20px;">
	<form action="" method="get">
		<label for="collection"><?= $kaTranslate->translate('Menu:Menu name'); ?>:</label>
		<?= b3_create_input("collection","text","","","200px"); ?><br />
		<br />
		<div class="submit"><input type="submit" name="newCollection" class="button" value="<?= addslashes($kaTranslate->translate('Menu:Create menu')); ?>" /></div>
		</form>
	</div>
