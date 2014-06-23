<?php
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Shop:Create a new manufacturer");

include_once("../inc/head.inc.php");
include_once("../inc/metadata.lib.php");
include_once("./shop.lib.php");

$kaShop=new kaShop;
$kaMetadata=new kaMetadata();
$pageLayout=$kaImpostazioni->getVar('admin-shop-layout',1,"*");



/*
if the current language is different from the page language, means that the user have clicked on the flag and are requesting the translation of this page.
- if the page has a translation in the requested language, edit the translation
- if the page hasn't a translated version, create a new translate page
*/
if(isset($_GET['translate'])) {
	$page=$kaPages->get($_GET['translate']);
	if($_SESSION['ll']==$page['ll']) $page['traduzioni'][$_SESSION['ll']]=$page['idpag'];
	if(isset($page['traduzioni'][$_SESSION['ll']])&&$page['traduzioni'][$_SESSION['ll']]!="") {
		$url="edit.php?idpag=".$page['traduzioni'][$_SESSION['ll']];
		?>
		<div class="MsgNeutral">
			<h2><?= $kaTranslate->translate('Pages:Searching for translation'); ?></h2>
			<a href="<?= $url; ?>"><?= $kaTranslate->translate('Pages:if nothing happens, click here'); ?></a>
			<meta http-equiv="refresh" content="0;URL='<?= $url; ?>'">
			</div>
		<?
		die();
		}
	}


/**************************************************************************/
/* ACTIONS: create database entries, copy contents or set translations... */
/**************************************************************************/
if(isset($_POST['save'])) {
	$log="";

	$categorie=",";
	if(isset($_POST['idcat'])) {
		foreach($_POST['idcat'] as $idcat) {
			$categorie.=$idcat.',';
			}
		}
	if(trim($categorie,",")=="") $categorie=",,";

	if(!isset($_POST['dir'])&&isset($_POST['titolo'])) $_POST['dir']=preg_replace("/[^\w\/\.\-\x{C0}-\x{D7FF}\x{2C00}-\x{D7FF}]+/","-",strtolower($_POST['titolo']));
	if(!isset($_POST['dir'])||$_POST['dir']==""||$_POST['dir']=="-.html") $_POST['dir']=rand(10,999999);
	if(strlen($_POST['dir'])>64) $_POST['dir']=substr(str_replace(".html","",$_POST['dir']),0,64).".html";

	//insert page
	$vars=array();
	$vars['name']=$_POST['titolo'];
	$vars['dir']=$_POST['dir'];
	$id=$kaShop->createManufacturer($vars);
	if($id==false) $log=$kaTranslate->translate("Shop:Errors occurred while creating manufacturer's page");

	//if the page is a translated version of another page
	if(isset($_POST['translation_id'])&&$_POST['translation_id']!="") {
		$page=$kaShop->getManufacturer($_POST['translation_id']);
		// first of all, clear translations from previous+current pages
		foreach($page['traduzioni'] as $k=>$v) {
			if($v!="") $kaPages->removePageFromTranslations($v);
			}
		// translation has this format: |LL=idpag|LL=idpag|...
		$page['traduzioni'][$_SESSION['ll']]=$id;
		$translations="|";
		foreach($page['traduzioni'] as $k=>$v) {
			$translations.=$k."=".$v."|";
			}
		// then set the new translations in the current pages
		foreach($page['traduzioni'] as $k=>$v) {
			if($v!="") {
				$kaPages->setTranslations($v,$translations);
				}
			}
		}

	//copy contents from another page
	if(isset($_POST['copyfrom'])) {
		$query="SELECT * FROM ".TABLE_PAGINE." WHERE `idpag`=".mysql_real_escape_string($_POST['copyfrom'])." LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			$query="UPDATE ".TABLE_PAGINE." SET `sottotitolo`='".mysql_real_escape_string($row['sottotitolo'])."',anteprima='".mysql_real_escape_string($row['anteprima'])."',testo='".mysql_real_escape_string($row['testo'])."',template='".mysql_real_escape_string($row['template'])."',layout='".mysql_real_escape_string($row['layout'])."',traduzioni='".mysql_real_escape_string($row['traduzioni'])."' WHERE idpag=".mysql_real_escape_string($id)." LIMIT 1";
			if(!mysql_query($query)) $log=$kaTranslate->translate('Pages:Errors occurred while copying contents');
			
			foreach($kaMetadata->getList(TABLE_PAGINE,$row['idpag']) as $ka=>$v) {
				$kaMetadata->set(TABLE_PAGINE,$id,$ka,$v);
				}
			}
		}

	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Shop: Errors occurred while creating a new manufacturer at <em>'.b3_htmlize($_POST['dir'],true,"").'</em>');
		}
	else {
		$kaLog->add("INS",'Creata la pagina: <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$_POST['dir'].'">'.$_POST['dir'].'</a>');
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Pages:Page saved').'</div>';
		echo '<meta http-equiv="refresh" content="0; url=manufacturers-edit.php?idsman='.$id.'">';
		}
	}
