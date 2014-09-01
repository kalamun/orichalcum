<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Shop:Manage items");
include_once("../inc/head.inc.php");
include_once("./shop.lib.php");
include_once("../inc/comments.lib.php");
include_once("../inc/metadata.lib.php");

$pageLayout=$kaImpostazioni->getVar('admin-shop-layout',1,"*");
$pageMode=$kaImpostazioni->getVar('admin-shop-layout',2,"*");

$kaShop=new kaShop();
$kaMetadata=new kaMetadata;

if(isset($_GET['m'])) $currMonth=$_GET['m'];
else $currMonth=date("n");
if(isset($_GET['y'])) $currYear=$_GET['y'];
else $currYear=date("Y");

/* AZIONI */
if(!isset($_GET['idsitem'])) {
	if($kaImpostazioni->getVar('shop-order',1)=="ordine"&&isset($_POST['idsitem'])&&count($_POST['idsitem']>0)) {
		$log="";
		if(!$kaShop->sort($_POST['idsitem'])) $log="Errore durante il salvataggio dell'ordinamento";

		if($log!="") {
			echo '<div id="MsgAlert">'.$log.'</div>';
			$kaLog->add("ERR",'Shop: Errore nell\'ordinamento degli oggetti');
			}
		else {
			$kaLog->add("UPD",'Shop: Modificato l\'ordine degli oggetti');
			}
		}
	}
