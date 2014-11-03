<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Newsletter:Add a new subscriber");
require_once("../inc/head.inc.php");
require_once("./newsletter.lib.php");
require_once("../members/members.lib.php");

$kaNewsletter=new kaNewsletter();
$kaMembers=new kaMembers();

/* ACTIONS: save subscription */
if(isset($_POST['save'])) {
	$log="";

	//if member not exists, register it
	if(!isset($_POST['idmember'])) {
		$username=strtolower(preg_replace("/[^[:alnum:]]/","",$_POST['name']));
		if($username!="") {
			//if username exists, add a random number at the end
			if($kaMembers->getUserByUsername($username)) $username.=random(1000,9999);
			$_POST['idmember']=$kaMembers->add($_POST['name'],$_GET['email'],$username,"");
			}
		}

	if($_POST['idmember']=="") $log="Newsletter:Error while creating member";
	else {
		$lists=array();
		if(isset($_POST['idlista'])) {
			foreach($_POST['idlista'] as $idlista=>$true) { $lists[]=$idlista; }
			}
		if(!$kaNewsletter->subscribe($_POST['idmember'],$lists)) $log="Newsletter:Error while subscribing";
		}

	//welcome message
	if(isset($_POST['welcome'])) $kaNewsletter->sendWelcomeMessage($_POST['idmember']);

	if($log=="") {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate("Newsletter:Successfully saved").'</div>';
		$kaLog->add("UPD",'Newsletter: <em>member id '.$_POST['idmember'].'</em> successfully subscribed to <em>list ids '.implode(",",$_POST['idlista']).'</em>');
		}
	else {
		echo '<div id="MsgAlert">'.$kaTranslate->translate($log).'</div>';
		$kaLog->add("ERR",'Newsletter: Error subscribing <em>member id '.$_POST['idmember'].'</em> to <em>list ids '.implode(",",$_POST['idlista']).'</em>');
		}

	}
/* END ACTIONS */


?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>

<?php 
$queueCount=$kaNewsletter->getQueueCount();
if($queueCount>0) { ?>
	<br />
	<script type="text/javascript" src="js/newsletter.js"></script>
	<div class="box alert">
		<div style="display:inline-block;margin-right:20px;"><h3><?= $kaTranslate->translate('Newsletter:E-mail queue'); ?></h3>
			<?= $kaTranslate->translate('Newsletter:There are %d e-mails in queue',$queueCount); ?></div>
		<div style="display:inline-block;vertical-align:top;margin-right:20px;"><input type="button" onclick="processQueue();" value="<?= $kaTranslate->translate('Newsletter:Process queue'); ?>" class="button" /></div>
		<div style="clear:both;"></div>
		</div>
	<?php  }
?>

<br />
<?php  include('./subscribe_menu.inc.php'); ?>

<?php 
/* STEP 1: REQUEST E-MAIL ADDRESS */
if(!isset($_GET['email'])||$_GET['email']=="") { ?>
	<form action="" method="get">
			<?= b3_create_input("email","text",$kaTranslate->translate('Newsletter:E-mail address').' ',"","300px",250); ?>
			<br /><br />
			<div class="submit"><input type="submit" name="insert" value="<?= $kaTranslate->translate('Newsletter:Next step'); ?> &gt;" class="button"></div>
		</form>
	<?php  }

/* STEP 2: DISPLAY THE RIGHT FORM (depending by existence of the e-mail address in the members database) */
else { ?>
	<form action="?email=<?= urlencode($_GET['email']); ?>" method="post">
		<table>
			<tr><td><label for="email"><?= $kaTranslate->translate('Newsletter:E-mail address'); ?></label></td>
				<td><?= b3_create_input("email","text",'',$_GET['email'],"300px",250,'disabled="disabled"'); ?></td></tr>
			<?php 
			//if a user with this e-mail exists, show his data, otherwise show the form
			$members=$kaMembers->getUsersList(array("email"=>$_GET['email']));
			if(count($members)==0) { ?>
				<tr><td><label for="name"><?= $kaTranslate->translate('Newsletter:Full name'); ?></label></td>
					<td><?= b3_create_input("name","text",'',"","300px",250); ?></td></tr>
				<?php  }
			elseif(count($members)==1) {
				$m=$members[0];
				$selectedMember=$m['idmember'];
				?>
				<tr><td><label for="name"><?= $kaTranslate->translate('Newsletter:Full name'); ?></label></td>
					<td><?= b3_create_input("name","text",'',$m['name'],"300px",250,'disabled="disabled"'); ?>
						<input type="hidden" name="idmember" value="<?= $m['idmember']; ?>" /></td></tr>
				<?php  }
			else {
				$labels=array();
				$values=array();
				foreach($members as $m) {
					$labels[]=$m['name'];
					$values[]=$m['idmember'];
					if(!isset($selectedMember)) $selectedMember=$m['idmember'];
					}
				?>
				<tr><td><label for="name"><?= $kaTranslate->translate('Newsletter:Full name'); ?></label></td>
					<td><?= b3_create_select("idmember",'',$labels,$values,"","auto","",'onchange="updateSelectedLists(this.value)"'); ?></td></tr>
				<?php  } ?>
			</table>

		<br /><br />
		<fieldset class="box" id="lists"><legend><?= $kaTranslate->translate('Newsletter:Subscribed lists'); ?></legend>
			<table><tr>
				<?php 
				$languages=$kaAdminMenu->getLanguages();
				foreach($languages as $lang) { ?>
					<td style="padding-right:50px;">
					<h3><?= $lang['lingua']; ?></h3>
					<?php 
					$lists=$kaNewsletter->getNewslettersList(array("ll"=>$lang['ll']));
					foreach($lists as $list) {
						echo b3_create_input("idlista[".$list['idlista']."]","checkbox",$list['lista'],"1").' <small>('.$list['subscribers_number'].')</small><br />';
						}
					?>
					</td>
					<?php  } ?>
				</tr></table>
			</fieldset>
		<script type="text/javascript">
			/* select the right checkboxes according to the selected user */
			var selected=Array();
			<?php 
			if(count($members)>0) {
				foreach($members as $m) { ?>
					selected[<?= $m['idmember']; ?>]='<?= $m['newsletter_lists']; ?>';
					<?php  }
				}
			?>
			function updateSelectedLists(id) {
				//uncheck all
				var checks=document.getElementById('lists').getElementsByTagName('INPUT');
				for(var i=0;checks[i];i++) {
					checkid=parseInt(checks[i].id.substring(7));
					if(selected[id]&&selected[id].indexOf(','+checkid+',')>=0) checks[i].checked=true;
					else checks[i].checked=false;
					}
				}

			<?php  if(isset($selectedMember)) echo 'updateSelectedLists('.$selectedMember.');'; ?>

			</script>
		<br /><br />

		<div class="submit">
			<input type="button" value="<?= $kaTranslate->translate('UI:Cancel'); ?>" class="smallalertbutton" onclick="window.location='?';">
			<input type="submit" name="save" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button">
			<?= b3_create_input("welcome","checkbox",$kaTranslate->translate('Newsletter:Send the welcome message'),"false","","","checked"); ?>
			</div>
	</form>
	<?php  } ?>

<?php  include_once("../inc/foot.inc.php"); 