<?php 
define("PAGE_NAME","Shop:Categories management");
include_once("../inc/head.inc.php");
include_once("../inc/categorie.lib.php");

$kaCategorie=new kaCategorie();

/* AZIONI */
if(isset($_POST['save'])&&count($_POST['cat'])>0) {
	$log="";
	$order=array();
	foreach($_POST['cat'] as $ka=>$v) {
		$order[$_POST['ref'][$ka]][]=$v;
		}
	foreach($order as $ref=>$categories) {
		foreach($categories as $ordine=>$idcat) {
			if(!$kaCategorie->updateOrder($idcat,$ordine+1,$ref,TABLE_SHOP_ITEMS)) $log="Errore durante il salvataggio dell'ordinamento";
			}
		}
	if($log!="") echo '<div id="MsgAlert">'.$log.'</div>';
	}

elseif(isset($_POST['sortAZ'])) {
	$log=$kaCategorie->sortby('`categoria` ASC',TABLE_SHOP_ITEMS);
	}

elseif(isset($_POST['insert']))
{
	$vars = array();
	$vars['categoria'] = $_POST['titolo'];
	$vars['dir'] = $_POST['dir'];
	$vars['tabella'] = TABLE_SHOP_ITEMS;
	$vars['description'] = $_POST['description'];
	
	$log=$kaCategorie->add($vars);

	if($log==false)
	{
		echo '<div id="MsgAlert">Problemi durante la creazione della categoria</div>';
		$kaLog->add("ERR",'Errore durante la creazione della categoria <em>'.b3_htmlize($_POST['categoria'],true,"").'</em> nel Negozio');
	} else {
		echo '<div id="MsgSuccess">Categoria inserita con successo</div>';
		$kaLog->add("INS",'Creata la categoria <em>'.b3_htmlize($_POST['titolo'],true,"").'</em> nel Negozio');
	}
}

elseif(isset($_POST['update'])) {
	$vars=array();
	$vars['categoria']=$_POST['categoria'];
	$vars['description']=$_POST['description'];
	$vars['featuredimage']=$_POST['featuredimage'];
	$vars['dir']=$_POST['dir'];
	if(isset($_POST['photogallery'])) $vars['photogallery']=$_POST['photogallery'];
	$vars['tabella']=TABLE_SHOP_ITEMS;

	$log=$kaCategorie->update($_POST['idcat'],$vars);
	if($log==false) {
		echo '<div id="MsgAlert">Problemi durante la modifica della categoria</div>';
		$kaLog->add("ERR",'Errore durante la modifica della categoria <em>'.b3_htmlize($_POST['categoria'],true,"").'</em> nel Negozio');
		}
	else {
		echo '<div id="MsgSuccess">Categoria modificata con successo</div>';
		$kaLog->add("INS",'Modificata la categoria <em>'.b3_htmlize($_POST['categoria'],true,"").'</em> nel Negozio');
		}
	}

elseif(isset($_GET['delete'])) {
	$cat=$kaCategorie->get($_GET['delete']);
	$log=$kaCategorie->del($_GET['delete'],TABLE_SHOP_ITEMS);
	if($log==false) {
		echo '<div id="MsgAlert">Problemi durante l\'eliminazione della categoria</div>';
		$kaLog->add("ERR",'Errore durante l\'eliminazione della categoria <em>'.b3_htmlize($cat['categoria'],true,"").'</em> dal Negozio');
		}
	else {
		echo '<div id="MsgSuccess">Categoria eliminata con successo</div>';
		$kaLog->add("INS",'Eliminata la categoria <em>'.b3_htmlize($cat['categoria'],true,"").'</em> dal Negozio');
		}
	}
/* FINE AZIONI */



?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<?php 

