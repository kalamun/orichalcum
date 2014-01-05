<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Negozio Coupons");
include_once("../inc/head.inc.php");
include_once("../shop/shop.lib.php");

$kaShop=new kaShop();

?>
<h1><?= $kaTranslate->translate('Negozio'); ?></h1>
<? include('shopmenu.php'); ?>
<br />


<?
if(!isset($_GET['idscoup'])) {
	
	/* AZIONI */
	if(isset($_POST['idscoup'])&&is_array($_POST['idscoup'])) {
		for($i=0;isset($_POST['idscoup'][$i]);$i++) {
			$query="UPDATE ".TABLE_SHOP_COUPONS." SET ordine=".($i+1)." WHERE ll='".$_SESSION['ll']."' AND idscoup=".$_POST['idscoup'][$i]." LIMIT 1";
			mysql_query($query);
			}
		}

	elseif(isset($_POST['addcoupon'])) {
		$log="";
		$query="INSERT INTO ".TABLE_SHOP_COUPONS." (`title`,`context`,`action`,`starting_date`,`expiration_date`) VALUES('".b3_htmlize($_POST['title'],true,"")."','always','discount=0',NOW(),NOW())";
		if(!mysql_query($query)) $log="Errore durante l'inserimento del coupon";
		else $id=mysql_insert_id();

		if($log!="") {
			echo '<div id="MsgAlert">'.$log.'</div>';
			$kaLog->add("ERR",'Shop: Error creating a new coupon <em>'.b3_htmlize($_POST['title'],true,"").'</em>');
			}
		else {
			$kaLog->add("INS",'Shop: Created the new coupon <em>'.$_POST['title'].' (ID: '.$id.')</em>');
			echo '<div id="MsgSuccess">Coupon inserito con successo.<br />Attendi...</div>';
			echo '<meta http-equiv="refresh" content="0; url=?idscoup='.$id.'">';
			include(ADMINRELDIR.'inc/foot.inc.php');
			die();
			}
		}
	
	elseif(isset($_GET['delete'])) {
		$log="";
		$query="DELETE FROM ".TABLE_SHOP_COUPONS." WHERE ll='".$_SESSION['ll']."' AND idscoup=".$_GET['delete']." LIMIT 1";
		if(!mysql_query($query)) $log="Errore durante l'eliminazione";
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
	<table class="tabella">
	<tr>
		<th>Nome</th>
		<th>Data di inizio</th>
		<th>Data di fine</th>
		<th>Coupons attivi</th>
		<th>Coupons usati</th>
		<th>&nbsp;</th>
		</tr>
	<?
	$query="SELECT * FROM ".TABLE_SHOP_COUPONS." ORDER BY `expiration_date`,`starting_date`,`title`";
	$results=mysql_query($query);
	while($row=mysql_Fetch_array($results)) {
		//count valid coupons
		$rs=mysql_query("SELECT count(*) AS tot FROM ".TABLE_SHOP_COUPONS_CODES." WHERE `idscoup`='".$row['idscoup']."' AND `valid`=1");
		$r=mysql_fetch_array($rs);
		$valid=$r['tot'];
		//count used coupons
		$rs=mysql_query("SELECT count(*) AS tot FROM ".TABLE_SHOP_COUPONS_CODES." WHERE `idscoup`='".$row['idscoup']."' AND `valid`=0");
		$r=mysql_fetch_array($rs);
		$used=$r['tot'];
		?>
		<tr>
		<td><h2><?= $row['title']; ?></h2></td>
		<td><?= preg_replace("/(\d{4}).(\d{2}).(\d{2}).*/","$3-$2-$1",$row['starting_date']); ?></td>
		<td><?= preg_replace("/(\d{4}).(\d{2}).(\d{2}).*/","$3-$2-$1",$row['expiration_date']); ?></td>
		<td class="valid"><?= $valid; ?></td>
		<td class="used"><?= $used; ?></td>
		<td class="actions">
			<a href="?idscoup=<? echo $row['idscoup']; ?>" class="smallbutton">Modifica</a>
			<a href="?delete=<? echo $row['idscoup']; ?>" class="smallalertbutton" onclick="return confirm('Sei sicuro di voler rimuovere questo corriere?');">Elimina</a>
			</td>
		</tr>
		<? }
	?>
	</table>


	<br />	
	<div class="box">
		<form method="post" action="">
			Aggiungi un coupon: <input type="text" value="" placeholder="Nome del coupon" style="width:300px;" name="title" /> <input type="submit" name="addcoupon" value="Aggiungi" class="smallbutton" />
			</form>
		</div>
	<? }
	
	
else {

	/* AZIONI */
	if(isset($_POST['update'])) {
		$log="";

		$contextvalue="";
		if($_POST['context']==">") $contextvalue=floatval($_POST['>value']);
		elseif($_POST['context']==">#") $contextvalue=intval($_POST['>#value']);
		$context=$_POST['context'].$contextvalue;
		
		$actionvalue="";
		if($_POST['type']=="discount") $actionvalue="=".floatval($_POST['discountvalue']);
		elseif($_POST['type']=="discountpercent") $actionvalue="=".floatval($_POST['discountpercentvalue']);
		$action=$_POST['type'].$actionvalue;

		$starting_date=preg_replace("/(\d{1,2}).(\d{1,2}).(\d{4})/","$3-$2-$1",$_POST['starting_date']).' '.$_POST['starting_time'];
		$expiration_date=preg_replace("/(\d{1,2}).(\d{1,2}).(\d{4})/","$3-$2-$1",$_POST['expiration_date']).' '.$_POST['expiration_time'];
		
		$query="UPDATE ".TABLE_SHOP_COUPONS." SET `title`='".b3_htmlize($_POST['title'],true,"")."',`context`='".mysql_real_escape_string($context)."',`action`='".mysql_real_escape_string($action)."',`starting_date`='".mysql_real_escape_string($starting_date)."',`expiration_date`='".mysql_real_escape_string($expiration_date)."' WHERE `idscoup`='".$_GET['idscoup']."' LIMIT 1";
		if(!mysql_query($query)) $log="Errore durante la scrittura nel database";
		
		if($log!="") {
			echo '<div id="MsgAlert">'.$log.'</div>';
			$kaLog->add("ERR",'Shop: Errore nella modifica del coupon <em>'.b3_htmlize($_POST['title'],true,"").' (ID: '.$_GET['idscoup'].')</em>');
			}
		else {
			$kaLog->add("UPD",'Shop: Modificato il coupon <em>'.$_POST['title'].' (ID: '.$_GET['idscoup'].')</em>');
			echo '<div id="MsgSuccess">Modifiche salvate con successo</div>';
			}
		}
	if(isset($_POST['add'])) {
		$kaShop->insertCoupons(array("idscoup"=>$_GET['idscoup'],"quantity"=>$_POST['qty'],"format"=>$_POST['format'],"allowedchars"=>$_POST['allowedchars']));
		}
	/* FINE AZIONI */

	
	
	$query="SELECT * FROM ".TABLE_SHOP_COUPONS." WHERE `idscoup`='".intval($_GET['idscoup'])."' LIMIT 1";
	$results=mysql_query($query);
	$coupon=mysql_fetch_array($results);
	
	$context="always";
	if(substr($coupon['context'],0,1)==">") $context=">";
	elseif(substr($coupon['context'],0,2)=="#>") $context="#>";
	$morethantotalvalue=0;
	$morethanitemsvalue=0;
	if(substr($coupon['context'],0,1)==">") $morethantotalvalue=substr($coupon['context'],1);
	elseif(substr($coupon['context'],0,2)=="#>") $morethanitemsvalue=substr($coupon['context'],2);
	
	if(substr($coupon['action'],0,9)=="discount=") $action="discount";
	elseif(substr($coupon['action'],0,16)=="discountpercent=") $action="discountpercent";
	else $action=$coupon['action'];
	$discountvalue=0;
	$discountpercentvalue=0;
	if(substr($coupon['action'],0,9)=="discount=") $discountvalue=substr($coupon['action'],9);
	elseif(substr($coupon['action'],0,16)=="discountpercent=") $discountpercentvalue=substr($coupon['action'],16);
	
	$starting_date=preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}.\d{2}).*/","$3-$2-$1",$coupon['starting_date']);
	$starting_time=preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}.\d{2}).*/","$4",$coupon['starting_date']);
	$expiration_date=preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}.\d{2}).*/","$3-$2-$1",$coupon['expiration_date']);
	$expiration_time=preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}.\d{2}).*/","$4",$coupon['expiration_date']);
	?>

	<div class="topset">
	<form action="?idscoup=<?= $_GET['idscoup']; ?>" method="post">
		<div class="title">
			<?= b3_create_input("title","text","Coupon<br />",b3_lmthize($coupon['title'],"input"),"95%",250); ?>
			</div>
		<br />
		
		<?
		$options=array("Sempre","Importo superiore a...","Numero di oggetti presenti nel carrello superiore a...");
		$values=array("always",">","#>");
		echo b3_create_select("context","Quando applicare questo coupon ",$options,$values,$context,false,false,'onchange="kShowOptions(this);"');
		?><br />
		<div id=">options" style="display:none">
			<?= b3_create_input(">value","text","Importo da superare ",number_format($morethantotalvalue,2),"50px",6).' '.$kaImpostazioni->getVar('shop-currency',2); ?>
			</div>
		<div id="#>options" style="display:none">
			<?= b3_create_input("#>value","text","Numero di oggetti ",number_format($morethanitemsvalue,2),"50px",6); ?>
			</div>
		<br />
		
		<?
		$options=array("Sconto fisso","Sconto percentuale","Gratis le spese di spedizione","Gratis l'oggetto meno costoso del carrello","Gratis l'oggetto più costoso del carrello","Usa i prezzi scontati");
		$values=array("discount","discountpercent","freeshipping","freecheaper","freemoreexpensive","usediscountprices");
		echo b3_create_select("type","Tipo di promozione ",$options,$values,$action,false,false,'onchange="kShowOptions(this);"');
		?><br />
		<div id="discountoptions" style="display:none">
			<?= b3_create_input("discountvalue","text","Importo dello sconto ",number_format($discountvalue,2),"50px",6).' '.$kaImpostazioni->getVar('shop-currency',2); ?>
			</div>
		<div id="discountpercentoptions" style="display:none">
			<?= b3_create_input("discountpercentvalue","text","Importo dello sconto ",number_format($discountpercentvalue,2),"50px",6).' %'; ?>
			</div>
		<br />

		<?= b3_create_input("starting_date","text","Data di inizio validit&agrave; ",b3_lmthize($starting_date,"input"),"70px",15); ?>
		<?= b3_create_input("starting_time","text","ore ",b3_lmthize($starting_time,"input"),"40px",7); ?>
		<br />
		
		<?= b3_create_input("expiration_date","text","Data di fine validit&agrave; ",b3_lmthize($expiration_date,"input"),"70px",15); ?>
		<?= b3_create_input("expiration_time","text","ore ",b3_lmthize($expiration_time,"input"),"40px",7); ?>
		<br />
		
		<script type="text/javascript">
			function kShowOptions(sel) {
				for(var i=0,opt=sel.getElementsByTagName('option');opt[i];i++) {
					var div=document.getElementById(opt[i].value+'options');
					if(div) {
						div.style.display=(opt[i].value==sel.value?'block':'none');
						}
					}
				}
			kShowOptions(document.getElementById('context'));
			kShowOptions(document.getElementById('type'));
			</script>

		<br />
		<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" /></div>
	</form>
			
		<br />
		<div class="couponsValid">
			<h3>COUPON VALIDI</h3>
			<a href="javascript:k_openIframeWindow('ajax/shopCouponsManager.php?valid=1&idscoup=<?= $_GET['idscoup']; ?>','800px','400px');" class="count"><?
			$query="SELECT count(*) AS `tot` FROM ".TABLE_SHOP_COUPONS_CODES." WHERE `idscoup`='".$_GET['idscoup']."' AND `valid`=1";
			$results=mysql_query($query);
			$row=mysql_fetch_array($results);
			echo $row['tot'];
			?></a>
			<a href="ajax/shopCouponsExport.php?csv&valid=1&idscoup=<?= $_GET['idscoup']; ?>" class="smallbutton">Esporta in CSV</a>
			</div>
		
		<div class="couponsUsed">
			<h3>COUPON USATI</h3>
			<a href="javascript:k_openIframeWindow('ajax/shopCouponsManager.php?valid=0&idscoup=<?= $_GET['idscoup']; ?>','800px','400px');" class="count"><?
			$query="SELECT count(*) AS `tot` FROM ".TABLE_SHOP_COUPONS_CODES." WHERE `idscoup`='".$_GET['idscoup']."' AND `valid`=0";
			$results=mysql_query($query);
			$row=mysql_fetch_array($results);
			echo $row['tot'];
			?></a>
			<a href="ajax/shopCouponsExport.php?csv&valid=0&idscoup=<?= $_GET['idscoup']; ?>" class="smallbutton">Esporta in CSV</a>
			</div>
		
		<div style="clear:both;"></div>
		
		<div class="couponsValid">
			<div class="box closed">
				<h2 onclick="kBoxSwapOpening(this.parentNode);">Aggiungi altri coupons</h2>
				<form action="?idscoup=<?= $_GET['idscoup']; ?>" method="post">
					<table style="width:100%;">
						<tr><td><label for="qty">Quanti?</label></td><td><?= b3_create_input("qty","text","","100","95%",10); ?></td></tr>
						<tr><td><label for="format">Formato</label></td><td><?= b3_create_input("format","text","","%d%d%d%s%s%d%d%d","95%",32); ?><br />
								<small>%d=numero casuale, %s=lettera casuale</td></tr>
						<tr><td><label for="allowedchars">Caratteri ammessi</label></td><td><?= b3_create_input("allowedchars","text","","QWERTYUIOPASDFGHJKLZXCVBNM","95%",100); ?></td></tr>
						</table>
					<br />
					<div class="submit"><input type="submit" name="add" value="Aggiungi coupons" class="button"></div>
					</form>
				</div>
			</div>
		
		<br />
	</div>
	<? } ?>

<?
include_once("../inc/foot.inc.php");
?>
