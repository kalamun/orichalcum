<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Newsletter:Lists management");
require_once("../inc/head.inc.php");
require_once("./newsletter.lib.php");
require_once("../members/members.lib.php");

$kaNewsletter=new kaNewsletter();
$kaMembers=new kaMembers();


/* ACTIONS */

/* CREATE A NEW LIST */
if(isset($_POST['insert'])&&$_POST['lista']!="") {
	if($kaNewsletter->addList(array("listname"=>$_POST['lista'],"description"=>$_POST['descr']))) {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate("Newsletter:Successfully created").'</div>';
		$kaLog->add("INS",'Newsletter: Added list <em>'.$_POST['lista'].'</em>');
		}
	else {
		echo '<div id="MsgAlert">'.$kaTranslate->translate('Newsletter:Error while creating list').'</div>';
		$kaLog->add("ERR",'Newsletter: Error while creating list <em>'.$_POST['lista'].'</em>');
		}
	}

/* UPDATE A LIST */
elseif(isset($_POST['update'])&&$_POST['idlista']!=""&&$_POST['lista']!="") {
	if($kaNewsletter->updateList(array("idlista"=>$_POST['idlista'],"listname"=>$_POST['lista'],"description"=>$_POST['descr']))) {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate("Newsletter:Successfully updated").'</div>';
		$kaLog->add("UPD",'Newsletter: Updated list <em>'.$_POST['lista'].' (ID:'.$_POST['idlista'].')</em>');
		}
	else {
		echo '<div id="MsgAlert">'.$kaTranslate->translate('Newsletter:Error while updating list').'</div>';
		$kaLog->add("ERR",'Newsletter: Error while updating list <em>'.$_POST['lista'].' (ID:'.$_POST['idlista'].')</em>');
		}
	}

/* DELETE A LIST */
elseif(isset($_GET['delete'])&&isset($_GET['idlista'])&&!isset($_GET['idmember'])&&isset($_GET['confirm'])) {
	$log="";
	$vars=array();
	$vars['idlista']=$_GET['idlista'];
	if(isset($_POST['move_subscribers'])) $vars['move_to']=$_POST['destination'];
	if(!$kaNewsletter->deleteList($vars)) $log="Newsletter:Error while saving";

	if($log=="") {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate("Newsletter:Successfully removed").'</div>';
		$kaLog->add("DEL",'Newsletter: Removed list <em>ID '.$_GET['idlista'].'</em>');
		}
	else {
		echo '<div id="MsgAlert">'.$kaTranslate->translate('Newsletter:Error while deleting list').'</div>';
		$kaLog->add("ERR",'Newsletter: Error while creating list <em>'.$_POST['lista'].'</em>');
		}

	unset($_GET['delete']);
	unset($_GET['idlista']);
	}

/* REMOVE A MEMBER FROM A NEWSLETTER */
elseif(isset($_GET['delete'])&&isset($_GET['idmember'])&&isset($_GET['idlista'])) {
	$log="";
	$user=$kaMembers->getUserById($_GET['idmember']);

	if(isset($user['newsletter_lists'])) {
		$newsletter_lists=str_replace(",".$_GET['idlista'].",",",",$user['newsletter_lists']);

		if(!$kaMembers->updateNewsletter($user['idmember'],$newsletter_lists)) $log="Newsletter:Error while saving";
		}
	else $log="Newsletter:User not found";

	if($log=="") echo '<div id="MsgSuccess">'.$kaTranslate->translate('Newsletter:User successfully removed').'</div>';
	else echo '<div id="MsgAlert">'.$kaTranslate->translate($log).'</div>';

	unset($_GET['delete']);
	unset($_GET['idmember']);
	}

/* END ACTIONS */


?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>

<?
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
	<? }
?>


