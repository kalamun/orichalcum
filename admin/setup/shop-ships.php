<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Negozio Spedizione");
include_once("../inc/head.inc.php");

?>
<h1><?= $kaTranslate->translate('Negozio'); ?></h1>
<? include('shopmenu.php'); ?>
<br />


<?
if(!isset($_GET['iddel'])) {
	
	/* AZIONI */
	if(isset($_POST['iddel'])&&is_array($_POST['iddel'])) {
		for($i=0;isset($_POST['iddel'][$i]);$i++) {
			$query="UPDATE ".TABLE_SHOP_DELIVERERS." SET ordine=".($i+1)." WHERE iddel=".$_POST['iddel'][$i]." LIMIT 1";
			mysql_query($query);
			}
		}

	elseif(isset($_POST['adddeliverer'])) {
		$log="";
		$query="SELECT * FROM ".TABLE_SHOP_DELIVERERS." ORDER BY ordine DESC LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$ordine=$row['ordine']+1;
		$query="INSERT INTO ".TABLE_SHOP_DELIVERERS." (`name`,`zones`,`ordine`) VALUES('".b3_htmlize($_POST['name'],true,"")."',',',".$ordine.")";
		if(!mysql_query($query)) $log="Errore durante l'inserimento del corriere";
		else $id=mysql_insert_id();

		if($log!="") {
			echo '<div id="MsgAlert">'.$log.'</div>';
			$kaLog->add("ERR",'Shop: Errore nella creazione del corriere <em>'.b3_htmlize($_POST['name'],true,"").' (ID: '.$id.')</em>');
			}
		else {
			$kaLog->add("INS",'Shop: Creato il corriere <em>'.$_POST['name'].' (ID: '.$id.')</em>');
			echo '<div id="MsgSuccess">Corriere inserito con successo.<br />Attendi...</div>';
			echo '<meta http-equiv="refresh" content="0; url=?iddel='.$id.'">';
			include(ADMINRELDIR.'inc/foot.inc.php');
			die();
			}
		}
	
	elseif(isset($_GET['delete'])) {
		$log="";
		$query="DELETE FROM ".TABLE_SHOP_DELIVERERS." WHERE iddel=".$_GET['delete']." LIMIT 1";
		if(!mysql_query($query)) $log="Errore durante l'eliminazione";
		if($log!="") {
			echo '<div id="MsgAlert">'.$log.'</div>';
			$kaLog->add("ERR",'Shop: Errore nell\'eliminazione del corriere <em>ID: '.b3_htmlize($_GET['delete'],true,"").'</em>');
			}
		else {
			$kaLog->add("DEL",'Shop: Eliminato il corriere <em>ID: '.$_GET['delete'].'</em>');
			echo '<div id="MsgSuccess">Corriere eliminato con successo</div>';
			}
		}
	
	/* FINE AZIONI */
	
	?>
	<form action="" method="post" id="saveOrder">
	<ul class="DragDeveloper">
	<?
	$query="SELECT * FROM ".TABLE_SHOP_DELIVERERS." ORDER BY ordine";
	$results=mysql_query($query);
	while($row=mysql_Fetch_array($results)) { ?>
		<li onmouseover="showActions(this)" onmouseout="hideActions(this)">
		<div class="small" style="visibility:hidden;float:right;"><a href="?iddel=<? echo $row['iddel']; ?>">Modifica</a> | <a href="?delete=<? echo $row['iddel']; ?>" onclick="return confirm('Sei sicuro di voler rimuovere questo corriere?');">Elimina</a></div>
		<?= $row['name']; ?>
		<input type="hidden" name="iddel[]" value="<?= $row['iddel']; ?>" />
		</li>
		<? }
	?>
	</ul></form>

		<script type="text/javascript" src="<? echo ADMINDIR; ?>/js/drag_and_drop.js"></script>
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

				kDragAndDrop=new kDrago();
				kDragAndDrop.dragClass("DragDeveloper");
				kDragAndDrop.dropClass("DragDeveloper");
				kDragAndDrop.containerTag('LI');
				kDragAndDrop.onDrag(function (drag,target) {
					var container=drag.parentNode.childNodes;
					if(target.className!='DragDeveloper'&&target!=drag) {
						if((parseInt(target.getAttribute("ddTop"))+target.offsetHeight/2)>kWindow.mousePos.y) target.parentNode.insertBefore(drag,target);
						else target.parentNode.insertBefore(drag,target.nextSibling);
						}
					kDragAndDrop.savePosition();
					});
				kDragAndDrop.onDrop(function (drag,target) {
					b3_openMessage('Salvataggio in corso',false);
					document.getElementById('saveOrder').submit();
					});
			</script>

	<br />	
	<div class="box">
		<form method="post" action="">
			Aggiungi un corriere: <input type="text" value="" placeholder="Nome dello spedizioniere" style="width:300px;" name="name" /> <input type="submit" name="adddeliverer" value="Aggiungi" class="smallbutton" />
			</form>
		</div>
	<? }
	
	
