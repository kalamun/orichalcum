<?php 
define("PAGE_NAME","Banner:Banner categories");
include_once("../inc/head.inc.php");
include_once("../inc/categorie.lib.php");

$kaCategorie=new kaCategorie();

/* AZIONI */
if(isset($_POST['categorie']) && count($_POST['categorie'])>0)
{
	$log="";
	$ordine=array();
	foreach($_POST['categorie'] as $ka=>$v)
	{
		$ordine[]=$v;
	}

	if(!$kaCategorie->sort($ordine,TABLE_BANNER)) $log="Errore durante il salvataggio dell'ordinamento";
	if($log!="") echo '<div id="MsgAlert">'.$log.'</div>';
	
} elseif(isset($_POST['insert'])) {
	$log=$kaCategorie->add($_POST['titolo'],$_POST['dir'],TABLE_BANNER);
	
	if($log==false)
	{
		echo '<div id="MsgAlert">Problemi durante la creazione della categoria</div>';
		$kaLog->add("ERR",'Errore durante la creazione della categoria <em>'.b3_htmlize($_POST['categoria'],true,"").'</em> nei Banner');
	} else {
		echo '<div id="MsgSuccess">Categoria inserita con successo</div>';
		$kaLog->add("INS",'Creata la categoria <em>'.b3_htmlize($_POST['titolo'],true,"").'</em> nei Banner');
	}

} elseif(isset($_POST['update'])) {
	$log=$kaCategorie->update($_POST['idcat'],$_POST['categoria'],$_POST['dir'],TABLE_BANNER);

	if($log==false)
	{
		echo '<div id="MsgAlert">Problemi durante la modifica della categoria</div>';
		$kaLog->add("ERR",'Errore durante la modifica della categoria <em>'.b3_htmlize($_POST['categoria'],true,"").'</em> nei Banner');

	} else {
		$kaMetadata->set(TABLE_CATEGORIE, $_POST['idcat'], 'width', $_POST['width']);
		$kaMetadata->set(TABLE_CATEGORIE, $_POST['idcat'], 'height', $_POST['height']);
		$kaMetadata->set(TABLE_CATEGORIE, $_POST['idcat'], 'orderby', $_POST['orderby']);
		echo '<div id="MsgSuccess">Categoria modificata con successo</div>';
		$kaLog->add("INS",'Modificata la categoria <em>'.b3_htmlize($_POST['categoria'],true,"").'</em> nei Banner');
	}

} elseif(isset($_GET['delete'])) {
	$cat=$kaCategorie->get($_GET['delete']);
	$log=$kaCategorie->del($_GET['delete'],TABLE_BANNER);

	if($log==false)
	{
		echo '<div id="MsgAlert">Problemi durante l\'eliminazione della categoria</div>';
		$kaLog->add("ERR",'Errore durante l\'eliminazione della categoria <em>'.b3_htmlize($cat['categoria'],true,"").'</em> dai Banner');
	} else {
		echo '<div id="MsgSuccess">Categoria eliminata con successo</div>';
		$kaLog->add("INS",'Eliminata la categoria <em>'.b3_htmlize($cat['categoria'],true,"").'</em> dai Banner');
	}
}
/* FINE AZIONI */



?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<?php 

