<?php 
/* 2010 (c) Roberto Kalamun Pasini - GPLv3 */

define("PAGE_NAME","Inserisci un nuovo membro in anagrafica");
include_once("../inc/head.inc.php");
include_once("./members.lib.php");
$kaMembers=new kaMembers();

/* AZIONI */
if(isset($_POST['insert'])&&$_POST['m_username']!=""&&$_POST['m_name']!=""&&$_POST['m_password']!="") {
	$log="";
	if(preg_match("/\d{2}.\d{2}.\d{4}/",$_POST['m_expiration'])) $_POST['m_expiration']=preg_replace("/(\d{2}).(\d{2}).(\d{4})/","$3-$2-$1",$_POST['m_expiration']);
	else $_POST['m_expiration']="";
	$id=$kaMembers->add($_POST['m_name'],$_POST['m_email'],$_POST['m_username'],$_POST['m_password'],$_POST['m_affiliation'],$_POST['m_expiration']);
	//$kaMembers->refreshHtpasswd();
	if($id==false) $log="Problemi durante la creazione del nuovo utente";
	if($log=="") {
		$id=mysql_insert_id();
		$kaLog->add("INS",'Creato un nuovo Membro: <em>'.$_POST['m_name'].'</em> - '.$_POST['m_username'].' (<em>ID: '.$id.'</em>)');
		echo '<div id="MsgSuccess">Membro inserito con successo</div>';
		echo '<meta http-equiv="refresh" content="0; url=modifica.php?idmember='.$id.'">';
		}
	else {
		$kaLog->add("ERR",'Errore nella creazione del nuovo Membro <em>'.$_POST['m_name'].'</em> - '.$_POST['m_username'].'');
		echo '<div id="MsgAlert">'.$log.'</div>';
		}
	}
/* FINE AZIONI */

?>

	<script type="text/javascript">
		function checkForm(f) {
			if(f.m_name.value.length==0) { alert("Devi specificare un nome"); return false; }
			else if(f.m_username.value.length==0) { alert("Devi specificare uno username"); return false; }
			else if(f.m_password.value.length<6) { alert("La password deve essere lunga almeno 6 caratteri"); return false; }
			for(var i=0;f.getElementsByTagName('INPUT')[i];i++) f.getElementsByTagName('INPUT')[i].disabled=false;
			return true;
			}

		var timer=null;
		var markUsernamefield=function(success) {
			if(success=="true") document.getElementById('usernameExists').style.display="inline";
			else document.getElementById('usernameExists').style.display="none";
			}
		function checkUsername(field) {
			var target=document.getElementById('m_username')
			//cancello i caratteri non ammessi
			target.value=target.value.replace(/[^\w^\/]+/g,"-");
			//controllo maiuscole
			if(target.value==target.value.replace(/[A-Z]+/g,"")) document.getElementById('maiuscole').style.display='none';
			else document.getElementById('maiuscole').style.display='block';
			if(typeof(ajaxTimer)!=='undefined') clearTimeout(ajaxTimer);
			t=setTimeout("b3_ajaxSend('post','ajax/checkUsername.php','username="+escape(field.value)+"',markUsernamefield);",500);
			}
		function title2user() {
			var titleField=document.getElementById('m_name');
			var urlField=document.getElementById('m_username');
			if(!urlField.getAttribute("completed")&&titleField.value!="") urlField.value=titleField.value.replace(/[^\w]+/g,"-").toLowerCase();
			}
		function titleBlur() {
			var titleField=document.getElementById('m_name');
			var urlField=document.getElementById('m_username');
			if(urlField.value!="") urlField.setAttribute("completed","true");
			checkUsername(urlField);
			}
		</script>

	<h1><?= $kaTranslate->translate('Members:Create member'); ?></h1>
	<br />
	<?php  include('nuovomenu.php'); ?>
	<br />
	<form action="" method="post" onSubmit="return checkForm(this);">
	<div class="topset">
	<table>
	<tr><td><label for="m_name"><?= $kaTranslate->translate('Members:Name'); ?></label></td><td><?= b3_create_input("m_name","text","","","300px",64,'onkeyup="title2user()" onblur="titleBlur()"'); ?></td></tr>
	<tr><td><label for="m_email"><?= $kaTranslate->translate('Members:E-mail'); ?></label></td><td><?= b3_create_input("m_email","text","","","300px",255); ?></td></tr>
	<tr><td><label for="m_username"><?= $kaTranslate->translate('Members:Username'); ?></label></td><td><div class="title"><?= b3_create_input("m_username","text","","","400px",64,'onkeyup="checkUsername(this)"'); ?> <span id="usernameExists" style="display:none;">Questo username esiste gi&agrave;!</span></div></td></tr>
	<tr><td><label for="m_password"><?= $kaTranslate->translate('Members:Password'); ?></label></td><td><div class="title"><?= b3_create_input("m_password","text","","","400px",64); ?></div></td></tr>
	<tr><td><label for="m_affiliation"><?= $kaTranslate->translate('Members:Affiliation'); ?></label></td><td><?= b3_create_input("m_affiliation","text","","","100px",16); ?></td></tr>
	<tr><td><label for="m_scadenza"><?= $kaTranslate->translate('Members:Expiration'); ?></label></td><td><?= b3_create_input("m_expiration","text","",date("d-m-Y",mktime(0,0,0,date("m")+1,date("d"),date("Y"))),"100px",16); ?> <span class="small"><?= $kaTranslate->translate('Members:Leave empty for no expiration'); ?></span></td></tr>
	</table>
	<div class="note" id="maiuscole" style="display:none;">Stai attento a maiuscole e minuscole!</div><br />
	</div>
	<div class="submit"><input type="submit" name="insert" value="Crea Utente" class="button"></div>
	</form>

<?php 
	
include_once("../inc/foot.inc.php");
