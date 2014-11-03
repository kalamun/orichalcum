<?php 
/* 2010 (c) Roberto Kalamun Pasini - GPLv3 */

define("PAGE_NAME","Users:Create a new user");
include_once("../inc/head.inc.php");
include_once("./users.lib.php");
$kaUsers=new kaUsers();

/* AZIONI */
if(isset($_POST['insert'])&&$_POST['n_username']!=""&&$_POST['n_name']!=""&&$_POST['n_password']!="") {
	$log="";
	if(!$kaUsers->add($_POST['n_name'],$_POST['n_email'],$_POST['n_username'],$_POST['n_password'],$_POST['n_p'])) $log="Problemi durante la creazione del nuovo utente";
	if($log=="") {
		$id=mysql_insert_id();
		if(!$kaUsers->propReplace($id,'ui','lang',addslashes(stripslashes($_POST['ui_lang'])))) $log.='Impossibile aggiornare la lingua preferita.<br />';
		$kaLog->add("INS",'Users: Created <em>'.$_POST['n_name'].'</em> - '.$_POST['n_username'].' (<em>ID: '.$id.'</em>)');
		echo '<div id="MsgSuccess">Utente inserito con successo</div>';
		echo '<meta http-equiv="refresh" content="0; url=modifica.php?iduser='.$id.'">';
		}
	else {
		$kaLog->add("ERR",'Users: Errore creating <em>'.$_POST['n_name'].'</em> - '.$_POST['n_username'].'');
		echo '<div id="MsgAlert">'.$log.'</div>';
		}
	}
/* FINE AZIONI */

?>

	<script type="text/javascript">
		function checkForm(f) {
			if(f.n_name.value.length==0) { alert("Devi specificare un nome"); return false; }
			else if(f.n_username.value.length==0) { alert("Devi specificare uno username"); return false; }
			else if(f.n_username.value!=f.r_username.value) { alert("Le due password non combaciano. Controlla di aver scritto bene"); return false; }
			else if(f.n_password.value.length<6) { alert("La password deve essere lunga almeno 6 caratteri"); return false; }
			for(var i=0;f.getElementsByTagName('INPUT')[i];i++) f.getElementsByTagName('INPUT')[i].disabled=false;
			return true;
			}

		var ajaxTimer=null;
		var markUsernamefield=function(success) {
			if(success=="true") document.getElementById('usernameExists').style.display="inline";
			else document.getElementById('usernameExists').style.display="none";
			}
		function checkUsername(field) {
			var target=document.getElementById('n_username')
			//cancello i caratteri non ammessi
			target.value=target.value.replace(/[^\w^\/]+/g,"-");
			//controllo maiuscole
			if(ajaxTimer) clearTimeout(ajaxTimer);
			var aj=new kAjax;
			aj.onSuccess(markUsernamefield);
			ajaxTimer=setTimeout(function() { aj.send('post','ajax/checkUsername.php','username='+escape(field.value)); },500);
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
		function checkPassword(field) {
			//calculate password strenght
			var ps=0;
			var length=field.value.length;
			ps=length/10;
			for(var i=0;i<field.value.length;i++) {
				var code=field.value.charCodeAt(i);
				//numbers 48->57 //uppercase 65->90 //lowercase 97->122
				if(code>=48&&code<=57) ps*=1.3;
				else if(code>=65&&code<=90) ps*=1.2;
				else if(code>=97&&code<=122) ps*=1.1;
				else ps*=1.4;
				//ps=Math.round(ps);
				}
			ps=ps*10;
			var className='low';
			if(ps>40) className='mid';
			if(ps>75) {
				ps=100;
				className='high';
				}
			document.getElementById('meterStripe').style.width=ps+'%';
			document.getElementById('meterStripe').className=className;

			//check password pairing
			var n_password=document.getElementById('n_password');
			var r_password=document.getElementById('r_password');
			var save=document.getElementById('save');
			if(n_password.value!=r_password.value||n_password.value.length<6) {
				r_password.className='pwalert';
				save.disabled=true;
				}
			else {
				r_password.className='';
				save.disabled=false;
				}
			}
		</script>

	<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
	<br />
	<form action="" method="post" onSubmit="return checkForm(this);">
	<div class="topset">
	<table>
	<tr><td><label for="n_name"><?= $kaTranslate->translate('Users:Name'); ?></label></td><td><?= b3_create_input("n_name","text","","","300px",150,'onkeyup="title2user()" onblur="titleBlur()"'); ?></td></tr>
	<tr><td><label for="n_email"><?= $kaTranslate->translate('Users:E-mail'); ?></label></td><td><?= b3_create_input("n_email","text","","","300px",250); ?></td></tr>
	<tr><td>&nbsp;</td><td></td></tr>
	<tr><td><label for="n_username"><?= $kaTranslate->translate('Users:Username'); ?></label></td><td><div class="title"><?= b3_create_input("n_username","text","","","400px",64,'onkeyup="checkUsername(this)"'); ?> <small id="usernameExists" style="display:none;"><?= $kaTranslate->translate('Users:This username already exists!'); ?></small></div></td></tr>
	<tr><td><label for="n_password"><?= $kaTranslate->translate('Users:Password'); ?></label></td><td><?= b3_create_input("n_password","password","","","300px",150,'onkeyup="checkPassword(this);"'); ?></td>
	<tr><td></td><td>
		<div id="meter"><div id="meterStripe"></div></div>
		</td><td></td></tr>
	<tr><td><label for="r_password"><?= $kaTranslate->translate('Users:Repeat Password'); ?></label></td><td><?= b3_create_input("r_password","password","","","300px",150,'onkeyup="checkPassword(document.getElementById(\'n_password\'));"'); ?></td><td></td></tr>
	<tr><td>&nbsp;</td><td></td></tr>
	<tr><td><label for="ui_lang"><?= $kaTranslate->translate('Users:Language'); ?></label></td><td><?= b3_create_select("ui_lang","",$kaUsers->getLanguages('labels'),$kaUsers->getLanguages('codes'),$_SESSION['ui']['lang']); ?></td></tr>
	</table>

	<br />
	<br />
	<table><thead><h3><?= $kaTranslate->translate('Users:Grant access to the following features'); ?>:</h3></thead>
		<tr><?php 
			foreach($kaUsers->getPermissionsList() as $ka=>$p) {
				echo '<td><h4>'.$p['title'].'</h4>';
				foreach($p['submenu'] as $subp) {
					echo b3_create_input("n_p[]","checkbox",$subp['title'],$subp['id'],"","","checked".($ka==0?" disabled":"")).'<br />';
					}
				echo '</td>';
				}
			?></tr>
		</table>
	<br />
	<br />
	</div>
	<div class="submit"><input type="submit" name="insert" value="<?= $kaTranslate->translate('Users:Create user'); ?>" class="button"></div>
	</form>

<?php 
	
include_once("../inc/foot.inc.php");
