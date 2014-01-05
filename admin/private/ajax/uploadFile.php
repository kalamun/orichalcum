<?
session_start();

define("PAGE_ID","private");
require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../inc/main.lib.php');
if(!isset($_SESSION['iduser'])) die('Non hai il permesso di utilizzare questa funzione');

if(!isset($_GET['dir'])) die('Fatal Error: no base directory specified');
$_GET['dir']=trim($_GET['dir']," ./");

require_once('../private.lib.php');
$kaPrivate=new kaPrivate();


if(isset($_POST['uploadFiles'])) {
	$log="";
	$_GET['dir']=utf8_decode($_GET['dir']);
	foreach($_FILES['file']['tmp_name'] as $ka=>$f) {
		$success=$kaPrivate->uploadFile($f,trim($_GET['dir'].'/'.utf8_decode($_FILES['file']['name'][$ka])," ./"));
		if($success==false) $log.="Errore durante il caricamento del file ".$_FILES['file']['name'][$ka]." in ".$_GET['dir'].".<br />";
		}
	}

else {
	require_once('../../inc/log.lib.php');
	$kaLog=new kaLog();

	$kaTranslate=new kaAdminTranslate();

	?>
	<div id="iPopUpHeader">
		<h1><?= $kaTranslate->translate('Private:Upload one or more files'); ?></h1>
		<a href="javascript:kCloseIPopUp();" class="closeWindow"><img src="../img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
		</div>
	<form action="ajax/uploadFile.php?dir=<?= urlencode(utf8_encode($_GET['dir'])); ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="uploadFiles" value="y" />
		<div style="padding:20px;">
			<div class="advFileInput">
				<div id="inputContainer"><input type="file" name="file[]" accept="*" multiple onchange="showSelectedFiles(this)" /></div>
				<input type="button" id="progressNumber" value="<?= $kaTranslate->translate('Img:Browse'); ?>" class="button" />
				<div id="fileList"></div>
				</div>
			</div>
			<div class="submit">
				<input type="button" id="uploadFileSave" value="<?= $kaTranslate->translate('Private:Upload files'); ?>" class="button" onclick="uploadFile(this.form);" />
				<input type="button" id="uploadFileUploading" value="<?= $kaTranslate->translate('Private:Uploading...'); ?>" class="button" style="display:none;" />
				</div>
				<div style="text-align:center;"><small><?
					$max_upload=(int)(ini_get('upload_max_filesize'));
					$max_post=(int)(ini_get('post_max_size'));
					$memory_limit=(int)(ini_get('memory_limit'));
					$uploadLimit=min($max_upload,$max_post,$memory_limit);
					echo $kaTranslate->translate('Private:Max upload size allowed').': '.$uploadLimit.' Mb';
					?>
					-
					<?
					$max_file_uploads=(int)(ini_get('max_file_uploads'));
					echo $kaTranslate->translate('Private:Max number of files').': '.$max_file_uploads;
					?>
				</small></div>
		</form>

	<? } ?>