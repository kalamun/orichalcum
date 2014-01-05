<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Newsletter:Newsletter");
require_once("../inc/head.inc.php");

require_once("./newsletter.lib.php");
$kaNewsletter=new kaNewsletter();

?><script type="text/javascript" src="js/newsletter.js"></script><?


/* ACTIONS */
if(isset($_POST['send'])&&$_POST['subject']!=""&&$_POST['message']!="") {
	$log="";

	$config=$kaNewsletter->getConfig();

	$mail=array();
	$mail['subject']=b3_htmlize($config['prefix']." ".$_POST['subject'],false,"");
	$mail['message']=b3_htmlize($_POST['message'],false);
	$mail['message']=str_replace('="/','="'.SITE_URL.'/',$mail['message']);
	$mail['from']=$config['from'];
	if(!isset($_POST['template'])) $_POST['template']="";
	$mail['template']=$_POST['template'];

	//get the recipients
	if(!isset($_POST['idlista'])) $_POST['idlista']=array();
	$lists=array();
	foreach($_POST['idlista'] as $k=>$v) { $lists[]=$k; }
	$recipients=$kaNewsletter->getRecipients(array("lists"=>$lists,"groupby"=>"email","mandatary"=>array("email")));

	//save into archive
	$vars=array();
	$vars['subject']=$mail['subject'];
	$vars['from']=$mail['from'];
	$vars['message']=$mail['message'];
	$vars['template']=$mail['template'];
	$vars['recipients_number']=count($recipients);
	$idarch=$kaNewsletter->addToArchive($vars);

	//save all e-mails of selected lists in the mail queue
	if($idarch==false) $log=$kaTranslate->translate('Newsletter:Error while archiving message. No e-mail sent');
	else {
		foreach($recipients as $member) {
			$vars['idarch']=$idarch;

			$vars['to']=$member['name'].' <'.$member['email'].'>';

			//reload message
			$vars['message']=$mail['message'];
			//replace placeholders
			$vars['message']=str_replace("{NAME}",$member['name'],$vars['message']);
			$vars['message']=str_replace("{EMAIL}",$member['email'],$vars['message']);
			$vars['message']=str_replace("{USERNAME}",$member['username'],$vars['message']);
			$vars['message']=str_replace("{PASSWORD}",$member['password'],$vars['message']);
			$vars['message']=str_replace("{AFFILIATION}",$member['affiliation'],$vars['message']);
			$vars['message']=str_replace("{EXPIRATION}",$member['expiration'],$vars['message']);

			$kaNewsletter->addToQueue($vars);
			}
		}
	if($log!="") {
		echo '<div id="MsgAlert">'.$kaTranslate->translate($log).'</div>';
		$kaLog->add("ERR",'Newsletter: Error while sending e-mail (archiviation)');
		}
	else {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Newsletter:E-mail added to queue').'</div>';
		$kaLog->add("INS",'Newsletter: New e-mail added to queue. Subject: <em>'.$mail['subject'].'</em>');
		$queueCount=$kaNewsletter->getQueueCount();
		?><script type="text/javascript">processQueue();</script><?
		}

	}


/* ACTIONS END */

if(isset($_GET['import'])) {
	$arch=$kaNewsletter->getFromArchive(array("idarch"=>$_GET['import']));
	$_POST['subject']=$arch['titolo'];
	$_POST['message']=$arch['testo'];
	$_POST['template']=$arch['template'];
	}
if(!isset($_POST['subject'])) $_POST['subject']="";
if(!isset($_POST['message'])) $_POST['message']="";
if(!isset($_POST['template'])) $_POST['template']="";

?>

<h1><?= $kaTranslate->translate('Newsletter:Write an e-mail'); ?></h1>

<?
$queueCount=$kaNewsletter->getQueueCount();
if($queueCount>0&&!(isset($_POST['send'])&&$_POST['subject']!=""&&$_POST['message']!="")) { ?>
	<br />
	<div class="box alert">
		<div style="display:inline-block;margin-right:20px;"><h3><?= $kaTranslate->translate('Newsletter:E-mail queue'); ?></h3>
			<?= $kaTranslate->translate('Newsletter:There are %d e-mails in queue',$queueCount); ?></div>
		<div style="display:inline-block;vertical-align:top;margin-right:20px;"><input type="button" onclick="processQueue();" value="<?= $kaTranslate->translate('Newsletter:Process queue'); ?>" class="button" /></div>
		<div style="clear:both;"></div>
		</div>
	<? }
?>

<br />

<form action="?" method="post">
	<div class="subset"><fieldset class="box"><?
		$label=array();
		$value=array();
		$default="";
		foreach($kaNewsletter->getTemplatesList() as $template) {
			$label[]=$template['label'];
			$value[]=$template['filename'];
			if($template['default']==true) $default=$template['filename'];
			}
		if($_POST['template']!="") $default=$_POST['template'];
		echo b3_create_select("template",$kaTranslate->translate("Newsletter:Template").' ',$label,$value,$default).'<br />';
		?></fieldset><br />
		</div>
	
<div class="topset">
	<?= b3_create_input("subject","text",$kaTranslate->translate("Newsletter:Subject").' ',$_POST['subject'],"300px",250); ?>
	<br /><br />
	<?= b3_create_textarea("message",$kaTranslate->translate("Newsletter:Message").'<br />',$_POST['message'],"99%","250px",RICH_EDITOR); ?>
	<br /><br />

	<fieldset class="box"><legend><?= $kaTranslate->translate('Newsletter:Which lists to send?'); ?></legend>
		<table><tr>
			<?
			$languages=$kaAdminMenu->getLanguages();
			foreach($languages as $lang) { ?>
				<td style="padding-right:50px;">
				<h3><?= $lang['lingua']; ?></h3>
				<?
				$lists=$kaNewsletter->getNewslettersList(array("ll"=>$lang['ll']));
				foreach($lists as $list) {
					echo b3_create_input("idlista[".$list['idlista']."]","checkbox",$list['lista'],"1").' <small>('.$list['subscribers_number'].')</small><br />';
					}
				?>
				</td>
				<? } ?>
			</tr></table>
		</fieldset>
	<br /><br />
	</div>


	<div class="submit">
		<input type="button" value="<?= $kaTranslate->translate('Newsletter:Preview'); ?>" onclick="openPreviewPopup();" class="button" />
		<input type="submit" name="send" onclick="processQueue();" value="<?= $kaTranslate->translate('Newsletter:Send newsletter'); ?>" class="button" />
		</div>

</form>

<? include_once("../inc/foot.inc.php"); ?>
