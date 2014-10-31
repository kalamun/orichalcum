<?php
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Photogalleries:Create a new gallery");

include_once("../inc/head.inc.php");
include_once("./photogallery.lib.php");
$kaPhotogallery=new kaPhotogallery();

$pageLayout=$kaImpostazioni->getVar('admin-photogallery-layout',1,"*");

/* AZIONI */
if(isset($_POST['save'])) {
	$log="";
	$vars=array();
	if(isset($_POST['titolo'])) $vars['title']=$_POST['titolo'];
	if(isset($_POST['dir'])) $vars['dir']=$_POST['dir'];
	
	if(strpos($pageLayout,",categories,")!==false)
	{
		$vars['categories']=",";
		if(isset($_POST['idcat']))
		{
			foreach($_POST['idcat'] as $idcat)
			{
				$vars['categories'].=$idcat.',';
			}
		}
		if(trim($vars['categories'],",")=="")
		{
			require_once('../inc/categorie.lib.php');
			$kaCategorie=new kaCategorie();
			foreach($kaCategorie->getList(TABLE_PHOTOGALLERY) as $cat)
			{
				$vars['categories']=','.$cat['idcat'].',';
				break;
			}
		}
	}

	$id=$kaPhotogallery->add($vars);
	
	if($id==false)
	{
		$log="Photogalleries:An error occurred while creating the photogallery";
	
	} else {
		//if the page is a translated version of another page
		if(isset($_POST['translation_id'])&&$_POST['translation_id']!="") {
			$item=$kaShop->getItem($_POST['translation_id']);
			// first of all, clear translations from previous+current pages
			foreach($item['traduzioni'] as $k=>$v) {
				if($v!="") $kaPhotogallery->removePageFromTranslations($v);
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
					$kaPhotogallery->setTranslations($v,$translations);
					}
				}
			}

		//aggiungo al menu
		if(isset($_POST['addtomenu'])) {
			$addtomenu=explode(",",$_POST['addtomenu']);
			if($addtomenu[1]=="after") {
				$query="SELECT ordine,ref,collection FROM ".TABLE_MENU." WHERE idmenu=".$addtomenu[0]." AND ll='".$_SESSION['ll']."' LIMIT 1";
				$results=mysql_query($query);
				$row=mysql_fetch_array($results);
				$ordine=$row['ordine']+1;
				$ref=$row['ref'];
				$query="UPDATE ".TABLE_MENU." SET ordine=ordine+1 WHERE ref='".$ref."' AND ordine>='".$ordine."' AND ll='".$_SESSION['ll']."'";
				mysql_query($query);
				}
			elseif($addtomenu[1]=="inside") {
				$query="SELECT ordine,ref,collection FROM ".TABLE_MENU." WHERE ref=".$addtomenu[0]." AND ll='".$_SESSION['ll']."' ORDER BY ordine DESC LIMIT 1";
				$results=mysql_query($query);
				$row=mysql_fetch_array($results);
				$ordine=$row['ordine']+1;
				$ref=$addtomenu[0];
				}
			elseif($addtomenu[1]=="before") {
				$query="SELECT ordine,ref,collection FROM ".TABLE_MENU." WHERE idmenu=".$addtomenu[0]." AND ll='".$_SESSION['ll']."' LIMIT 1";
				$results=mysql_query($query);
				$row=mysql_fetch_array($results);
				$ordine=$row['ordine'];
				$ref=$row['ref'];
				$query="UPDATE ".TABLE_MENU." SET ordine=ordine+1 WHERE ref='".$ref."' AND ordine>='".$ordine."' AND ll='".$_SESSION['ll']."'";
				mysql_query($query);
				}
			$query="INSERT INTO ".TABLE_MENU." (label,url,ref,ordine,ll,collection) VALUES('".b3_htmlize($_POST['titolo'],true,"")."','".$kaImpostazioni->getVar('dir_photogallery',1)."/".mysql_real_escape_string($_POST['dir'])."','".$ref."','".$ordine."','".$_SESSION['ll']."','".mysql_real_escape_string($row['collection'])."')";
			if(!mysql_query($query)) $log="Problemi durante l'inserimento nel men&ugrave;";
			}
		}

	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Photogallery: Error creating <em>'.b3_htmlize($_POST['dir'],true,"").'</em>');
		}
	else {
		$kaLog->add("INS",'Photogallery: Created <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$_POST['dir'].'">'.$_POST['dir'].'</a>');
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Photogalleries:Photogallery successfully created').'</div>';
		echo '<meta http-equiv="refresh" content="0; url=edit.php?idphg='.$id.'">';
		}
	}
