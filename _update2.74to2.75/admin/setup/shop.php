<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Negozio Configurazione generale");
include_once("../inc/head.inc.php");
include_once("../shop/shop.lib.php");
$kaShop=new kaShop();

/* AZIONI */
if(isset($_POST['update'])) {
	if(!isset($_POST['shop1'])) $_POST['shop1']="";
	if(!isset($_POST['shop2'])) $_POST['shop2']="";
	if(!isset($_POST['shop-discount1'])) $_POST['shop-discount1']="";
	if(!isset($_POST['shop-discount2'])) $_POST['shop-discount2']="";
	if(!isset($_POST['shop-order1'])) $_POST['shop-order1']="";
	if(!isset($_POST['shop-order2'])) $_POST['shop-order2']="";
	if(!isset($_POST['shop-template1'])) $_POST['shop-template1']="";
	if(!isset($_POST['shop-template2'])) $_POST['shop-template2']="";
	if(!isset($_POST['shop-commenti1'])) $_POST['shop-commenti1']="";
	if(!isset($_POST['shop-commenti2'])) $_POST['shop-commenti2']="";

	$_POST['shop-virtualpay1']=$_POST['shop-virtualpay1a'].'|'.$_POST['shop-virtualpay1b'];
	$_POST['shop-pagonline1']=$_POST['shop-pagonline1a'].'|'.$_POST['shop-pagonline1b'];

	$_POST['shop2']=",";
	if(!isset($_POST['cat'])) $_POST['shop2']=",,";
	elseif(isset($_POST['cat']['all'])) $_POST['shop2']=",".$_POST['cat']['all'].",";
	else {
		foreach($_POST['cat'] as $cat) {
			$_POST['shop2'].=$cat.',';
			}
		}

	$layout=",";
	if(isset($_POST['layout'])) {
		foreach($_POST['layout'] as $ka=>$v) {
			$layout.=$ka.",";
			}
		}
	$kaImpostazioni->setParam('admin-shop-layout',$layout,"","*");

	$layoutmanufacturers=",";
	if(isset($_POST['layoutmanufacturers'])) {
		foreach($_POST['layoutmanufacturers'] as $ka=>$v) {
			$layout.=$ka.",";
			}
		}
	$kaImpostazioni->setParam('admin-manufacturers-layout',$layout,"","*");

	foreach(file('../shop/currencies.txt') as $line) {
		$line=trim($line);
		if(substr($line,0,1)!="#") {
			$line=explode("\t",$line);
			if($line[1]==$_POST['shop-currency1']) $_POST['shop-currency2']=$line[2];
			}
		}

	$kaImpostazioni->setParam('shop',$_POST['shop1'],$_POST['shop2']);
	$kaImpostazioni->setParam('shop-discount',$_POST['shop-discount1'],$_POST['shop-discount2']);
	$kaImpostazioni->setParam('shop-order',$_POST['shop-order1'],$_POST['shop-order2']);
	$kaImpostazioni->setParam('shop-template',$_POST['shop-template1'],$_POST['shop-template2']);
	$kaImpostazioni->setParam('shop-commenti',$_POST['shop-commenti1'],$_POST['shop-commenti2']);
	$kaImpostazioni->setParam('shop-paypal',$_POST['shop-paypal1'],"");
	$kaImpostazioni->setParam('shop-paypal-return',$_POST['shop-paypal-return1'],$_POST['shop-paypal-return2']);
	$kaImpostazioni->setParam('shop-virtualpay',$_POST['shop-virtualpay1'],$_POST['shop-virtualpay2']);
	$kaImpostazioni->setParam('shop-pagonline',$_POST['shop-pagonline1'],$_POST['shop-pagonline2']);
	$kaImpostazioni->setParam('shop-xpay',$_POST['shop-xpay1'],$_POST['shop-xpay2']);
	$kaImpostazioni->setParam('shop-currency',$_POST['shop-currency1'],$_POST['shop-currency2']);
	
	if(!isset($_POST['customFieldsOrder'])) $_POST['customFieldsOrder']=array();
	$kaShop->sortCustomFields($_POST['customFieldsOrder']);

	echo '<div id="MsgSuccess">Impostazioni salvate con successo</div>';
	
	}

