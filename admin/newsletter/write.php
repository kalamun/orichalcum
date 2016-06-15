<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Newsletter:Newsletter");
require_once("../inc/head.inc.php");

require_once("./newsletter.lib.php");
$kaNewsletter=new kaNewsletter();

?><script type="text/javascript" src="js/newsletter.js"></script><?php 


/* ACTIONS */
if(isset($_POST['send']) && isset($_POST['subject']) && isset($_POST['message']))
{
	$log="";
	
	if(empty($_POST['subject'])) $_POST['subject'] = $kaImpostazioni->getVar('sitename',1);

	// collect all message parts: if only one block is defined (the default one) save it as string, otherwise serialize an array of blocks
	$message = array();
	$blocks = array();
	foreach($_POST as $k=>$v)
	{
		if(substr($k,0,6)=="block-") $message[substr($k,6)] = b3_htmlize($v, false);
	}
	
	if(count($message)>0) $message['-default-'] = b3_htmlize($_POST['message'],false);
	else $message = b3_htmlize($_POST['message'],false);
	
	// convert local URLs into absolute URLs, adding the site name at the start
	if(is_array($message))
	{
		foreach($message as $k=>$v)
		{
			$message[$k] = str_replace('="/', '="'.SITE_URL.'/', $message[$k]);
			// remove html comments
			$message[$k] = preg_replace("/\<\!--.*?--\>/s", "", $message[$k]);
		}
	} else {
		$message = str_replace('="/','="'.SITE_URL.'/',$message);
		$message = preg_replace("/\<\!--.*?--\>/s", "", $message);
	}

	$config = $kaNewsletter->getConfig();

	// compose email
	$mail=array();
	$mail['subject'] = b3_htmlize($config['prefix']." ".$_POST['subject'],false,"");
	$mail['message'] = serialize($message);
	$mail['from'] = $config['from'];
	
	if(!isset($_POST['template'])) $_POST['template'] = "";
	$mail['template'] = $_POST['template'];

	// get the recipients
	if(!isset($_POST['idlista'])) $_POST['idlista']=array();
	$lists=array();
	foreach($_POST['idlista'] as $k=>$v) { $lists[]=$k; }
	$recipients=$kaNewsletter->getRecipients(array("lists"=>$lists, "groupby"=>"email", "mandatary"=>array("email"), "conditions"=>"`status`='act'"));

	// save into archive
	$vars=array();
	$vars['subject']=b3_htmlize($_POST['subject'],false,"");
	$vars['from']=$mail['from'];
	$vars['message']=$mail['message'];
	$vars['template']=$mail['template'];
	$vars['recipients_number']=count($recipients);
	$idarch=$kaNewsletter->addToArchive($vars);

	//save all e-mails of selected lists in the mail queue
	if($idarch==false) $log=$kaTranslate->translate('Newsletter:Error while archiving message. No e-mail sent');
	else {
		foreach($recipients as $member)
		{
			$vars['idarch']=$idarch;

			$vars['to']=$member['name'].' <'.$member['email'].'>';

			//reload message
			$vars['message']=$mail['message'];
			//add at the end of the message the placeholders' values that will be processed by email.lib.php when sending message.
			$vars['mergevars']=array();
			$vars['mergevars']['NAME']=$member['name'];
			$vars['mergevars']['EMAIL']=$member['email'];
			$vars['mergevars']['USERNAME']=$member['username'];
			$vars['mergevars']['PASSWORD']=$member['password'];
			$vars['mergevars']['EXPIRATION']=$member['expiration'];
			$vars['mergevars']['AFFILIATION']=$member['affiliation'];
			
			$kaNewsletter->addToQueue($vars);
		}
	}

	if($log!="")
	{
		echo '<div id="MsgAlert">'.$kaTranslate->translate($log).'</div>';
		$kaLog->add("ERR",'Newsletter: Error while sending e-mail (archiviation)');
	} else {
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Newsletter:E-mail added to queue').'</div>';
		$kaLog->add("INS",'Newsletter: New e-mail added to queue. Subject: <em>'.$mail['subject'].'</em>');
		$queueCount=$kaNewsletter->getQueueCount();
		?><script type="text/javascript">processQueue();</script><?php 
	}

} elseif(isset($_POST['savedraft'])) {
	$kaImpostazioni->setParam("email_draft",$_POST['subject'],$_POST['message'],"--");
	echo '<div id="MsgSuccess">'.$kaTranslate->translate('Newsletter:Draft successfully saved').'</div>';
	
} elseif(isset($_POST['loaddraft'])) {
	$_POST['subject']=$kaImpostazioni->getVar('email_draft',1,"--");
	$_POST['message']=$kaImpostazioni->getVar('email_draft',2,"--");

} elseif(isset($_POST['deletedraft'])) {
	$kaImpostazioni->setParam("email_draft","","","--");
	echo '<div id="MsgSuccess">'.$kaTranslate->translate('Newsletter:Draft successfully deleted').'</div>';
	
}

/* ACTIONS END */


