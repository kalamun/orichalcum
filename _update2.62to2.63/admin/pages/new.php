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


/***********/
/* ACTIONS */
/***********/
//insert page
if(isset($_POST['save'])) {
	$log="";

	$categories=",";
	if(!empty($_POST['idcat']))
	{
		foreach($_POST['idcat'] as $idcat)
		{
			$categories.=$idcat.',';
		}
	}
	
	if(empty($_POST['idcat'])) $_POST['idcat']="";
	if(empty($_POST['title'])) $_POST['title']="";
	if(empty($_POST['dir'])) $_POST['dir']="";
	if(empty($_POST['translation_id'])) $_POST['translation_id']="";
	if(empty($_POST['copyfrom'])) $_POST['copyfrom']="";
	if(empty($_POST['addtomenu'])) $_POST['addtomenu']="";

	if(!isset($_POST['dir'])&&isset($_POST['titolo'])) $_POST['dir']=preg_replace("/[^\w\/\.\-\x{C0}-\x{D7FF}\x{2C00}-\x{D7FF}]+/","-",strtolower($_POST['titolo']));
	if(!isset($_POST['dir'])||$_POST['dir']==""||$_POST['dir']=="-.html") $_POST['dir']=rand(10,999999);
	if(strlen($_POST['dir'])>64) $_POST['dir']=substr(str_replace(".html","",$_POST['dir']),0,64).".html";

	$vars=[
		"idcat"=>$_POST['idcat'],
		"title"=>$_POST['titolo'],
		"categories"=>$categories,
		"dir"=>$_POST['dir'],
		"translation_id"=>$_POST['translation_id'],
		"copyfrom"=>$_POST['copyfrom'],
		"addtomenu"=>$_POST['addtomenu']
		];
	$log=$kaPages->add($vars);


	if(!is_numeric($log))
	{
		echo '<div id="MsgAlert">'.$kaTranslate->translate($log).'</div>';
		$kaLog->add("ERR",'Pages: Error while creating page <em>'.b3_htmlize($_POST['dir'],true,"").'</em> ('.$log.')');
	} else {
		$kaLog->add("INS",'Pages: Successfully added the page <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$_POST['dir'].'">'.$_POST['titolo'].'</a>');
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Pages:Page saved').'</div>';
		echo '<meta http-equiv="refresh" content="0; url=edit.php?idpag='.$log.'">';
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
