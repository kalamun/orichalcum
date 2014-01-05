<?
session_start();

require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../inc/main.lib.php');
if(!isset($_SESSION['iduser'])) die('Non hai il permesso di utilizzare questa funzione');

/* set default timezone in PHP and MySQL */
$timezone=kaGetVar('timezone',1);
if($timezone!="") {
	date_default_timezone_set($timezone);
	$query="SET time_zone='".date("P")."'";
	mysql_query($query);
	}

require_once('../../inc/log.lib.php');
$kaLog=new kaLog();

require_once('../../inc/config.lib.php');
$kaConfig=new kaImpostazioni();

require_once('../news.lib.php');
$kaNews=new kaNews();

define("PAGE_NAME","Facebook");
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
<title><?php echo ADMIN_NAME." - ".PAGE_NAME; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<style type="text/css">
	@import "<?php echo ADMINDIR; ?>css/screen.css";
	@import "<?php echo ADMINDIR; ?>css/main.lib.css";
	</style>

<script type="text/javascript">var ADMINDIR='<?php echo str_replace("'","\'",ADMINDIR); ?>';</script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/kalamun.js"></script>
</head>

<body>

<div id="iPopUpHeader">
	<h1>Crea un evento su facebook</h1>
	<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	</div>

<div style="padding:20px;">

<?

if(!isset($_GET['id'])) {
	/* no id passed */
	?><div class="alert">Errore di sincronizzazione AJAX</div>
	<div style="text-align:center;"><br /><a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow">chiudi</a></div>
	<? }

elseif($kaConfig->getVar('facebook',1)!='s') {
	/* facebook inactive */
	?><div class="alert">Facebook non attivo</div>
	<div style="text-align:center;"><br /><a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow">chiudi</a></div>
	<? }

elseif(trim($kaConfig->getVar('facebook-config',1))=='') {
	/* app_key is missing */
	?><div class="alert">Devi configurare l'App key sulla <a href="<?= ADMINDIR; ?>impostazioni/news.php">configurazione delle notizie</a></div>
	<div style="text-align:center;"><br /><a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow">chiudi</a></div>
	<? }

elseif(trim($kaConfig->getVar('facebook-config',2))=='') {
	/* secret_key is missing */
	?><div class="alert">Devi configurare la Secret key sulla <a href="<?= ADMINDIR; ?>impostazioni/news.php">configurazione delle notizie</a></div>
	<div style="text-align:center;"><br /><a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow">chiudi</a></div>
	<? }

else { /* all right! */
	$n=$kaNews->get($_GET['id']);
	$order=$kaConfig->getVar('news-order',1);
	$order=str_replace(" DESC","",$order);
	$data_start=mktime(substr($n[$order],11,2),substr($n[$order],14,2),substr($n[$order],17,2),substr($n[$order],5,2),substr($n[$order],8,2),substr($n[$order],0,4));
	$data_end=$data_start+1800;
	?>
	<form action="facebook_create_event.php?id=<?= $_GET['id']; ?>" method="post">
	<table>
	<tr><td><label for="name">Name</label></td><td class="title"><?= b3_create_input("name","text","",b3_lmthize($n['titolo'].($n['sottotitolo']!=""?' - '.$n['sottotitolo']:''),"input"),"450px",255); ?></td></tr>
	<tr><td><label for="description">Description</label></td><td><textarea name="description" id="description" class="arial" style="width:450px;height:100px;"><?= trim(strip_tags(str_replace("<br />","<br />\n\n",str_replace("</p>","</p>\n\n",str_replace("\n","",$n['anteprima'].'<p></p>'.$n['testo']))))); ?></textarea></td></tr>
	<tr><td></td><td>
		<label for="start_time">Start time</label> <?= b3_create_input("start_time","text","",date("d-m-Y H:i:s",$data_start),"120px",255); ?> -
		<label for="end_time">End time</label> <?= b3_create_input("end_time","text","",date("d-m-Y H:i:s",$data_end),"120px",255); ?>
		<br /><br /></td></tr>
	<tr><td><label for="location">Location</label></td><td><?= b3_create_input("location","text","",b3_lmthize($kaConfig->getVar('facebook-location',1),"input"),"120px",255); ?></td></tr>
	<tr><td><label for="street">Street</label></td><td><?= b3_create_input("street","text","",b3_lmthize($kaConfig->getVar('facebook-address',1),"input"),"200px",255); ?></td></tr>
	<tr><td><label for="city">City</label></td><td><?= b3_create_input("city","text","",b3_lmthize($kaConfig->getVar('facebook-address',2),"input"),"150px",255); ?>
			<label for="state">State</label> <?= b3_create_input("state","text","",b3_lmthize($kaConfig->getVar('facebook-country',1),"input"),"50px",255); ?></td></tr>
	<tr><td><label for="country">Country</label></td><td><?= b3_create_input("country","text","",b3_lmthize($kaConfig->getVar('facebook-country',2),"input"),"150px",255); ?></td></tr>
	<tr><td><label for="phone">Phone</label></td><td><?= b3_create_input("phone","text","",b3_lmthize($kaConfig->getVar('facebook-contacts',1),"input"),"120px",255); ?></td></tr>
	<tr><td><label for="email">E-mail</label></td><td><?= b3_create_input("email","text","",b3_lmthize($kaConfig->getVar('facebook-contacts',2),"input"),"200px",255); ?></td></tr>
	</table>

	<br />
	<div class="submit" id="submit">
		<input type="submit" name="insert" class="button" value="Crea evento" />
		</div>
	</form>
	<?
	}
	?>

	</div>

</body>
</html>
