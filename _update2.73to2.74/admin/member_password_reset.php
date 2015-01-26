<?php /* (c) Kalamun.org - GNU/GPL 3 */
define("PAGE_ID",".");
define("PAGE_NAME","Home:Member Password Reset");

/* prevent XSS */
header('X-Frame-Options: deny');

/* connect to db and set default constants */
require_once("./inc/config.inc.php");
if(!isset($db['id'])) require_once("./inc/connect.inc.php");
require_once("./inc/main.lib.php");
require_once("./inc/kalamun.lib.php");

/* set language */
if(!isset($_SESSION['ll'])||$_SESSION['ll']=='') $_SESSION['ll']=DEFAULT_LANG;
if($_SESSION['ll']=='') $_SESSION['ll']='EN';

/* set default timezone in PHP and MySQL */
$timezone=kaGetVar('timezone',1);
if($timezone=="") $timezone='Europe/Rome';
date_default_timezone_set($timezone);
$query="SET time_zone='".date("P")."'";
ksql_query($query);

/* load setup variables */
require_once(ADMINRELDIR."inc/log.lib.php");
$kaLog=new kaLog();
$kaImpostazioni=new kaImpostazioni();
$kaTranslate=new kaAdminTranslate();

require_once(ADMINRELDIR."members/members.lib.php");
$kaMembers=new kaMembers();

?>
<!DOCTYPE html>
<html>
<head>
<title><?= $kaImpostazioni->getVar("sitename",1)." &gt; "; ?>Login</title>
<meta name="description" content="<?= $kaImpostazioni->getVar("sitename",1)." Pannello di Controllo"; ?>" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<link rel="shortcut icon" href="<?= ADMINDIR; ?>img/favicon.png" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/init.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/screen.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/main.lib.css?<?= SW_VERSION; ?>" type="text/css" />

<script type="text/javascript">
	var ADMINDIR='<?= addslashes(ADMINDIR); ?>';
	var BASEDIR='<?= addslashes(BASEDIR); ?>';
	</script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/kalamun.js?<?= SW_VERSION; ?>"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/main.lib.js?<?= SW_VERSION; ?>"></script>

<style>
	#meter {
		width:99%;
		height:5px;
		background-color:#ddd;
		border-radius:2px;
		margin:2px 0;
		}
	#meterStripe {
		width:0;
		height:5px;
		background-color:#f00;
		-ms-transition:width .25s linear,background-color .25s linear;
		-o-transition:width .25s linear,background-color .25s linear;
		-moz-transition:width .25s linear,background-color .25s linear;
		-webkit-transition:width .25s linear,background-color .25s linear;
		transition:width .25s linear,background-color .25s linear;
		box-shadow:0px 0px 2px #aaa;
		border-radius:2px;
		}
	#meterStripe.low {
		background-color:#b00;
		}
	#meterStripe.mid {
		background-color:#fa0;
		}
	#meterStripe.high {
		background-color:#8c0;
		}

	#orichalcum_newpassword_repeat.pwalert {
		border-color:#f00;
		}
	</style>
</head>

<body>

<script type="text/javascript">
	function checkForm() {
		f=document.getElementById('theForm');
		if(f.orichalcum_newpassword.value.length<6) { alert("<?= addslashes($kaTranslate->translate('Profile:Password must be length at least 6 chars')); ?>"); return false; }
		if(f.orichalcum_newpassword.value!=f.orichalcum_newpassword_repeat.value) { alert("<?= addslashes($kaTranslate->translate('Profile:Passwords doesn\'t match!')); ?>"); return false; }
		return true;
		}

	function checkPassword(field,updatemeter) {
		if(updatemeter) {
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
			}

		//check password matching
		var n_password=document.getElementById('orichalcum_newpassword');
		var r_password=document.getElementById('orichalcum_newpassword_repeat');
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


<div class="pageCenter">
	<div id="login">

		<?php 
		if(!isset($_GET['t'])) { ?>
			<h2><a href="<?= SITE_URL.BASEDIR; ?>"><?= $kaTranslate->translate('UI:Nothing to do here, visit our home page.'); ?></a></h2>
			<?php  }

		else {
			$t=base64_decode($_GET['t']);
			$separator=strrpos($t,"|");
			$username=substr($t,0,$separator);
			$md5=substr($t,$separator+1);
			
			$u=$kaMembers->getUserByUsername($username);

			if( isset($u['idmember'])
				&& $u['status']=="act"
				&& (trim($u['expiration'],"0- :")=="" || mktime(substr($u['expiration'],11,2),substr($u['expiration'],14,2),substr($u['expiration'],17,2),substr($u['expiration'],5,2),substr($u['expiration'],8,2),substr($u['expiration'],0,4))>time())
				&& $md5==md5($u['idmember'].$u['username'].$u['password'].$u['created'].$u['lastlogin'])
				)
				{
				
				if(isset($_POST['orichalcum_newpassword'])&&isset($_POST['orichalcum_newpassword_repeat'])&&$_POST['orichalcum_newpassword']==$_POST['orichalcum_newpassword_repeat']&&strlen($_POST['orichalcum_newpassword'])>=6) {
					if($kaMembers->password($u['idmember'],$_POST['orichalcum_newpassword'])) { ?>
						<h2><?= $kaTranslate->translate('UI:Well done! Your new password has been approved.'); ?></h2>
						<a href="<?= SITE_URL.BASEDIR; ?>"><?= $kaTranslate->translate('UI:Click here to go to our home page'); ?></a>
						<?php  }
					else { ?>
						<h2><?= $kaTranslate->translate('UI:Ops, something went wrong!'); ?></h2>
						<a href="mailto:<?= ADMIN_MAIL; ?>"><?= $kaTranslate->translate('UI:Click here to contact the site admin'); ?></a>
						<?php  }
					}
				else {
					?>
					<form action="?t=<?= $_GET['t']; ?>" method="post" onsubmit="return checkForm();" id="theForm">
					<?= b3_create_input("orichalcum_newpassword","password",$kaTranslate->translate('UI:Write your new password')."<br />","","",250,'placeholder="'.$kaTranslate->translate('UI:your new password').'" onkeyup="checkPassword(this,true);"'); ?><br />
					<div id="meter"><div id="meterStripe"></div></div>
					<?= b3_create_input("orichalcum_newpassword_repeat","password","","","",250,'placeholder="'.$kaTranslate->translate('UI:please repeat').'" onkeyup="checkPassword(document.getElementById(\'orichalcum_newpassword_repeat\'),false);"'); ?><br />
					<br />
					<div class="submit"><input type="submit" id="save" name="login" value="<?= $kaTranslate->translate('UI:Reset your password'); ?>" class="button" disabled></div>
					</form>
					<?php  }

			} else { ?>
				<h2><?= $kaTranslate->translate('UI:Error while processing your request...'); ?></h2>
				<?php  }

			}
			?>

		</div>
	</div>

</body>
</html>