<?php
/* (c) Kalamun.org - GNU/GPL 3 */

require_once('./connect.inc.php');
require_once('./kalamun.lib.php');
require_once('./sessionmanager.inc.php');
$kaTranslate=new kaAdminTranslate();
if(!isset($_SESSION['iduser'])) die($kaTranslate->translate('You don\'t have permission to use this function'));

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
<title><?php echo ADMIN_NAME." - Upload"; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<link rel="stylesheet" href="<?php echo ADMINDIR; ?>css/screen.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?php echo ADMINDIR; ?>css/main.lib.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?php echo ADMINDIR; ?>css/imgallery.css?<?= SW_VERSION; ?>" type="text/css" />
<script type="text/javascript">var ADMINDIR='<?php echo str_replace("'","\'",ADMINDIR); ?>';</script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/main.lib.js"></script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/kalamun.js"></script>
</head>

<body>
<div class="bkg">
	<?php
	if(!isset($_GET['mediatable'])) die('Errore di caricamento: manca un riferimento alla tabella.');
	if(!isset($_GET['mediaid'])) die('Errore di caricamento: manca un riferimento all\'id.');
	if(!isset($_GET['start'])||$_GET['start']<1) $_GET['start']=1;
	if(!isset($_GET['max'])||$_GET['max']<$_GET['start']) $_GET['max']=999;
	if(!isset($_GET['label'])) $_GET['label']="Carica un'immagine";

	require_once('./images.lib.php');
	require_once('./imgallery.lib.php');
	$kaImages=new kaImages();
	$kaImgallery=new kaImgallery();


	if(isset($_POST['img'])) {
		$kaImgallery->sort($_GET['mediatable'],$_GET['mediaid'],$_POST['img'],$_GET['start'],$_GET['max']);
		}

	$immagini_tmp=$kaImgallery->getList($_GET['mediatable'],$_GET['mediaid'],'ordine','ordine>='.$_GET['start'].' AND ordine<'.($_GET['start']+$_GET['max']));
	$immagini=array();
	foreach($immagini_tmp as $img) {
		if(isset($img['idimg'])&&$img['idimg']>0) {
			$immagini[]=$img;
			}
		else {
			//rimuovo i file che non esistono più
			$kaImgallery->del($img['idimga']);
			}
		}

	if(count($immagini)<$_GET['max']||$_GET['max']==0) { ?>
		<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/imgalleryManager.inc.php?refid=<?php echo $_GET['refid']; ?>&mediatable=<?php echo $_GET['mediatable']; ?>&mediaid=<?php echo $_GET['mediaid']; ?>&start=<?php echo $_GET['start']; ?>&max=<?php echo $_GET['max']; ?>','800px','500px');" class="smallbutton"><img src="<?= ADMINRELDIR; ?>img/upload.png" width="16" height="16" /> <?php echo $_GET['label']; ?></a>
		<?php } ?>

	<form action="?refid=<?php echo $_GET['refid']; ?>&mediatable=<?php echo $_GET['mediatable']; ?>&mediaid=<?php echo $_GET['mediaid']; ?>&start=<?php echo $_GET['start']; ?>&max=<?php echo $_GET['max']; ?>&label=<?php echo str_replace('"','\"',$_GET['label']); ?>" method="post" id="formOrdinamento">
	<div id="wait" style="display:none;"><h3><?= $kaTranslate->translate('UI:Please Wait...'); ?></h3></div>
	<div id="DragZone" class="DragZone">
		<ul class="dragdrop"><?php
		if(count($immagini)==0) echo '<li><div class="empty">'.$kaTranslate->translate('Img:No Images').'</div></li>';
		else {
			foreach($immagini as $img) {
				if(isset($img['idimg'])&&$img['idimg']>0) {
					?><li class="imagePreview"><input type="hidden" name="img[]" value="<?php echo $img['idimga']; ?>" /><?php
					?><a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/imgalleryManager.inc.php?refid=<?php echo $_GET['refid']; ?>&mediatable=<?php echo $_GET['mediatable']; ?>&mediaid=<?php echo $_GET['mediaid']; ?>&start=<?php echo $_GET['start']; ?>&max=<?php echo $_GET['max']; ?>&mode=remove&idimga=<?php echo $img['idimga']; ?>','800px','500px');"><?php
					if(file_exists(BASERELDIR.$img['thumb']['url'])) {
						$size=getimagesize(BASERELDIR.$img['thumb']['url']);
						$size[0]>100?$w=100:$w=$size[0];
						$h=round($w/$size[0]*$size[1]);
						if($h>100) { $h=100; $w=round($h/$size[1]*$size[0]); }
						echo '<img src="'.BASERELDIR.$img['thumb']['url'].'?'.rand(0,666).'" width="'.$w.'" height="'.$h.'" style="margin-top:'.ceil((100-$h)/2).'px;" alt="'.str_replace('"','&quot;',$img['alt']).'" class="thumb">';
						}
					else echo 'Error loading image';
					echo '</a>';
					echo '</li>';
					}
				}
			}
		?></ul></div>
	</form>

		<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/drag_and_drop.js"></script>
		<script type="text/javascript">
				kDragAndDrop=new kDrago();
				kDragAndDrop.dragClass="DragZone";
				kDragAndDrop.dropClass="DragZone";
				kDragAndDrop.containerTag('LI');
				kDragAndDrop.onDrag(function () {
					var drag=kDragAndDrop.getDragObject();
					var target=kDragAndDrop.getFlyingOver();
					var container=drag.parentNode.childNodes;
					for(var i=0;container[i];i++) {
						if(container[i]==drag) {
							target.parentNode.insertBefore(drag,target.nextSibling);
							break;
							}
						else if(container[i]==target) {
							target.parentNode.insertBefore(drag,target);
							break;
							}
						}
					kDragAndDrop.savePosition();
					});
				kDragAndDrop.onDrop(function (drag,target) {
					document.getElementById('DragZone').style.display='none';
					document.getElementById('wait').style.display='block';
					document.getElementById('formOrdinamento').submit();
					});
			</script>
	<div style="clear:both;"></div>
	</div>
</body>
</html>