elseif(isset($_POST['addCustomField'])) {
	$vars=array();
	$vars['name']=$_POST['name'];
	$vars['type']=$_POST['type'];
	$vars['values']=$_POST['values'];
	if(!isset($_POST['categories'])) $_POST['categories']=array();
	$vars['categories']=",".implode(",",$_POST['categories']).",";
	$kaShop->addCustomField($vars);
	echo '<div id="MsgSuccess">Campo inserito con successo</div>';
	}

elseif(isset($_POST['updateCustomField'])) {
	$vars=array();
	$vars['idsfield']=$_POST['idsfield'];
	$vars['name']=$_POST['name'];
	$vars['type']=$_POST['type'];
	$vars['values']=$_POST['values'];
	if(!isset($_POST['categories'])) $_POST['categories']=array();
	$vars['categories']=",".implode(",",$_POST['categories']).",";
	$kaShop->updateCustomField($vars);
	echo '<div id="MsgSuccess">Campo modificato con successo</div>';
	}

elseif(isset($_GET['deleteCustomField'])&&is_numeric($_GET['deleteCustomField'])) {
	$kaShop->removeCustomField($_GET['deleteCustomField']);
	echo '<div id="MsgSuccess">Campo rimosso con successo</div>';
	}
/* FINE AZIONI */

?>
<h1><?= $kaTranslate->translate('Negozio'); ?></h1>
<?php  include('shopmenu.php'); ?>
<br />
<?php 

$v=array();
$v['shop1']="";
$v['shop2']="";
$v['shop-discount1']="";
$v['shop-discount2']="";
$v['shop-order1']="";
$v['shop-order2']="";
$v['shop-template1']="";
$v['shop-template2']="";
$v['shop-commenti1']="";
$v['shop-commenti2']="";
$v['shop-paypal1']="";
$v['shop-virtualpay1']="";
$v['shop-virtualpay2']="";
$v['shop-pagonline1']="";
$v['shop-pagonline2']="";
$v['shop-xpay1']="";
$v['shop-xpay2']="";
$v['shop-paypal-return1']="";
$v['shop-paypal-return2']="";
$v['shop-currency1']="";
$layout=$kaImpostazioni->getParam('admin-shop-layout',"*");
$layoutmanufacturers=$kaImpostazioni->getParam('admin-manufacturers-layout',"*");

$query="SELECT * FROM ".TABLE_CONFIG." WHERE param LIKE 'shop%' AND ll='".$_SESSION['ll']."'";
$results=ksql_query($query);
while($row=ksql_fetch_array($results)) {
	$v[$row['param'].'1']=$row['value1'];
	$v[$row['param'].'2']=$row['value2'];
	}

$v['shop-virtualpay1a']=substr($v['shop-virtualpay1'],0,strpos($v['shop-virtualpay1'],"|"));
$v['shop-virtualpay1b']=substr($v['shop-virtualpay1'],strpos($v['shop-virtualpay1'],"|")+1);
$v['shop-pagonline1a']=substr($v['shop-pagonline1'],0,strpos($v['shop-pagonline1'],"|"));
$v['shop-pagonline1b']=substr($v['shop-pagonline1'],strpos($v['shop-pagonline1'],"|")+1);
?>

