<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Shop:Delete items");
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
if(isset($_GET['offline']))
{
	$log="";
	$id=$kaShop->offlineItem($_GET['offline']);
	if($id==false) $log="Problemi durante la modifica del database<br />";

	if($log!="")
	{
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Shop: Errore nella messa off-line dell\'oggetto <em>'.$_GET['offline'].'</em>');
	} else {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Shop:Item successfully off-lined').'</div>';
		$kaLog->add("UPD",'Shop: Messo off-line l\'oggetto <em>'.$_GET['offline'].'</em>');
	}

} elseif(isset($_GET['delete'])) {
	$log="";
	$id=$kaShop->deleteItem($_GET['delete']);
	if($id==false) $log="Problemi durante la modifica del database<br />";

	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Shop: Errore durante l\'eliminazione dell\'oggetto <em>'.$_GET['delete'].'</em>');
		}
	else {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Shop:Item successfully deleted').'</div>';
		$kaLog->add("UPD",'Shop: Eliminato l\'oggetto <em>'.$_GET['delete'].'</em>');
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
	else $vars['conditions'].="`".$vars['orderby']."` LIKE '".ksql_real_escape_string($_GET['l'])."%'";
	
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
					
					<small class="actions"><?php  if($row['online']=='y') { ?><a href="?offline=<?= $row['idsitem']; ?>" onclick="return confirm('<?= addslashes($kaTranslate->translate('Shop:Do you want to off-line this item?')); ?>');"><?= $kaTranslate->translate('Shop:Turn off-line'); ?></a> | <?php  } ?><a href="?delete=<?= $row['idsitem']; ?>" class="delete" onclick="return confirm('<?= addslashes($kaTranslate->translate('Shop:Do you want to COMPLETELY DELETE this item?')); ?>');"><?= $kaTranslate->translate('UI:Delete'); ?></a></small>
					
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


include_once("../inc/foot.inc.php");