/***/

?><h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	
	<script type="text/javascript" src="js/edit.js" charset="UTF-8"></script>
	<script type="text/javascript">
		var timer=null;
		var markURLfield=function(success) {
			if(success=="true") document.getElementById('dirYetExists').style.display="inline";
			else document.getElementById('dirYetExists').style.display="none";
			}
		function checkURL(field) {
			var target=document.getElementById('dir')
			//cancello i caratteri non ammessi
			target.value=target.value.replace(/[^\w^\/]+/g,"-");
			if(typeof(ajaxTimer)!=='undefined') clearTimeout(ajaxTimer);
			t=setTimeout("b3_ajaxSend('post','ajax/checkUrl.php','url="+escape(field.value)+"',markURLfield);",500);
			}
		function title2url() {
			var titleField=document.getElementById('titolo');
			var urlField=document.getElementById('dir');
			if(!urlField.getAttribute("completed")&&titleField.value!="") urlField.value=titleField.value.replace(/[^\w]+/g,"-");
			}
		function titleBlur() {
			var titleField=document.getElementById('titolo');
			var urlField=document.getElementById('dir');
			if(urlField.value!="") urlField.setAttribute("completed","true");
			checkURL(urlField);
			}
		function selectMenuRef(f) {
			if(f.checked) {
				k_openIframeWindow(ADMINDIR+"inc/selectMenuRef.inc.php","450px","500px");
				}
			}
		function selectElement(id,where) {
			document.getElementById('addtomenu').value=id+','+where;
			}
		</script>

	<form action="" method="post">
	<div class="title"><?= b3_create_input("titolo","text",$kaTranslate->translate('Photogalleries:Title')."<br />","","95%",250,'autocomplete="off" onkeyup="title2url()" onblur="titleBlur()"'); ?></div>
	<div class="URLBox"><?= b3_create_input("dir","text",$kaTranslate->translate("Photogalleries:Gallery URL").': '.BASEDIR.strtolower($_SESSION['ll'])."/".$kaImpostazioni->getVar('dir_photogallery',1).'/',(isset($copyfrom['dir'])?$copyfrom['dir'].'-'.date("Ymd"):''),"400px",64,'onkeyup="checkURL(this)"'); ?> <span id="dirYetExists" style="display:none;"><?= $kaTranslate->translate('Photogalleries:This URL already exists'); ?></span></div><br />

	<table class="options">
	<? if(strpos($pageLayout,",categories,")!==false) { ?>
		<tr>
		<th><?= $kaTranslate->translate('Shop:Categories'); ?></th>
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
		<? } ?>
	</table><br>

	<?
	$query="SELECT * FROM ".TABLE_MENU." WHERE ref='0' AND ll='".$_SESSION['ll']."' ORDER BY ordine DESC LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
	echo b3_create_input("addtomenu","checkbox",$kaTranslate->translate('Photogalleries:Add this gallery to the site menu'),$row['idmenu'].',after',"","",'onchange="selectMenuRef(this)"'); ?><br />
	<br />
	<div class="submit" id="submit">
		<input type="submit" name="save" class="button" value="<?= $kaTranslate->translate('UI:Save'); ?>" />
		</div>
	</form>


<?php	
include_once("../inc/foot.inc.php");
?>