<form action="?" method="post" onsubmit="return ableAllFields(this)">

	<h3>PayPal</h3>
	<?= b3_create_input("shop-paypal1","text","Account ID ",b3_lmthize($v['shop-paypal1'],"input"),"100px",255); ?><br />
	<br />

	<h3>VirtualPay</h3>
	<?= b3_create_input("shop-virtualpay1a","text","Merchant ID ",b3_lmthize($v['shop-virtualpay1a'],"input"),"100px",20); ?><br />
	<?= b3_create_input("shop-virtualpay1b","text","ABI ",b3_lmthize($v['shop-virtualpay1b'],"input"),"50px",5); ?><br />
	<?= b3_create_input("shop-virtualpay2","text","Secret KEY ",b3_lmthize($v['shop-virtualpay2'],"input"),"250px",64); ?><br />
	<br />

	<h3>PagOnline</h3>
	<?= b3_create_input("shop-pagonline1a","text","Merchant ID ",b3_lmthize($v['shop-pagonline1a'],"input"),"100px",20); ?><br />
	<?= b3_create_input("shop-pagonline1b","text","Password ",b3_lmthize($v['shop-pagonline1b'],"input"),"100px",20); ?><br />
	<?= b3_create_input("shop-pagonline2","text","Secret KEY ",b3_lmthize($v['shop-pagonline2'],"input"),"250px",64); ?><br />
	<br />

	<h3>XPay / Quì Pago / Carta Sì</h3>
	<?= b3_create_input("shop-xpay1","text","Alias ",b3_lmthize($v['shop-xpay1'],"input"),"200px",20); ?><br />
	<?= b3_create_input("shop-xpay2","text","Chiave di MAC ",b3_lmthize($v['shop-xpay2'],"input"),"250px",64); ?><br />
	<br />

	
	<?= b3_create_input("shop-paypal-return1","text","Pagina di pagamento riuscito ",b3_lmthize($v['shop-paypal-return1'],"input"),"200px",255); ?><br />
	<?= b3_create_input("shop-paypal-return2","text","Pagina di pagamento fallito ",b3_lmthize($v['shop-paypal-return2'],"input"),"200px",255); ?><br />
	<br />
	<hr /><br />
	<?php 
	$option=array();
	$value=array();
	foreach(file('../shop/currencies.txt') as $line)
	{
		$line=trim($line);
		if(substr($line,0,1)!="#") {
			$line=explode("\t",$line);
			$option[]=$line[1];
			$value[]=$line[0].' ('.$line[1].')';
		}
	}
	echo b3_create_select("shop-currency1","Valuta",$value,$option,$v['shop-currency1']);
	?>
	<br /><br />

	<?php 
	$option=array("never","qty","always");
	$value=array("mai","per acquisti con molti oggetti","sempre");
	echo b3_create_select("shop-discount1","Usa i prezzi scontati ",$value,$option,$v['shop-discount1']).' ';
	echo '<span id="discountOptions">'.b3_create_input("shop-discount2","text","quanti oggetti devi avere nel carrello? ",b3_lmthize($v['shop-discount2'],"input"),"50px").'</span><br />';
	echo '<br />';
	?>
	<script type="text/javascript">
		var s=document.getElementById('shop-discount1');
		var o=document.getElementById('discountOptions');
		if(s.value!='qty') o.style.display='none';
		function showDiscountOptions() {
			var o=document.getElementById('discountOptions');
			if(this.value=='qty') o.style.display='inline';
			else o.style.display='none';
			}
		s.onchange=showDiscountOptions;
		</script>
	<?php 
	
	
	$option=array("");
	$value=array("-default-");
	//scandaglio la directory per i template
	if($handle=opendir(BASERELDIR.DIR_TEMPLATE)) {
		while(false!==($file=readdir($handle))) {
			if(is_dir(BASERELDIR.DIR_TEMPLATE.$file)&&trim($file,".")!="") {
				$option[]=$file;
				$value[]=str_replace("_"," ",$file);
				}
			}
		closedir($handle);
		}
	echo b3_create_select("shop-template1","Template di default per il negozio ",$value,$option,$v['shop-template1']).'<br /><br />';

	if($v['shop-template1']=="") $v['shop-template1']=$kaImpostazioni->getVar('template_default',1);
	$option=array("");
	$value=array("-default-");
	//scandaglio la directory per i template
	if(file_exists(BASERELDIR.DIR_TEMPLATE.$v['shop-template1'].'/layouts/') && is_dir(BASERELDIR.DIR_TEMPLATE.$v['shop-template1'].'/layouts/'))
	{
		if($handle=opendir(BASERELDIR.DIR_TEMPLATE.$v['shop-template1'].'/layouts/')) {
			while(false!==($file=readdir($handle))) {
				if(trim($file,".")!="") {
					$option[]=$file;
					$value[]=str_replace("_"," ",$file);
					}
				}
			closedir($handle);
		}
	}
	echo b3_create_select("shop-template2","Layout di default per il negozio ",$value,$option,$v['shop-template2']).'<br /><br />';

	$option=array("ordine","titolo","sottotitolo","created","public","expired");
	$value=array("Ordinamento manuale","Nome dell'oggetto","Sottotitolo dell'oggetto","Data di inserimento","Data di pubblicazione","Data di scadenza");
	echo b3_create_select("shop-order1","Ordina il negozio per ",$value,$option,$v['shop-order1']).'<br />';
	
	$option=array("","nascondi");
	$value=array("mantienili visibili","nascondili completamente");
	echo b3_create_select("shop-order2","Gli oggetti scaduti ",$value,$option,$v['shop-order2']).'<br /><br />';

	echo b3_create_input("shop1","text","Numero di oggetti per pagina ",b3_lmthize($v['shop1'],"input"),"50px",3).'<br /><br />';
	
	echo '<h3>Categorie da visualizzare</h3>';
	echo b3_create_input("cat[all]","checkbox","Tutte (anche quelle future)","*","","",(',*,'==$v['shop2']?'checked':''),true).'<br />';
	$query_c="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_SHOP_ITEMS."' AND ll='".$_SESSION['ll']."' ORDER BY ordine";
	$results_c=ksql_query($query_c);
	while($row_c=ksql_fetch_array($results_c)) {
		echo '&nbsp;&nbsp;&nbsp;'.b3_create_input("cat[]","checkbox",$row_c['categoria'],$row_c['idcat'],"","",(strpos($v['shop2'],','.$row_c['idcat'].',')!==false?'checked':''),true).'<br />';
		}
	echo '<br /><br />';

	echo '<h3>Commenti</h3>';
	echo b3_create_input("shop-commenti1","checkbox","Abilita i commenti","s","","",($v['shop-commenti1']=="s"?'checked':''),true).'<br />';
	echo b3_create_input("shop-commenti2","checkbox","Modera i commenti","s","","",($v['shop-commenti2']=="s"?'checked':''),true).'<br />';
	?>

