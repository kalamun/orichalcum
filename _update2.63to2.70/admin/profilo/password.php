<?php 
/* 2010 (c) Roberto Kalamun Pasini - GPLv3 */

define("PAGE_NAME","Profile:Change your Password");
require_once("../inc/head.inc.php");
require_once("../users/users.lib.php");
$kaUsers=new kaUsers();

/* AZIONI */
if(isset($_SESSION['iduser'])&&isset($_POST['o_password'])&&isset($_POST['n_password'])&&isset($_POST['r_password'])) {
	$log="";
	if(strlen($_POST['n_password'])<6) $log=$kaTranslate->translate('Profile:Your new password must be longer than %s chars',6);
	elseif($_POST['n_password']!=$_POST['r_password']) $log=$kaTranslate->translate("Profile:Your new password don't match the repeated one. Please retype both passwords, and be carefull!");
	elseif(md5($_POST['o_password'])!=$_SESSION['password']) $log=$kaTranslate->translate("Profile:Your old password is incorrect. Did you forgot it? <a href=\"mailto:".ADMIN_MAIL."\">Write to the administrator.</a>");
	elseif(!$kaUsers->password($_SESSION['iduser'],$_POST['n_password'])) $log=$kaTranslate->translate('Profile:An Error occurred while saving your password');
	
	$_SESSION['password']=md5($_POST['n_password']);

	$u=$kaUsers->getUserFromId($_SESSION['iduser']);
	if($log=="") {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Profile:Password successfully updated').'</div>';
		$kaLog->add("UPD",'Users:Update of <em>'.$u['name'].'</em>\'s password (username: '.$u['username'].' - ID: '.$u['iduser'].')');
		}
	else {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Users:Error updating <em>'.$u['name'].'</em>\'s password (username: '.$u['username'].' - ID: '.$u['iduser'].')');
		}
	}
/* FINE AZIONI */


/* CONTROLLO FORM */
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<script type="text/javascript">
	function checkForm() {
		f=document.getElementById('theForm');
		if(f.n_password.value.length<6) { alert("<?= addslashes($kaTranslate->translate('Profile:Password must be length at least %s chars',6)); ?>"); return false; }
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

<form id="theForm" action="?" method="post" onsubmit="return checkForm();">
<div class="topset">
<table>
<tr><td><label for="o_password"><?= $kaTranslate->translate('Profile:Old Password'); ?></label></td><td><?= b3_create_input("o_password","password","","","300px",150); ?><br /><br /></td><td></td></tr>
<tr><td><label for="n_password"><?= $kaTranslate->translate('Profile:New Password'); ?></label></td><td><?= b3_create_input("n_password","password","","","300px",150,'onkeyup="checkPassword(this);"'); ?></td>
<tr><td></td><td>
	<div id="meter"><div id="meterStripe"></div></div>
	</td><td></td></tr>
<tr><td><label for="r_password"><?= $kaTranslate->translate('Profile:Repeat Password'); ?></label></td><td><?= b3_create_input("r_password","password","","","300px",150,'onkeyup="checkPassword(document.getElementById(\'n_password\'));"'); ?></td><td></td></tr>
</table>
<br />
</div>
<div class="submit"><input type="submit" name="update" id="save" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" disabled="disabled"> <input type="button" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" onclick="window.location='?'" class="button" /></div>
</form>

<?php 	
include_once("../inc/foot.inc.php");
