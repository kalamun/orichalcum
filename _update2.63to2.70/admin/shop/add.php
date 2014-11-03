<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Shop:Add an item");
include_once("../inc/head.inc.php");
include_once("./shop.lib.php");
$kaShop=new kaShop();
$pageLayout=$kaImpostazioni->getVar('admin-shop-layout',1,"*");

/*
if the current language is different from the page language, means that the user have clicked on the flag and are requesting the translation of this page.
- if the page has a translation in the requested language, edit the translation
- if the page hasn't a translated version, create a new translate page
*/
if(isset($_GET['translate'])) {
	$item=$kaShop->getItem($_GET['translate']);
	if($_SESSION['ll']==$item['ll']) $item['traduzioni'][$_SESSION['ll']]=$item['idsitem'];
	if(isset($item['traduzioni'][$_SESSION['ll']])&&$item['traduzioni'][$_SESSION['ll']]!="") {
		$url="edit.php?idsitem=".$item['traduzioni'][$_SESSION['ll']];
		?>
		<div class="MsgNeutral">
			<h2><?= $kaTranslate->translate('Shop:Searching for translation'); ?></h2>
			<a href="<?= $url; ?>"><?= $kaTranslate->translate('Shop:if nothing happens, click here'); ?></a>
			<meta http-equiv="refresh" content="0;URL='<?= $url; ?>'">
			</div>
		<?php 
		die();
		}
	}


/**************************************************************************/
/* ACTIONS: create database entries, copy contents or set translations... */
/**************************************************************************/
if(isset($_POST['insert'])) {
	$log="";
	$categorie=",";
	if(isset($_POST['idcat'])) {
		foreach($_POST['idcat'] as $idcat) {
			$categorie.=$idcat.',';
			}
		}
	if(trim($categorie,",")=="") {
		require_once('../inc/categorie.lib.php');
		$kaCategorie=new kaCategorie();
		foreach($kaCategorie->getList(TABLE_SHOP_ITEMS) as $cat) {
			$categorie=','.$cat['idcat'].',';
			break;
			}
		}

	if(isset($_POST['visible_day'])&&isset($_POST['visible_hour'])) $visible_date=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['visible_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['visible_hour']);
	else $visible_date=date("Y-m-d H:i");

	if(isset($_POST['expiration_day'])&&isset($_POST['expiration_hour'])) $expiration_date=preg_replace('/(\d{1,2})[^\d](\d{1,2})[^\d](\d{4})/','$3-$2-$1',$_POST['expiration_day']).' '.preg_replace('/(\d{1,2})[^\d](\d{1,2})/','$1:$2:00',$_POST['expiration_hour']);
	else $expiration_date=date("Y-m-d H:i");
	
	$id=$kaShop->addItem($_POST['dir'],$categorie,b3_htmlize($_POST['titolo'],false,""),'','<p></p>','<p></p>',0,0,date("Y-m-d H:i"),$visible_date,$expiration_date,false,false,'');
	if($id==false) $log=$kaTranslate->translate('Shop:Problems while adding the item to the database');
	else {
		//if the page is a translated version of another page
		if(isset($_POST['translation_id'])&&$_POST['translation_id']!="") {
			$item=$kaShop->getItem($_POST['translation_id']);
			// first of all, clear translations from previous+current pages
			foreach($item['traduzioni'] as $k=>$v) {
				if($v!="") $kaShop->removePageFromTranslations($v);
				}
			// translation has this format: |LL=idsitem|LL=idsitem|...
			$item['traduzioni'][$_SESSION['ll']]=$id;
			$translations="|";
			foreach($item['traduzioni'] as $k=>$v) {
				$translations.=$k."=".$v."|";
				}
			// then set the new translations in the current pages
			foreach($item['traduzioni'] as $k=>$v) {
				if($v!="") {
					$kaShop->setTranslations($v,$translations);
					}
				}
			}
		}
	
	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Shop: Errore nella creazione dell\'oggetto <em>'.b3_htmlize($_POST['dir'],true,"").'</em>');
		}
	else {
		$kaLog->add("INS",'Shop: creato l\'oggetto <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$kaImpostazioni->getVar('dir_news',1).'/tmp/'.$_POST['dir'].'">'.$_POST['titolo'].'</a> (<em>ID: '.$id.'</em>)');
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Shop:Item successfully added').'<br />'.$kaTranslate->translate('UI:Please Wait...').'</div>';
		echo '<meta http-equiv="refresh" content="0; url=edit.php?idsitem='.$id.'&firstedit">';
		include(ADMINRELDIR.'inc/foot.inc.php');
		die();
		}
	}
/* FINE AZIONI */


/* CONTROLLO FORM */
?>
<script type="text/javascript"><!--
	function checkForm() {
		if(document.getElementById("titolo").value.length==0) { alert("Devi specificare un titolo"); return false; }
		else { return true; }
	}
--></script>
<?php 
/* FINE CONTROLLO FORM */


if(isset($_GET['copyfrom'])) $copyfrom=$kaShop->getItem($_GET['copyfrom']);

?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<script type="text/javascript" src="js/edit.js"></script>