<br /><br />

<script type="text/javascript">
	function ableAllFields(f) {
		inputs=f.getElementsByTagName('INPUT');
		for(var i=0;inputs[i];i++) {
			inputs[i].disabled=false;
			}
		return true;
		}
	</script>

<table style="width:100%;"><tr><td>
	<h3><?= $kaTranslate->translate('Setup:Fields to display in administration panel'); ?></h3>
		<?php 
		$elm=array("productcode"=>"Product code","title"=>"Title","subtitle"=>"Subtitle","featuredimage"=>"Shop:Featured Image","preview"=>"Preview","text"=>"Text","price"=>"Price","discounted"=>"Discounted","qta"=>"Quantity","privatearea"=>"Private area","categories"=>"Categories","created"=>"Creation date","public"=>"Visible from","expiration"=>"Expiration date","weight"=>"Weight","rating"=>"Rating","votes"=>"Votes","variations"=>"Variazioni","photogallery"=>"Photogallery","documentgallery"=>"Document gallery","layout"=>"Layout","translate"=>"Translations","metadata"=>"Metadata","seo"=>"SEO (Search Engine Optimization)","manufacturers"=>"Manufacturers","ordersummary"=>"Orders Summary (opened, closed, canceled)");
		$elmobl=array("title"=>true,"text"=>true);
		foreach($elm as $ka=>$v) {
			echo b3_create_input("layout[".$ka."]","checkbox",$kaTranslate->translate('Setup:'.$v),"s","","",(strpos($layout['value1'],",".$ka.",")!==false||isset($elmobl[$ka])?'checked':'').' '.(isset($elmobl[$ka])?'disabled':''),true).'<br />';
			}
		?>
	<br>
	<h3><?= $kaTranslate->translate('Setup:Fields to display for manufacturers'); ?></h3>
		<?php 
		$elm=array("name"=>"Name","subtitle"=>"Subtitle","featuredimage"=>"Shop:Featured Image","preview"=>"Preview","description"=>"Description","created"=>"Creation date","photogallery"=>"Photogallery","documentgallery"=>"Document gallery","translate"=>"Translations","metadata"=>"Metadata","seo"=>"SEO (Search Engine Optimization)");
		$elmobl=array("name"=>true,"description"=>true);
		foreach($elm as $ka=>$v) {
			echo b3_create_input("layoutmanufacturers[".$ka."]","checkbox",$kaTranslate->translate('Setup:'.$v),"s","","",(strpos($layoutmanufacturers['value1'],",".$ka.",")!==false||isset($elmobl[$ka])?'checked':'').' '.(isset($elmobl[$ka])?'disabled':''),true).'<br />';
			}
		?>
	</td>
