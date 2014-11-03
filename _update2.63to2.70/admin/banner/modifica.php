<?php 
define("PAGE_NAME","Banner:Edit a banner");
include_once("../inc/head.inc.php");
include_once("./banner.lib.php");
include('../inc/categorie.lib.php');
$kaBanner=new kaBanner();
$kaCategorie=new kaCategorie();


/* AZIONI */
if(isset($_POST['update'])) {
	$log="";
	$_POST['online']=!isset($_POST['online'])?'s':'n';
	if(!$kaBanner->update($_POST['idbanner'],$_POST['alt'],$_POST['description'],$_POST['url'],$_POST['idcat'],$_POST['online'])) $log=$kaTranslate->translate('Banner:Error while updating database');
	if(isset($_FILES['banner']['tmp_name'])) {
		$w=$kaMetadata->get(TABLE_CATEGORIE,$_POST['idcat'],'width');
		$h=$kaMetadata->get(TABLE_CATEGORIE,$_POST['idcat'],'height');
		$r=$kaMetadata->get(TABLE_CATEGORIE,$_POST['idcat'],'resize');
		if(!$kaBanner->updateFile($_POST['idbanner'],$_FILES['banner'],$_POST['alt'],intval($w['value']),intval($h['value']),$r['value'])) $log=$kaTranslate->translate('Banner:Error while uploading the new file');
		}

	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Banner: Error while updating banner "'.$_POST['alt'].'"(<em>ID: '.$_POST['idbanner'].'</em>)');
		}
	else {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Banner:Successfully Updated').'</div>';
		$kaLog->add("UPD",'Banner: Successfully updated banner "'.$_POST['alt'].'" (<em>ID: '.$_POST['idbanner'].'</em>)');
		}
	}

elseif(isset($_POST['idbanner'])&&count($_POST['idbanner'])>0) {
	$log="";
	$ordine=array();
	foreach($_POST['idbanner'] as $ka=>$v) {
		$ordine[]=$v;
		}
	if(!$kaBanner->sort($ordine)) $log="Errore durante il salvataggio dell'ordinamento";
	if($log!="") echo '<div id="MsgAlert">'.$log.'</div>';
	}
/* FINE AZIONI */
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<?php 
if(!isset($_GET['idbanner'])) { ?>
	<div class="tab"><dl>
		<?php 		foreach($kaCategorie->getList(TABLE_BANNER) as $c) {
			if(!isset($_GET['idcat'])) $_GET['idcat']=$c['idcat'];
			?>
			<dt>
				<a href="?idcat=<?= urlencode($c['idcat']); ?>" class="<?= ($c['idcat']==$_GET['idcat']?'sel':''); ?>"><?= $c['categoria']; ?></a>
				</dt>
			<?php } ?>
		</dl></div>
	<br />


	<script type="text/javascript" src="<?php  echo ADMINDIR; ?>/js/drag_and_drop.js"></script>
	<script type="text/javascript">
		kDragAndDrop=new kDrago();
		kDragAndDrop.dragClass("DragZone");
		kDragAndDrop.dropClass("DragZone");
		kDragAndDrop.containerTag('TR');
		kDragAndDrop.onDrag(function (drag,target) {
			var container=drag.parentNode.childNodes;
			if(target.className!='DragZone'&&target!=drag) {
				if((parseInt(target.getAttribute("ddTop"))+target.offsetHeight/2)>kWindow.mousePos.y) target.parentNode.insertBefore(drag,target);
				else target.parentNode.insertBefore(drag,target.nextSibling);
				}
			kDragAndDrop.savePosition();
			});
		kDragAndDrop.onDrop(function (drag,target) {
			b3_openMessage('Salvataggio in corso',false);
			document.getElementById('orderby').submit();
			});
		</script>

	<div>
		<form action="" method="post" id="orderby">
			<table class="tabella">
			<thead><tr><th><?= $kaTranslate->translate('Banner:Title'); ?></th><th><?= $kaTranslate->translate('Banner:Target URL'); ?></th><th><?= $kaTranslate->translate('Banner:Views'); ?></th><th>Ordine</th></thead>
			<tbody  class="DragZone">
			<?php 
				foreach($kaBanner->getList($_GET['idcat']) as $banner) {
					?><tr>
						<td><a href="?idcat=<?= $_GET['idcat']; ?>&idbanner=<?= $banner['idbanner']; ?>"><?php  echo $banner['title']; ?></a>
							<?php  if($banner['online']=='n') echo '<small class="alert">'.$kaTranslate->translate('Banner:DRAFT').'</small>'; ?><br />
							<small class="actions"><a href="?idcat=<?= $_GET['idcat']; ?>&idbanner=<?= $banner['idbanner']; ?>">Modifica</a></small>
						<td class="percorso"><?= $banner['url']; ?></td>
						<td class="views"><?= $banner['views']; ?></td>
						<td class="sposta"><input type="hidden" name="idbanner[]" value="<?= $banner['idbanner']; ?>" /><img src="<?= ADMINRELDIR; ?>img/drag_v.gif" width="18" height="18" alt="Sposta" /> Sposta</td>
						</tr>
						<?php 
					}
				?></tbody></table>
			</form>
		</div>

<?php  }

