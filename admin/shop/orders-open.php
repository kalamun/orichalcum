<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Shop:Opened orders");
include_once("../inc/head.inc.php");
include_once("./shop.lib.php");
include_once("../inc/comments.lib.php");
include_once("../inc/metadata.lib.php");

$kaShop=new kaShop();
$kaMetadata=new kaMetadata;

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
	</div>
	
<div class="topset">
	<table class="tabella">
	<tr>
	<th><?= $kaTranslate->translate('Shop:Order #'); ?></th>
	<th><?= $kaTranslate->translate('Shop:Date'); ?></th>
	<th><?= $kaTranslate->translate('Shop:Customer'); ?></th>
	<th><?= $kaTranslate->translate('Shop:Payment'); ?></th>
	<th><?= $kaTranslate->translate('Shop:Delivery'); ?></th>
	<th colspan="2">&nbsp;</th>
	</tr>
	<?php
	$conditions="";
	if(isset($_GET['search'])&&$_GET['search']!="") {
		$conditions.="summary LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
		$conditions.="uid LIKE '%".b3_htmlize($_GET['search'],true,"")."%'";
		}
	foreach($kaShop->getOrderList($conditions) as $row) {
		?><tr id="order<?= $row['uid']; ?>">
		<td>
			<h2><?= $row['uid']; ?></h2>
			</td>
		<td class="percorso"><?= str_replace(" ",'<br /><img src="../img/clock10.png" width="10" height="10" /> ',$row['friendlydate']); ?></td>
		<td><?= isset($row['member']['name'])?$row['member']['name']:''; ?></td>
		<td class="payment"><?= $row['totalprice']; ?> <?= $kaImpostazioni->getVar('shop-currency',2); ?> <div class="payed <?= $row['payed']; ?>" onmouseover="kOpenBaloon('ajax/paymentBaloon.php?idord=<?= $row['idord']; ?>',kGetPosition(this).y,(kGetPosition(this).x+this.offsetWidth/2));" onmouseout="kCloseBaloon();"><?= $row['payment_method']['name']; ?></div></td>
		<td class="shippment"><?= $row['shipped']=='n'?$kaTranslate->translate('Shop:Not yet shipped'):$kaTranslate->translate('Shop:Shipped'); ?> <div class="shipped <?= $row['shipped']; ?>" onmouseover="kOpenBaloon('ajax/deliverBaloon.php?idord=<?= $row['idord']; ?>',kGetPosition(this).y,(kGetPosition(this).x+this.offsetWidth/2));" onmouseout="kCloseBaloon();"><?= $row['deliverer']['name']; ?></div></td>
		<td>
			<input type="button" class="button" value="<?= $kaTranslate->translate('Shop:Details'); ?>" onclick="k_openIframeWindow('ajax/orderSummary.php?idord=<?= $row['idord']; ?>','800px','450px');">
			</td>
		<td style="white-spaces:nowrap;vertical-align:middle;">
			<small class="actions">
				<a href="#" onclick="k_openIframeWindow('ajax/orderClose.php?idord=<?= $row['idord']; ?>','600px','400px'); return false;"><?= $kaTranslate->translate('Shop:Close order'); ?></a><br />
				<a href="#" onclick="k_openIframeWindow('ajax/orderCancel.php?idord=<?= $row['idord']; ?>','600px','400px'); return false;" class="warning"><?= $kaTranslate->translate('Shop:Cancel'); ?></a>
				</small>
			</td>
		</tr>
		<? } ?>
	</div>

<? include_once("../inc/foot.inc.php"); ?>
