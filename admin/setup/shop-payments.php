<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Negozio Pagamento");
include_once("../inc/head.inc.php");

?>
<h1><?= $kaTranslate->translate('Negozio'); ?></h1>
<?php  include('shopmenu.php'); ?>
<br />


<?php 
if(!isset($_GET['idspay'])) {
	
	/* AZIONI */
	if(isset($_POST['idspay'])&&is_array($_POST['idspay'])) {
		for($i=0;isset($_POST['idspay'][$i]);$i++) {
			$query="UPDATE ".TABLE_SHOP_PAYMENTS." SET ordine=".($i+1)." WHERE ll='".$_SESSION['ll']."' AND idspay=".$_POST['idspay'][$i]." LIMIT 1";
			ksql_query($query);
			}
		}

	elseif(isset($_POST['addpayments'])) {
		$log="";
		$query="SELECT * FROM ".TABLE_SHOP_PAYMENTS." WHERE ll='".$_SESSION['ll']."' ORDER BY ordine DESC LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$ordine=$row['ordine']+1;
		$gateway="";
		if(preg_match("/.*Pay.?Pal.*/i",$_POST['name'])) $gateway="paypal";
		$query="INSERT INTO ".TABLE_SHOP_PAYMENTS." (`name`,`descr`,`zones`,`price`,`pricepercent`,`gateway`,`paypal_account`,`mail_instructions`,`ordine`,`ll`) VALUES('".b3_htmlize($_POST['name'],true,"")."','<p></p>',',','0','0','".$gateway."','','<p></p>',".$ordine.",'".$_SESSION['ll']."')";
		if(!ksql_query($query)) $log="Errore durante l'inserimento del sistema di pagamento";
		else $id=ksql_insert_id();

		if($log!="") {
			echo '<div id="MsgAlert">'.$log.'</div>';
			$kaLog->add("ERR",'Shop: Errore nella creazione del metodo di pagamento <em>'.b3_htmlize($_POST['name'],true,"").'</em>');
			}
		else {
			$kaLog->add("INS",'Shop: Creato il metodo di pagamento <em>'.$_POST['name'].' (ID: '.$id.')</em>');
			echo '<div id="MsgSuccess">Metodo di pagamento inserito con successo.<br />Attendi...</div>';
			echo '<meta http-equiv="refresh" content="0; url=?idspay='.$id.'">';
			include(ADMINRELDIR.'inc/foot.inc.php');
			die();
			}
		}
	
	elseif(isset($_GET['delete'])) {
		$log="";
		$query="DELETE FROM ".TABLE_SHOP_PAYMENTS." WHERE ll='".$_SESSION['ll']."' AND idspay=".$_GET['delete']." LIMIT 1";
		if(!ksql_query($query)) $log="Errore durante l'eliminazione";
		if($log!="") {
			echo '<div id="MsgAlert">'.$log.'</div>';
			$kaLog->add("ERR",'Shop: Errore nell\'eliminazione del metodo di pagamento <em>ID: '.b3_htmlize($_GET['delete'],true,"").'</em>');
			}
		else {
			$kaLog->add("DEL",'Shop: Eliminato il metodo di pagamento <em>ID: '.$_GET['delete'].'</em>');
			echo '<div id="MsgSuccess">Metodo di pagamento eliminato con successo</div>';
			}
		}
	
	/* FINE AZIONI */
	
	?>
	<form action="" method="post" id="saveOrder">
	<ul class="DragDeveloper">
	<?php 
	$query="SELECT * FROM ".TABLE_SHOP_PAYMENTS." WHERE ll='".$_SESSION['ll']."' ORDER BY ordine";
	$results=ksql_query($query);
	while($row=ksql_Fetch_array($results)) { ?>
		<li onmouseover="showActions(this)" onmouseout="hideActions(this)">
		<div class="small" style="visibility:hidden;float:right;"><a href="?idspay=<?php  echo $row['idspay']; ?>">Modifica</a> | <a href="?delete=<?php  echo $row['idspay']; ?>" onclick="return confirm('Sei sicuro di voler rimuovere questo corriere?');">Elimina</a></div>
		<?= $row['name']; ?>
		<input type="hidden" name="idspay[]" value="<?= $row['idspay']; ?>" />
		</li>
		<?php  }
	?>
	</ul></form>

		<script type="text/javascript" src="<?php  echo ADMINDIR; ?>/js/drag_and_drop.js"></script>
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
			Aggiungi un metodo di pagamento: <input type="text" value="" placeholder="Nome del tipo di pagamento" style="width:300px;" name="name" /> <input type="submit" name="addpayments" value="Aggiungi" class="smallbutton" />
			</form>
		</div>
	<div class="help">Ricorda che devi inserire i metodi di pagamento in ogni lingua!</div>
	<?php  }
	
	
