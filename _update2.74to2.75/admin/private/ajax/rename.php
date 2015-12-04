<?php 
require_once('../../inc/main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("x-frame-options"=>"") );

/* check params */
if(!isset($_GET['dir'])) die('Fatal Error: no base directory specified');
$_GET['dir']=trim($_GET['dir']," ./");
$_GET['dir']=str_replace("../","",$_GET['dir']);
if(!file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$_GET['dir'])) die('The directory you request not exists');
if($_GET['dir']=="") die('You can\'t change permissions to the root dir');

$kaTranslate=new kaAdminTranslate();

require_once('../private.lib.php');
$kaPrivate=new kaPrivate();

?>
<div id="iPopUpHeader">
	<h1><?= $kaTranslate->translate('Private:Rename'); ?></h1>
	<a href="javascript:window.parent.kCloseIPopUp();" class="closeWindow"><img src="../img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	</div>
<form action="" method="post">
	<input type="hidden" name="oldname" value="<?= utf8_encode($_GET['dir']); ?>" />
	<div style="padding:20px;">
		<h2><img src="img/folder.png" width="16" height="16" alt="folder" /> <?= dirname(utf8_encode($_GET['dir'])); ?></h2>
		<hr />
		<br />
		<div class="title">
			<label for="permissions"><?= $kaTranslate->translate('Private:New name'); ?></label>
			<input type="text" name="newname" style="width:400px;" value="<?= basename(utf8_encode($_GET['dir'])); ?>" />
			</div>
		<br />
		<br />

		</div>
		<div class="submit">
			<input type="submit" name="rename" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" />
			</div>
	</form>