else { ?>
	<div class="bannerList">
		<form action="" method="post" enctype="multipart/form-data">
			<?php 
			$banner=$kaBanner->get($_GET['idbanner']);
			$w=$kaMetadata->get(TABLE_CATEGORIE,$banner['categoria'],'width');
			$h=$kaMetadata->get(TABLE_CATEGORIE,$banner['categoria'],'height');
			$r=$kaMetadata->get(TABLE_CATEGORIE,$banner['categoria'],'resize');
			$banner['width']=intval($w['value']);
			$banner['height']=intval($h['value']);
			$banner['resize']=intval($r['value']);

			if(isset($banner['banner']['url'])) {
				$ext=strtolower(substr($banner['banner']['url'],strrpos($banner['banner']['url'],".")));
				if($ext=='.jpg'||$ext=='.gif'||$ext=='.png') {
					$size=getimagesize(BASERELDIR.$banner['banner']['url']);
					?><div class="bannerPreview"><img src="<?= BASEDIR.$banner['banner']['url']; ?>" width="<?= $size[0]; ?>" alt="" /></div><br /><br /><?php 
					}
				}
			?>
			<input type="hidden" name="idbanner" value="<?= $banner['idbanner']; ?>" />
			<table width="700">
			<tr><th><label for="idcat"><?= $kaTranslate->translate('Banner:Category'); ?></label></th><td><?php 
				$option=array();
				$value=array();
				foreach($kaCategorie->getList(TABLE_BANNER) as $c) {
					$option[]=$c['idcat'];
					$value[]=$c['categoria'];
					}
				echo b3_create_select("idcat","",$value,$option,$banner['categoria']);

				b3_create_input("alt","text","",$banner['title'],"300px");
				?></td></tr>
			<tr><th><label for="alt"><?= $kaTranslate->translate('Banner:Title'); ?></label></th><td><?= b3_create_input("alt","text","",$banner['title'],"300px"); ?></td></tr>
			<tr><th><label for="description"><?= $kaTranslate->translate('Banner:Short description'); ?></label></th><td><?= b3_create_textarea("description","",b3_lmthize($banner['description'],"textarea"),"99%","100px",RICH_EDITOR,false,TABLE_BANNER,$banner['idbanner']); ?></td></tr>
			<tr><th><label for="banner"><?= $kaTranslate->translate('Banner:New File'); ?></label></th><td><?= b3_create_input("banner","file","",""); ?>
				<?php  if($banner['width']>0) { ?><small>(<?= $banner['width']; ?> x <?= $banner['height']; ?> px)</small><?php  } ?>
				</td></tr>
			<tr><th><label for="url"><?= $kaTranslate->translate('Banner:Target URL'); ?></label></th><td><?= b3_create_input("url","text","",$banner['url'],"300px"); ?></td></tr>
			<tr><th><label><?= $kaTranslate->translate('Banner:Views'); ?></label></th><td><?= $banner['views']; ?></td></tr>
			</table>
			<br />
			<div class="submit">
				<input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" onclick="b3_openMessage('<?= addslashes($kaTranslate->translate('Banner:Saving...')); ?>');" />
				<div class="draft"><?= b3_create_input("online","checkbox",$kaTranslate->translate('Banner:DRAFT'),"n","","",($banner['online']=='n'?'checked':'')); ?></div>
				</div>
			</form>
		</div><?php 
	}

include_once("../inc/foot.inc.php");
