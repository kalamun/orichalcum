<?php 
/* 2010 (c) Roberto Kalamun Pasini - GPLv3 */

define("PAGE_NAME",'Profile:Change your Personal Profile');
require_once("../inc/head.inc.php");
require_once("../users/users.lib.php");
$kaUsers=new kaUsers();

/* AZIONI */
if(isset($_POST['update'])&&isset($_POST['n_name'])&&isset($_SESSION['iduser'])) {
	$log="";
	if(!$kaUsers->update($_SESSION['iduser'],$_POST['n_name'],$_POST['n_email'],$_SESSION['username'])) $log="Problemi durante la modifica dell'utente";
	
	if($log=="") {
		if(!$kaUsers->propReplace($_SESSION['iduser'],'info','summary',b3_htmlize($_POST['summary'],false))) $log.='Impossibile aggiornare le informazioni brevi sull\'utente.<br />';
		if(!$kaUsers->propReplace($_SESSION['iduser'],'info','description',b3_htmlize($_POST['description'],false))) $log.='Impossibile aggiornare le informazioni sull\'utente.<br />';
		if(!$kaUsers->propReplace($_SESSION['iduser'],'ui','lang',addslashes(stripslashes($_POST['ui_lang'])))) $log.='Impossibile aggiornare la lingua preferita.<br />';
		}
	
	if($log=="") {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Profile:Your profile was successfully saved').'</div>';
		$kaLog->add("UPD",'Modificato il proprio profilo (<em>ID: '.$_SESSION['iduser'].'</em>)');
		}
	else {
		$kaLog->add("ERR",'Errore nella modifica proprio profilo (<em>ID: '.$_SESSION['iduser'].'</em>)');
		echo '<div id="MsgAlert">'.$log.'</div>';
		}
	}
/* FINE AZIONI */


/* CONTROLLO FORM */
?>

<h1><?= $kaTranslate->translate('Profile:Change your Personal Profile'); ?></h1>
<br />
<?php 

$u=$kaUsers->getUserFromId($_SESSION['iduser']);
if(!isset($u['ui'])) $u['ui']=array("lang"=>DEFAULT_LANG);
?>
<div class="subset">
	<strong><?= $kaTranslate->translate('Profile:Profile Picture'); ?></strong>
	<iframe src="<?php echo ADMINDIR; ?>inc/imgallery.inc.php?refid=imgallery&mediatable=<?php echo TABLE_USERS; ?>&mediaid=<?php echo $row['iduser']; ?>&start=1&max=1&label=<?= urlencode($kaTranslate->translate('Profile:Insert your personal picture')); ?>" class="imgframe" id="imgallery"></iframe>
	<br />
	</div>

<script type="text/javascript">
	function checkForm() {
		f=document.getElementById('theForm');
		if(f.n_name.value.length==0) { alert("<?= $kaTranslate->translate('Profile:Please write your name'); ?>"); return false; }
		else if(f.n_username.value.length==0) { alert("<?= $kaTranslate->translate('Profile:Please write your username'); ?>"); return false; }
		for(var i=0;f.getElementsByTagName('INPUT')[i];i++) f.getElementsByTagName('INPUT')[i].disabled=false;
		return true;
		}

	var timer=null;
	var markUsernamefield=function(success) {
		if(success=="true") document.getElementById('usernameExists').style.display="inline";
		else document.getElementById('usernameExists').style.display="none";
		}
	function checkUsername(field) {
		var target=document.getElementById('n_username')
		//cancello i caratteri non ammessi
		target.value=target.value.replace(/[^\w^\/]+/g,"-");
		//controllo maiuscole
		if(target.value==target.value.replace(/[A-Z]+/g,"")) document.getElementById('maiuscole').style.display='none';
		else document.getElementById('maiuscole').style.display='block';
		if(typeof(ajaxTimer)!=='undefined') clearTimeout(ajaxTimer);
		t=setTimeout("b3_ajaxSend('post','ajax/checkUsername.php','username="+escape(field.value)+"',markUsernamefield);",500);
		}
	function title2user() {
		var titleField=document.getElementById('n_name');
		var urlField=document.getElementById('n_username');
		if(!urlField.getAttribute("completed")&&titleField.value!="") urlField.value=titleField.value.replace(/[^\w]+/g,"-");
		}
	function titleBlur() {
		var titleField=document.getElementById('n_name');
		var urlField=document.getElementById('n_username');
		if(urlField.value!="") urlField.setAttribute("completed","true");
		checkUsername(urlField);
		}
	</script>

<form id="theForm" action="?iduser=<?= $u['iduser']; ?>" method="post" onsubmit="return checkForm();">
<div class="topset">
<table>
<tr><td><label for="n_name"><?= $kaTranslate->translate('Profile:Name'); ?></label></td><td><?= b3_create_input("n_name","text","",$u['name'],"300px",150,'onkeyup="title2user()" onblur="titleBlur()"'); ?></td></tr>
<tr><td><label for="n_email"><?= $kaTranslate->translate('Profile:E-mail'); ?></label></td><td><?= b3_create_input("n_email","text","",$u['email'],"300px",250); ?></td></tr>
<tr><td><label for="ui_lang"><?= $kaTranslate->translate('Profile:Main Language'); ?></label></td><td><div class="title"><?= b3_create_select("ui_lang","",$kaUsers->getLanguages('labels'),$kaUsers->getLanguages('codes'),$u['ui']['lang']); ?></td></tr>
</table>
<div class="note" id="maiuscole" style="display:none;"><?= $kaTranslate->translate('Profile:Please pay attention to letter case'); ?></div><br />

<br />
<?= b3_create_textarea("summary",$kaTranslate->translate('Profile:About you'),b3_lmthize($kaUsers->propGetValue($u['iduser'],'info','summary'),"textarea"),"600px","100px",RICH_EDITOR); ?><br /><br />
<?= b3_create_textarea("description",$kaTranslate->translate('Profile:More infos about you'),b3_lmthize($kaUsers->propGetValue($u['iduser'],'info','description'),"textarea"),"100%","200px",RICH_EDITOR); ?><br /><br />

</div>
<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button"> <input type="button" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" onclick="window.location='?'" class="button" /></div>
</form>
<?php 
	
include_once("../inc/foot.inc.php");
