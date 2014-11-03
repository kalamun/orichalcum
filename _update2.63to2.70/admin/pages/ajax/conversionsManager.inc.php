<?php /* (c) Kalamun.org - GNU/GPL 3 */

require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../inc/main.lib.php');
if(!isset($_SESSION['iduser'])) die('Non hai il permesso di utilizzare questa funzione');

/* set default timezone in PHP and MySQL */
$timezone=kaGetVar('timezone',1);
if($timezone=="") $timezone='Europe/Rome';
date_default_timezone_set($timezone);
$query="SET time_zone='".date("P")."'";
mysql_query($query);

require_once('../../inc/log.lib.php');
$kaLog=new kaLog();
require_once('../../users/users.lib.php');
$kaUsers=new kaUsers();
$kaImpostazioni=new kaImpostazioni();
$pageLayout=$kaImpostazioni->getVar('admin-page-layout',1,"*");
$kaTranslate=new kaAdminTranslate();
$kaTranslate->import('pages');

require_once('../pages.lib.php');
$kaPages=new kaPages();

define("PAGE_NAME",$kaTranslate->translate('Pages:Conversions Manager'));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
<title><?php echo ADMIN_NAME." - ".PAGE_NAME; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/init.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/screen.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/main.lib.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/imgmanager.css?<?= SW_VERSION; ?>" type="text/css" />
<link rel="stylesheet" href="<?= ADMINDIR; ?>css/kzeneditor.css?<?= SW_VERSION; ?>" type="text/css" />

<script type="text/javascript">var ADMINDIR='<?= str_replace("'","\'",ADMINDIR); ?>';</script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/main.lib.js"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/kalamun.js"></script>
<script type="text/javascript" src="<?= ADMINDIR; ?>js/imgframe.js"></script>
<script type="text/javascript" src="../js/conversions.js"></script>
</head>

<body>