if(isset($_GET['import']))
{
	$arch = $kaNewsletter->getFromArchive(array("idarch"=>$_GET['import']));
	$_POST['subject'] = $arch['titolo'];
	
	$_POST['message'] = $arch['testo']['-default-'];
	foreach($arch['testo'] as $k=>$v)
	{
		if($k=='' || $k=='-default-') continue;
		$_POST['block-'.$k] = $v;
	}
	
	$_POST['template'] = $arch['template'];
}
if(!isset($_POST['subject'])) $_POST['subject']="";
if(!isset($_POST['message'])) $_POST['message']="";
if(!isset($_POST['template'])) $_POST['template']="";

?>

<h1><?= $kaTranslate->translate('Newsletter:Write an e-mail'); ?></h1>

<?php 
$queueCount=$kaNewsletter->getQueueCount();
if($queueCount>0&&!(isset($_POST['send'])&&$_POST['subject']!=""&&$_POST['message']!="")) { ?>
	<br />
	<div class="box alert">
		<div style="display:inline-block;margin-right:20px;"><h3><?= $kaTranslate->translate('Newsletter:E-mail queue'); ?></h3>
			<?= $kaTranslate->translate('Newsletter:There are %d e-mails in queue',$queueCount); ?></div>
		<div style="display:inline-block;vertical-align:top;margin-right:20px;"><input type="button" onclick="processQueue();" value="<?= $kaTranslate->translate('Newsletter:Process queue'); ?>" class="button" /></div>
		<div style="clear:both;"></div>
	</div>
	<?php  }
?>

<br />

<form action="?" method="post">
	<div class="subset">
		<fieldset class="box">
			<h2><?= $kaTranslate->translate("Newsletter:Template"); ?></h2>
			<ul id="templatesList">
			<?php 
			/* templates */
			$default = $_POST['template'];
			
			foreach($kaNewsletter->getTemplatesList() as $i=>$template)
			{
				if(empty($default) && !empty($template['default'])) $default = $template['filename'];
				?>
				<li class="<?= $default == $template['filename'] ? "selected" : ""; ?>" data-template="<?= $template['filename']; ?>">
					<input type="radio" name="template" value="<?= $template['filename']; ?>" id="template<?= $i; ?>" <?= $default == $template['filename'] ? 'checked':''; ?>>
					<label for="template<?= $i; ?>">
						<?= $template['label']; ?><br>
						<small><?= $value[] = $template['filename']; ?></small>
					</label>
				</li>
				<?php
			}
			?>
		</fieldset><br />
		<script type="text/javascript">
			for(var i=0, c=document.getElementById('templatesList').getElementsByTagName('LI'); c[i]; i++)
			{
				kAddEvent(c[i], "click", loadTemplate);
			}
		</script>
	</div>
	
<div class="topset">
	<?= b3_create_input("subject","text",$kaTranslate->translate("Newsletter:Subject").' ',$_POST['subject'],"300px",250); ?>
	<br /><br />
	<?= b3_create_textarea("message",$kaTranslate->translate("Newsletter:Message").'<br />',$_POST['message'],"100%","250px",RICH_EDITOR); ?>
	<div id="additionalBlocks"><?php
		foreach($kaNewsletter->getTextBlocksFromTemplate($default) as $block)
		{
			if($block=="") continue;
			if(!isset($_POST['block-'.$block])) $_POST['block-'.$block]="";
			?><div data-block="<?= $block; ?>"><br><?= b3_create_textarea("block-".$block, $kaTranslate->translate("Newsletter:".$block).'<br />', $_POST['block-'.$block], "100%", "200px", RICH_EDITOR); ?></div><?php
		}
	?></div>
	<br />
	<br />
	
	<fieldset class="box"><legend><?= $kaTranslate->translate('Newsletter:Which lists to send?'); ?></legend>
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
	<br /><br />
	</div>


	<div class="submit">
		<input type="button" value="<?= $kaTranslate->translate('Newsletter:Preview'); ?>" onclick="openPreviewPopup();" class="button" />
		<input type="submit" name="send" onclick="processQueue();" value="<?= $kaTranslate->translate('Newsletter:Send newsletter'); ?>" class="button" />
	</div>
	<div class="note">
		<input type="submit" name="savedraft" value="<?= htmlentities($kaTranslate->translate('Newsletter:Save draft')); ?>" class="smallbutton">

		<?php
		if($kaImpostazioni->getVar("email_draft",2,"--")!="" || $kaImpostazioni->getVar("email_draft",1,"--")!="") { ?>
			<input type="submit" name="loaddraft" value="<?= htmlentities($kaTranslate->translate('Newsletter:Load draft')); ?>" class="smallbutton" onclick="return confirm('<?= htmlentities($kaTranslate->translate('Newsletter:Loading draft you will overwrite current contents: do you want to proceed?')); ?>');">
			<input type="submit" name="deletedraft" value="<?= htmlentities($kaTranslate->translate('Newsletter:Delete draft')); ?>" class="smallalertbutton" onclick="return confirm('<?= htmlentities($kaTranslate->translate('Newsletter:Do you really want to delete saved draft?')); ?>');">
		<?php }
		?>
	</div>

</form>

<?php  include_once("../inc/foot.inc.php"); 