<?php 
session_start();
if(!isset($_SESSION['iduser'])) die();

require_once('../../inc/main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("check-permissions"=>false, "x-frame-options"=>"") );

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