/* edit a category */
if(isset($_GET['idcat']))
{
	// get the category config
	$cat = $kaCategorie->get($_GET['idcat'],TABLE_BANNER);
	$w = $kaMetadata->get(TABLE_CATEGORIE, $cat['idcat'], 'width');
	$h = $kaMetadata->get(TABLE_CATEGORIE, $cat['idcat'], 'height');
	$o = $kaMetadata->get(TABLE_CATEGORIE, $cat['idcat'], 'orderby');
	$cat['width'] = empty($w['value']) ? 0 : intval($w['value']);
	$cat['height'] = empty($h['value']) ? 0 : intval($h['value']);
	$cat['orderby'] = empty($o['value']) ? 'ordine' : $o['value'];
	?>
	<script type="text/javascript" src="js/categorie.js"></script>
	<form action="?idcat=<?= $_GET['idcat']; ?>" method="post">
		<input type="hidden" name="idcat" value="<?= $cat['idcat']; ?>" />

		<div class="title"><?= b3_create_input("categoria", "text", $kaTranslate->translate("Banner:Category name").'<br />', b3_lmthize($cat['categoria'],"input"), "70%", 250); ?></div>
		<div class="URLBox"><?= b3_create_input("dir","text", $kaTranslate->translate("Banner:Category URL").": ".BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_news',1).'/[categoria]/',b3_lmthize($cat['dir'],"input"),"400px",64,'onkeyup="checkURL(this)"'); ?> <span id="dirYetExists" style="display:none;"><?= $kaTranslate->translate("Banner:This URL already exists!"); ?></span></div>
		<script type="text/javascript">
			var target=document.getElementById('dir');
			target.setAttribute("oldvalue",target.value);
		</script>
		<br />

		<?= b3_create_input("width","text","Larghezza ",$cat['width'],"input","50px",4); ?><br />
		
		<?= b3_create_input("height","text","Altezza ",$cat['height'],"input","50px",4); ?><br />
		
		<?php 
		$option=array(
			"views",
			"clicks",
			"ordine",
		);
		$value=array(
			$kaTranslate->translate("Banner:Less viewed before"),
			$kaTranslate->translate("Banner:Less clicked before"),
			$kaTranslate->translate("Banner:Manual order"),
		);
		echo b3_create_select("orderby", $kaTranslate->translate("Banner:Banner order").' ', $value, $option, b3_lmthize($cat['orderby'], "input"));
		?><br />
		
		<br />

		<div class="box closed">
			<h2 onclick="kBoxSwapOpening(this.parentNode);">Meta-dati</h2>
			<div id="divMetadata"></div>
			<script type="text/javascript">kaMetadataReload('<?= TABLE_CATEGORIE; ?>',<?= $cat['idcat']; ?>);</script>
			<a href="javascript:kOpenIPopUp(ADMINDIR+'inc/ajax/metadataNew.php','t=<?= TABLE_CATEGORIE; ?>&id=<?= $cat['idcat']; ?>','600px','400px')" class="smallbutton">Nuovo meta-dato</a>
		</div>

		<br />

		<div class="submit"><input type="button" value="Annulla" class="button" onclick="window.location='?';" /> <input type="submit" name="update" value="Salva le modifiche" class="button" /></div>
	</form>
	<?php 
	}

else { ?>
	<div class="subset">
		</div>

	<div class="topset">

	<form action="" method="post" id="orderby">

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
			b3_openMessage('Salvataggio in corso',false);
			document.getElementById('orderby').submit();
			});
		</script>
	
	<div>
		<table class="tabella">
		<thead><tr><th>Categoria</th><th>Indirizzo</th><th>Ordine</th></thead>
		<tbody  class="DragZone">
		<?php 
			$categorie=$kaCategorie->getList(TABLE_BANNER);
			foreach($categorie as $cat) {
				?><tr>
					<td>
						<h2><a href="?idcat=<?php  echo $cat['idcat']; ?>"><?php  echo $cat['categoria']; ?></a></h2>
						<small class="actions"><a href="?idcat=<?php  echo $cat['idcat']; ?>">Modifica</a> | <a href="?delete=<?php  echo $cat['idcat']; ?>" onclick="return confirm('Sei sicuro di voler rimuovere questa categoria?');">Elimina</a></small>
					</td>
					<td class="percorso"><?= $cat['dir']; ?></td>
					<td class="sposta"><input type="hidden" name="categorie[]" value="<?= $cat['idcat']; ?>" /><img src="<?= ADMINRELDIR; ?>img/drag_v.gif" width="18" height="18" alt="Sposta" /> Sposta</td>
					</tr>
					<?php 
				}
			?></tbody></table>
		</div>
		</form>
	<br />
	<br />
	<script type="text/javascript" src="js/categorie.js"></script>
	<table><tr>
	<td><input type="button" class="button" value="Nuova categoria" onclick="showReq('nuovaCat');" /></td>
	<td><div id="nuovaCat" style="display:none;">
		<fieldset class="box"><legend>Aggiungi una Nuova Categoria</legend>
		<form action="" method="post">
		<div class="title"><?= b3_create_input("titolo","text","Nome<br />","","95%",250,'autocomplete="off" onkeyup="title2url()" onblur="titleBlur()"'); ?></div>
		<div class="URLBox"><?= b3_create_input("dir","text","Indirizzo della categoria: ","","400px",64,'onkeyup="checkURL(this)"'); ?> <span id="dirYetExists" style="display:none;">Questo indirizzo esiste gi&agrave;!</span></div><br />
		<div class="submit"><input type="submit" name="insert" value="Salva" class="button" /></div>
		</form>
		</fieldset>
		</div></td>
		</tr>
		</table>
	</form>

	<?php  }

include_once("../inc/foot.inc.php");
