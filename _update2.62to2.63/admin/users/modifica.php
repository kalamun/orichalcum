<?
/* 2010 (c) Roberto Kalamun Pasini - GPLv3 */

define("PAGE_NAME","Users:Edit user");
require_once("../inc/head.inc.php");
require_once("./users.lib.php");
$kaUsers=new kaUsers();

/* AZIONI */
if(isset($_POST['update'])&&isset($_POST['n_username'])&&$_POST['n_username']!=""&&isset($_POST['n_name'])&&isset($_GET['iduser'])) {
	$log="";
	if(!$kaUsers->update($_GET['iduser'],$_POST['n_name'],$_POST['n_email'],$_POST['n_username'],$_POST['n_p'],$_POST['featuredimage'])) $log="Problemi durante la modifica dell'utente";
	
	if($log=="") {
		if(!$kaUsers->propReplace($_GET['iduser'],'info','summary',b3_htmlize($_POST['summary'],false))) $log.='Impossibile aggiornare le informazioni brevi sull\'utente.<br />';
		if(!$kaUsers->propReplace($_GET['iduser'],'info','description',b3_htmlize($_POST['description'],false))) $log.='Impossibile aggiornare le informazioni sull\'utente.<br />';
		if(!$kaUsers->propReplace($_GET['iduser'],'ui','lang',addslashes(stripslashes($_POST['ui_lang'])))) $log.='Impossibile aggiornare la lingua preferita.<br />';
		}
	
	if($log=="") {
		echo '<div id="MsgSuccess">Utente modificato con successo</div>';
		$kaLog->add("UPD",'Users: Changed user <em>'.$_POST['n_name'].'</em> - '.$_POST['n_username'].' (<em>ID: '.$_GET['iduser'].'</em>)');
		}
	else {
		$kaLog->add("ERR",'Users: Error while saving changes to <em>'.$_POST['n_name'].'</em> - '.$_POST['n_username'].' (<em>ID: '.$_GET['iduser'].'</em>)');
		echo '<div id="MsgAlert">'.$log.'</div>';
		}
	}
elseif(isset($_POST['iduser'])&&isset($_POST['n_password'])) {
	$log="";
	if(!$kaUsers->password($_POST['iduser'],$_POST['n_password'])) $log="Problemi durante il salvataggio della password";
	
	$u=$kaUsers->getUserFromId($_POST['iduser']);
	if($log=="") {
		echo '<div id="MsgSuccess">Password modificata con successo</div>';
		$kaLog->add("UPD",'Users: Setted a new password for <em>'.$u['name'].'</em> - '.$u['username'].' (<em>ID: '.$u['iduser'].'</em>)');
		}
	else {
		$kaLog->add("ERR",'Users: Error changing the password of <em>'.$u['name'].'</em> - '.$u['username'].' (<em>ID: '.$u['iduser'].'</em>)');
		echo '<div id="MsgAlert">'.$log.'</div>';
		}
	}
/* FINE AZIONI */


/* CONTROLLO FORM */
?>
<script type="text/javascript" src="./js/edit.js"></script>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<?

