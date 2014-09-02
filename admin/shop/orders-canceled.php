<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Shop:Canceled orders");
include_once("../inc/head.inc.php");
include_once("./shop.lib.php");
include_once("../inc/comments.lib.php");
include_once("../inc/metadata.lib.php");

$kaShop=new kaShop();
$kaMetadata=new kaMetadata;

if(!isset($_GET['y'])) $_GET['y']=date("Y");
if(!isset($_GET['m'])) $_GET['m']=date("m");


/* AZIONI */
if(isset($_POST['update'])&&isset($_GET['idord'])) {
	$log="";
	if(isset($_POST['date_day'])&&isset($_POST['date_hour'])) $date_date=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['date_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['date_hour']);
	else $date_date="false";
	if(isset($_POST['visible_day'])&&isset($_POST['visible_hour'])) $visible_date=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['visible_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['visible_hour']);
	else $visible_date="false";
	if(isset($_POST['expiration_day'])&&isset($_POST['expiration_hour'])) $expiration_date=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['expiration_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['expiration_hour']);
	else $expiration_date="false";

	if(isset($_POST['idcat'])) {
		$categorie=",";
		foreach($_POST['idcat'] as $idcat) { $categorie.=$idcat.','; }
		}
	else $categorie="false";

	isset($_POST['titolo'])?$_POST['titolo']=b3_htmlize($_POST['titolo'],false,""):$_POST['titolo']="false";
	isset($_POST['sottotitolo'])?$_POST['sottotitolo']=b3_htmlize($_POST['sottotitolo'],false,""):$_POST['sottotitolo']="false";
	isset($_POST['anteprima'])?$_POST['anteprima']=b3_htmlize($_POST['anteprima'],false):$_POST['anteprima']="false";
	isset($_POST['testo'])?$_POST['testo']=b3_htmlize($_POST['testo'],false):$_POST['testo']="false";
	isset($_POST['dir'])?$_POST['dir']=b3_htmlize($_POST['dir'],false,""):$_POST['dir']="false";
	isset($_POST['prezzo'])?$_POST['prezzo']=number_format($_POST['prezzo'],2):$_POST['prezzo']="0";
	isset($_POST['scontato'])?$_POST['scontato']=number_format($_POST['scontato'],2):$_POST['scontato']="0";
	isset($_POST['weight'])?$_POST['weight']=intval($_POST['weight']):$_POST['weight']="0";
	if(!isset($_POST['template'])) $_POST['template']="false";
	if(!isset($_POST['layout'])) $_POST['layout']="false";

	$id=$kaShop->updateItem($_GET['idord'],$_POST['online'],$_POST['titolo'],$_POST['sottotitolo'],$_POST['anteprima'],$_POST['testo'],$categorie,$_POST['prezzo'],$_POST['scontato'],$date_date,$visible_date,$expiration_date,$_POST['qta'],$_POST['weight'],$_POST['layout'],$_POST['dir']);
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


?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<div class="subset">
	<fieldset class="box"><legend><?= $kaTranslate->translate('UI:Search'); ?></legend>
	<input type="text" name="search" id="searchQ" style="width:180px;" value="<? if(isset($_GET['search'])) echo str_replace('"','&quot;',$_GET['search']); ?>" />
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
		
		function markPayment(uid) {
			for(var i=0;document.getElementById('order'+uid).getElementsByTagName('TD')[i];i++) {
				var td=document.getElementById('order'+uid).getElementsByTagName('TD')[i];
				if(td.className=='payment') {
					td.getElementsByTagName('DIV')[0].className='payed s';
					}
				}
			}
		function markShipment(uid) {
			var tr=document.getElementById('order'+uid);
			tr.parentNode.removeChild(tr,true);
			}
		function removeOrder(uid) {
			var tr=document.getElementById('order'+uid);
			tr.parentNode.removeChild(tr,true);
			}
		</script>
	</fieldset>
	<br />

	<h2><?= $kaTranslate->translate('Shop:Archive'); ?></h2>
	<?
	$tmpyyyy="";
	foreach($kaShop->getOrderArchiveMonths("",'CNC') as $date) {
		$yyyy=substr($date,0,4);
		$mm=substr($date,5,2);
		if($tmpyyyy!=$yyyy) {
			if($tmpyyyy!="") echo '</ul>';
			echo '<ul class="archive"><li>'.$yyyy.'</li>';
			$tmpyyyy=$yyyy;
			}
		if($_GET['y']==date("Y")) $_GET['y']=$yyyy;
		if($_GET['m']==date("m")) $_GET['m']=$mm;
		echo '<li><a href="?m='.ltrim($mm,'0').'&y='.$yyyy.'">'.strftime("%B",mktime(1,0,0,$mm,1,$yyyy)).'</a></li>';
		}
	echo '</ul>';
	?>
	</div>
	
<div class="topset">
	<table class="tabella">
	<tr>
	<th><?= $kaTranslate->translate('Shop:Order #'); ?></th>
	<th><?= $kaTranslate->translate('Shop:Date'); ?></th>
	<th><?= $kaTranslate->translate('Shop:Customer'); ?></th>
	<th><?= $kaTranslate->translate('Shop:Payment'); ?></th>
	<th><?= $kaTranslate->translate('Shop:Delivery'); ?></th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	</tr>
	<?php
	$conditions="";
	if(isset($_GET['search'])&&$_GET['search']!="")
	{
		$conditions.="order_summary LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
		$conditions.="uid LIKE '%".b3_htmlize($_GET['search'],true,"")."%'";
	} else {
		$conditions.="`date` LIKE '".mysql_real_escape_string($_GET['y'].'-'.str_pad($_GET['m'],2,"0",STR_PAD_LEFT))."%'";
	}

	foreach($kaShop->getOrderList($conditions,'CNC') as $row) {
		?><tr id="order<?= $row['uid']; ?>">
		<td>
			<h2><?= $row['uid']; ?></h2>
		</td>

		<td class="percorso"><?= str_replace(" ",'<br /><img src="../img/clock10.png" width="10" height="10" /> ',$row['friendlydate']); ?></td>

		<td><?= isset($row['member']['name'])?$row['member']['name']:''; ?></td>
		
		<td class="payment">
			<?= $row['totalprice']; ?> <?= $kaImpostazioni->getVar('shop-currency',2); ?>
			<div class="payed <?= $row['payed']; ?>" onmouseover="kOpenBaloon('ajax/paymentBaloon.php?idord=<?= $row['idord']; ?>',kGetPosition(this).y,(kGetPosition(this).x+this.offsetWidth/2));" onmouseout="kCloseBaloon();">
				<?= $row['payment_method']['name']; ?>
			</div>
		</td>
		
		<td class="shipment">
			<?= $row['shipped']=='n'?$kaTranslate->translate('Shop:Not yet shipped'):$kaTranslate->translate('Shop:Shipped'); ?>
			<div class="shipped <?= $row['shipped']; ?>" onmouseover="kOpenBaloon('ajax/deliverBaloon.php?idord=<?= $row['idord']; ?>',kGetPosition(this).y,(kGetPosition(this).x+this.offsetWidth/2));" onmouseout="kCloseBaloon();">
				<?= $row['deliverer']['name']; ?>
			</div>
		</td>

		<td class="details">
			<input type="button" class="button" value="<?= $kaTranslate->translate('Shop:Details'); ?>" onclick="k_openIframeWindow('ajax/orderSummary.php?idord=<?= $row['idord']; ?>','800px','450px');">
			<?
			if(trim($row['notes'])!="") { ?>
				<div class="notes" onmouseover="kOpenBaloon('ajax/notesBaloon.php?idord=<?= $row['idord']; ?>',kGetPosition(this).y,(kGetPosition(this).x+this.offsetWidth/2));" onmouseout="kCloseBaloon();">
					<img src="<?= ADMINRELDIR; ?>img/comment.png" width="12" height="12" alt=""> <?= substr($row['notes'],0,max(10,strpos($row['notes']," "))); ?>…
				</div>
			<? }
			?>
		</td>

		<td class="options">
			<small class="actions">
				<a href="#" onclick="k_openIframeWindow('ajax/orderDelete.php?idord=<?= $row['idord']; ?>','600px','400px'); return false;" class="warning"><?= $kaTranslate->translate('Shop:Delete'); ?></a>
				</small>
			</td>
		</tr>
		<? } ?>
	</div>

<? include_once("../inc/foot.inc.php"); ?>
