<?php 
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
	
	$vars=array();

	if(isset($_POST['date_day'])&&isset($_POST['date_hour'])) $vars['created']=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['date_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['date_hour']);
	if(isset($_POST['visible_day'])&&isset($_POST['visible_hour'])) $vars['public']=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['visible_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['visible_hour']);
	if(isset($_POST['expiration_day'])&&isset($_POST['expiration_hour'])) $vars['expiration']=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['expiration_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['expiration_hour']);

	if(strpos($pageLayout,",privatearea,")!==false)
	{
		$vars['privatearea']="";
		if(isset($_POST['private']))
		{
			foreach($_POST['private'] as $dir)
			{
				$vars['privatearea'].=$dir."\n";
			}
		}
	}
	
	if(strpos($pageLayout,",categories,")!==false)
	{
		$vars['categories']=",";
		if(isset($_POST['idcat']))
		{
			foreach($_POST['idcat'] as $idcat) { $vars['categories'].=$idcat.','; }
		}
	}

	/* simple fields */
	foreach(array("dir","template","layout") as $field)
	{
		if(isset($_POST[$field])) $vars[$field] = $_POST[$field];
	}
	
	/* single line text fields */
	foreach(array("productcode","titolo","sottotitolo") as $field)
	{
		if(isset($_POST[$field])) $vars[$field] = b3_htmlize($_POST[$field],false,"");
	}
	
	/* multiline text fields */
	foreach(array("testo","anteprima") as $field)
	{
		if(isset($_POST[$field])) $vars[$field] = b3_htmlize($_POST[$field],false);
	}
	
	$vars['prezzo'] = isset($_POST['prezzo']) ? number_format($_POST['prezzo'],2,'.','') : 0;
	$vars['scontato'] = isset($_POST['scontato']) ? number_format($_POST['prezzo'],2,'.','') : 0;
	$vars['weight'] = isset($_POST['weight']) ? floatval($_POST['prezzo']) : 0;
	$vars['qta'] = isset($_POST['qta']) ? intval($_POST['qta']) : 0;
	$vars['online'] = !isset($_POST['online']) ? 'y' : 'n';
	$vars['customfields'] = empty($_POST['customfield']) ? array() : $_POST['customfield'];
	if(isset($_POST['featuredimage'])) $vars['featuredimage']=intval($_POST['featuredimage']);
	if(isset($_POST['manufacturer'])) $vars['manufacturer']=intval($_POST['manufacturer']);
	if(isset($_POST['photogallery'])) $vars['photogallery']=$_POST['photogallery'];
	
	$id=$kaShop->updateItem($_GET['idsitem'],$vars);
	
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
<?php 
if(!isset($_GET['idsitem'])) { ?>
	<?php  if($kaImpostazioni->getVar('shop-order',1)=="ordine") { ?>
		<script type="text/javascript" src="<?php  echo ADMINDIR; ?>js/drag_and_drop.js"></script>
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
	<?php  } ?>
		
	<?php 
	/* if there are a search key, show her */
	if(isset($_GET['search'])&&$_GET['search']!="") { ?>
		<div class="box pager" style="text-align:center;">
			<?= $kaTranslate->translate('Shop:You are filtering results by').' "<strong>'.$_GET['search'].'</strong>"'; ?>
			<small><a href="?"><?= $kaTranslate->translate('remove filter'); ?></a></small>
		</div>
		<br />
		<?php 

	/* if there are more than 50 items, split into different pages */
	} elseif($numberOfItems>50) { ?>
		<div class="box pager" style="text-align:center;">
			<?php 
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
				<?php  }
			?>
		</div>
		<br />
		<?php 
	}
	
	/* pagination */
	if($numberOfItemsInThisView > $paginationLimit) { ?>
		<div class="box pager" style="text-align:center;">
			<?php 
			echo $kaTranslate->translate('Shop:Page').': ';
			$append_var=$_SERVER['QUERY_STRING'];
			foreach($_GET as $kaey => $value) {
				if($kaey=="chg_lang"||$kaey=="delete"||$kaey=="confirm"||$kaey=="p") {
					$append_var=preg_replace("/".$kaey."=?[^&]*&?/","",$append_var);
					}
				}

			for($i=1;$i<=ceil($numberOfItemsInThisView/$paginationLimit);$i++) { ?>
				<a href="?p=<?= $i; ?>&<?= $append_var; ?>" class="<?= $_GET['p']==$i?'selected':''; ?>"><?= $i; ?></a>
				<?php  }
			?>
		</div>
		<br />
		<?php 
	}
	
	?>

	<div class="subset">
		<fieldset class="box"><legend><?= $kaTranslate->translate('UI:Search'); ?></legend>
		<input type="text" name="search" id="searchQ" style="width:180px;" value="<?php  if(isset($_GET['search'])) echo str_replace('"','&quot;',$_GET['search']); ?>" />
		</fieldset>
		<br />
		</div>
		
	<div class="topset">
		<form action="" method="post" id="orderby">
		
		<table class="tabella">
			<tr>
				<?php  if($vars['orderby']=='ordine') { ?><th>&nbsp;</th><?php  } ?>
				
				<?php  if(strpos($pageLayout,",featuredimage,")!==false) { ?>
					<th>&nbsp;</th>
				<?php  } ?>
				
				<?php  if(strpos($pageLayout,",productcode,")!==false) { ?>
					<th><?= $kaTranslate->translate('Shop:Product Code'); ?></th>
				<?php  } ?>
				
				<th><?= $kaTranslate->translate('Shop:Title'); ?></th>
				
				<?php if(strpos($pageLayout,",public,")!==false) { ?>
					<th><?= $kaTranslate->translate('Shop:Visible from'); ?></th>
				<?php } ?>
				
				<th><?= $kaTranslate->translate('Shop:Price'); ?></th>
			</tr>

			<tbody class="DragZone">
			<?php 			foreach($kaShop->getQuickList($vars) as $row)
			{
				if(!isset($row['categorie'][0])) $row['categorie'][0]=array('dir'=>'tmp');
				?>
				<tr>
				<?php if($vars['orderby']=='ordine') { ?>
					<td class="move">
						<input type="hidden" name="idsitem[]" value="<?= $row['idsitem']; ?>" />
						<div class="grip"><?= $kaTranslate->translate('UI:Move'); ?></div>
					</td>
					<?php  } ?>

				<?php if(strpos($pageLayout,",featuredimage,")!==false) { ?>
					<td class="featuredimage">
						<div class="container"><?php 							if($row['featuredimage']>0)
							{
								$img=$kaImages->getImage($row['featuredimage']);
								?>
								<img src="<?= BASEDIR.$img['thumb']['url']; ?>">
								<?php 
							}
						?></div>
					</td>
				<?php } ?>

				<?php if(strpos($pageLayout,",productcode,")!==false) { ?>
					<td class="productcode"><?= $row['productcode']; ?></td>
				<?php } ?>

				<td>
					<a href="?idsitem=<?= $row['idsitem']; ?>" class="title"><?= $row['titolo']; ?></a>
					<?php  if($row['online']=='n') echo '<small class="alert">'.$kaTranslate->translate('Shop:DRAFT').'</small>'; ?><br />
					<?php  if($row['sottotitolo']!="") { ?><a href="?idsitem=<?= $row['idsitem']; ?>"><?= $row['sottotitolo']; ?></a><br /><?php  } ?>
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
			<?php  } ?>
			</tbody>
		</table>

		</div>
		</form>

	<?php 	/* pagination */
	if($numberOfItemsInThisView > $paginationLimit) { ?>
		<br>
		<div class="box pager" style="text-align:center;">
			<?php 
			echo $kaTranslate->translate('Shop:Page').': ';
			$append_var=$_SERVER['QUERY_STRING'];
			foreach($_GET as $kaey => $value) {
				if($kaey=="chg_lang"||$kaey=="delete"||$kaey=="confirm"||$kaey=="p") {
					$append_var=preg_replace("/".$kaey."=?[^&]*&?/","",$append_var);
					}
				}

			for($i=1;$i<=ceil($numberOfItemsInThisView/$paginationLimit);$i++) { ?>
				<a href="?p=<?= $i; ?>&<?= $append_var; ?>" class="<?= $_GET['p']==$i?'selected':''; ?>"><?= $i; ?></a>
				<?php  }
			?>
		</div>
		<br />
		<?php 
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

	<?php  }

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
		<?php 
		die();
		}

	?><form action="?idsitem=<?= $row['idsitem']; ?>" method="post" onsubmit="return checkForm();">
		<script style="text/javascript" src="<?= ADMINRELDIR; ?>js/comments.js"></script>
		<script style="text/javascript" src="js/edit.js"></script>
		<div class="subset">

		<?php  if(strpos($pageLayout,",productcode,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Product Code'); ?></legend>
				<?= b3_create_input("productcode","text","",$row['productcode'],"180px"); ?>
				</fieldset>
			<hr />
			<?php  } ?>
		<?php  if(strpos($pageLayout,",price,")!==false) { ?>
			<fieldset class="box price"><legend><?= $kaTranslate->translate('Shop:Price'); ?></legend>
				<?= b3_create_input("prezzo","text","",number_format($row['prezzo'],2,'.',''),"120px",11); ?> <?= $kaImpostazioni->getVar('shop-currency',2); ?>
				</fieldset>
			<?php  } ?>
		<?php  if(strpos($pageLayout,",discounted,")!==false) { ?>
			<fieldset class="box price"><legend><?= $kaTranslate->translate('Shop:Discounted'); ?></legend>
				<?= b3_create_input("scontato","text","",number_format($row['scontato'],2,'.',''),"120px",11); ?> <?= $kaImpostazioni->getVar('shop-currency',2); ?>
				</fieldset>
			<?php  } ?>
		<?php  if(strpos($pageLayout,",weight,")!==false) { ?>
			<hr />
			<fieldset class="box price"><legend><?= $kaTranslate->translate('Shop:Weight'); ?></legend>
				<?= b3_create_input("weight","text","",$row['weight'],"120px",6); ?> Kg.
				</fieldset>
			<?php  } ?>
		<?php  if(strpos($pageLayout,",qta,")!==false) { ?>
			<hr />
			<fieldset class="box price"><legend><?= $kaTranslate->translate('Shop:Quantity'); ?></legend>
				<?= b3_create_input("qta","text","",$row['qta'],"120px",6); ?>
				</fieldset>
			<?php  } ?>

		<?php  if(strpos($pageLayout,",manufacturers,")!==false) { ?>
			<hr />
			<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Manufacturer'); ?></legend>
				<?php 
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
			<?php  } ?>
		<hr />
		<?php  if(strpos($pageLayout,",date,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Created on'); ?></legend>
				<?= b3_create_input("date_day","text"," ",preg_replace('/(\d{4}).(\d{2}).(\d{2}).*/','$3-$2-$1',$row['created']),"70px",250); ?> <?= b3_create_input("date_hour","text","alle ore ",preg_replace('/.*(\d{2}):(\d{2}):(\d{2})/','$1:$2',$row['created']),"40px",250); ?>
				</fieldset>
			<?php  } ?>
		<?php  if(strpos($pageLayout,",public,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Visible from'); ?></legend>
				<?= b3_create_input("visible_day","text"," ",preg_replace('/(\d{4}).(\d{2}).(\d{2}).*/','$3-$2-$1',$row['public']),"70px",250); ?> <?= b3_create_input("visible_hour","text","alle ore ",preg_replace('/.*(\d{2}):(\d{2}):(\d{2})/','$1:$2',$row['public']),"40px",250); ?>
				</fieldset>
			<?php  } ?>
		<?php  if(strpos($pageLayout,",expiration,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Expiration date'); ?></legend>
				<?= b3_create_input("expiration_day","text"," ",preg_replace('/(\d{4}).(\d{2}).(\d{2}).*/','$3-$2-$1',$row['expired']),"70px",250); ?> <?= b3_create_input("expiration_hour","text","alle ore ",preg_replace('/.*(\d{2}):(\d{2}):(\d{2})/','$1:$2',$row['expired']),"40px",250); ?>
				</fieldset>
			<?php  } ?>
		<br />

		<?php 
		if($kaImpostazioni->getVar('shop-commenti',1)=='s'||$row['commentiTot']>0) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('UI:Comments'); ?></legend>
				<?= $kaTranslate->translate('Shop:This item has'); ?> <?= $row['commentiTot']; ?> <?= $kaTranslate->translate('UI:comments'); ?><?php  if($row['commentiTot']-$row['commentiOnline']>0) echo ', di cui '.($row['commentiTot']-$row['commentiOnline']).' ancora da moderare'; ?>.<br />
				<div class="newCat"><a href="javascript:k_openIframeWindow(ADMINDIR+'inc/commentsManager.php?t=<?= TABLE_SHOP_ITEMS; ?>&id=<?= $row['idsitem']; ?>','600px','400px')" class="smallbutton"><?= $kaTranslate->translate('UI:Comments management'); ?></a></div>
				</fieldset><br />
			<?php  } ?>

		<?php  if(strpos($pageLayout,",featuredimage,")!==false) { ?>
			<fieldset class="box"><legend><?= $kaTranslate->translate('Shop:Featured Image'); ?></legend>
				<div id="featuredImageContainer"><?php 					if($row['featuredimage']>0)
					{
						$img=$kaImages->getImage($row['featuredimage']);
						?>
						<img src="<?= BASEDIR.$img['thumb']['url']; ?>">
						<?php 
					}
					?></div>
				<input type="hidden" name="featuredimage" id="featuredimage" value="<?= $row['featuredimage']; ?>">
				<a href="javascript:k_openIframeWindow('../inc/uploadsManager.inc.php?limit=1&submitlabel=<?= urlencode($kaTranslate->translate('Shop:Set featured image')); ?>&onsubmit=setFeaturedImage','90%','90%');" class="smallbutton"><?= $kaTranslate->translate('Shop:Choose featured image'); ?></a>
				<small><a href="javascript:removeFeaturedImage();" id="removeFeaturedImage" class="warning" <?php  if($row['featuredimage']==0) echo 'style="display:none;"'; ?>><?= $kaTranslate->translate('UI:Delete'); ?></a></small>
				</fieldset><br />
			<?php  } ?>
		</div>
		
		<div class="topset">
		<?php  if(strpos($pageLayout,",title,")!==false) {
			echo '<div class="title">'.b3_create_input("titolo","text",$kaTranslate->translate('Shop:Title')."<br />",b3_lmthize($row['titolo'],"input"),"70%",250).'</div>';
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

		<?php  if(strpos($pageLayout,",subtitle,")!==false) {
			echo b3_create_input("sottotitolo","text",$kaTranslate->translate('Shop:Subtitle')."<br />",b3_lmthize($row['sottotitolo'],"input"),"90%",250);
			echo '<br /><br />';
			} ?>

		<?php  if(strpos($pageLayout,",preview,")!==false) {
			echo b3_create_textarea("anteprima",$kaTranslate->translate('Shop:Preview')."<br />",b3_lmthize($row['anteprima'],"textarea"),"100%","100px",RICH_EDITOR,true,TABLE_SHOP_ITEMS,$row['idsitem']);
			echo '<br />';
			} ?>
	
		<?php  if(strpos($pageLayout,",text,")!==false) {
			echo b3_create_textarea("testo",$kaTranslate->translate('Shop:Description')."<br />",b3_lmthize($row['testo'],"textarea"),"100%","300px",RICH_EDITOR,true,TABLE_SHOP_ITEMS,$row['idsitem']);
			echo '<br />';
			} ?>

		<?php 
		/* CUSTOM FIELDS */
		$vars=array("categories"=>array());
		foreach($row['categorie'] as $cat) {
			$vars['categories'][]=$cat['idcat'];
			}
		foreach($kaShop->getCustomFields($vars) as $field) {
			if($field['type']=="text") {
				echo b3_create_input("customfield[".$field['idsfield']."]","text",$field['name']." ",b3_lmthize($row['customfields'][$field['idsfield']],"input"),"400px");
				?><br /><br />
				<?php  }
			elseif($field['type']=="checkbox") {
				echo b3_create_input("customfield[".$field['idsfield']."]","checkbox",$field['name'],b3_lmthize($field['values'],"input"),false,false,($row['customfields'][$field['idsfield']]==$field['values']?'checked':''));
				?><br /><br />
				<?php  }
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
				<?php  }
			elseif($field['type']=="select") {
				$option=explode("\n",$field['values']);
				echo b3_create_select("customfield[".$field['idsfield']."]",$field['name']." ",$option,$option,$row['customfields'][$field['idsfield']]);
				?><br /><br />
				<?php  }
			elseif($field['type']=="textarea") {
				echo b3_create_textarea("customfield[".$field['idsfield']."]",$field['name']."<br />",b3_lmthize($row['customfields'][$field['idsfield']],"textarea"),"100%","100px",RICH_EDITOR,true,TABLE_SHOP_ITEMS,$row['idsitem']);
				?><br /><br />
				<?php  }
			}
		?>
		
		<br />
		<?php 
		/* VARIATIONS*/
		if(strpos($pageLayout,",variations,")!==false) { ?>
		<div class="box closed">
			<h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Shop:Variations'); ?></h2>
			<div id="variationsList"></div>
			<script type="text/javascript" src="<?php  echo ADMINDIR; ?>/js/drag_and_drop.js"></script>
			<script type="text/javascript">
				var idsitem=<?php  echo $row['idsitem']; ?>;
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
			<?php  } ?>


		<?php  if(strpos($pageLayout,",privatearea,")!==false&&$kaUsers->canIUse('private')) { ?>
			<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Shop:Actions to do if bought'); ?></h2>
				<table><tr><td><?= $kaTranslate->translate('Shop:Grant access to the following directories'); ?></td>
				<td>
					<?php 
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
									<?php 
									if(isset($dir[0])) echo '<ol class="privateDirs">';
									for($i=0;isset($dir[$i]);$i++) {
										printDir($dir[$i]);
										}
									if(isset($dir[0])) echo '</ol>';
									?></li>
								<?php  }
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
			<?php  } ?>

		<?php  if(strpos($pageLayout,",categories,")!==false) { ?>
			<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Shop:Categories'); ?></h2>
				<div id="categorie">Loading...</div>
				<script type="text/javascript" src="./ajax/categorie.js" charset="UTF-8"></script>
				<script type="text/javascript">k_reloadCat(<?php  echo $row['idsitem']; ?>);</script>
				</div>
			<?php  } ?>

		<?php  if(strpos($pageLayout,",photogallery,")!==false) { ?>
			<div class="box <?= trim($row['photogallery'],",")=="" ? "closed" : "opened"; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('UI:Photogallery'); ?></h2>
				<a href="javascript:k_openIframeWindow('../inc/uploadsManager.inc.php?submitlabel=<?= urlencode($kaTranslate->translate('UI:Add selected images to the list')); ?>&onsubmit=kAddImagesToPhotogallery','90%','90%');" class="smallbutton"><?= $kaTranslate->translate('UI:Add images to gallery'); ?></a>
				<div id="photogallery"></div>
				<script type="text/javascript">
					kLoadPhotogallery('<?= $row['photogallery']; ?>');
				</script>
			</div>
			<?php  } ?>

		<?php  if(strpos($pageLayout,",documentgallery,")!==false) { ?>
			<div class="box <?= count($row['docgallery'])==0?'closed':'opened'; ?>"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('Shop:Document gallery'); ?></h2>
			<iframe src="<?php echo ADMINDIR; ?>inc/docgallery.inc.php?refid=docgallery&mediatable=<?php echo TABLE_SHOP_ITEMS; ?>&mediaid=<?php echo $row['idsitem']; ?>&label=<?= urlencode($kaTranslate->translate('Doc:Insert a file')); ?>" class="docsframe" id="docgallery"></iframe>
			</div>
			<?php  } ?>

		<?php  if(strpos($pageLayout,",seo,")!==false) {
			?><div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);"><?= $kaTranslate->translate('SEO:SEO'); ?></h2>
			<table>
				<tr>
					<td><label for="seo_changefreq"><?= $kaTranslate->translate('SEO:Change frequency'); ?></label></td>
					<td><select name="seo_changefreq" id="seo_changefreq">
						<?php 
						foreach(array(""=>"","always"=>$kaTranslate->translate('SEO:Always'),"hourly"=>$kaTranslate->translate('SEO:Hourly'),"daily"=>$kaTranslate->translate('SEO:Daily'),"weekly"=>$kaTranslate->translate('SEO:Weekly'),"monthly"=>$kaTranslate->translate('SEO:Monthly'),"yearly"=>$kaTranslate->translate('SEO:Yearly'),"never"=>$kaTranslate->translate('SEO:Never')) as $ka=>$v) {
							$md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_changefreq');
							?><option value="<?= $ka; ?>" <?= ($md['value']==$ka?'selected':''); ?>><?= $v; ?></option><?php 
							} ?>
						</select>&nbsp;&nbsp;&nbsp;&nbsp;
						</td>
					<td><label for="seo_title"><?= $kaTranslate->translate('News:Title'); ?></label></td>
					<td><input type="text" name="seo_title" id="seo_title" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_title'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				<tr>
					<td><label for="seo_priority"><?= $kaTranslate->translate('SEO:Priority'); ?></label></td>
					<td><input type="text" name="seo_priority" id="seo_priority" style="width:50px;" value="<?php  $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_priority'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					<td><label for="seo_description"><?= $kaTranslate->translate('SEO:Description'); ?></label></td>
					<td><input type="text" name="seo_description" id="seo_description" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_description'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				<tr><td colspan="2"></td>
					<td><label for="seo_keywords"><?= $kaTranslate->translate('SEO:Keywords'); ?></label></td>
					<td><input type="text" name="seo_keywords" id="seo_keywords" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_keywords'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				<tr>
					<td colspan="2">
						<input type="checkbox" name="seo_robots[]" id="seo_robots_noindex" value="noindex" <?php  $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_robots'); if(strpos($md['value'],"noindex")!==false) { echo 'checked'; }; ?> /> <label for="seo_robots_noindex"><?= $kaTranslate->translate('SEO:No index'); ?></label>,
						<input type="checkbox" name="seo_robots[]" id="seo_robots_nofollow" value="nofollow" <?php  $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_robots'); if(strpos($md['value'],"nofollow")!==false) { echo 'checked'; }; ?>/> <label for="seo_robots_nofollow"><?= $kaTranslate->translate('SEO:No follow'); ?></label>,
						<input type="checkbox" name="seo_robots[]" id="seo_robots_noarchive" value="noarchive" <?php  $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_robots'); if(strpos($md['value'],"noarchive")!==false) { echo 'checked'; }; ?>/> <label for="seo_robots_noarchive"><?= $kaTranslate->translate('SEO:No archive'); ?></label>
						</td>
					<td><label for="seo_canonical">Canonical URL</label></td>
					<td><input type="text" name="seo_canonical" id="seo_canonical" style="width:300px;" value="<?php  $md=$kaMetadata->get(TABLE_SHOP_ITEMS,$row['idsitem'],'seo_canonical'); echo b3_lmthize($md['value'],"input"); ?>" /></td>
					</tr>
				</table>
			</div><?php 
			} ?>

		<?php  if(strpos($pageLayout,",metadata,")!==false) {
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
			<?php 
			} ?>

		<?php  if(strpos($pageLayout,",layout,")!==false) { ?>
			<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);">Template</h2>
			<?php  if(strpos($pageLayout,",layout,")!==false) { ?>
				<?php 
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
			<?php  } ?>

		<?php 
		if(strpos($pageLayout,",translate,")!==false) { ?>
			<div class="box closed"><h2 onclick="kBoxSwapOpening(this.parentNode);">Traduzioni</h2>
				<table><?php 
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
						<?php  } ?>
					</table>
				</div>
			<?php  } ?>
		<br /><br />
	
		<div style="clear:both;"></div>
		<div class="submit">
			<input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" />
			<div class="draft"><?= b3_create_input("online","checkbox",$kaTranslate->translate('Shop:DRAFT'),'n',false,false,($row['online']=='y'||isset($_GET['firstedit'])?'':'checked')); ?></div>
			</div>
		</div>
	</form>
	
	<?php 
	}

include_once("../inc/foot.inc.php");