if(isset($_POST['update'])&&isset($_GET['idsitem'])) {
	$log="";
	
	$row=$kaShop->getItem($_GET['idsitem']);

	/* update translation table in all involved pages (past and current) */
	if(isset($_POST['translation_id'])) {
		// translation has this format: |LL=idpag|LL=idpag|...
		$translations="";
		$_POST['translation_id'][$_SESSION['ll']]=$_GET['idsitem'];
		foreach($_POST['translation_id'] as $k=>$v) {
			if($v!="") {
				$translations.=$k.'='.$v.'|';
				$kaShop->removePageFromTranslations($v);
				}
			}
		// first of all, clear translations from previous+current pages
		foreach($row['traduzioni'] as $k=>$v) {
			if($v!="") $kaShop->removePageFromTranslations($v);
			}
		// then set the new translations in the current pages
		foreach($_POST['translation_id'] as $k=>$v) {
			if($v!="") {
				$kaShop->setTranslations($v,$translations);
				}
			}
		}

	if(isset($_POST['date_day'])&&isset($_POST['date_hour'])) $date_date=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['date_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['date_hour']);
	else $date_date="false";
	if(isset($_POST['visible_day'])&&isset($_POST['visible_hour'])) $visible_date=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['visible_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['visible_hour']);
	else $visible_date="false";
	if(isset($_POST['expiration_day'])&&isset($_POST['expiration_hour'])) $expiration_date=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['expiration_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['expiration_hour']);
	else $expiration_date="false";

	if(strpos($pageLayout,",privatearea,")!==false) {
		$privatearea="";
		if(isset($_POST['private'])) {
			foreach($_POST['private'] as $dir) {
				$privatearea.=$dir."\n";
				}
			}
		}
	else $privatearea="false";
	
	if(isset($_POST['idcat'])) {
		$categorie=",";
		foreach($_POST['idcat'] as $idcat) { $categorie.=$idcat.','; }
		}
	else $categorie="false";

	isset($_POST['productcode'])?$_POST['productcode']=b3_htmlize($_POST['productcode'],false,""):$_POST['productcode']="false";
	isset($_POST['titolo'])?$_POST['titolo']=b3_htmlize($_POST['titolo'],false,""):$_POST['titolo']="false";
	isset($_POST['sottotitolo'])?$_POST['sottotitolo']=b3_htmlize($_POST['sottotitolo'],false,""):$_POST['sottotitolo']="false";
	isset($_POST['anteprima'])?$_POST['anteprima']=b3_htmlize($_POST['anteprima'],false):$_POST['anteprima']="false";
	isset($_POST['testo'])?$_POST['testo']=b3_htmlize($_POST['testo'],false):$_POST['testo']="false";
	isset($_POST['dir'])?$_POST['dir']=b3_htmlize($_POST['dir'],false,""):$_POST['dir']="false";
	isset($_POST['prezzo'])?$_POST['prezzo']=number_format($_POST['prezzo'],2,'.',''):$_POST['prezzo']="0";
	isset($_POST['scontato'])?$_POST['scontato']=number_format($_POST['scontato'],2,'.',''):$_POST['scontato']="0";
	isset($_POST['weight'])?$_POST['weight']=floatval($_POST['weight']):$_POST['weight']="0";
	if(!isset($_POST['template'])) $_POST['template']="false";
	if(!isset($_POST['layout'])) $_POST['layout']="false";
	if(!isset($_POST['qta'])) $_POST['qta']=0;
	if(!isset($_POST['online'])) $_POST['online']='y';
	if(!isset($_POST['customfield'])) $_POST['customfield']=array();
	if(!isset($_POST['featuredimage'])) $_POST["featuredimage"]=-1;
	if(!isset($_POST['manufacturer'])) $_POST["manufacturer"]=-1;

	$id=$kaShop->updateItem($_GET['idsitem'],$_POST['online'],$_POST['productcode'],$_POST['titolo'],$_POST['sottotitolo'],$_POST['anteprima'],$_POST['testo'],$categorie,$_POST['prezzo'],$_POST['scontato'],$date_date,$visible_date,$expiration_date,$_POST['qta'],$_POST['weight'],$_POST['layout'],$_POST['dir'],$privatearea,$_SESSION['ll'],$_POST['template'],$_POST['customfield'],$_POST['featuredimage'],$_POST['manufacturer']);
	if($id==false) $log="Problemi durante la modifica del database<br />";
	else {
		if(strpos($pageLayout,",seo,")!==false) {
			if(isset($_POST['seo_robots'])) $_POST['seo_robots']=implode(",",$_POST['seo_robots']);
			else $_POST['seo_robots']="";
			foreach($_POST as $ka=>$v) {
				if(substr($ka,0,4)=="seo_") $kaMetadata->set(TABLE_SHOP_ITEMS,$id,$ka,$v);
				}
			}
		}
	
	/* SAVE VARIATIONS ORDER */
	if(isset($_POST['idsvarOrder'])&&is_array($_POST['idsvarOrder'])) {
		foreach($_POST['idsvarOrder'] as $c=>$var) {
			if(is_array($var)) {
				foreach($var as $order=>$idsvar) {
					$kaShop->updateVariation(array("idsvar"=>$idsvar,"order"=>$order));
					}
				}
			}
		}


	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Shop: Errore nella modifica dell\'oggetto <em>'.b3_htmlize($_POST['titolo'],true,"").'</em>');
		}
	else {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Shop:Item successfully saved').'</div>';
		$kaLog->add("UPD",'Shop: Modificato l\'oggetto <em>'.$_POST['titolo'].'</em>');
		}
	}
/* FINE AZIONI */



/* FILTERS AND CONDITIONS */

$vars=array();
$vars['conditions']="";
if(!isset($_GET['p'])) $_GET['p']=1;
$paginationLimit=intval($kaImpostazioni->getVar('shop',1));
$vars['start']=$paginationLimit*(intval($_GET['p'])-1);
$vars['limit']=$paginationLimit;
$vars['orderby']=$kaImpostazioni->getVar('shop-order',1);
if($vars['orderby']!="ordine"&&$vars['orderby']!="titolo"&&$vars['orderby']!="sottotitolo") $vars['orderby']="titolo";

// when you are searching for something, do not split by first letters
if(!isset($_GET['l'])) $_GET['l']="!";
if(!isset($_GET['search'])||$_GET['search']=="")
{
	if($_GET['l']=="!")
	{
		$vars['conditions']="";
		$vars['orderby']="`modified` DESC";
		$numberOfItemsInThisView=$vars['limit']*2;
	}
	elseif($_GET['l']=="#") $vars['conditions'].="`".$vars['orderby']."` RLIKE '^[^[A-Za-z].*'";
	else $vars['conditions'].="`".$vars['orderby']."` LIKE '".mysql_real_escape_string($_GET['l'])."%'";
	
} else {
	$vars['conditions'].="(productcode LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
	$vars['conditions'].="titolo LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
	$vars['conditions'].="sottotitolo LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
	$vars['conditions'].="dir LIKE '%".b3_htmlize($_GET['search'],true,"")."%')";
}


$numberOfItems=$kaShop->countItems();
if(!isset($numberOfItemsInThisView)) $numberOfItemsInThisView=$kaShop->countItems($vars['conditions']);

/* END FILTERS */


?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?> (<?= $numberOfItems; ?>)</h1>
<br />
<?
if(!isset($_GET['idsitem'])) { ?>
	<? if($kaImpostazioni->getVar('shop-order',1)=="ordine") { ?>
		<script type="text/javascript" src="<? echo ADMINDIR; ?>js/drag_and_drop.js"></script>
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
	<? } ?>
		
	<?
	/* if there are a search key, show her */
	if(isset($_GET['search'])&&$_GET['search']!="") { ?>
		<div class="box pager" style="text-align:center;">
			<?= $kaTranslate->translate('Shop:You are filtering results by').' "<strong>'.$_GET['search'].'</strong>"'; ?>
			<small><a href="?"><?= $kaTranslate->translate('remove filter'); ?></a></small>
		</div>
		<br />
		<?

	/* if there are more than 50 items, split into different pages */
	} elseif($numberOfItems>50) { ?>
		<div class="box pager" style="text-align:center;">
			<?
			$append_var=$_SERVER['QUERY_STRING'];
			foreach($_GET as $kaey => $value) {
				if($kaey=="chg_lang"||$kaey=="delete"||$kaey=="confirm"||$kaey=="p"||$kaey=="l") {
					$append_var=preg_replace("/".$kaey."=?[^&]*&?/","",$append_var);
					}
				}

			$letters="!#ABCDEFGHIJKLMNOPQRSTUWYXZ";
			for($i=0;isset($letters[$i]);$i++) { ?>
				<a href="?l=<?= urlencode($letters[$i]); ?>&<?= $append_var; ?>" class="<?= $_GET['l']==$letters[$i]?'selected':''; ?>">
					<?= ($letters[$i]=="!" ? $kaTranslate->translate('Shop:recently added') : $letters[$i]); ?>
				</a>
				<? }
			?>
		</div>
		<br />
		<?
	}
	
	/* pagination */
	if($numberOfItemsInThisView > $paginationLimit) { ?>
		<div class="box pager" style="text-align:center;">
			<?
			echo $kaTranslate->translate('Shop:Page').': ';
			$append_var=$_SERVER['QUERY_STRING'];
			foreach($_GET as $kaey => $value) {
				if($kaey=="chg_lang"||$kaey=="delete"||$kaey=="confirm"||$kaey=="p") {
					$append_var=preg_replace("/".$kaey."=?[^&]*&?/","",$append_var);
					}
				}

			for($i=1;$i<=ceil($numberOfItemsInThisView/$paginationLimit);$i++) { ?>
				<a href="?p=<?= $i; ?>&<?= $append_var; ?>" class="<?= $_GET['p']==$i?'selected':''; ?>"><?= $i; ?></a>
				<? }
			?>
		</div>
		<br />
		<?
	}
	
	?>

	<div class="subset">
		<fieldset class="box"><legend><?= $kaTranslate->translate('UI:Search'); ?></legend>
		<input type="text" name="search" id="searchQ" style="width:180px;" value="<? if(isset($_GET['search'])) echo str_replace('"','&quot;',$_GET['search']); ?>" />
		</fieldset>
		<br />
		</div>
		
	<div class="topset">
		<form action="" method="post" id="orderby">
		
		<table class="tabella">
			<tr>
				<? if($vars['orderby']=='ordine') { ?><th>&nbsp;</th><? } ?>
				
				<? if(strpos($pageLayout,",featuredimage,")!==false) { ?>
					<th>&nbsp;</th>
				<? } ?>
				
				<? if(strpos($pageLayout,",productcode,")!==false) { ?>
					<th><?= $kaTranslate->translate('Shop:Product Code'); ?></th>
				<? } ?>
				
				<th><?= $kaTranslate->translate('Shop:Title'); ?></th>
				
				<?php if(strpos($pageLayout,",public,")!==false) { ?>
					<th><?= $kaTranslate->translate('Shop:Visible from'); ?></th>
				<?php } ?>
				
				<th><?= $kaTranslate->translate('Shop:Price'); ?></th>
			</tr>

			<tbody class="DragZone">
			<?php
			foreach($kaShop->getQuickList($vars) as $row)
			{
				if(!isset($row['categorie'][0])) $row['categorie'][0]=array('dir'=>'tmp');
				?>
				<tr>
				<?php if($vars['orderby']=='ordine') { ?>
					<td class="move">
						<input type="hidden" name="idsitem[]" value="<?= $row['idsitem']; ?>" />
						<div class="grip"><?= $kaTranslate->translate('UI:Move'); ?></div>
					</td>
					<? } ?>

				<?php if(strpos($pageLayout,",featuredimage,")!==false) { ?>
					<td class="featuredimage">
						<div class="container"><?php
							if($row['featuredimage']>0)
							{
								$img=$kaImages->getImage($row['featuredimage']);
								?>
								<img src="<?= BASEDIR.$img['thumb']['url']; ?>">
								<?
							}
						?></div>
					</td>
				<?php } ?>

				<?php if(strpos($pageLayout,",productcode,")!==false) { ?>
					<td class="productcode"><?= $row['productcode']; ?></td>
				<?php } ?>

				<td>
					<a href="?idsitem=<?= $row['idsitem']; ?>" class="title"><?= $row['titolo']; ?></a>
					<? if($row['online']=='n') echo '<small class="alert">'.$kaTranslate->translate('Shop:DRAFT').'</small>'; ?><br />
					<? if($row['sottotitolo']!="") { ?><a href="?idsitem=<?= $row['idsitem']; ?>"><?= $row['sottotitolo']; ?></a><br /><? } ?>
					<a href="?idsitem=<?= $row['idsitem']; ?>" class="url"><?= $kaTranslate->translate('Shop:URL'); ?>: <?= $row['dir']; ?></a>
					
					<small class="actions"><a href="?idsitem=<?= $row['idsitem']; ?>"><?= $kaTranslate->translate('UI:Edit'); ?></a> | <a href="<?= SITE_URL.BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_shop',1).'/'.$row['categorie'][0]['dir'].'/'.$row['dir']; ?>"><?= $kaTranslate->translate('UI:View'); ?></a></small>
					
				<?php if(strpos($pageLayout,",public,")!==false) {
					$timestamp=mktime(substr($row['public'],11,2),substr($row['public'],14,2),substr($row['public'],17,2),substr($row['public'],5,2),substr($row['public'],8,2),substr($row['public'],0,4))
					?>
					<td>
						<div class="date">
							<div class="day"><?= strftime("%d %B %Y",$timestamp); ?></div>
							<div class="time"><?= substr($row['public'],11,5); ?></div>
						</div>
					</td>
				<?php } ?>

				<td class="price">
				<?php if(strpos($pageLayout,",discounted,")!==false&&$row['scontato']>0) { ?>
					<del><?= $row['prezzo'].' '.$kaImpostazioni->getVar('shop-currency',2); ?></del><br />
					<?= $row['scontato'].' '.$kaImpostazioni->getVar('shop-currency',2); ?>
				<?php } else { ?>
					<?= $row['prezzo'].' '.$kaImpostazioni->getVar('shop-currency',2); ?>
				<?php } ?>
				</td>
				
				</tr>
			<? } ?>
			</tbody>
		</table>

		</div>
		</form>

	<?php
	/* pagination */
	if($numberOfItemsInThisView > $paginationLimit) { ?>
		<br>
		<div class="box pager" style="text-align:center;">
			<?
			echo $kaTranslate->translate('Shop:Page').': ';
			$append_var=$_SERVER['QUERY_STRING'];
			foreach($_GET as $kaey => $value) {
				if($kaey=="chg_lang"||$kaey=="delete"||$kaey=="confirm"||$kaey=="p") {
					$append_var=preg_replace("/".$kaey."=?[^&]*&?/","",$append_var);
					}
				}

			for($i=1;$i<=ceil($numberOfItemsInThisView/$paginationLimit);$i++) { ?>
				<a href="?p=<?= $i; ?>&<?= $append_var; ?>" class="<?= $_GET['p']==$i?'selected':''; ?>"><?= $i; ?></a>
				<? }
			?>
		</div>
		<br />
		<?
	}
	?>
	</div>

	<script type="text/javascript">
		function submitSearch() {
			var q=document.getElementById('searchQ').value;
			window.location="?search="+escape(q);
			}
		function searchKeyUp(e) {
		   var KeyID=(window.event)?event.keyCode:e.keyCode;
		   if(KeyID==13) submitSearch(); //invio
		   }
		document.getElementById('searchQ').onkeyup=searchKeyUp;
	</script>

	<? }

/****** VISUALIZZAZIONE SINGOLO OGGETTO *******/
else {
	$row=$kaShop->getItem($_GET['idsitem']);


	/*
	if the current language is different from the page language, means that the user have clicked on the flag and are requesting the translation of this page.
	- if the page has a translation in the requested language, edit the translation
	- if the page hasn't a translated version, create a new translate page
	*/
	if($_SESSION['ll']!=$row['ll']) {
		if(isset($row['traduzioni'][$_SESSION['ll']])&&$row['traduzioni'][$_SESSION['ll']]!="") $url="?idsitem=".$row['traduzioni'][$_SESSION['ll']];
		else $url="add.php?translate=".$_GET['idsitem'];
		?>
		<div class="MsgNeutral">
			<h2><?= $kaTranslate->translate('News:Searching for translation'); ?></h2>
			<a href="<?= $url; ?>"><?= $kaTranslate->translate('News:if nothing happens, click here'); ?></a>
			<meta http-equiv="refresh" content="0;URL='<?= $url; ?>'">
			</div>
		<?
		die();
		}

	?><form action="?idsitem=<?= $row['idsitem']; ?>" method="post" onsubmit="return checkForm();">
		<script style="text/javascript" src="<?= ADMINRELDIR; ?>js/comments.js"></script>
		<script style="text/javascript" src="js/edit.js"></script>
		<div class="subset">

		<? if(strpos($pageLayout,",productcode,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Product Code'); ?></legend>
				<?= b3_create_input("productcode","text","",$row['productcode'],"180px"); ?>
				</fieldset>
			<hr />
			<? } ?>
		<? if(strpos($pageLayout,",price,")!==false) { ?>
			<fieldset class="box price"><legend><?= $kaTranslate->translate('Shop:Price'); ?></legend>
				<?= b3_create_input("prezzo","text","",number_format($row['prezzo'],2,'.',''),"120px",11); ?> <?= $kaImpostazioni->getVar('shop-currency',2); ?>
				</fieldset>
			<? } ?>
		<? if(strpos($pageLayout,",discounted,")!==false) { ?>
			<fieldset class="box price"><legend><?= $kaTranslate->translate('Shop:Discounted'); ?></legend>
				<?= b3_create_input("scontato","text","",number_format($row['scontato'],2,'.',''),"120px",11); ?> <?= $kaImpostazioni->getVar('shop-currency',2); ?>
				</fieldset>
			<? } ?>
		<? if(strpos($pageLayout,",weight,")!==false) { ?>
			<hr />
			<fieldset class="box price"><legend><?= $kaTranslate->translate('Shop:Weight'); ?></legend>
				<?= b3_create_input("weight","text","",$row['weight'],"120px",6); ?> Kg.
				</fieldset>
			<? } ?>
		<? if(strpos($pageLayout,",qta,")!==false) { ?>
			<hr />
			<fieldset class="box price"><legend><?= $kaTranslate->translate('Shop:Quantity'); ?></legend>
				<?= b3_create_input("qta","text","",$row['qta'],"120px",6); ?>
				</fieldset>
			<? } ?>

		<? if(strpos($pageLayout,",manufacturers,")!==false) { ?>
			<hr />
			<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Manufacturer'); ?></legend>
				<?
				$labels=array("");
				$options=array("");
				foreach($kaShop->getManufacturersList() as $man)
				{
					$labels[]=$man['name'];
					$options[]=$man['idsman'];
				}
				echo b3_create_select("manufacturer","",$labels,$options,$row['manufacturer'],"100%");
				?>
				</fieldset>
			<? } ?>
		<hr />
		<? if(strpos($pageLayout,",date,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Created on'); ?></legend>
				<?= b3_create_input("date_day","text"," ",preg_replace('/(\d{4}).(\d{2}).(\d{2}).*/','$3-$2-$1',$row['created']),"70px",250); ?> <?= b3_create_input("date_hour","text","alle ore ",preg_replace('/.*(\d{2}):(\d{2}):(\d{2})/','$1:$2',$row['created']),"40px",250); ?>
				</fieldset>
			<? } ?>
		<? if(strpos($pageLayout,",public,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Visible from'); ?></legend>
				<?= b3_create_input("visible_day","text"," ",preg_replace('/(\d{4}).(\d{2}).(\d{2}).*/','$3-$2-$1',$row['public']),"70px",250); ?> <?= b3_create_input("visible_hour","text","alle ore ",preg_replace('/.*(\d{2}):(\d{2}):(\d{2})/','$1:$2',$row['public']),"40px",250); ?>
				</fieldset>
			<? } ?>
		<? if(strpos($pageLayout,",expiration,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Expiration date'); ?></legend>
				<?= b3_create_input("expiration_day","text"," ",preg_replace('/(\d{4}).(\d{2}).(\d{2}).*/','$3-$2-$1',$row['expired']),"70px",250); ?> <?= b3_create_input("expiration_hour","text","alle ore ",preg_replace('/.*(\d{2}):(\d{2}):(\d{2})/','$1:$2',$row['expired']),"40px",250); ?>
				</fieldset>
			<? } ?>
		<br />

		<?
		if($kaImpostazioni->getVar('shop-commenti',1)=='s'||$row['commentiTot']>0) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('UI:Comments'); ?></legend>
				<?= $kaTranslate->translate('Shop:This item has'); ?> <?= $row['commentiTot']; ?> <?= $kaTranslate->translate('UI:comments'); ?><? if($row['commentiTot']-$row['commentiOnline']>0) echo ', di cui '.($row['commentiTot']-$row['commentiOnline']).' ancora da moderare'; ?>.<br />
				<div class="newCat"><a href="javascript:k_openIframeWindow(ADMINDIR+'inc/commentsManager.php?t=<?= TABLE_SHOP_ITEMS; ?>&id=<?= $row['idsitem']; ?>','600px','400px')" class="smallbutton"><?= $kaTranslate->translate('UI:Comments management'); ?></a></div>
				</fieldset><br />
			<? } ?>

		<? if(strpos($pageLayout,",featuredimage,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Featured Image'); ?></legend>
				<div id="featuredImageContainer"><?php
					if($row['featuredimage']>0)
					{
						$img=$kaImages->getImage($row['featuredimage']);
						?>
						<img src="<?= BASEDIR.$img['thumb']['url']; ?>">
						<?
					}
					?></div>
				<input type="hidden" name="featuredimage" id="featuredimage" value="<?= $row['featuredimage']; ?>">
				<a href="javascript:k_openIframeWindow('../inc/uploadsManager.inc.php?limit=1&submitlabel=<?= urlencode($kaTranslate->translate('Pages:Set featured image')); ?>&onsubmit=setFeaturedImage','90%','90%');" class="smallbutton"><?= $kaTranslate->translate('Shop:Choose featured image'); ?></a>
				<small><a href="javascript:removeFeaturedImage();" id="removeFeaturedImage" class="warning" <? if($row['featuredimage']==0) echo 'style="display:none;"'; ?>><?= $kaTranslate->translate('UI:Delete'); ?></a></small>
				</fieldset><br />
			<? } ?>
		</div>
		
		<div class="topset">
		<? if(strpos($pageLayout,",title,")!==false) {
			echo '<div class="title">'.b3_create_input("titolo","text",$kaTranslate->translate('Shop:Title')."<br />",b3_lmthize($row['titolo'],"input"),"70%",64).'</div>';
			}
		
		if(!isset($row['categorie'][0])) $cat=array("dir"=>"");
		else $cat=$row['categorie'][0];
		?>
		<div class="URLBox"><?= b3_create_input("dir","text",$kaTranslate->translate('Shop:Item\'s URL').": ".BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_news',1).'/[categoria]/',b3_lmthize($row['dir'],"input"),"400px",64,'onkeyup="checkURL(this)"'); ?> <span id="dirYetExists" style="display:none;">Questo indirizzo esiste gi&agrave;!</span> <a href="<?= BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_shop',1).'/'.$cat['dir'].'/'.$row['dir'].'?preview='.md5(ADMIN_MAIL); ?>" target="_blank"><?= $kaTranslate->translate('UI:View'); ?></a></div>
		<script type="text/javascript">
			var target=document.getElementById('dir');
			target.setAttribute("oldvalue",target.value);
			</script>
		<br />

		<? if(strpos($pageLayout,",subtitle,")!==false) {
			echo b3_create_input("sottotitolo","text",$kaTranslate->translate('Shop:Subtitle')."<br />",b3_lmthize($row['sottotitolo'],"input"),"90%",250);
			echo '<br /><br />';
			} ?>

		<? if(strpos($pageLayout,",preview,")!==false) {
			echo b3_create_textarea("anteprima",$kaTranslate->translate('Shop:Preview')."<br />",b3_lmthize($row['anteprima'],"textarea"),"100%","100px",RICH_EDITOR,true,TABLE_SHOP_ITEMS,$row['idsitem']);
			echo '<br />';
			} ?>
	
		<? if(strpos($pageLayout,",text,")!==false) {
			echo b3_create_textarea("testo",$kaTranslate->translate('Shop:Description')."<br />",b3_lmthize($row['testo'],"textarea"),"100%","300px",RICH_EDITOR,true,TABLE_SHOP_ITEMS,$row['idsitem']);
			echo '<br />';
			} ?>

		<?
		/* CUSTOM FIELDS */
		$vars=array("categories"=>array());
		foreach($row['categorie'] as $cat) {
			$vars['categories'][]=$cat['idcat'];
			}
		foreach($kaShop->getCustomFields($vars) as $field) {
			if($field['type']=="text") {
				echo b3_create_input("customfield[".$field['idsfield']."]","text",$field['name']." ",b3_lmthize($row['customfields'][$field['idsfield']],"input"),"400px");
				?><br /><br />
				<? }
			elseif($field['type']=="checkbox") {
				echo b3_create_input("customfield[".$field['idsfield']."]","checkbox",$field['name'],b3_lmthize($field['values'],"input"),false,false,($row['customfields'][$field['idsfield']]==$field['values']?'checked':''));
				?><br /><br />
				<? }
			elseif($field['type']=="multichoice") {
				echo '<label>'.$field['name'].'</label><br />';
				$values=explode("\n",trim($row['customfields'][$field['idsfield']],"\n"));
				foreach(explode("\n",$field['values']) as $option) {
					$checked=false;
					foreach($values as $v) {
						if($v==$option) $checked=true;
						}
					echo b3_create_input("customfield[".$field['idsfield']."][]","checkbox",$option,b3_lmthize($option,"input"),false,false,($checked?'checked':''),true).'<br />';
					}
				?><br /><br />
				<? }
			elseif($field['type']=="select") {
				$option=explode("\n",$field['values']);
				echo b3_create_select("customfield[".$field['idsfield']."]",$field['name']." ",$option,$option,$row['customfields'][$field['idsfield']]);
				?><br /><br />
				<? }
			elseif($field['type']=="textarea") {
				echo b3_create_textarea("customfield[".$field['idsfield']."]",$field['name']."<br />",b3_lmthize($row['customfields'][$field['idsfield']],"textarea"),"100%","100px",RICH_EDITOR,true,TABLE_SHOP_ITEMS,$row['idsitem']);
				?><br /><br />
				<? }
			}
		?>
		
		<br />
		<?
		/* VARIATIONS*/
		if(strpos($pageLayout,",variations,")!==false) { ?>
		<div class="box closed">
			<h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Shop:Variations'); ?></h2>
			<div id="variationsList"></div>
			<script type="text/javascript" src="<? echo ADMINDIR; ?>/js/drag_and_drop.js"></script>
			<script type="text/javascript">
				var idsitem=<? echo $row['idsitem']; ?>;
				function k_showVariations(html,xml) {
					var container=document.getElementById('variationsList');
					container.innerHTML=html;
					}
				function k_reloadVariations() {
					var container=document.getElementById('variationsList');
					var aj=new kAjax();
					aj.onSuccess(k_showVariations);
					aj.send('post','ajax/variationsHandler.php','getList=true&idsitem='+escape(idsitem));
					}
				function k_deleteVariation(idsvar) {
					if(confirm('<?= addslashes($kaTranslate->translate('Shop:Do you really want to delete this variation?')); ?>')) {
						var aj=new kAjax();
						aj.onSuccess(k_reloadVariations);
						aj.send('post','ajax/variationsHandler.php','delete='+escape(idsvar));
						}
					}
				k_reloadVariations();
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
					});
				</script>
			<div style="padding:10px 0;">
				<a href="javascript:k_openIframeWindow('ajax/variationsAdd.php?idsitem=<?= $row['idsitem']; ?>','800px','500px');" class="smallbutton">+ <?= $kaTranslate->translate('Shop:Add variation'); ?></a>
				<a href="javascript:k_openIframeWindow('ajax/variationsImport.php?idsitem=<?= $row['idsitem']; ?>','800px','500px');" class="smallbutton">+ <?= $kaTranslate->translate('Shop:Import from another item'); ?></a>
				</div>
			</div>
			<? } ?>


		<? if(strpos($pageLayout,",privatearea,")!==false&&$kaUsers->canIUse('private')) { ?>
			<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Shop:Actions to do if bought'); ?></h2>
				<table><tr><td><?= $kaTranslate->translate('Shop:Grant access to the following directories'); ?></td>
				<td>
					<?
					function printDir($dir) {
						global $row;
						if(isset($dir['dirname'])&&$dir['dirname']!="") {
							if(isset($dir['permissions']['permissions'])&&$dir['permissions']['permissions']!="private") {
								$checked=false;
								foreach($row['privatearea'] as $v) {
									if($v==$dir['dirname']) $checked=true;
									}
								?>
								<li><?= b3_create_input("private[]","checkbox",$dir['dirname'],$dir['dirname'],"","",$checked?"checked":"",true); ?><br />
									<?
									if(isset($dir[0])) echo '<ol class="privateDirs">';
									for($i=0;isset($dir[$i]);$i++) {
										printDir($dir[$i]);
										}
									if(isset($dir[0])) echo '</ol>';
									?></li>
								<? }
							}
						}
					require_once('../private/private.lib.php');
					$kaPrivate=new kaPrivate();
					foreach($kaPrivate->getDirContent("") as $dir) {
						if(isset($dir['dirname'])) {
							echo '<ul class="privateDirs">';
							printDir($dir);
							echo '</ul>';
							}
						}
					
					?>
					</td></tr></table>
				</div>
			<? } ?>

		<? if(strpos($pageLayout,",categories,")!==false) { ?>
			<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Shop:Categories'); ?></h2>
				<div id="categorie">Loading...</div>
				<script type="text/javascript" src="./ajax/categorie.js"></script>
				<script type="text/javascript">k_reloadCat(<? echo $row['idsitem']; ?>);</script>
				</div>
			<? } ?>

		<? if(strpos($pageLayout,",photogallery,")!==false) { ?>
			<div class="box <?= count($row['imgallery'])==0?'closed':'opened'; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Shop:Photogallery'); ?></h2>
			<iframe src="<?php echo ADMINDIR; ?>inc/imgallery.inc.php?refid=imgallery&mediatable=<?php echo TABLE_SHOP_ITEMS; ?>&mediaid=<?php echo $row['idsitem']; ?>&label=<?= urlencode($kaTranslate->translate('Img:Insert a picture')); ?>" class="imgframe" id="imgallery"></iframe>
			</div>
			<? } ?>

		<? if(strpos($pageLayout,",documentgallery,")!==false) { ?>
			<div class="box <?= count($row['docgallery'])==0?'closed':'opened'; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Shop:Document gallery'); ?></h2>
			<iframe src="<?php echo ADMINDIR; ?>inc/docgallery.inc.php?refid=docgallery&mediatable=<?php echo TABLE_SHOP_ITEMS; ?>&mediaid=<?php echo $row['idsitem']; ?>&label=<?= urlencode($kaTranslate->translate('Doc:Insert a file')); ?>" class="docsframe" id="docgallery"></iframe>
			</div>
			<? } ?>

		<? if(strpos($pageLayout,",seo,")!==false) {
			?><div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('SEO:SEO'); ?></h2>
			<table>
				<tr>
					<td><label for="seo_changefreq"><?= $kaTranslate->translate('SEO:Change frequency'); ?></label></td>
					<td><select name="seo_changefreq" id="seo_changefreq">
						<?
						foreach(array(""=>"","always"=>$kaTranslate->translate('SEO:Always'),"hourly"=>$kaTranslate->translate('SEO:Hourly'),"daily"=>$kaTranslate->translate('SEO:Daily'),"weekly"=>$kaTranslate->translate('SEO:Weekly'),"monthly"=>$kaTranslate->translate('SEO:Monthly'),"yearly"=>$kaTranslate->translate('SEO:Yearly'),"never"=>$kaTranslate->translate('SEO:Never')) as $ka=>$v) {
							$md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_changefreq');
							?><option value="<?= $ka; ?>" <?= ($md['value']==$ka?'selected':''); ?>><?= $v; ?></option><?
							} ?>
						</select>&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
					<td><label for="seo_title"><?= $kaTranslate->translate('News:Title'); ?></label></td>
					<td><input type="text" name="seo_title" id="seo_title" style="width:300px;" value="<? $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_title'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				<tr>
					<td><label for="seo_priority"><?= $kaTranslate->translate('SEO:Priority'); ?></label></td>
					<td><input type="text" name="seo_priority" id="seo_priority" style="width:50px;" value="<? $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_priority'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					<td><label for="seo_description"><?= $kaTranslate->translate('SEO:Description'); ?></label></td>
					<td><input type="text" name="seo_description" id="seo_description" style="width:300px;" value="<? $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_description'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				<tr><td colspan="2"></td>
					<td><label for="seo_keywords"><?= $kaTranslate->translate('SEO:Keywords'); ?></label></td>
					<td><input type="text" name="seo_keywords" id="seo_keywords" style="width:300px;" value="<? $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_keywords'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				<tr>
					<td colspan="2">
						<input type="checkbox" name="seo_robots[]" id="seo_robots_noindex" value="noindex" <? $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_robots'); if(strpos($md['value'],"noindex")!==false) { echo 'checked'; }; ?> /> <label for="seo_robots_noindex"><?= $kaTranslate->translate('SEO:No index'); ?></label>,
						<input type="checkbox" name="seo_robots[]" id="seo_robots_nofollow" value="nofollow" <? $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_robots'); if(strpos($md['value'],"nofollow")!==false) { echo 'checked'; }; ?>/> <label for="seo_robots_nofollow"><?= $kaTranslate->translate('SEO:No follow'); ?></label>,
						<input type="checkbox" name="seo_robots[]" id="seo_robots_noarchive" value="noarchive" <? $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_robots'); if(strpos($md['value'],"noarchive")!==false) { echo 'checked'; }; ?>/> <label for="seo_robots_noarchive"><?= $kaTranslate->translate('SEO:No archive'); ?></label>
						</td>
					<td><label for="seo_canonical">Canonical URL</label></td>
					<td><input type="text" name="seo_canonical" id="seo_canonical" style="width:300px;" value="<? $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_canonical'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				</table>
			</div><?
			} ?>

		<? if(strpos($pageLayout,",metadata,")!==false) {
			?><div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);">Meta-dati</h2>
			<div id="divMetadata"></div>
			<script type="text/javascript">kaMetadataReload('<?= TABLE_SHOP_ITEMS; ?>',<?= $row['idsitem']; ?>);</script>
			<a href="javascript:kOpenIPopUp(ADMINDIR+'inc/ajax/metadataNew.php','t=<?= TABLE_SHOP_ITEMS; ?>&id=<?= $row['idsitem']; ?>','600px','400px')" class="smallbutton">Nuovo meta-dato</a>
			</div>
			<script type="text/javascript">
				function showActions(td) {
					for(var i=0;td.getElementsByTagName('DIV')[i];i++) {
						td.getElementsByTagName('DIV')[i].style.visibility='visible';
						}
					}
				function hideActions(td) {
					for(var i=0;td.getElementsByTagName('DIV')[i];i++) {
						td.getElementsByTagName('DIV')[i].style.visibility='hidden';
						}
					}
				</script>
			<?
			} ?>

		<? if(strpos($pageLayout,",layout,")!==false) { ?>
			<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);">Template</h2>
			<? if(strpos($pageLayout,",layout,")!==false) { ?>
				<?
				$option=array("");
				$value=array("-default-");
				foreach($kaImpostazioni->getLayoutList() as $file) {
					$option[]=$file;
					$file=str_replace("_"," ",$file);
					$file=str_replace(".php"," ",$file);
					$file=str_replace(".html"," ",$file);
					$value[]=$file;
					}
				echo b3_create_select("layout","Layout ",$value,$option,$row['layout']);
				} ?>
				</div>
			<? } ?>

		<?
		if(strpos($pageLayout,",translate,")!==false) { ?>
			<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);">Traduzioni</h2>
				<table><?
					$translation=array();
					$translation_id=array();
					$query_l="SELECT * FROM ".TABLE_LINGUE." WHERE ll<>'".$row['ll']."' ORDER BY `lingua`";
					$results_l=mysql_query($query_l);
					while($page_l=mysql_fetch_array($results_l)) {
						if(!isset($row['traduzioni'][$page_l['ll']])||$row['traduzioni'][$page_l['ll']]=="") {
							$translation[$page_l['ll']]="";
							$translation_id[$page_l['ll']]="";
							}
						else {
							$tmp=$kaShop->getTitleById($row['traduzioni'][$page_l['ll']]);
							$translation[$page_l['ll']]=$tmp['titolo'];
							$translation_id[$page_l['ll']]=$tmp['idsitem'];
							}
						?>
						<tr>
						<td><label for="translation['<?= $page_l['ll']; ?>']"><strong><?= $page_l['lingua']; ?></strong></label></td>
						<td><div class="suggestionsContainer">
							<?= b3_create_input("translation[".$page_l['ll']."]","text","",$translation[$page_l['ll']],"200px",250,'autocomplete="off"'); ?>
							<?= b3_create_input("translation_id[".$page_l['ll']."]","hidden","",$translation_id[$page_l['ll']]); ?>
							<img src="<?= ADMINDIR; ?>img/close.png" alt="clear" width="12" height="12" id="translation_clear<?= $page_l['ll']; ?>" class="suggestionsClear" />
							<script type="text/javascript">translation<?= $page_l['ll']; ?>Handler=new kAutocomplete();translation<?= $page_l['ll']; ?>Handler.init('<?= $page_l['ll']; ?>');</script>
							</div></td>
						</tr>
						<? } ?>
					</table>
				</div>
			<? } ?>
		<br /><br />
	
		<div style="clear:both;"></div>
		<div class="submit">
			<input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" />
			<div class="draft"><?= b3_create_input("online","checkbox",$kaTranslate->translate('Shop:DRAFT'),'n',false,false,($row['online']=='y'||isset($_GET['firstedit'])?'':'checked')); ?></div>
			</div>
		</div>
	</form>
	
	<?
	}

include_once("../inc/foot.inc.php");
?>
