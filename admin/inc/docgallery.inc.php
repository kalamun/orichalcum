<?php
/* (c) Kalamun.org - GNU/GPL 3 */

require_once('./connect.inc.php');
require_once('./kalamun.lib.php');
require_once('./sessionmanager.inc.php');
if(!isset($_SESSION['iduser'])) die('Non hai il permesso di utilizzare questa funzione');

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
<title><?php echo ADMIN_NAME." - Upload Immagini"; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<link rel="stylesheet" href="<?php echo ADMINDIR; ?>css/screen.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?php echo ADMINDIR; ?>css/main.lib.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?php echo ADMINDIR; ?>css/docgallery.css?<?= SW_VERSION; ?>" type="text/css" />

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
	if(!isset($_GET['max'])) $_GET['max']=999;
	if(!isset($_GET['label'])) $_GET['label']="Inserisci un documento";

	require_once('./documents.lib.php');
	require_once('./docgallery.lib.php');
	$kaDocuments=new kaDocuments();
	$kaDocgallery=new kaDocgallery();


	if(isset($_POST['doc'])) {
		$kaDocgallery->sort($_GET['mediatable'],$_GET['mediaid'],$_POST['doc'],$_GET['start'],$_GET['max']);
		}

	$documenti=$kaDocgallery->getList($_GET['mediatable'],$_GET['mediaid'],'ordine','ordine>='.$_GET['start'].' AND ordine<'.($_GET['start']+$_GET['max']));
	if(count($documenti)<$_GET['max']||$_GET['max']==0) { ?>
		<a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/docgalleryManager.inc.php?refid=<?php echo $_GET['refid']; ?>&mediatable=<?php echo $_GET['mediatable']; ?>&mediaid=<?php echo $_GET['mediaid']; ?>&start=<?php echo $_GET['start']; ?>&max=<?php echo $_GET['max']; ?>','800px','500px');" class="smallbutton"><img src="<?= ADMINRELDIR; ?>img/upload.png" width="16" height="16" alt="" /> <?php echo $_GET['label']; ?></a>
		<?php } ?>

	<form action="?refid=<?php echo $_GET['refid']; ?>&mediatable=<?php echo $_GET['mediatable']; ?>&mediaid=<?php echo $_GET['mediaid']; ?>&start=<?php echo $_GET['start']; ?>&max=<?php echo $_GET['max']; ?>&label=<?php echo str_replace('"','\"',$_GET['label']); ?>" method="post" id="formOrdinamento">
	<div id="wait" style="display:none;"><h3>Operazione in corso, aspetta...</h3></div>
	<div id="DragZone" class="DragZone">
		<ul class="dragdrop"><?php
		if(count($documenti)==0) echo '<li><div class="empty">Nessun documento caricato</div></li>';
		else {
			foreach($documenti as $doc) {
				if(isset($doc['iddoc'])&&$doc['iddoc']>0) {
					?><li class="documentPreview"><input type="hidden" name="doc[]" value="<?php echo $doc['iddocg']; ?>" /><?php
					?><a href="javascript:window.parent.k_openIframeWindow(ADMINDIR+'inc/docgalleryManager.inc.php?refid=<?php echo $_GET['refid']; ?>&mediatable=<?php echo $_GET['mediatable']; ?>&mediaid=<?php echo $_GET['mediaid']; ?>&mode=remove&iddocg=<?php echo $doc['iddocg']; ?>&start=<?php echo $_GET['start']; ?>&max=<?php echo $_GET['max']; ?>','800px','500px');" title="<?php echo str_replace('"','&quot;',$doc['filename']); ?>"><?php
					if(trim($doc['alt'])=="") $doc['alt']=$doc['filename'];
					echo $doc['alt'];
					echo '</a>';
					echo '</li>';
					}
				else {
					//rimuovo i file che non esistono più
					$kaDocgallery->del($doc['iddocg']);
					}
				}
			}
		?></ul></div>
	</form>

		<script type="text/javascript" src="<?php echo ADMINDIR; ?>/js/drag_and_drop.js"></script>
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