if(isset($_GET['iduser'])) {
	$u=$kaUsers->getUserFromId($_GET['iduser']);
	if(!isset($u['ui'])) $u['ui']=array("lang"=>$_SESSION['ll']);
	if(!isset($u['info'])) $u['info']=array("summary"=>"","description"=>"");
	?>

	<form id="theForm" action="?iduser=<?= $u['iduser']; ?>" method="post" onsubmit="return checkForm();">
	<div class="subset">
		<fieldset class="box"><legend><?= $kaTranslate->translate('Users:Profile Picture'); ?></legend>
			<div id="featuredImageContainer"><?php
				if($u['featuredimage']>0)
				{
					$img=$kaImages->getImage($u['featuredimage']);
					?>
					<img src="<?= BASEDIR.$img['thumb']['url']; ?>">
					<?
				}
				?></div>
			<input type="hidden" name="featuredimage" id="featuredimage" value="<?= $u['featuredimage']; ?>">
			<a href="javascript:k_openIframeWindow('../inc/uploadsManager.inc.php?limit=1&submitlabel=<?= urlencode($kaTranslate->translate('Users:Choose profile picture')); ?>&onsubmit=setFeaturedImage','90%','90%');" class="smallbutton"><?= $kaTranslate->translate('News:Choose profile picture'); ?></a>
			<small><a href="javascript:removeFeaturedImage();" id="removeFeaturedImage" class="warning" <? if($u['featuredimage']==0) echo 'style="display:none;"'; ?>><?= $kaTranslate->translate('UI:Delete'); ?></a></small>
			</fieldset><br />
	</div>

	<script type="text/javascript">
		function checkForm() {
			f=document.getElementById('theForm');
			if(f.n_name.value.length==0) { alert("<?= $kaTranslate->translate('Users:Name is missing'); ?>"); return false; }
			else if(f.n_username.value.length==0) { alert("<?= $kaTranslate->translate('Users:Username is missing'); ?>"); return false; }
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

	<div class="topset">
	<table>
	<tr><td><label for="n_name"><?= $kaTranslate->translate('Users:Name'); ?></label></td><td><?= b3_create_input("n_name","text","",$u['name'],"300px",150,'onkeyup="title2user()" onblur="titleBlur()"'); ?></td></tr>
	<tr><td><label for="n_email"><?= $kaTranslate->translate('Users:E-mail'); ?></label></td><td><?= b3_create_input("n_email","text","",$u['email'],"300px",250); ?></td></tr>
	<tr><td><label for="n_username"><?= $kaTranslate->translate('Users:Username'); ?></label></td><td><div class="title"><?= b3_create_input("n_username","text","",$u['username'],"400px",64,'onkeyup="checkUsername(this)"'); ?> <span id="usernameExists" style="display:none;"><?= $kaTranslate->translate('Users:This username already exists!'); ?></span></div></td></tr>
	<tr><td><label for="ui_lang"><?= $kaTranslate->translate('Users:Language'); ?></label></td><td><?= b3_create_select("ui_lang","",$kaUsers->getLanguages('labels'),$kaUsers->getLanguages('codes'),$u['ui']['lang']); ?></td></tr>
	</table>

	<br />
	<br />
	<table><thead><h3><?= $kaTranslate->translate('Users:Grant access to the following features'); ?>:</h3></thead>
		<tr><?
			foreach($kaUsers->getPermissionsList() as $ka=>$p) {
				echo '<td><h4>'.$p['title'].'</h4>';
				foreach($p['submenu'] as $subp) {
					echo b3_create_input("n_p[]","checkbox",$subp['title'],$subp['id'],"","",(strpos($u['permissions'],",".$subp['id'].",")||$ka==0?"checked":"").($ka==0?" disabled":""),true).'<br />';
					}
				echo '</td>';
				}
			?></tr>
		</table><br />

	<br />
	<?= b3_create_textarea("summary",$kaTranslate->translate('Users:A short description of you'),b3_lmthize($u['info']['summary'],"textarea"),"600px","100px",RICH_EDITOR); ?><br /><br />
	<?= b3_create_textarea("description",$kaTranslate->translate('Users:More infos'),b3_lmthize($u['info']['description'],"textarea"),"100%","200px",RICH_EDITOR); ?><br /><br />
	
	</div>
	<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button"> <input type="button" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" onclick="window.location='?'" class="button" /></div>
	</form>
	<?
	}

elseif(isset($_GET['password'])) {
	$u=$kaUsers->getUserFromId($_GET['password']);
	?>
	<script type="text/javascript">
		function checkForm(f) {
			if(f.n_password.value.length==0) { alert('Your new password can\'t be empty'); return false; }
			if(f.n_password.value!=f.r_password.value) { alert('Passwords doesn\'t match'); return false; }
			return true;
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

	<form action="?" method="post" onsubmit="return checkForm(this);">
	<input type="hidden" name="iduser" value="<?= $u['iduser']; ?>" />
	<div class="topset">
	<h2><?= $u['name']; ?> (<?= $u['username']; ?>)</h2>
	<br />
	<table>
	<tr><td><label for="n_password"><?= $kaTranslate->translate('Users:Password'); ?></label></td><td><?= b3_create_input("n_password","password","","","300px",150,'onkeyup="checkPassword(this);"'); ?></td>
	<tr><td></td><td>
		<div id="meter"><div id="meterStripe"></div></div>
		</td><td></td></tr>
	<tr><td><label for="r_password"><?= $kaTranslate->translate('Users:Repeat Password'); ?></label></td><td><?= b3_create_input("r_password","password","","","300px",150,'onkeyup="checkPassword(document.getElementById(\'n_password\'));"'); ?></td><td></td></tr>
	</table>
	<br /><br />
	<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button"> <input type="button" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" onclick="window.location='?'" class="button" /></div>
	</form>
	<?
	}
	
else {
	?>
	<script type="text/javascript">
		function selectMenuRef(useUser) {
			document.getElementById('useUser').value=useUser;
			k_openIframeWindow(ADMINDIR+"inc/selectMenuRef.inc.php","450px","500px");
			}
		function selectElement(id,where) {
			var useUser=document.getElementById('useUser').value;
			var get="";
			if(String(window.location).indexOf("search=")>-1) {
				get=String(window.location);
				get=get.replace(/.*search=/,"");
				get="search="+get.replace(/^[[^\d]*].*/,"");
				}
			var url=String(window.location).replace(/\?.*/,"");
			window.location=url+'?useUser='+useUser+'&addtomenu='+id+','+where+'&'+get;
			}
		</script>
	
	<div class="topset">
		<input type="hidden" id="useUser" />
		<table class="tabella">
		<tr><th><?= $kaTranslate->translate('Users:User'); ?></th><th><?= $kaTranslate->translate('Users:Username'); ?></th><th><?= $kaTranslate->translate('Users:Created on'); ?> / <?= $kaTranslate->translate('Users:Last log-in'); ?></th></tr><?php
		foreach($kaUsers->getUsersList() as $ka=>$v) { ?>
			<tr>
			<td><h2><a href="?iduser=<?= $v['iduser']; ?>"><?= $v['name']; ?></a></h2>
				<small class="actions"><a href="?iduser=<?= $v['iduser']; ?>"><?= $kaTranslate->translate('UI:Edit'); ?></a> | <a href="?password=<?= $v['iduser']; ?>"><?= $kaTranslate->translate('Users:Change password'); ?></a></small>
				</td>
			<td class="percorso"><a href="?iduser=<?= $v['iduser']; ?>"><?= $v['username']; ?></a></td>
			<td class="percorso"><a href="?iduser=<?= $v['iduser']; ?>"><?= $v['created_leggibile']; ?><br /><?= $v['lastlogin_leggibile']; ?></a></td>
			</tr>
			<? } ?>
		</table>
		</div>
		<?
	}

	
include_once("../inc/foot.inc.php");
?>
