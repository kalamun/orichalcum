<?php 
require_once('../../inc/main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("check-permissions"=>false, "x-frame-options"=>"") );

require_once('../news.lib.php');
$kaNews=new kaNews();

define("PAGE_NAME", $kaTranslate->translate('News:Create a facebook post'));
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
	<h1><?= PAGE_NAME; ?></h1>
	<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
</div>

<div style="padding:20px;">

<?php 

if(!isset($_GET['id']))
{
	/* no id passed */
	?><div class="alert">Errore di sincronizzazione AJAX</div>
	<div style="text-align:center;"><br /><a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><?= $kaTranslate->translate('UI:Close'); ?></a></div>
	<?php 

} elseif($kaImpostazioni->getVar('facebook',1)!='s') {
	/* facebook inactive */
	?><div class="alert">Facebook non attivo</div>
	<div style="text-align:center;"><br /><a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><?= $kaTranslate->translate('UI:Close'); ?></a></div>
	<?php
	
} elseif(trim($kaImpostazioni->getVar('facebook-config',1))=='') {
	/* app_key is missing */
	?><div class="alert">Devi configurare l'App key sulla <a href="<?= ADMINDIR; ?>setup/news.php" target="_top">configurazione delle notizie</a></div>
	<div style="text-align:center;"><br /><a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><?= $kaTranslate->translate('UI:Close'); ?></a></div>
	<?php
	
} elseif(trim($kaImpostazioni->getVar('facebook-config',2))=='') {
	/* secret_key is missing */
	?><div class="alert">Devi configurare la Secret key sulla <a href="<?= ADMINDIR; ?>setup/news.php" target="_top">configurazione delle notizie</a></div>
	<div style="text-align:center;"><br /><a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><?= $kaTranslate->translate('UI:Close'); ?></a></div>
	<?php

} elseif(trim($kaImpostazioni->getVar('facebook-page',1))=='') {
    /* secret_key is missing */
	?><div class="alert">Devi configurare l'ID della pagina sulla <a href="<?= ADMINDIR; ?>setup/news.php" target="_top">configurazione delle notizie</a></div>
    <div style="text-align:center;"><br /><a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><?= $kaTranslate->translate('UI:Close'); ?></a></div>
    <?php

} else {
	/* all right! */
	$n=$kaNews->get($_GET['id']);
	$order=$kaImpostazioni->getVar('news-order',1);
	$order=str_replace(" DESC","",$order);
    $news_category = $n['categorie'][0]["dir"];
    $news_base_dir = $kaImpostazioni->getVar('dir_news',1);
    $news_dir = $n['dir'];
    $link=SITE_URL.BASEDIR.strtolower($_SESSION['ll']).'/'.$news_base_dir.'/'.$news_category.'/'.$news_dir;
	?>
	<form action="facebook_create_post.php?id=<?= $_GET['id']; ?>" method="post">
		<?= b3_create_textarea("fb_post_text", $kaTranslate->translate('News:Contents')."<br />", b3_lmthize($n['titolo'].($n['sottotitolo']!=""?' - '.$n['sottotitolo']:''), "textarea"), "100%", "200px"); ?><br>
		<br>
		<?= b3_create_input("link", "text", $kaTranslate->translate('News:News URL'), $link, "100%", "", "readonly") ?><br>
		<br>
		
		<div style="text-align:center">
			<a href="" onClick="window.open('https://developers.facebook.com/tools/debug/og/object?q=<?= urlencode($link); ?>','Windows','width=960,height=700,toolbar=no,menubar=no,scrollbars=no,resizable=no,location=no,directories=no,status=no');return false;"><?= $kaTranslate->translate('News:Link preview'); ?></a><br><small><?= $kaTranslate->translate('News:Scroll down until "When shared..."'); ?></small><br>
		</div>
		<br>
		<div class="submit" id="submit">
			<input type="submit" name="insert" class="button" value="<?= $kaTranslate->translate('News:Create post on your facebook page'); ?>" />
		</div>
	</form>
	<?php 
}
?>

	</div>

</body>
</html>