if(isset($_GET['idcat'])) {
	//modifica
	$cat=$kaCategorie->get($_GET['idcat'],TABLE_SHOP_ITEMS);
	?>
	<script type="text/javascript" src="js/categorie.js"></script>
	<script style="text/javascript" src="js/edit.js"></script>
	
	<form action="?" method="post">
		<div class="subset">
			<div class="featuredimage">
				<div class="container">
					<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Featured Image'); ?></legend>
						<div id="featuredImageContainer"><?php
							if($cat['featuredimage']>0)
							{
								$img = $kaImages->getImage($cat['featuredimage']);
								?>
								<img src="<?= BASEDIR.$img['thumb']['url']; ?>">
								<?php 
							}
							?></div>
						<input type="hidden" name="featuredimage" id="featuredimage" value="<?= $cat['featuredimage']; ?>">
						<a href="javascript:k_openIframeWindow('../inc/uploadsManager.inc.php?limit=1&submitlabel=<?= urlencode($kaTranslate->translate('Shop:Set featured image')); ?>&onsubmit=setFeaturedImage','90%','90%');" class="smallbutton"><?= $kaTranslate->translate('Shop:Choose featured image'); ?></a>
						<small><a href="javascript:removeFeaturedImage();" id="removeFeaturedImage" class="warning" <?php  if($cat['featuredimage']==0) echo 'style="display:none;"'; ?>><?= $kaTranslate->translate('UI:Delete'); ?></a></small>
					</fieldset><br />
				</div>
			</div>
		</div>

		<div class="topset">
			<input type="hidden" name="idcat" value="<?= $cat['idcat']; ?>" />
			<div class="title"><?= b3_create_input("categoria","text",$kaTranslate->translate("Shop:Category name")."<br />",b3_lmthize($cat['categoria'],"input"),"70%",250); ?></div>
			<div class="URLBox"><?= b3_create_input("dir","text","Indirizzo della pagina: ".BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_shop',1).'/[categoria]/',b3_lmthize($cat['dir'],"input"),"400px",64,'onkeyup="checkURL(this)"'); ?> <span id="dirYetExists" style="display:none;">Questo indirizzo esiste gi&agrave;!</span></div>
			<script type="text/javascript">
				var target=document.getElementById('dir');
				target.setAttribute("oldvalue",target.value);
			</script>
			<br>
			
			<div class="description"><?= b3_create_textarea("description",$kaTranslate->translate('Shop:Category description')."<br />",b3_htmlize($cat['description'],"textarea"),"95%","100px",RICH_EDITOR); ?></div><br>

			<div class="box <?= trim($cat['photogallery'],",")=="" ? "closed" : "opened"; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('UI:Photogallery'); ?></h2>
				<a href="javascript:k_openIframeWindow('../inc/uploadsManager.inc.php?submitlabel=<?= urlencode($kaTranslate->translate('UI:Add selected images to the list')); ?>&onsubmit=kAddImagesToPhotogallery','90%','90%');" class="smallbutton"><?= $kaTranslate->translate('UI:Add images to gallery'); ?></a>
				<div id="photogallery"></div>
				<script type="text/javascript">
					kLoadPhotogallery('<?= $cat['photogallery']; ?>');
				</script>
			</div>
		</div>
	<br>

	<div class="submit"><input type="button" value="Annulla" class="button" onclick="window.location='?';" /> <input type="submit" name="update" value="Salva le modifiche" class="button" /></div>
	</form>
	<?php 
	}

else { ?>
	<div class="subset">
		</div>

	<div class="topset">

	<script type="text/javascript" src="<?php  echo ADMINDIR; ?>/js/drag_and_drop.js"></script>
	<script type="text/javascript">
		kDragAndDrop=new kDrago();
		kDragAndDrop.dragClass("DragZone");
		kDragAndDrop.dropClass("DragZone");
		kDragAndDrop.containerTag('LI');
		kDragAndDrop.addDropTag('LI');
		kDragAndDrop.addDropTag('UL');
		kDragAndDrop.onDrag(function (drag,target) {
			document.getElementById('orderby').className='ondrag';
			var container=drag.parentNode.childNodes;
			if(target.className!='DragZone'&&target!=drag) {
				if(target.tagName=='LI') {
					if((parseInt(target.getAttribute("ddTop"))+target.childNodes[0].offsetHeight/2)>kWindow.mousePos.y) target.parentNode.insertBefore(drag,target);
					else target.parentNode.insertBefore(drag,target.nextSibling);
					}
				else if(target.tagName=='UL') target.appendChild(drag);
				}
			kDragAndDrop.savePosition();
			});
		kDragAndDrop.onDrop(function (drag,target) {
			document.getElementById('orderby').className='';
			var ref=0;
			for(var i=0;drag.parentNode.parentNode.childNodes[0].childNodes[i];i++) {
				if(drag.parentNode.parentNode.childNodes[0].childNodes[i].name=="cat[]") {
					ref=drag.parentNode.parentNode.childNodes[0].childNodes[i].value;
					break;
					}
				}
			for(var i=0;drag.getElementsByTagName('INPUT')[i];i++) {
				if(drag.getElementsByTagName('INPUT')[i].name=="ref[]") {
					drag.getElementsByTagName('INPUT')[i].value=ref;
					break;
					}
				}
			});
		
		function saving() {
			b3_openMessage('saving...',false);
			//document.getElementById('orderby').submit();
			}
		</script>

	
	
	<form action="" method="post" id="orderby" onsubmit="saving();">
		<div class="dragdrop">
			<ul class="DragZone"><?php 
				function printSubcat($cat) {
					global $categorie;
					global $kaTranslate;
					?><li><div class="elm">
					<strong><?= $cat['data']['categoria']; ?></strong><br />
					<small><?= $cat['data']['dir']; ?>&nbsp;</small>
					<input type="hidden" name="cat[]" value="<?= $cat['data']['idcat']; ?>" />
					<input type="hidden" name="ref[]" value="<?= $cat['data']['ref']; ?>" />
					<span style="text-align:right;">
						<a href="?idcat=<?= $cat['data']['idcat']; ?>" class="smallbutton"><?= $kaTranslate->translate('UI:Edit'); ?></a>
						<a href="?delete=<?= $cat['data']['idcat']; ?>" onclick="return confirm('Sei sicuro di voler eliminare questa voce?');" class="smallalertbutton"><?= $kaTranslate->translate('UI:Delete'); ?></a>
						</span>
					</div>
					<ul><?php 
					if(count($cat)>1) {
						foreach($cat as $ka=>$v) {
							if(is_numeric($ka)) {
								printSubcat($v);
								}
							}
						} ?>
					</ul></li><?php 
					}

				$categorie=$kaCategorie->getStructuredList(TABLE_SHOP_ITEMS);
				foreach($categorie as $cat) {
					printSubcat($cat);
					}

				?></ul>
			</div>
		<br />
		<br />
		
		<div class="submit" id="submit">
			<input type="submit" name="save" class="button" value="<?= $kaTranslate->translate('Shop:Save order'); ?>" />
			<input type="submit" name="sortAZ" class="smallbutton" value="<?= $kaTranslate->translate('Shop:Sort A-Z'); ?>" />
			</div>
	</form>

	<br />
	<br />
	<br />
	
	<script type="text/javascript" src="js/categorie.js"></script>
	
	<input type="button" class="button" value="<?= $kaTranslate->translate('Shop:New category'); ?>" onclick="showReq('nuovaCat');" /></td>
	<div id="nuovaCat" style="display:none;">
		<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Add a new category'); ?></legend>
		<form action="" method="post">
		<div class="title"><?= b3_create_input("titolo","text",$kaTranslate->translate('Shop:Category name')."<br />","","95%",250,'autocomplete="off" onkeyup="title2url()" onblur="titleBlur()"'); ?></div>
		<div class="URLBox"><?= b3_create_input("dir","text",$kaTranslate->translate('Shop:Category URL').": ".BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_shop',1).'/',(isset($copyfrom['dir'])?$copyfrom['dir'].'-'.date("Ymd"):''),"400px",64,'onkeyup="checkURL(this)"'); ?> <span id="dirYetExists" style="display:none;">Questo indirizzo esiste gi&agrave;!</span></div><br>
		<div class="description"><?= b3_create_textarea("description",$kaTranslate->translate('Shop:Category description')."<br />","","95%","100px",RICH_EDITOR); ?></div><br>
		<br>
		<div class="submit"><input type="submit" name="insert" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" /></div>
		</form>
		</fieldset>
	</div>

	<?php  }

include_once("../inc/foot.inc.php");
