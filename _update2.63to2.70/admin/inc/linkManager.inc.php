<?php /* (c) Kalamun.org - GNU/GPL 3 */
require_once('./connect.inc.php');
require_once('kalamun.lib.php');
require_once('./sessionmanager.inc.php');
require_once('./main.lib.php');
$kaTranslate=new kaAdminTranslate();
if(!isset($_SESSION['iduser'])) die('Non hai il permesso di utilizzare questa funzione');

if(!isset($_GET['addBefore'])) $_GET['addBefore']="";
if(!isset($_GET['addAfter'])) $_GET['addAfter']="";
if(!isset($_GET['href'])) $_GET['href']="";
if(!isset($_GET['nofollow'])) $_GET['nofollow']="";
if(!isset($_GET['title'])) $_GET['title']="";
if(!isset($_GET['target'])) $_GET['target']="";
if(!isset($_GET['class'])) $_GET['class']="";
if(!isset($_POST['addBefore'])) $_POST['addBefore']="";
if(!isset($_POST['addAfter'])) $_POST['addAfter']="";
if(!isset($_POST['href'])) $_POST['href']="";
if(!isset($_POST['nofollow'])) $_POST['nofollow']="";
if(!isset($_POST['title'])) $_POST['title']="";
if(!isset($_POST['target'])) $_POST['target']="";
if(!isset($_POST['class'])) $_POST['class']="";

require_once('./log.lib.php');
$kaLog=new kaLog();

define("PAGE_NAME",$kaTranslate->translate('Link:Link Manager'));
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="it" lang="it">
<head>
<title><?php echo ADMIN_NAME." - ".PAGE_NAME; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="author" content="Roberto Pasini - www.kalamun.org" />
<meta name="copyright" content="no(c)" />
<style type="text/css">
	@import "<?php echo ADMINDIR; ?>css/screen.css";
	@import "<?php echo ADMINDIR; ?>css/main.lib.css";
	@import "<?php echo ADMINDIR; ?>css/docmanager.css";
	</style>

<script type="text/javascript">var ADMINDIR='<?php echo str_replace("'","\'",ADMINDIR); ?>';</script>
<script type="text/javascript" src="<?php echo ADMINDIR; ?>js/kalamun.js"></script>
</head>

<body>

<div id="docheader">
	<h1>Collegamento</h1>
	<a href="javascript:window.parent.k_closeIframeWindow();" class="closeWindow"><img src="<?= ADMINRELDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	</div>
<div id="doccontents">
	<?php 
	 if(isset($_POST['save'])) {
		?>
		<script type="text/javascript">
			window.parent.txts.getArea('<?= $_GET['refid']; ?>').setLink('<?= $_POST['addBefore']; ?>','','<?= str_replace("'","\'",$_POST['href']); ?>','<?= str_replace("'","\'",$_POST['title']); ?>','<?= str_replace("'","\'",$_POST['target']); ?>','<?= str_replace("'","\'",$_POST['class']); ?>','<?= str_replace("'","\'",$_POST['nofollow']); ?>');
			window.parent.k_closeIframeWindow();
			</script>
		<?php  }
	elseif(isset($_POST['remove'])) {
		?>
		<script type="text/javascript">
			window.parent.txts.getArea('<?= $_GET['refid']; ?>').removeLink();
			window.parent.k_closeIframeWindow();
			</script>
		<?php  }
	elseif(isset($_POST['update'])) {
		?>
		<script type="text/javascript">
			window.parent.txts.getArea('<?= $_GET['refid']; ?>').updateLink('<?= $_POST['addBefore']; ?>','','<?= str_replace("'","\'",$_POST['href']); ?>','<?= str_replace("'","\'",$_POST['title']); ?>','<?= str_replace("'","\'",$_POST['target']); ?>','<?= str_replace("'","\'",$_POST['class']); ?>','<?= str_replace("'","\'",$_POST['nofollow']); ?>');
			window.parent.k_closeIframeWindow();
			</script>
		<?php  }

	else { ?>
	<form action="?refid=<?= $_GET['refid']; ?>" method="post">
		<table style="margin:0 auto;">
			<tr><td><label for="href"><?= $kaTranslate->translate('Link:Target URL'); ?></label></td><td><?= b3_create_input("href","text","",$_GET['href'],"500px"); ?></td></tr>
			<tr><td><label for="title"><?= $kaTranslate->translate('Link:Description'); ?></label></td><td><?= b3_create_input("title","text","",$_GET['title'],"300px"); ?></td></tr>
			<tr><td>&nbsp;</td><td><?= b3_create_input("nofollow","checkbox",$kaTranslate->translate('Link:Don\'t follow'),"1","","",($_GET['nofollow']=="true"?'checked':'')); ?></td></tr>
			<tr><td><label for="target"><?= $kaTranslate->translate('Link:Open mode'); ?></label></td><td><?= b3_create_select("target","",array($kaTranslate->translate('Link:Default'),$kaTranslate->translate('Link:_blank'),$kaTranslate->translate('Link:_top')),array("","_blank","_top"),$_GET['target']); ?></td></tr>
			<tr><td><label for="class"><?= $kaTranslate->translate('Link:CSS class'); ?></label></td><td><?= b3_create_input("class","text","",$_GET['class'],"100px"); ?></td></tr>
			</table>
		<br />
		<div class="submit">
			<?php  if($_GET['href']!=""&&$_GET['href']!="http://"&&$_GET['href']!="mailto") { ?>
				<input type="submit" name="update" value="<?= $kaTranslate->translate('Link:Save changes'); ?>" class="button" />
				<input type="submit" name="remove" value="<?= $kaTranslate->translate('Link:Remove link'); ?>" class="smallbutton" />
				<?php  }
			else { ?>
				<input type="submit" name="save" value="<?= $kaTranslate->translate('Link:Create link'); ?>" class="button" />
				<?php  } ?>
			</div>
		</form>
		<?php  } ?>
	</div>
</body>
</html>