<div id="imgheader">

	<?php 
	/***************************/
	/* ACTIONS                 */
	/***************************/

	if(isset($_POST['update'])&&isset($_GET['idpag'])) {
		$vars=array("idpag"=>$_GET['idpag']);
		$vars['moderate']=intval($_POST['moderate']);
		$vars['create_member']=isset($_POST['create_member'])?true:false;
		$vars['create_member_config']="";
		$vars['create_member_config'].="u:".$_POST['create_member_username']."\n";
		$vars['create_member_config'].="p:".$_POST['create_member_password']."\n";
		$vars['create_member_config'].="e:".$_POST['create_member_expiration']."\n";
		$vars['create_member_config'].="a:".$_POST['create_member_affiliation']."\n";
		
		$vars['private_dir']=isset($_POST['private_dir'])?true:false;
		$vars['newsletters_add']=",";
		if(isset($_POST['newsletters_add'])) {
			foreach($_POST['newsletters_add'] as $idnl=>$true) {
				$vars['newsletters_add'].=$idnl.",";
				}
			}

		$vars['newsletters_remove']=",";
		if(isset($_POST['newsletters_remove'])) {
			foreach($_POST['newsletters_remove'] as $idnl=>$true) {
				$vars['newsletters_remove'].=$idnl.",";
				}
			}

		$variables="";
		if(isset($_POST['variable_name'][0]))
		{
			for($i=0;isset($_POST['variable_name'][$i]);$i++)
			{
				if(isset($_POST['variable_name'][$i]) && $_POST['variable_name'][$i]!="")
				{
					if(!isset($_POST['variable_correspondence'][$i])) $_POST['variable_correspondence'][$i]="";
					if(!isset($_POST['variable_mandatory'][$i])) $_POST['variable_mandatory'][$i]="n";
					$variables.=$_POST['variable_name'][$i]."\t".$_POST['variable_correspondence'][$i]."\t".$_POST['variable_mandatory'][$i]."\n";
				}
			}
		}
		$vars['variables']=$variables;

		if(isset($_POST['notification_emails'])) $vars['notification_emails']=$_POST['notification_emails'];
		if($_POST['notification_custom']=="") $_POST['notification_custom']=ADMIN_MAIL;
		if(isset($_POST['notification_from'])) $vars['notification_from']=($_POST['notification_from']=='custom'?$_POST['notification_custom']:$_POST['notification_from']);
		if(isset($_POST['notification_subject'])) $vars['notification_subject']=$_POST['notification_subject'];
		if(isset($_POST['notification_text'])) $vars['notification_text']=$_POST['notification_text'];
		if(isset($_POST['conversion_code'])) $vars['conversion_code']=$_POST['conversion_code'];
		if(isset($_POST['fail_code'])) $vars['fail_code']=$_POST['fail_code'];
		if(isset($_POST['followup_from'])) $vars['followup_from']=$_POST['followup_from'];
		if(isset($_POST['followup_subject'])) $vars['followup_subject']=$_POST['followup_subject'];
		if(isset($_POST['followup_text'])) $vars['followup_text']=$_POST['followup_text'];
		$showResultMsg=$kaPages->updateConversions($vars);
		}

	$page=$kaPages->get(array("idpag"=>$_GET['idpag']));
	?>

	<h1><?= PAGE_NAME; ?></h1>
	<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	<?php  if($page['idpag']=="") die('</div><h1>Error loading page</h1>'); ?>
	<div class="smenu sel" id="tabs">
		<ul>
		<?php 
		$menu=array();
		$menu['settings']=$kaTranslate->translate("Pages:Settings");
		$menu['variables']=$kaTranslate->translate("Pages:Input variables");
		$menu['notifications']=$kaTranslate->translate("Pages:Notifications");
		$menu['followup']=$kaTranslate->translate("Pages:Follow-up");
		$menu['code']=$kaTranslate->translate("Pages:Conversion code");
		foreach($menu as $ka=>$m) {
			if(!isset($_POST['activetab'])) $_POST['activetab']=$ka;
			echo '<li><a href="#" onclick="showTab(\''.$ka.'\');" id="tab_'.$ka.'" class="'.($_POST['activetab']==$ka?'sel':'').'">'.$m.'</a></li>';
			}
		?>
		</ul>
		</div>
	</div>