<form action="?" method="post" onsubmit="return checkForm();">

	<div class="topset">

		<script type="text/javascript">
		var timer=null;
		var markURLfield=function(success) {
			if(success=="true") {
				var d=new Date();
				document.getElementById('dirYetExists').style.display="inline";
				document.getElementById('dir').value=d.getUTCFullYear()+'-'+(d.getUTCMonth()+1)+'-'+d.getUTCDate()+'-'+document.getElementById('dir').value;
				checkURL(document.getElementById('dir'));
				}
			else document.getElementById('dirYetExists').style.display="none";
			}
		function checkURL(urlField) {
			var target=document.getElementById('dir')
			}
		function title2url() {
			var titleField=document.getElementById('titolo');
			var urlField=document.getElementById('dir');
			if(!urlField.getAttribute("manualmode")&&titleField.value!="") {
				urlField.value=titleField.value.toLowerCase().replace(/[^\w\/\.\-\u00C0-\uD7FF\u2C00-\uD7FF]+/g,"-")+'.html';
				checkURL(urlField);
				}
			}
		function titleBlur() {
			var titleField=document.getElementById('titolo');
			var urlField=document.getElementById('dir');
			checkURL(urlField);
			}
		function selectMenuRef(f) {
			if(f.checked) {
				k_openIframeWindow(ADMINDIR+"inc/selectMenuRef.inc.php","450px","500px");
				}
			}
		function dirKeyUp(e) {
			urlField=this;
			if(e.keyCode!=37&&e.keyCode!=38&&e.keyCode!=39&&e.keyCode!=40&&e.keyCode!=9&&urlField.value!=""&&urlField.value!=".html") urlField.setAttribute("manualmode","true");
			checkURL(urlField);
			}
		function selectElement(id,where) {
			document.getElementById('addtomenu').value=id+','+where;
			}
		</script>

	<?php  if(strpos($pageLayout,",title,")!==false) { ?>
		<div class="title"><?= b3_create_input("titolo","text",$kaTranslate->translate('Shop:Item\'s name').":<br />","","95%",250,'autocomplete="off" onkeyup="title2url()" onblur="titleBlur()"'); ?></div>
		<?php  } ?>
	<div class="URLBox"><?= b3_create_input("dir","text",$kaTranslate->translate('Shop:Item URL').": ".BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_shop',1).'/[categoria]/',(isset($copyfrom['dir'])?$copyfrom['dir'].'-'.date("Ymd"):''),"400px",64,''); ?> <span id="dirYetExists" style="display:none;"><?= $kaTranslate->translate('News:This URL already exists!'); ?>!</span></div><br />
	<script type="text/javascript">document.getElementById('dir').onkeyup=dirKeyUp;</script>

	<br />
	<table class="options">

	<?php  if(strpos($pageLayout,",categories,")!==false) { ?>
		<tr>
		<th><?= $kaTranslate->translate('Shop:Categories'); ?></th>
		<td>
			<div id="categorie">Loading...</div>
			<script type="text/javascript" src="./ajax/categorie.js"></script>
			<script type="text/javascript">k_reloadCat(0);</script>
			</td>
		</tr>
		<?php  } ?>
		
		
	<?php 
	//translations
	if(strpos($pageLayout,",translate,")!==false) {
		if(isset($_GET['translate'])) $page_l=$kaShop->getItem($_GET['translate']);
		else $page_l=array("titolo"=>"","idsitem"=>"");
		?>
		<tr>
			<th><?= $kaTranslate->translate('Shop:Translations'); ?></th>
			<td>
			<table><tr>
				<td><?= $kaTranslate->translate('Shop:This page is the translated version of'); ?></td>
				<td>
					<div class="suggestionsContainer">
						<?= b3_create_input("translation","text","",$page_l['titolo'],"200px",250,'autocomplete="off"'); ?>
						<?= b3_create_input("translation_id","hidden","",$page_l['idsitem']); ?>
						<script type="text/javascript">translationHandler=new kAutocomplete();translationHandler.init('-<?= $_SESSION['ll']; ?>');</script>
						</div>
					</td>
				<td>
					<small><?= $kaTranslate->translate('UI:optional'); ?></small>
					</td>
				</tr></table>
			</td>
		</tr>
		<?php  } ?>

		</table>
		<br />
	
		<?php  if(strpos($pageLayout,",public,")!==false) { ?>
			<div class="box" style="float:left;width:48%;text-align:center;"><?= b3_create_input("visible_day","text",$kaTranslate->translate('Shop:Visible from')." ",date('d-m-Y'),"80px",250); ?> <?= b3_create_input("visible_hour","text",$kaTranslate->translate('Shop:and from time')." ",date('H:i'),"40px",250); ?></div>
			<?php  } ?>
		<?php  if(strpos($pageLayout,",expiration,")!==false) { ?>
			<div class="box" style="float:right;width:48%;text-align:center;"><?= b3_create_input("expiration_day","text",$kaTranslate->translate('Shop:Expiration date')." ",date('d-m-Y',time()+604800),"80px",250); ?> <?= b3_create_input("expiration_hour","text",$kaTranslate->translate('Shop:and time')." ",date('H:i'),"40px",250); ?></div>
			<?php  } ?>

		<div style="clear:both;"></div>
		<br /><br />
	<div class="submit"><input type="submit" name="insert" value="<?= $kaTranslate->translate('Shop:Create Item'); ?> &gt;" class="button" /></div>
	</div>
</form>
<?php 
include_once("../inc/foot.inc.php");