/***/

	
if(isset($_GET['copyfrom'])) $copyfrom=$kaPages->get($_GET['copyfrom']);


?><h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	<script type="text/javascript" src="js/edit.js" charset="UTF-8"></script>

	<form action="" method="post">
	<div class="title"><?= b3_create_input("titolo","text",$kaTranslate->translate('Shop:Manufacturer\'s name').": <br />",(isset($copyfrom['name'])?$copyfrom['name']:''),"70%",250,'autocomplete="off" onkeyup="title2url()" onblur="titleBlur()"'); ?><br /></div>
	<div class="URLBox"><?= b3_create_input("dir","text",$kaTranslate->translate('Shop:Page URL').": ".BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_shop',1).'/'.$kaImpostazioni->getVar('dir_shop_manufacturers',1)."/",(isset($copyfrom['dir'])?$copyfrom['dir'].'-'.date("Ymd"):''),"400px",64,'onkeyup="checkURL(this)"'); ?> <span id="dirYetExists" style="display:none;"><?= $kaTranslate->translate('Pages:This URL already exists'); ?>!</span></div><br />
	
	<br />
	<table class="options">
	<?
	//copy contents from another page?
	if(isset($copyfrom['idpag'])) { ?>
		<tr>
		<th></th>
		<td><?= b3_create_input("copyfrom","checkbox",$kaTranslate->translate('Shop:Copy contents from the page')." <em>".$copyfrom['name']."</em>",$copyfrom['idpag'],"","","checked"); ?></td>
		</tr>
		<? } ?>

	<?
	//translations
	if(strpos($pageLayout,",traduzioni,")!==false) {
		if(isset($_GET['translate'])) $page_l=$kaShop->getManufacturer($_GET['translate']);
		else $page_l=array("name"=>"","idsman"=>"");
		?>
		<tr>
			<th><?= $kaTranslate->translate('Pages:Translations'); ?></th>
			<td>
			<table><tr>
				<td><?= $kaTranslate->translate('Pages:This page is the translated version of'); ?></td>
				<td>
					<div class="suggestionsContainer">
						<?= b3_create_input("translation","text","",$page_l['name'],"200px",250,'autocomplete="off"'); ?>
						<?= b3_create_input("translation_id","hidden","",$page_l['idsman']); ?>
						<script type="text/javascript">translationHandler=new kAutocomplete();translationHandler.init('-<?= $_SESSION['ll']; ?>');</script>
						</div>
					</td>
				<td>
					<small><?= $kaTranslate->translate('UI:optional'); ?></small>
					</td>
				</tr></table>
			</td>
		</tr>
		<? } ?>

	</table>
	<br /><br />

	<div style="clear:both;"></div>
	<div class="submit" id="submit">
		<input type="submit" name="save" class="button" value="<?= $kaTranslate->translate('Shop:Create manufacturer'); ?> &gt;" />
		</div>
	</form>


<?php	
include_once("../inc/foot.inc.php");
?>
