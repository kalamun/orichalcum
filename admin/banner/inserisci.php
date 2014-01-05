<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Banner:Add a new banner");
include_once("../inc/head.inc.php");
include_once("./banner.lib.php");
$kaBanner=new kaBanner();

/* AZIONI */
if(isset($_POST['insert'])) {
	$log=$kaBanner->add($_FILES['banner'],$_POST['alt'],$_POST['description'],$_POST['url'],$_POST['idcat']);
	if($log==false) {
		$kaLog->add("ERR",'Banner: Error uploading a new banner: "'.$_POST['alt'].'"');
		echo '<div id="MsgAlert">'.$kaTranslate->translate('Banner:Error while uploading').'</div>';
		}
	else {
		$kaLog->add("INS",'Banner: Successfully added a new banner: "'.$_POST['alt'].'" (<em>ID: '.$log.'</em>)');
		echo '<div id="MsgSuccess">'.$kaTranslate->translate('Banner:Successfully uploaded').'</div>';
		echo '<meta http-equiv="refresh" content="0; url=modifica.php?idbanner='.$log.'">';
		include(ADMINRELDIR.'inc/foot.inc.php');
		die();
		}
	}
/* FINE AZIONI */


/* CONTROLLO FORM */
?>
<script type="text/javascript"><!--
	function checkForm() {
		if(f.alt=='') { alert('<?= addslashes($kaTranslate->translate('Banner:Please write the title')); ?>'); f.alt.focus(); return false; }
		return true;
	}
--></script>
<?
/* FINE CONTROLLO FORM */

?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<?
echo '<form name="insert" action="" method="post" enctype="multipart/form-data" onsubmit="return checkForm(f);">';

	?><div class="topset">
	
	<table width="700">
	<tr><th><label for="alt"><?= $kaTranslate->translate('Banner:Title'); ?></label></th><td><?= b3_create_input("alt","text","","","400px"); ?></td></tr>
	<tr><th><label for="description"><?= $kaTranslate->translate('Banner:Short description'); ?></label></th><td><?= b3_create_textarea("description","","","99%","100px",RICH_EDITOR); ?></td></tr>
	<tr><th><label for="banner"><?= $kaTranslate->translate('Banner:File'); ?></label></th><td><?= b3_create_input("banner","file","",""); ?></td></tr>
	<tr><th><label for="url"><?= $kaTranslate->translate('Banner:Target URL'); ?></label></th><td><?= b3_create_input("url","text","","http://","300px"); ?></td></tr>
	<tr><th><label><?= $kaTranslate->translate('Banner:Category'); ?></label></th><td>
		<div id="categorie">Loading...</div>
		</fieldset><br />
		<script type="text/javascript" src="./ajax/categorie.js"></script>
		<script type="text/javascript">k_reloadCat(0);</script>
		</td></tr>
	</table>

	<div class="submit"><input type="submit" name="insert" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" onclick="b3_openMessage('<?= addslashes($kaTranslate->translate('Banner:Saving...')); ?>');" /></div>
	</form>
</div><?

include_once("../inc/foot.inc.php");
?>