<br />
<?
if(isset($_GET['delete'])&&isset($_GET['idlista'])&&!isset($_GET['idmember'])&&!isset($_GET['confirm'])) {
	$list=$kaNewsletter->getNewslettersList(array("idlista"=>$_GET['idlista']));
	$lists=$kaNewsletter->getNewslettersList();
	if(isset($list[0])) {
		$list=$list[0];
		?>
		<div class="topset">
			<form action="?delete&idlista=<?= $_GET['idlista']; ?>&confirm" method="post">
				<h2><?= $kaTranslate->translate('Newsletter:You are going to delete the following list'); ?>: <em><?= $list['lista']; ?></em></h2>
				<br />
				<? if($list['subscribers_number']>0) { ?>
					<?= $kaTranslate->translate('Newsletter:What do you want to do of its subscribers?'); ?>
					<br /><br />
					<? } ?>

				<? if($list['subscribers_number']>0) { ?>
					<div class="submit"><input type="submit" name="remove_subscribers" value="<?= $kaTranslate->translate('Newsletter:Remove all subscribers'); ?>" class="alertbutton" /></div>
					<br /><br />

					<div class="submit">
						<?
						foreach($lists as $l) {
							if($l['idlista']!=$_GET['idlista']) {
								$option[]=$l['lista'].' ('.$l['ll'].')';
								$value[]=$l['idlista'];
								}
							}
						echo b3_create_select("destination",$kaTranslate->translate('Newsletter:Move here')." ",$option,$value);
						?>
						<input type="submit" name="move_subscribers" value="Sposta" class="button"/>
						</div>
					<br /><br />
					<? }

				else { ?>
					<div class="submit"><input type="submit" name="remove_subscribers" value="<?= $kaTranslate->translate('UI:Delete'); ?>" class="alertbutton" /></div>
						<br /><br />
					<? } ?>

				<div style="text-align:center;"><input type="button" value="Annulla" class="button" onclick="window.location.href='?';" /></div>

				</form>
			</div>
		<? }
	else {
		unset($_GET['idlista']);
		}
	}

elseif(isset($_GET['edit'])&&isset($_GET['idlista'])&&!isset($_GET['idmember'])) {
	$list=$kaNewsletter->getNewslettersList(array("idlista"=>$_GET['idlista']));
	if(isset($list[0])) {
		$list=$list[0];
		?>
		<div class="topset">
		<h2><?= $kaTranslate->translate('Newsletter:Edit a list'); ?></h2><br />
		<form action="?" method="post">
			<input type="hidden" name="idlista" value="<?= $_GET['idlista']; ?>" />
			<?= b3_create_input("lista","text",$kaTranslate->translate('Newsletter:List name').'<br />',b3_lmthize($list['lista'],"input"),"300px",250); ?>
			<br /><br />
			<?= b3_create_textarea("descr",$kaTranslate->translate('Newsletter:Description'),b3_lmthize($list['descr'],"textarea"),"500px","100px",RICH_EDITOR); ?>
			<br /><br />
			<div class="submit">
				<input type="button" value="<?= $kaTranslate->translate('UI:Back'); ?>" class="smallbutton" onclick="window.location.href='?';" />
				<input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" />
				</div>
			</form>
			</div>
		<? }
	else {
		unset($_GET['idlista']);
		}
	}

if(!isset($_GET['delete'])&&!isset($_GET['edit'])&&!isset($_GET['idlista'])) {
	?>
	<table border="0" cellpadding="2" cellspacing="1" class="tabella">
		<tr>
			<th><?= $kaTranslate->translate('Newsletter:Lists'); ?></th>
			<th><?= $kaTranslate->translate('Newsletter:Description'); ?></th>
			<th><?= $kaTranslate->translate('Newsletter:Subscribers'); ?></th>
			<th><?= $kaTranslate->translate('Newsletter:Actions'); ?></th>
			</tr>
		<?
		$i=0;
		foreach($kaNewsletter->getNewslettersList(array("ll"=>$_SESSION['ll'])) as $list) { ?>
			<tr class="<?= ($i%2==0?'odd':'even'); ?>">
			<td><a href="?idlista=<?= $list['idlista']; ?>"><strong><?= $list['lista']; ?></strong></a></td>
			<td class="descr"><?= $list['descr']; ?></td>
			<td class="count"><?= $list['subscribers_number']; ?></td>
			<td>
				<a href="?idlista=<?= $list['idlista']; ?>" class="smallbutton"><?= $kaTranslate->translate('Newsletter:Show subscribers'); ?></a>
				<a href="?edit&idlista=<?= $list['idlista']; ?>" class="smallbutton"><?= $kaTranslate->translate('Newsletter:Edit'); ?></a>
				<a href="?delete&idlista=<?= $list['idlista']; ?>" class="smallalertbutton"><?= $kaTranslate->translate('Newsletter:Delete'); ?></a>
				</td>
			</tr>
			<?
			$i++;
			}
		?>
		</table>

	<br /><br />

	<div style="float:left;">
		<input type="button" value="<?= $kaTranslate->translate('Newsletter:Add a new list'); ?>" class="button" onclick="document.getElementById('newlist').style.display='block';"/>
		</div>

	<div id="newlist" style="float:left;display:none;width:500px;margin-left:30px;" class="box">
		<h1><?= $kaTranslate->translate('Newsletter:Add a new list'); ?></h1><br />
		<form action="?" method="post">
		<?= b3_create_input("lista","text",$kaTranslate->translate('Newsletter:List name').'<br />',"","300px",250); ?>
		<br /><br />
		<?= b3_create_textarea("descr",$kaTranslate->translate('Newsletter:Description'),"","100%","100px",RICH_EDITOR); ?>
		<br /><br />
		<div class="submit"><input type="submit" name="insert" value="<?= $kaTranslate->translate('Newsletter:Create list'); ?>" class="button" /></div>
		</form>
		</div>

	<div style="clear:both;"></div>

	<?
	}


