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

$mese=array("","Gennaio","Febbraio","Marzo","Aprile","Maggio","Giugno","Luglio","Agosto","Settembre","Ottobre","Novembre","Dicembre");
$mesebreve=array("","Gen","Feb","Mar","Apr","Mag","Giu","Lug","Ago","Set","Ott","Nov","Dic");

/* AZIONI */
if(isset($_GET['offline'])) {
	$id=$kaShop->offlineItem($_GET['offline']);
	if($id==false) $log="Problemi durante la modifica del database<br />";

	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Shop: Errore nella messa off-line dell\'oggetto <em>'.$_GET['offline'].'</em>');
		}
	else {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Shop:Item successfully off-lined').'</div>';
		$kaLog->add("UPD",'Shop: Messo off-line l\'oggetto <em>'.$_GET['offline'].'</em>');
		}
	}
elseif(isset($_GET['delete'])) {
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


?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<?
?>
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
			</script>
		</fieldset>
		<br />
		</div>
		
	<div class="topset">
		<table class="tabella">
		<tr>
		<th>on/off</th>
		<th><?= $kaTranslate->translate('Shop:Title'); ?></th><th><?= $kaTranslate->translate('Shop:URL'); ?></th>
		<?= (strpos($pageLayout,",public,")!==false?'<th style="text-align:center;">'.$kaTranslate->translate('Shop:Visible from').'</th>':''); ?>
		<th><?= $kaTranslate->translate('Shop:Price'); ?></th>
		</tr>
		<?php
		$conditions="";
		if(isset($_GET['search'])&&$_GET['search']!="") {
			$conditions.="titolo LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
			$conditions.="sottotitolo LIKE '%".b3_htmlize($_GET['search'],true,"")."%' OR ";
			$conditions.="dir LIKE '%".b3_htmlize($_GET['search'],true,"")."%'";
			}
		foreach($kaShop->getItemsList($conditions) as $row) {
			if(!isset($row['categorie'][0])) $row['categorie'][0]=array('dir'=>'tmp');
			?><tr>
			<td><img src="img/<?= $row['online']=='y'?'act':'sus'; ?>.png" width="16" height="16" title="<?= $row['online']=='y'?'On-line':'Off-line'; ?>" /></td>
			<td onmouseover="showActions(this)" onmouseout="hideActions(this)">
				<h2><a href="?offline=<?= $row['idsitem']; ?>"><?= $row['titolo']; ?></a></h2>
				<div class="small" style="visibility:hidden;"><? if($row['online']=='y') { ?><a href="?offline=<?= $row['idsitem']; ?>" onclick="return confirm('<?= addslashes($kaTranslate->translate('Shop:Do you want to off-line this item?')); ?>');"><?= $kaTranslate->translate('Shop:Turn off-line'); ?></a> | <? } ?><a href="?delete=<?= $row['idsitem']; ?>" class="delete" onclick="return confirm('<?= addslashes($kaTranslate->translate('Shop:Do you want to COMPLETELY DELETE this item?')); ?>');"><?= $kaTranslate->translate('UI:Delete'); ?></a></div>
				</div>
			<td class="percorso"><a href="?idnews='.$row['idnews'].'"><?= $row['dir']; ?></a></td>
			<?= (strpos($pageLayout,",public,")!==false?'<td><div class="data"><div class="giorno">'.substr($row['public'],8,2).' '.$mesebreve[ltrim(substr($row['public'],5,2),"0")].'</div><div class="ora">'.substr($row['public'],11,5).'</div></div></td>':''); ?>
			<td>
				<?= (strpos($pageLayout,",discounted,")!==false&&$row['scontato']>0?'<del>'.$row['prezzo'].'</del><br />'.$row['scontato']:$row['prezzo']); ?>
				</td>
			</tr>
			<? } ?>
		</div>
<?
include_once("../inc/foot.inc.php");
?>
