<?php /* (c) Kalamun.org - GNU/GPL 3 */

require_once("main.lib.php");
$orichalcum = new kaOrichalcum();
$orichalcum->init( array("x-frame-options"=>"", "check-permissions"=>false) );

define("PAGE_NAME",$kaTranslate->translate('Uploads:Uploads'));


/*
fileType = image | document | media
search = search key
limit = maximum number of items you can upload / select
*/
if(!isset($_GET['fileType']) || $_GET['fileType']=="") $_GET['fileType']='image';
if(!isset($_GET['search'])) $_GET['search']="";
if(!isset($_GET['limit'])) $_GET['limit']=1000;
if(!isset($_GET['orderby'])) $_GET['orderby']='creation_date';
if(!isset($_GET['submitlabel'])) $_GET['submitlabel']=$kaTranslate->translate('Uploads:Confirm');
if(!isset($_GET['onsubmit'])) $_GET['onsubmit']="false";
else $_GET['onsubmit']='window.parent.'.$_GET['onsubmit'];
if(!isset($_GET['submitlabel2'])) $_GET['submitlabel2']="";
if(!isset($_GET['onsubmit2'])) $_GET['onsubmit2']="false";
else $_GET['onsubmit2']='window.parent.'.$_GET['onsubmit2'];

$getcollection="";
foreach($_GET as $k=>$v)
{
	$getcollection.='&'.$k.'='.urlencode($v);
}
$getcollection=substr($getcollection,1);


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

<?php
/****************************************
* UPLOAD
****************************************/
?>
<div id="uploadcontainer">
	<div id="uploadheader">
		<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
		<div class="topbar">
			<input type="hidden" id="fileType" value="<?= $_GET['fileType']; ?>">
			<input type="text" id="search" placeholder="<?= $kaTranslate->translate('Uploads:search for name, caption, id...'); ?>">

			<div class="orderby">
				<?= $kaTranslate->translate('Uploads:Order by'); ?>
				<ul id="filters">
				<?php
 				// filters
				$menu=array();
				$menu['creation_date']=$kaTranslate->translate('Uploads:Date');
				$menu['filename']=$kaTranslate->translate('Uploads:File name');
				$menu['alt']=$kaTranslate->translate('Uploads:Caption');
				
				foreach($menu as $ka=>$m)
				{
					echo '<li><a href="#" class="'.($_GET['orderby']==$ka?'selected':'').'" ref="'.$ka.'">'.$m.'</a></li>';
				}
				?>
				</ul>
			</div>
			
		</div>
	</div>

	<div id="uploadcontents">

		<div id="drop"><div class="hover"></div>
			<div class="uploadinfo">
				<?= $kaTranslate->translate('Uploads:drag and drop your files here'); ?> <?= $kaTranslate->translate('Uploads:or'); ?>:<br>
				<span id="browseContainer">
					<form id="upload" method="post" action="<?= ADMINDIR.'inc/ajax/uploadHandler.php'; ?>" enctype="multipart/form-data">
						<input type="file" name="fileselect[]" id="browse" multiple="multiple" />
					</form>
					<?= $kaTranslate->translate('Uploads:Upload from your computer'); ?></span>
				<?= $kaTranslate->translate('Uploads:or'); ?>
				<span id="internetuploadButton"><?= $kaTranslate->translate('Uploads:Upload from the internet'); ?></span>
			</div>
			<ul id="uploadedFileList"></ul>
		</div>		
		
		<div id="submit">
			<input type="submit" id="submitButton" value="<?= $_GET['submitlabel']; ?>" class="button">
			<?php if($_GET['submitlabel2']!="") { ?>
				<input type="submit" id="submitButton2" value="<?= $_GET['submitlabel2']; ?>" class="button">
				<?php } ?>
		</div>
		
	</div>
</div>

<div id="uploadeditor">
	<div class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></div>
	<iframe src=""></iframe>
</div>

<div id="internetupload">
	<div class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></div>
	<iframe src=""></iframe>
</div>
<?php
require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'inc/images.lib.php');
$kaImages = new kaImages();
$kaImages->cleanTmpDir();
?>
<script type="text/javascript">
	var kUploads=new kUploads();
	kUploads.init(document.getElementById('upload'),document.getElementById('drop'),document.getElementById('browse'),document.getElementById('uploadeditor'),document.getElementById('internetupload'),document.getElementById('uploadedFileList'),document.getElementById('search'),document.getElementById('filters'),document.getElementById('submitButton'),document.getElementById('submitButton2'),<?= $_GET['onsubmit']; ?>,<?= $_GET['onsubmit2']; ?>,'<?= $_GET['limit']; ?>');
	kUploads.loadImages();
	kAddEvent(document.getElementById('internetuploadButton'), "click", kUploads.openInternetUploadDialog);
</script>


</body>
</html>