elseif(!isset($_GET['delete'])&&!isset($_GET['edit'])) { ?>


	<?
	if(!isset($_GET['orderby'])||$_GET['orderby']=="") $_GET['orderby']='name';
	$orderby=array();
	$orderby['name']="`name`,`email` DESC";
	$orderby['email']="`email`,`name` DESC";
	$orderby['date']="`expiration` DESC,`name`,`email`";

	$lists=$kaNewsletter->getNewslettersList(array("idlista"=>$_GET['idlista']));
	if(isset($lists[0])) {
		$list=$lists[0];

		?><h2><?= $list['lista']; ?>
			<small><a href="?" class="smallbutton">&lt; <?= $kaTranslate->translate('UI:Back'); ?></a></small>
			</h2>
		<br />
		<table class="tabella">
			<tr>
				<th><a href="?idlista=<?= $_GET['idlista']; ?>&orderby=name"><?= $kaTranslate->translate('Newsletter:Full name'); ?></a></th>
				<th><a href="?idlista=<?= $_GET['idlista']; ?>&orderby=email"><?= $kaTranslate->translate('Newsletter:E-mail address'); ?></a></th>
				<th><a href="?idlista=<?= $_GET['idlista']; ?>&orderby=date"><?= $kaTranslate->translate('Newsletter:Expiration date'); ?></a></th>
				<th><?= $kaTranslate->translate('Newsletter:Actions'); ?></th>
				</tr>
			<?
			$i=0;
			$members=$kaNewsletter->getRecipients(array("lists"=>array($_GET['idlista']),"orderby"=>$orderby[$_GET['orderby']]));
			if($members!=false) {
				foreach($members as $m) {
					?>
					<tr class="<?= $i%2==0?'odd':'even'; ?>">
					<td><strong><?= $m['name']; ?></strong></td>
					<td><?= $m['email']; ?></td>
					<td><?= trim($m['expiration'],"0-: ")!=""?preg_replace("/(\d{4})-(\d{2})-(\d{2}) (\d{2}:\d{2}).*/","$3-$2-$1 $4",$m['expiration']):$kaTranslate->translate('Newsletter:Never'); ?></td>
					<td>
						<a href="subscribe.php?email=<?= urlencode($m['email']); ?>" class="smallbutton"><?= $kaTranslate->translate('Newsletter:Edit subscriptions'); ?></a>
						<a name="row<?= $i; ?>" href="?delete&idmember=<?= $m['idmember']; ?>&idlista=<?= $_GET['idlista']; ?>&orderby=<?= $_GET['orderby']; ?>#row<?= $i; ?>" class="smallalertbutton" onclick="return confirm('<?= $kaTranslate->translate('Newsletter:Are you sure you want to delete this user?'); ?>');"><?= $kaTranslate->translate('UI:Delete'); ?></a>
						</td>
					</tr>
					<?
					$i++;
					}
				} ?>
		</table>
		<br /><br />

		<a href="subscribe.php" class="smallbutton"><?= $kaTranslate->translate('Newsletter:Add a new subscriber'); ?></a>

		<? }
	}


include_once("../inc/foot.inc.php");
?>
