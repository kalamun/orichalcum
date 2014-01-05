<?php
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Photogalleries:Create a new gallery");

include_once("../inc/head.inc.php");
include_once("./photogallery.lib.php");
$kaPhotogallery=new kaPhotogallery();

/* AZIONI */
if(isset($_POST['save'])) {
	$log="";
	$kaPhotogallery->add(b3_htmlize($_POST['titolo'],true,""),b3_htmlize("",false),b3_htmlize($_POST['dir'],true,""));
	$id=mysql_insert_id();

	//aggiungo al menu
	if(isset($_POST['addtomenu'])) {
		$addtomenu=explode(",",$_POST['addtomenu']);
		if($addtomenu[1]=="after") {
			$query="SELECT ordine,ref FROM ".TABLE_MENU." WHERE idmenu=".$addtomenu[0]." AND ll='".$_SESSION['ll']."' LIMIT 1";
			$results=mysql_query($query);
			$row=mysql_fetch_array($results);
			$ordine=$row['ordine']+1;
			$ref=$row['ref'];
			$query="UPDATE ".TABLE_MENU." SET ordine=ordine+1 WHERE ref='".$ref."' AND ordine>='".$ordine."' AND ll='".$_SESSION['ll']."'";
			mysql_query($query);
			}
		elseif($addtomenu[1]=="inside") {
			$query="SELECT ordine,ref FROM ".TABLE_MENU." WHERE ref=".$addtomenu[0]." AND ll='".$_SESSION['ll']."' ORDER BY ordine DESC LIMIT 1";
			$results=mysql_query($query);
			$row=mysql_fetch_array($results);
			$ordine=$row['ordine']+1;
			$ref=$addtomenu[0];
			}
		elseif($addtomenu[1]=="before") {
			$query="SELECT ordine,ref FROM ".TABLE_MENU." WHERE idmenu=".$addtomenu[0]." AND ll='".$_SESSION['ll']."' LIMIT 1";
			$results=mysql_query($query);
			$row=mysql_fetch_array($results);
			$ordine=$row['ordine'];
			$ref=$row['ref'];
			$query="UPDATE ".TABLE_MENU." SET ordine=ordine+1 WHERE ref='".$ref."' AND ordine>='".$ordine."' AND ll='".$_SESSION['ll']."'";
			mysql_query($query);
			}
		$query="INSERT INTO ".TABLE_MENU." (label,url,ref,ordine,ll) VALUES('".b3_htmlize($_POST['titolo'],true,"")."','".$kaImpostazioni->getVar('dir_photogallery',1)."/".addslashes(stripslashes($_POST['dir']))."','".$ref."','".$ordine."','".$_SESSION['ll']."')";
		if(!mysql_query($query)) $log="Problemi durante l'inserimento nel men&ugrave;";
		}

	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Errore nella creazione della galleria fotografica <em>'.b3_htmlize($_POST['dir'],true,"").'</em>');
		}
	else {
		$kaLog->add("INS",'Creata la galleria fotografica: <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$_POST['dir'].'">'.$_POST['dir'].'</a>');
		echo '<div id="MsgSuccess">Galleria fotografica inserita con successo.</div>';
		echo '<meta http-equiv="refresh" content="0; url=edit.php?idphg='.$id.'">';
		}
	}
/***/

?><h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	
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
