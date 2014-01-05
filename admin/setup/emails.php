<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Setup:E-mails and newsletters");
include_once("../inc/head.inc.php");
include_once("../inc/template.lib.php");


/* AZIONI */
if(isset($_POST['update'])) {
	if(!isset($_POST['email_log'][1])||$_POST['email_log'][1]!="true") $_POST['email_log'][1]="false";
	$kaImpostazioni->setParam("email_log",$_POST['email_log'][1],$_POST['email_log'][2],"*");
	$kaImpostazioni->setParam("email-queue-mailperhour",$_POST['email-queue-mailperhour'],"","*");

	if(!isset($_POST['email_smtp_on'][1])||$_POST['email_smtp_on'][1]!="true") $_POST['email_smtp_on'][1]="false";
	$kaImpostazioni->setParam("email_smtp_on",$_POST['email_smtp_on'][1],"","*");
	$kaImpostazioni->setParam("email_smtp_server",$_POST['email_smtp_server'][1],$_POST['email_smtp_server'][2],"*");
	$kaImpostazioni->setParam("email_smtp_account",$_POST['email_smtp_account'][1],$_POST['email_smtp_account'][2],"*");

	$kaImpostazioni->setParam("newsletter_mittente",$_POST['sender'][1],$_POST['sender'][2]);
	$kaImpostazioni->setParam("newsletter_pretitolo",$_POST['pretitle'][1],"");
	$kaImpostazioni->setParam("newsletter_footer",$_POST['footer'][1],"");
	$kaImpostazioni->setParam("newsletter_benvenuto",$_POST['optin'][1],$_POST['optin'][2]);
	$kaImpostazioni->setParam("newsletter_addio",$_POST['optout'][1],$_POST['optout'][2]);
	
	$kaLog->add("UPD",'Setup:Successfully updated e-mail settings');
	echo '<div id="MsgSuccess">'.$kaTranslate->translate('Setup:Successfully saved').'</div>';
	}
/**/

$sender=$kaImpostazioni->getParam('newsletter_mittente');
$pretitle=$kaImpostazioni->getParam('newsletter_pretitolo');
$footer=$kaImpostazioni->getParam('newsletter_footer');
$optin=$kaImpostazioni->getParam('newsletter_benvenuto');
$optout=$kaImpostazioni->getParam('newsletter_addio');

?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<form action="?" method="post">
	<table>
	<tr><td colspan="2"><?= b3_create_input("email_log[1]","checkbox",$kaTranslate->translate('Setup:Track sent mail'),"true","","",($kaImpostazioni->getVar('email_log',1,"*")=="true"?'checked':'')); ?></td></tr>
	<tr><td><label for="email_log2"><?= $kaTranslate->translate('Setup:Max logged records'); ?></label></td><td><?= b3_create_input("email_log[2]","text","",b3_lmthize($kaImpostazioni->getVar('email_log',2,"*"),"input"),"100px",250); ?>
		<small><?= $kaTranslate->translate('Setup:It helps to save space into database'); ?></small></td></tr>
	<tr><td><label for="email_queue_mailperhour"><?= $kaTranslate->translate('Setup:Mail per hour limit'); ?></label></td><td><?= b3_create_input("email-queue-mailperhour","text","",b3_lmthize($kaImpostazioni->getVar('email-queue-mailperhour',1,"*"),"input"),"100px",10); ?>
		<small><?= $kaTranslate->translate('Setup:A lower value helps to not be blacklisted'); ?></small></td></tr>
	</table>
	<br /><br />
	
	<table>
	<tr><td colspan="2"><?= b3_create_input("email_smtp_on[1]","checkbox",$kaTranslate->translate('Setup:Send via SMTP instead of mail()'),"true","","",($kaImpostazioni->getVar('email_smtp_on',1,"*")=="true"?'checked':'')); ?></td></tr>
	<tr><td><label for="email_smtp_server1"><?= $kaTranslate->translate('Setup:Host'); ?></label></td><td><?= b3_create_input("email_smtp_server[1]","text","",b3_lmthize($kaImpostazioni->getVar('email_smtp_server',1,"*"),"input"),"200px",250); ?></td></tr>
	<tr><td><label for="email_smtp_server2"><?= $kaTranslate->translate('Setup:Port'); ?></label></td><td><?= b3_create_input("email_smtp_server[2]","text","",b3_lmthize($kaImpostazioni->getVar('email_smtp_server',2,"*"),"input"),"50px",4); ?> <small>(<?= $kaTranslate->translate('Usually 25'); ?>)</small></td></tr>
	<tr><td><label for="email_smtp_account1"><?= $kaTranslate->translate('Setup:Username'); ?></label></td><td><?= b3_create_input("email_smtp_account[1]","text","",b3_lmthize($kaImpostazioni->getVar('email_smtp_account',1,"*"),"input"),"200px",250); ?></td></tr>
	<tr><td><label for="email_smtp_account2"><?= $kaTranslate->translate('Setup:Password'); ?></label></td><td><?= b3_create_input("email_smtp_account[2]","password","",b3_lmthize($kaImpostazioni->getVar('email_smtp_account',2,"*"),"input"),"200px",250); ?></td></tr>
	</table>
	<br /><br />

	<h2>Newsletter</h2>
	<br />
	<?= b3_create_input("pretitle[1]","text","Pre-titolo ",b3_lmthize($pretitle['value1'],"input"),"100px",32); ?><br />
	<br />
	<?= b3_create_textarea("footer[1]","Footer<br />",b3_lmthize($footer['value1'],"textarea"),"100%","100px",RICH_EDITOR); ?><br />
	<br />

	<h3>Mittente</h3>
	<?= b3_create_input("sender[1]","text","Nome ",b3_lmthize($sender['value1'],"input"),"200px",250); ?><br />
	<?= b3_create_input("sender[2]","text","E-mail ",b3_lmthize($sender['value2'],"input"),"300px",250); ?><br />
	<br /><br />

	<h3>E-mail di benvenuto</h3>
	<?= b3_create_input("optin[1]","text","Titolo ",b3_lmthize($optin['value1'],"input"),"300px",250); ?><br />
	<?= b3_create_textarea("optin[2]","Testo<br />",b3_lmthize($optin['value2'],"textarea"),"100%","150px",RICH_EDITOR); ?>
	<br /><br />

	<h3>E-mail di addio</h3>
	<?= b3_create_input("optout[1]","text","Titolo ",b3_lmthize($optout['value1'],"input"),"300px",250); ?><br />
	<?= b3_create_textarea("optout[2]","Testo<br />",b3_lmthize($optout['value2'],"textarea"),"100%","150px",RICH_EDITOR); ?>
	<br /><br />

	<br />
	<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button"></div>
	</form>
<br /><br />

<?
include_once("../inc/foot.inc.php");
?>