else {
	$zone=array();
	$query="SELECT * FROM ".TABLE_SHOP_COUNTRIES." GROUP BY zone ORDER BY zone";
	$results=ksql_query($query);
	while($row=ksql_fetch_array($results)) {
		$zone[$row['zone']]=true;
		}
	
	/* AZIONI */
	if(isset($_POST['update'])) {
		$log="";
		$zones=",";
		if(isset($_POST['zones'])) {
			foreach($_POST['zones'] as $ka=>$v) { $zones.=$ka.','; }
			}
		$query="UPDATE ".TABLE_SHOP_PAYMENTS." SET `name`='".b3_htmlize($_POST['name'],true,"")."',`descr`='".b3_htmlize($_POST['descr'],true)."',`price`='".round(floatval($_POST['price']),2)."',`pricepercent`='".round(floatval($_POST['pricepercent']),2)."',gateway='".$_POST['gateway']."',`paypal_account`='',zones='".$zones."',`mail_instructions`='".b3_htmlize($_POST['mail_instructions'],true)."' WHERE `idspay`='".intval($_GET['idspay'])."' LIMIT 1";
		if(!ksql_query($query)) $log="Errore durante la scrittura nel database";
		
		if($log!="") {
			echo '<div id="MsgAlert">'.$log.'</div>';
			$kaLog->add("ERR",'Shop: Errore nella modifica del metodo di pagamento <em>'.b3_htmlize($_POST['name'],true,"").' (ID: '.$_GET['idspay'].')</em>');
			}
		else {
			$kaLog->add("UPD",'Shop: Modificato il metodo di pagamento <em>'.$_POST['name'].'</em>');
			echo '<div id="MsgSuccess">Modifiche salvate con successo</div>';
			}
		}
	/* FINE AZIONI */

	$query="SELECT * FROM ".TABLE_SHOP_PAYMENTS." WHERE idspay='".intval($_GET['idspay'])."' LIMIT 1";
	$results=ksql_query($query);
	$payment=ksql_fetch_array($results);
	
	?>

	<form action="?idspay=<?= $_GET['idspay']; ?>" method="post">
	<div class="title">
		<?= b3_create_input("name","text","Metodo di pagamento<br />",b3_lmthize($payment['name'],"input"),"95%",250); ?>
		</div>
	<div class="box">
		<?php 
		$values=array("","paypal","virtualpay","pagonline");
		$options=array("","PayPal","VirtualPay","pagonline");
		echo b3_create_select("gateway","Gateway ",$options,$values,$payment['gateway']);
		?>
		</div>
	<br />
	<table><tr>
		<td style="padding:0 5px;vertical-align:middle;"><h3>Commissione:</h3></td>
		<td style="padding:0 5px;"><?= b3_create_input("price","text","fisso ",$payment['price'],'40px',false); ?> <?= $kaImpostazioni->getVar('shop-currency',2); ?></td>
		<td style="padding:0 5px;"><?= b3_create_input("pricepercent","text","percentuale ",$payment['pricepercent'],'40px',false); ?> %</td>
		</tr></table>
	<br />
	
	<div class="subset">
		<h2>Disponibile per</h2>
		<?php 
		foreach($zone as $ka=>$v) { ?>
			<input type="checkbox" name="zones[<?= $ka; ?>]" value="1" <?= (strpos($payment['zones'],",".$ka.",")!==false?'checked':''); ?> /> Zona <?= $ka; ?><br />
			<?php  }
		?>
		</div>
	
	<div class="topset">
		<?= b3_create_textarea("descr","Descrizione<br />",b3_lmthize($payment['descr'],"textarea"),"100%","150px",RICH_EDITOR); ?>
		<br />
		<?= b3_create_textarea("mail_instructions","Istruzioni di pagamento da inserire nella e-mail di riepilogo dell'ordine<br />",b3_lmthize($payment['mail_instructions'],"textarea"),"100%","200px",RICH_EDITOR); ?>
	
		</div>
		<div style="clear:both;"></div>
		<br />
		<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" /></div>
	</form>
	<?php  } ?>

<?php 
include_once("../inc/foot.inc.php");
