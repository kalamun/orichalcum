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
	$_POST['online'] = !isset($_POST['online']) ? 's' : 'n';
	
	$vars = array();
	$vars['idbanner'] = $_POST['idbanner'];
	$vars['type'] = $_POST['type'];
	$vars['online'] = $_POST['online'];
	$vars['title'] = $_POST['title'];
	$vars['idcat'] = $_POST['idcat'];
	$vars['description'] = $_POST['description'];
	$vars['featuredimage'] = $_POST['featuredimage'];
	
	if(!$kaBanner->update($_POST['idbanner'], $vars)) $log=$kaTranslate->translate('Banner:Error while updating database');

	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Banner: Error while updating banner "'.$_POST['title'].'"(<em>ID: '.$_POST['idbanner'].'</em>)');
		}
	else {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Banner:Successfully Updated').'</div>';
		$kaLog->add("UPD",'Banner: Successfully updated banner "'.$_POST['title'].'" (<em>ID: '.$_POST['idbanner'].'</em>)');
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
/* BANNERS LIST */
if(!isset($_GET['idbanner']))
{ ?>
	<div class="tab">
		<dl>
		<?php
		$currentCategory = array();
		
 		foreach($kaCategorie->getList(TABLE_BANNER) as $c)
		{
			if(!isset($_GET['idcat'])) $_GET['idcat'] = $c['idcat'];
			if($_GET['idcat'] == $c['idcat']) $currentCategory = $c;
			?>
			<dt>
				<a href="?idcat=<?= $c['idcat']; ?>" class="<?= ($c['idcat']==$_GET['idcat']?'sel':''); ?>"><?= $c['categoria']; ?></a>
			</dt>
			<?php
		}
		
		$orderby = $kaMetadata->get(TABLE_CATEGORIE, $currentCategory['idcat'], 'orderby');
		$currentCategory['orderby'] = $orderby['value'];
		?>
		</dl>
	</div>
	<br />

	<?php
	// load js libraries for manual sorting
	if($currentCategory['orderby'] == 'ordine')
	{ ?>
		<script type="text/javascript" src="<?= ADMINDIR; ?>/js/drag_and_drop.js"></script>
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
		<?php
	}
	?>

	<div>
		<form action="" method="post" id="orderby">
			<table class="tabella">
			<thead>
				<tr>
					<th><?= $kaTranslate->translate('Banner:Title'); ?></th>
					<th><?= $kaTranslate->translate('Banner:Target URL'); ?></th>
					<th><?= $kaTranslate->translate('Banner:Views'); ?></th>
					<?php if($currentCategory['orderby'] == 'ordine') { ?><th><?= $kaTranslate->translate('Banner:Order'); ?></th><?php } ?>
				</tr>
			</thead>
			<tbody  class="DragZone">
			<?php 
			foreach($kaBanner->getList($_GET['idcat']) as $banner)
			{
				?><tr>
					<td>
						<a href="?idcat=<?= $_GET['idcat']; ?>&idbanner=<?= $banner['idbanner']; ?>"><?php  echo $banner['title']; ?></a>
						<?php  if($banner['online']=='n') echo '<small class="alert">'.$kaTranslate->translate('Banner:DRAFT').'</small>'; ?><br />
						<small class="actions"><a href="?idcat=<?= $_GET['idcat']; ?>&idbanner=<?= $banner['idbanner']; ?>">Modifica</a></small>
					</td>
					<td class="percorso"><?= $banner['url']; ?></td>
					<td class="views"><?= $banner['views']; ?></td>
					<?php if($currentCategory['orderby'] == 'ordine') { ?>
						<td class="sposta">
							<input type="hidden" name="idbanner[]" value="<?= $banner['idbanner']; ?>" />
							<img src="<?= ADMINRELDIR; ?>img/drag_v.gif" width="18" height="18" alt="Sposta" /> Sposta
						</td>
					<?php } ?>
				</tr>
				<?php 
			}
			?>
			</tbody>
			</table>
		</form>
	</div>

<?php


/* EDIT A SINGLE BANNER*/
} else { ?>
	<div class="bannerList">
		<form action="" method="post">
			<?php 
			$banner = $kaBanner->get($_GET['idbanner']);
			$w = $kaMetadata->get(TABLE_CATEGORIE, $banner['categoria'], 'width');
			$h = $kaMetadata->get(TABLE_CATEGORIE, $banner['categoria'], 'height');
			$o = $kaMetadata->get(TABLE_CATEGORIE, $banner['categoria'], 'orderby');
			$banner['width'] = intval($w['value']);
			$banner['height'] = intval($h['value']);
			$banner['orderby'] = intval($o['value']);
			if($banner['width'] == 0) $banner['width']=300;
			?>
			<input type="hidden" name="idbanner" value="<?= $banner['idbanner']; ?>" />
			
			<div class="box">
			<?php 
			$option=array("image", "text", "code");
			$value=array($kaTranslate->translate("Banner:Image"), $kaTranslate->translate("Banner:Text"), $kaTranslate->translate("Banner:External code"));
			echo b3_create_select("type", $kaTranslate->translate("Banner:Banner type").' ', $value, $option, $banner['type']);
			?></div><br>


			<div class="box">
				<?= $kaTranslate->translate('Banner:Category'); ?><br>
				<?php
				$kaCategorie=new kaCategorie();

				$i=0;
				foreach($kaCategorie->getList(TABLE_BANNER) as $cat)
				{
					$w = $kaMetadata->get(TABLE_CATEGORIE, $cat['idcat'], 'width');
					$h = $kaMetadata->get(TABLE_CATEGORIE, $cat['idcat'], 'height');

					echo b3_create_input("idcat", "radio", ' '.$cat['categoria'].' ('.$w['value'].' x '.$h['value'].')', $cat['idcat'], "", "", ( $cat['idcat']==$banner['categoria'] ? 'checked="checked"' : ''), true).'<br>';
					$i++;
				}
				?>
			</div><br>
			<br>

			<div class="title"><?= b3_create_input("title","text",$kaTranslate->translate('Banner:Title').'<br>', $banner['title'], "100%"); ?></div><br>
			
			<div class="hidewhencode">
				<?= b3_create_textarea("description",$kaTranslate->translate('Banner:Short description').'<br>', ($banner['type'] != 'code' ? $banner['description'] : ''), "100%","100px",RICH_EDITOR); ?><br><br>
			</div>
			
			<div class="hidewhenimage hidewhentext">
				<?= b3_create_textarea("code",$kaTranslate->translate('Banner:Code').'<br>', ($banner['type'] == 'code' ? $banner['description'] : ''), "100%","100px",false); ?><br><br>
			</div>

			<div class="hidewhencode hidewhentext">
				<fieldset class="box" style="max-width:<?= $banner['width']; ?>px;"><legend><?= $kaTranslate->translate('News:Featured Image'); ?></legend>
					<div id="featuredImageContainer"><?php
					if($banner['featuredimage']!=0)
					{
						?><img src="<?= BASEDIR.$banner['banner']['thumb']['url']; ?>"><?php
					}
					?></div>
					<input type="hidden" name="featuredimage" id="featuredimage" value="<?= $banner['featuredimage']; ?>">
					<a href="javascript:k_openIframeWindow('../inc/uploadsManager.inc.php?limit=1&submitlabel=<?= urlencode($kaTranslate->translate('Banner:Set featured image')); ?>&onsubmit=setFeaturedImage','90%','90%');" class="smallbutton"><?= $kaTranslate->translate('News:Choose banner image'); ?></a><br>
					<small><a href="javascript:removeFeaturedImage();" id="removeFeaturedImage" class="warning" style="display:none;"><?= $kaTranslate->translate('UI:Delete'); ?></a></small>
				</fieldset>
				<script type="text/javascript">
				var setFeaturedImage=function(ids)
				{
					var inpt=document.getElementById('featuredimage');
					if(inpt)
					{
						inpt.value=ids[0].id;
						var imgcontainer=document.getElementById('featuredImageContainer');
						imgcontainer.innerHTML='';
						var img=document.createElement('IMG');
						img.src=ids[0].dir + ids[0].thumbnail;
						imgcontainer.appendChild(img);
						var imgcontainer=document.getElementById('removeFeaturedImage').style.display='';
					}
					k_closeIframeWindow();
				}
				
				var removeFeaturedImage=function(ids)
				{
					var inpt=document.getElementById('featuredimage');
					if(inpt)
					{
						inpt.value='';
						var imgcontainer=document.getElementById('featuredImageContainer');
						imgcontainer.innerHTML='';
						var imgcontainer=document.getElementById('removeFeaturedImage').style.display='none';
					}
				}
				</script>
			</div>
			<br>

			<div class="hidewhencode">
				<?= b3_create_input("url", "text", $kaTranslate->translate('Banner:Target URL'), "http://","100%"); ?><br><br>
			</div>

			<label><?= $kaTranslate->translate('Banner:Views'); ?>:</label> <?= $banner['views']; ?><br>
			<br>
			
			<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" onclick="b3_openMessage('<?= addslashes($kaTranslate->translate('Banner:Saving...')); ?>');" /></div>
		</form>
	</div>

	<script>
		function setType()
		{
			var type = document.getElementById('type').value;
			for(var i=0, c=document.querySelectorAll('.hidewhenimage'); c[i]; i++)
			{
				c[i].style.display = 'block';
			}
			for(var i=0, c=document.querySelectorAll('.hidewhentext'); c[i]; i++)
			{
				c[i].style.display = 'block';
			}
			for(var i=0, c=document.querySelectorAll('.hidewhencode'); c[i]; i++)
			{
				c[i].style.display = 'block';
			}
			for(var i=0, c=document.querySelectorAll('.hidewhen'+type); c[i]; i++)
			{
				c[i].style.display = 'none';
			}
		}
		kAddEvent(document.getElementById('type'), "change", setType);
		setType();
	</script>
			
			
	<?php 
}

include_once("../inc/foot.inc.php");