else {
	$zone=array();
	$query="SELECT * FROM ".TABLE_SHOP_COUNTRIES." GROUP BY zone ORDER BY zone";
	$results=mysql_query($query);
	while($row=mysql_fetch_array($results)) {
		$zone[$row['zone']]=true;
		}
	
	/* AZIONI */
	if(isset($_POST['update'])) {
		$log="";
		$zones=",";
		if(isset($_POST['zones'])) {
			foreach($_POST['zones'] as $ka=>$v) { $zones.=$ka.','; }
			}
		$query="UPDATE ".TABLE_SHOP_DELIVERERS." SET `name`='".b3_htmlize($_POST['name'],true,"")."',zones='".$zones."' WHERE `iddel`='".intval($_GET['iddel'])."' LIMIT 1";
		if(!mysql_query($query)) $log="Errore durante la scrittura nel database";
		
		$query="DELETE FROM ".TABLE_SHOP_DEL_PRICES." WHERE `iddel`='".intval($_GET['iddel'])."'";
		if(!mysql_query($query)) $log="Errore durante la pulizia dei vecchi prezzi dal database";
		
		if(isset($_POST['weight'])) {
			foreach($_POST['weight'] as $ka=>$v) {
				if(trim($_POST['weight'][$ka],"0.")!="") {
					$prices=array();
					foreach($zone as $kak=>$vv) {
						while($kak<count($prices)-1) $prices[]='';
						$prices[$kak]=$_POST['prices'][$kak][$ka];
						}
					$prices=",".implode(",",$prices).",";
					$query="INSERT INTO ".TABLE_SHOP_DEL_PRICES." (`iddel`,`maxweight`,`prices`) VALUES('".intval($_GET['iddel'])."','".number_format($_POST['weight'][$ka],3)."','".$prices."')";
					if(!mysql_query($query)) $log="Errore durante l'inserimento dei prezzi nel database";
					}
				}
			}

		if($log!="") {
			echo '<div id="MsgAlert">'.$log.'</div>';
			$kaLog->add("ERR",'Shop: Errore nella modifica del corriere <em>'.b3_htmlize($_POST['name'],true,"").'</em>');
			}
		else {
			$kaLog->add("UPD",'Shop: Modificato il corriere <em>'.$_POST['name'].'</em>');
			echo '<div id="MsgSuccess">Modifiche salvate con successo</div>';
			}
		}
	/* FINE AZIONI */

	$query="SELECT * FROM ".TABLE_SHOP_DELIVERERS." WHERE iddel='".intval($_GET['iddel'])."' LIMIT 1";
	$results=mysql_query($query);
	$deliverer=mysql_Fetch_array($results);
	
	?>

	<form action="?iddel=<?= $_GET['iddel']; ?>" method="post">
	<div class="title"><?= b3_create_input("name","text","Corriere<br />",b3_lmthize($deliverer['name'],"input"),"95%",250); ?></div>
	<br />
	
	<div class="subset">
		<h2>Disponibile per</h2>
		<?
		foreach($zone as $ka=>$v) { ?>
			<input type="checkbox" name="zones[<?= $ka; ?>]" value="1" <?= (strpos($deliverer['zones'],",".$ka.",")!==false?'checked':''); ?> /> Zona <?= $ka; ?><br />
			<? }
		?>
		</div>
	
	<div class="topset">
		<script type="text/javascript">
			function addWeight() {
				var tabella=document.getElementById('tariffe');
				var nl=tabella.getElementsByTagName('TR')[tabella.getElementsByTagName('TR').length-1].cloneNode(true);
				tabella.appendChild(nl);
				}
			function delWeight(line) {
				line.parentNode.removeChild(line);
				}
			</script>
		<h2>Tariffe</h2>
		<table class="tabella" id="tariffe">
			<tr><th>peso massimo</th>
			<? foreach($zone as $ka=>$v) { ?>
				<th>Tariffa Zona <?= $ka; ?></th>
				<? } ?>
				<th>&nbsp;</th>
				</tr>
			<?
			$c=0;
			$query="SELECT * FROM ".TABLE_SHOP_DEL_PRICES." WHERE iddel='".$deliverer['iddel']."' ORDER BY maxweight";
			$results=mysql_query($query);
			while($row=mysql_fetch_array($results)) {
				$prezzi=explode(",",$row['prices']);
				?>
				<tr>
				<td class="weight"><input type="text" name="weight[]" value="<?= number_format($row['maxweight'],3); ?>" /> Kg</td>
				<? foreach($zone as $ka=>$v) { ?>
					<td class="zone"><input type="text" name="prices[<?= $ka; ?>][]" value="<?= number_format($prezzi[$ka],2); ?>" /> euro</td>
					<? } ?>
					<td><img src="<?= ADMINDIR; ?>img/12close.gif" width="12" height="12" alt="Elimina" style="cursor:pointer;padding:0 10px;" onclick="delWeight(this.parentNode.parentNode);" /></td>
				</tr>
				<?
				$c++;
				}
			if($c==0) { ?>
				<tr>
				<td class="weight"><input type="text" name="weight[]" value="0.000" /> Kg</td>
				<? foreach($zone as $ka=>$v) { ?>
					<td class="zone"><input type="text" name="prices[<?= $ka; ?>][]" value="0.00" /> euro</td>
					<? } ?>
					<td><img src="<?= ADMINDIR; ?>img/12close.gif" width="12" height="12" alt="Elimina" style="cursor:pointer;padding:0 10px;" onclick="delWeight(this.parentNode.parentNode);" /></td>
				</tr>
				<? } ?>
			</table>
		<br /><a href="javascript:addWeight();" class="smallbutton"><img src="<?= ADMINDIR; ?>img/add.png" width="10" height="10" alt="" /> Aggiungi una linea</a><br />
		
		</div>
		<div style="clear:both;"></div>
		<br />
		<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" /></div>
	</form>
	<? } ?>

<?
include_once("../inc/foot.inc.php");
?>
