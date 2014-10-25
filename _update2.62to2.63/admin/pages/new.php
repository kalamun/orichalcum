<?php
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Pages:Create a new page");

include_once("../inc/head.inc.php");
include_once("../inc/metadata.lib.php");
include_once("./pages.lib.php");

$kaPages=new kaPages;
$kaMetadata=new kaMetadata();
$pageLayout=$kaImpostazioni->getVar('admin-page-layout',1,"*");



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
	$query="INSERT INTO ".TABLE_PAGINE." (created,modified,titolo,sottotitolo,anteprima,testo,categorie,ll,dir,template,layout,traduzioni,riservata,allowcomments,allowconversions,featuredimage) VALUES(NOW(),NOW(),'".b3_htmlize($_POST['titolo'],true,"")."','','<p></p>','<p></p>','".mysql_real_escape_string($categorie)."','".$_SESSION['ll']."','".mysql_real_escape_string($_POST['dir'])."','','','','s','n',false,0)";
	if(!mysql_query($query)) $log=$kaTranslate->translate('Pages:Errors occurred while saving');
	else $id=mysql_insert_id();

	//if the page is a translated version of another page
	if(isset($_POST['translation_id'])&&$_POST['translation_id']!="") {
		$page=$kaPages->get($_POST['translation_id']);
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

	//add to menu
	if(isset($_POST['addtomenu']))
	{
		require_once("../menu/menu.lib.php");
		$kaMenu=new kaMenu();

		$query="SELECT `idpag`,`titolo`,`dir` FROM ".TABLE_PAGINE." WHERE idpag='".$id."' AND ll='".$_SESSION['ll']."' LIMIT 1";
		$results=mysql_query($query);
		if($page=mysql_fetch_array($results))
		{
			$vars['title']=$page['titolo'];
			$vars['dir']=$page['dir'];
			$vars['idpag']=$page['idpag'];
			$addtomenu=explode(",",$_POST['addtomenu']);
			$vars['idmenu']=$addtomenu[0];
			$vars['where']=$addtomenu[1];
			$log=$kaMenu->addElement($vars);

			if($log==false)
			{
				echo '<div id="MsgAlert">'.$kaTranslate->translate('Pages:An error occurred while inserting page into menu').'</div>';
				$kaLog->add("ERR",'Pages: Error while inserting in the menu the page <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$vars['dir'].'">'.$vars['title'].'</a> <em>(ID: '.$vars['idpag'].')</em>');
			} else {
				$kaLog->add("INS",'Pages: Page was inserted in the menu: <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$vars['dir'].'">'.$vars['title'].'</a> <em>(ID: '.$vars['idpag'].')</em>');
				$log="";
			}
		}
	}

	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Pages: Error while creating page <em>'.b3_htmlize($_POST['dir'],true,"").'</em>');
		}
	else {
		$kaLog->add("INS",'Pages: Successfully added the page <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$_POST['dir'].'">'.$_POST['titolo'].'</a>');
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Pages:Page saved').'</div>';
		echo '<meta http-equiv="refresh" content="0; url=edit.php?idpag='.$id.'">';
		}
	}
/***/

	
if(isset($_GET['copyfrom'])) $copyfrom=$kaPages->get($_GET['copyfrom']);


?><h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	<script type="text/javascript" src="js/edit.js"></script>

	<form action="" method="post">
	<div class="title"><?= b3_create_input("titolo","text",$kaTranslate->translate('Pages:Title').": <br />",(isset($copyfrom['titolo'])?$copyfrom['titolo']:''),"70%",250,'autocomplete="off" onkeyup="title2url()" onblur="titleBlur()"'); ?><br /></div>
	<div class="URLBox"><?= b3_create_input("dir","text",$kaTranslate->translate('Pages:Page URL').": ".BASEDIR.strtolower($_SESSION['ll'])."/",(isset($copyfrom['dir'])?$copyfrom['dir'].'-'.date("Ymd"):''),"400px",64,'onkeyup="checkURL(this)"'); ?> <span id="dirYetExists" style="display:none;"><?= $kaTranslate->translate('Pages:This URL already exists'); ?>!</span></div><br />
	
	<br />
	<table class="options">
	<?
	//add to menu checkbox: if menu is empty, don't ask how to locate the link inside menu
	$query="SELECT * FROM ".TABLE_MENU." WHERE ref='0' AND ll='".$_SESSION['ll']."' ORDER BY ordine DESC LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
	$additionalAttributes=($row['idmenu']==""?'':'onchange="selectMenuRef(this)"');
	?>
	<tr>
		<th><?= $kaTranslate->translate('Menu:Navigation Menu'); ?></th>
		<td><?= b3_create_input("addtomenu","checkbox",$kaTranslate->translate('Pages:Add this page to the site menu'),$row['idmenu'].',after',"","",$additionalAttributes); ?></td>
		</tr>

	<?
	//copy contents from another page?
	if(isset($copyfrom['idpag'])) { ?>
		<tr>
		<th></th>
		<td><?= b3_create_input("copyfrom","checkbox",$kaTranslate->translate('Pages:Copy contents from the page')." <em>".$copyfrom['titolo']."</em>",$copyfrom['idpag'],"","","checked"); ?></td>
		</tr>
		<? } ?>

	<?
	//categories
	if(strpos($pageLayout,",categories,")!==false) { ?>
		<tr>
		<th><?= $kaTranslate->translate('Pages:Categories'); ?></th>
		<td>
			<div id="categorie">Loading...</div>
			<script type="text/javascript" src="./ajax/categorie.js"></script>
			<script type="text/javascript">k_reloadCat(0);</script>
			</td>
		</tr>
		<? } ?>
	
	<?
	//translations
	if(strpos($pageLayout,",traduzioni,")!==false) {
		if(isset($_GET['translate'])) $page_l=$kaPages->get($_GET['translate']);
		else $page_l=array("titolo"=>"","idpag"=>"");
		?>
		<tr>
			<th><?= $kaTranslate->translate('Pages:Translations'); ?></th>
			<td>
			<table><tr>
				<td><?= $kaTranslate->translate('Pages:This page is the translated version of'); ?></td>
				<td>
					<div class="suggestionsContainer">
						<?= b3_create_input("translation","text","",$page_l['titolo'],"200px",250,'autocomplete="off"'); ?>
						<?= b3_create_input("translation_id","hidden","",$page_l['idpag']); ?>
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
		<input type="submit" name="save" class="button" value="<?= $kaTranslate->translate('Pages:Create a new page'); ?> &gt;" />
		</div>
	</form>


<?php	
include_once("../inc/foot.inc.php");
?>
