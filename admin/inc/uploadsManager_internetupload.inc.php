<?php /* (c) Kalamun.org - GNU/GPL 3 */

if(!isset($_GET['id'])) die('Invalid ID');

require_once("main.lib.php");
$orichalcum = new kaOrichalcum();
$orichalcum->init( array("x-frame-options"=>"", "check-permissions"=>false) );

define("PAGE_NAME",$kaTranslate->translate('Uploads:Loading'));
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?= $_SESSION['ll']; ?>" lang="<?= $_SESSION['ll']; ?>">
<head>
<title><?= ADMIN_NAME." - ".PAGE_NAME; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/screen.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/main.lib.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/uploadsmanager.css?<?= SW_VERSION; ?>" type="text/css" />

<script type="text/javascript">
	var ADMINDIR='<?= str_replace("'","\'",ADMINDIR); ?>';
</script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/dictionary.js.php?<?= SW_VERSION; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/kalamun.js?<?= SW_VERSION; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/main.lib.js?<?= SW_VERSION; ?>" charset="utf-8"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/uploadsManager.js?<?= SW_VERSION; ?>" charset="utf-8"></script>
</head>

<body>

<script type="text/javascript">
	var closeModal=true;
	if(window.parent.kUploads) closeModal=false;
</script>


<div class="padding">
	<form action="" method="post" enctype="multipart/form-data" id="uploadForm">

		<div class="title">
			<?= b3_create_input("url", "text", $kaTranslate->translate('Uploads:URL of the file to import').' ', "", "100%", false, 'placeholder="http://"', false); ?>
		</div>

		<br>
		<?= b3_create_input("copy", "radio", $kaTranslate->translate('Uploads:Copy the remote file into your website').' ', "true", "auto", false, "checked", true); ?><br>
		<?= b3_create_input("copy", "radio", $kaTranslate->translate('Uploads:Dont\'t import, create an hotlink').' ', "false", "auto", false, "", true); ?><br>
		<br>
	
		<div class="submit">
			<input type="submit" name="save"  id="startUpload" value="<?= $kaTranslate->translate('UI:Upload'); ?>" class="button" />
			<input type="button" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" class="smallbutton" onclick="closeModal ? window.parent.k_closeIframeWindow() : window.parent.kUploads.closeInternetUploadDialog();">
		</div>
	</form>
</div>

<script type="text/javascript">
	var btn = document.getElementById('uploadForm');
	kAddEvent(btn, "submit", onSubmitHandler);
	
	function onSubmitHandler()
	{
		var url = document.getElementById('url').value;
		var copy = document.getElementsByName('copy')[0].checked ? 1 : 0;

		if(url=="") return false;
		if(url.substr(0,4)!="http") url = "http://"+url;
		
		window.parent.kUploads.addRemoteFileToQueue(url, copy);
		window.parent.kUploads.closeInternetUploadDialog();
	}
</script>

</body>
</html>