<div id="imgcontents">
	<br />
	<form action="?idpag=<?= $_GET['idpag']; ?>" method="post" id="checkthis">
	<input type="hidden" name="activetab" value="<?= $_POST['activetab']; ?>" id="activetab" />
	<?php 
	/*******************************/
	/* SETTINGS                    */
	/*******************************/
	?>
	<div id="panel_settings">
		<?php 
		$values=array(0,1,2,3);
		$labels=array($kaTranslate->translate('Pages:None'),$kaTranslate->translate('Pages:Moderate requests'),$kaTranslate->translate('Pages:Double opt-in'),$kaTranslate->translate('Pages:CAPTCHA'));
		echo b3_create_select("moderate",$kaTranslate->translate('Pages:Moderation')." ",$labels,$values,$page['conversions']['moderate']);
		?><br /><br />
		<?php  if($kaUsers->canIUse('members')) { ?>
			<?= b3_create_input("create_member","checkbox",$kaTranslate->translate('Pages:Save as member'),"y","","",($page['conversions']['create_member']==1?'checked':'')); ?><br />
			<table class="tabella">
			<tr><th rowspan="4" style="padding:0 20px;"><?= $kaTranslate->translate('Pages:Options'); ?><br />
					<small><?= $kaTranslate->translate('Pages:Leave blank if you don\'t know what to do'); ?></small></td>
				<td><label for="create_member_username"><?= $kaTranslate->translate('Pages:Username format'); ?></label></td><td><?= b3_create_input("create_member_username","text","",$page['conversions']['create_member_username']); ?> <small>%n = <?= $kaTranslate->translate('Pages:Name'); ?>, %u = <?= $kaTranslate->translate('Pages:Surname'); ?>, %N = <?= $kaTranslate->translate('Pages:First letter of the name'); ?>, %U = <?= $kaTranslate->translate('Pages:First letter of the surname'); ?>, %e = <?= $kaTranslate->translate('Pages:E-mail'); ?>, </small></td></tr>
			<tr><td><label for="create_member_password"><?= $kaTranslate->translate('Pages:Password format'); ?></label></td><td><?= b3_create_input("create_member_password","text",'',$page['conversions']['create_member_password']); ?> <small>%d = <?= $kaTranslate->translate('Pages:Random number'); ?>, %s = <?= $kaTranslate->translate('Pages:Random letter'); ?></small></td></tr>
			<tr><td><label for="create_member_expiration"><?= $kaTranslate->translate('Pages:Expiration'); ?></label></td><td><?= b3_create_input("create_member_expiration","text",'',$page['conversions']['create_member_expiration']); ?> <small><?= $kaTranslate->translate('Pages:Number of days. Leave blank for no expiration.'); ?></small></td></tr>
			<tr><td><label for="create_member_affiliation"><?= $kaTranslate->translate('Pages:Affiliation'); ?></label></td><td><?= b3_create_input("create_member_affiliation","text",'',$page['conversions']['create_member_affiliation']); ?></td></tr>
			</table>
			<br />
			<br />
			<?php  } ?>
		<?php  if($kaUsers->canIUse('private')) { ?>
			<?= b3_create_input("private_dir","checkbox",$kaTranslate->translate('Pages:Create a private directory called as the username'),"y","","",($page['conversions']['private_dir']==1?'checked':'')); ?><br /><br />
			<?php  } ?>
		<?php  if($kaUsers->canIUse('newsletter')) { ?>
		<table width="100%">
			<tr>
				<td width="50%"><label><?= $kaTranslate->translate('Pages:Subscribe to the following newsletters'); ?></label><br />
					<?php 
					require_once('../../newsletter/newsletter.lib.php');
					$kaNewsletter=new kaNewsletter();
					$i=0;
					foreach($kaNewsletter->getNewslettersList() as $l) {
						echo b3_create_input("newsletters_add[".$l['idlista']."]","checkbox",$l['lista'],"y","","",(strpos($page['conversions']['newsletters_add'],",".$l['idlista'].",")!==false?'checked':'')).'<br />';
						$i++;
						}
					if($i==0) echo $kaTranslate->translate('Pages:No newsletters available');
					?></td>
				<td width="50%"><label><?= $kaTranslate->translate('Pages:Unsubscribe from the following newsletters'); ?></label><br />
					<?php 
					require_once('../../newsletter/newsletter.lib.php');
					$kaNewsletter=new kaNewsletter();
					$i=0;
					foreach($kaNewsletter->getNewslettersList() as $l) {
						echo b3_create_input("newsletters_remove[".$l['idlista']."]","checkbox",$l['lista'],"y","","",(strpos($page['conversions']['newsletters_remove'],",".$l['idlista'].",")!==false?'checked':'')).'<br />';
						$i++;
						}
					if($i==0) echo $kaTranslate->translate('Pages:No newsletters available');
					?></td>
				</tr>
			</table>
			<?php  } ?>
		</div>

	<?php 
	/*******************************/
	/* VARIABLES                   */
	/*******************************/
	$select_values=array();
	$select_values[]='-';
	$select_values[]='name';
	$select_values[]='surname';
	$select_values[]='email';
	$select_values[]='username';
	$select_values[]='password';
	$select_values[]='affiliation';
	$select_values[]='expiration';
	$select_labels=array();
	$select_labels[]=$kaTranslate->translate('');
	$select_labels[]=$kaTranslate->translate('Pages:Name');
	$select_labels[]=$kaTranslate->translate('Pages:Surname');
	$select_labels[]=$kaTranslate->translate('Pages:E-mail');
	$select_labels[]=$kaTranslate->translate('Pages:Username');
	$select_labels[]=$kaTranslate->translate('Pages:Password');
	$select_labels[]=$kaTranslate->translate('Pages:Affiliation');
	$select_labels[]=$kaTranslate->translate('Pages:Expiration');
	?>
	<div id="panel_variables">
		<table class="tabella">
			<tr><th><?= $kaTranslate->translate('Pages:Variable name'); ?></th>
				<th><?= $kaTranslate->translate('Pages:Correspondence'); ?></th>
				<th></th><th></th></tr>
			<?php  if(isset($page['conversions']['variables'][0])) {
				for($i=0;isset($page['conversions']['variables'][$i]);$i++) { ?>
					<tr><td><?= b3_create_input("variable_name[]","text","",$page['conversions']['variables'][$i]['variable_name'],"200px"); ?></td>
						<td><?= b3_create_select("variable_correspondence[]","",$select_labels,$select_values,$page['conversions']['variables'][$i]['correspondence']); ?></td>
						<td><?= b3_create_input("variable_mandatory[".$i."]","checkbox",$kaTranslate->translate('UI:mandatory'),"y","","",($page['conversions']['variables'][$i]['mandatory']=="y"?'checked':''),true); ?></td>
						<td>
							<img src="<?= ADMINRELDIR; ?>img/add.png" onclick="duplicateLine(this.parentNode.parentNode);" />
							<img src="<?= ADMINRELDIR; ?>img/del.png" onclick="removeLine(this.parentNode.parentNode);" />
							</td></tr>
					<?php  }
				}
			else { ?>
				<tr><td><?= b3_create_input("variable_name[]","text","","","100px"); ?></td>
					<td><?= b3_create_select("variable_correspondence[]","",$select_labels,$select_values,"-"); ?></td>
					<td><?= b3_create_input("variable_mandatory[]","checkbox",$kaTranslate->translate('UI:mandatory'),"y","","","",true); ?></td>
					<td>
						<img src="<?= ADMINRELDIR; ?>img/add.png" onclick="duplicateLine(this.parentNode.parentNode);" />
						<img src="<?= ADMINRELDIR; ?>img/del.png" onclick="removeLine(this.parentNode.parentNode);" />
						</td></tr>
				<?php  } ?>
			</table>
		</div>

	<?php 
	/*******************************/
	/* NOTIFICATIONS               */
	/*******************************/
	?>
	<div id="panel_notifications">
		<table width="100%"><tr>
			<td>
				<?php 
				$values=array("self","admin","custom");
				$labels=array($kaTranslate->translate('Pages:Subscriber'),$kaTranslate->translate('Pages:Admin'),$kaTranslate->translate('Pages:Custom'));
				$selected=$page['conversions']['notification_from']!='self'&&$page['conversions']['notification_from']!='admin'?$selected='custom':$selected=$page['conversions']['notification_from'];
				echo b3_create_select("notification_from",$kaTranslate->translate('Pages:Notifications sender')." ",$labels,$values,$selected,"auto","",'onchange="switchCustomSender(this)"');
				?>
				<span style="visibility:<?= $selected=='custom'?'visible':'hidden'; ?>;">&nbsp;&nbsp;<?php 
				$value=($selected=='custom'?$page['conversions']['notification_from']:'');
				echo b3_create_input("notification_custom","text",$kaTranslate->translate('Pages:E-mail')." ",$value,"200px");
				?></span><br />
				<br />
				<?= b3_create_input("notification_subject","text",$kaTranslate->translate('Pages:Subject')." ",$page['conversions']['notification_subject'],"400px"); ?><br />
				<?= b3_create_textarea("notification_text",$kaTranslate->translate('Pages:Notification e-mail'),$page['conversions']['notification_text'],"99%","220px",true,false,TABLE_PAGINE,1); ?>
				<small><strong><?= $kaTranslate->translate('Pages:Placeholders'); ?>:</strong> {NAME} {SURNAME} {USERNAME} {PASSWORD} {EMAIL} {AFFILIATION} {EXPIRATION} {ANY_INPUT_VARIABLE}...</small>

				</td>
			<td style="width:230px;"><label for="notification_emails"><?= $kaTranslate->translate('Pages:Notify to this e-mails'); ?> (<em><?= $kaTranslate->translate('Pages:one for line'); ?></em>)</label><br />
				<textarea id="notification_emails" name="notification_emails" style="width:230px;height:300px;"><?= $page['conversions']['notification_emails']; ?></textarea>
				</td>
			</tr></table>
		</div>

	<?php 
	/*******************************/
	/* FOLLOW-UP                   */
	/*******************************/
	?>
	<div id="panel_followup">
		<?php 
		if($page['conversions']['followup_from']=='') $page['conversions']['followup_from']=ADMIN_MAIL;
		echo b3_create_input("followup_from","text",$kaTranslate->translate('Pages:Sender')." ",$page['conversions']['followup_from'],"250px");
		?><br />
		<br />
		<?= b3_create_input("followup_subject","text",$kaTranslate->translate('Pages:Subject')." ",$page['conversions']['followup_subject'],"400px"); ?><br />
		<?= b3_create_textarea("followup_text",$kaTranslate->translate('Pages:Follow-up e-mail'),$page['conversions']['followup_text'],"99%","250px",true,false,TABLE_CONFIG,1); ?>

		<small><strong><?= $kaTranslate->translate('Pages:Placeholders'); ?>:</strong> {NAME} {SURNAME} {USERNAME} {PASSWORD} {EMAIL} {AFFILIATION} {EXPIRATION} {ANY_INPUT_VARIABLE}...</small>
		</div>

	<?php 
	/*******************************/
	/* CONVERSION CODE             */
	/*******************************/
	?>
	<div id="panel_code">
		<table><tr>
		<td style="padding-right:6px;">
			<?= b3_create_textarea("conversion_code",$kaTranslate->translate('Pages:Conversion code'),b3_lmthize($page['conversions']['conversion_code'],"textarea"),"480px","300px",true,false,TABLE_CONFIG,1); ?>
			</td>
		<td>
			<?= b3_create_textarea("fail_code",$kaTranslate->translate('Pages:Fail code'),b3_lmthize($page['conversions']['fail_code'],"textarea"),"480px","300px",true,false,TABLE_CONFIG,1); ?>
			</td>
		</tr></table>
		<small><strong><?= $kaTranslate->translate('Pages:Placeholders'); ?>:</strong> {NAME} {SURNAME} {USERNAME} {PASSWORD} {EMAIL} {AFFILIATION} {EXPIRATION} {ANY_INPUT_VARIABLE}...</small>
		</div>

	<script type="text/javascript">showTab('<?= $_POST['activetab']; ?>');</script>
	<br />
	<div class="submit">
		<?php 
		if(isset($showResultMsg)) {
			if($showResultMsg==true) echo '<div id="MsgSuccess">'.$kaTranslate->translate('Pages:Changes successfully saved').'</div>';
			else echo '<div id="MsgAlert">'.$kaTranslate->translate('Pages:Errors occurred while saving').'</div>';
			?>
			<script type="text/javascript">
				function hideResults() {
					var elm=null;
					if(document.getElementById('MsgSuccess')) elm=document.getElementById('MsgSuccess');
					if(document.getElementById('MsgAlert')) elm=document.getElementById('MsgAlert');
					elm.parentNode.removeChild(elm,true);
					}
				setTimeout(hideResults,3000);
				</script>
			<?php 
			}
			?>
		<input name="update" type="submit" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" />
		</div>
	</form>
	</div>

<script type="text/javascript">
	var txts=new kInitZenEditor;
	txts.init('<?= addslashes(ADMINDIR); ?>');
	</script>

</body>
</html>