<td>
	<h3 id="customfields"><?= $kaTranslate->translate('Setup:Custom fields'); ?></h3>

	<script type="text/javascript" src="<?php  echo ADMINDIR; ?>/js/drag_and_drop.js"></script>
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
			});
		</script>

	<table class="tabella">
		<thead>
			<tr><th><?= $kaTranslate->translate('Setup:Name'); ?></th><th><?= $kaTranslate->translate('Setup:Type'); ?></th><th><?= $kaTranslate->translate('Setup:Values'); ?></th><th><?= $kaTranslate->translate('Setup:Categories'); ?></th><th><?= $kaTranslate->translate('Setup:Order'); ?></th></tr>
			</thead>
		<tbody  class="DragZone">
		<?php 
		foreach($kaShop->getCustomFields() as $field) { ?>
			<tr>
				<td><?= $field['name']; ?><br />
					<small class="actions"><a href="javascript:kOpenIPopUp('ajax/shopEditCustomField.php','idsfield=<?= $field['idsfield']; ?>','600px','400px');"><?= $kaTranslate->translate('UI:Edit'); ?></a> | <a href="?deleteCustomField=<?= $field['idsfield']; ?>" class="delete" onclick="return confirm('<?= addslashes($kaTranslate->translate('Setup:Do you really REALLY want to permanently delete this field?')); ?>');"><?= $kaTranslate->translate("UI:Delete"); ?></a></small>
					</td>
				<td><?= $field['type']; ?></td>
				<td><?= nl2br($field['values']); ?></td>
				<td><?= nl2br($field['categoriesList']); ?></td>
				<td class="sposta"><input type="hidden" name="customFieldsOrder[]" value="<?= $field['idsfield']; ?>" /><img src="<?= ADMINRELDIR; ?>img/drag_v.gif" width="18" height="18" alt="" /> <?= $kaTranslate->translate('UI:Move'); ?></td>
			</tr>
			<?php  } ?>
			</tbody>
		</table>
		<br />
		<input type="button" value="<?= $kaTranslate->translate('Setup:Add a field'); ?>" class="button" onclick="kOpenIPopUp('ajax/shopNewCustomField.php','','600px','400px')" />
	</td></tr></table>

	<br /><br />
	<div class="submit"><input type="submit" name="update" value="Salva" class="button"></div>
</form></div><br /><br />

<?php 
include_once("../inc/foot.inc.php");
