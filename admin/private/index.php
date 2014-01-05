<?
/* (c) Kalamun.org - GNU/GPL 3 */

if(!isset($_GET['dir'])) $_GET['dir']="";
$_GET['dir']=trim($_GET['dir']," ./");
$_GET['dir']=str_replace("../","",$_GET['dir']);

require_once('../inc/config.inc.php');

// force download if is a file
if(isset($_GET['dir'])&&file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$_GET['dir'])&&!is_dir($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$_GET['dir'])) {
	require_once("../inc/connect.inc.php");
	require_once('../inc/kalamun.lib.php');
	require_once('../inc/sessionmanager.inc.php');
	require_once('../inc/main.lib.php');
	if(!isset($_SESSION['iduser'])) die('You are not allowed to use this page');
	require_once("./private.lib.php");
	$kaPrivate=new kaPrivate();
	$kaPrivate->forceDownload($_GET['dir']);
	die();
	}


define("PAGE_NAME","Private:Private Area");
include_once("../inc/head.inc.php");
include_once("./private.lib.php");
$kaPrivate=new kaPrivate();


/* ACTIONS */
$log="";
if(isset($_POST['mkdir'])&&isset($_POST['permissions'])) {
	$_POST['dir']=utf8_decode($_POST['dir']);
	$_GET['dir']=trim($_GET['dir']," ./");
	if(!isset($_POST['members'])) $_POST['members']=array();
	if(!isset($_POST['membersw'])) $_POST['membersw']=array();
	$kaPrivate->mkdir($_GET['dir'].'/'.$_POST['dir'],$_POST['permissions'],$_POST['members'],$_POST['permissionsw'],$_POST['membersw']);
	$kaPrivate->kaPrivate();
	}
elseif(isset($_POST['rename'])) {
	$_POST['oldname']=trim(utf8_decode($_POST['oldname'])," ./");
	$dir=dirname($_POST['oldname']);
	$_POST['newname']=trim(utf8_decode($_POST['newname'])," ./");
	if(!$kaPrivate->rename($_POST['oldname'],$dir.'/'.$_POST['newname'])) $log="Errors occurred while renaming";
	$kaPrivate->kaPrivate();
	}
elseif(isset($_GET['delete'])) {
	$_GET['dir']=trim($_GET['dir']," ./");
	$_GET['delete']=trim($_GET['delete']," ./");
	$kaPrivate->delete($_GET['dir'].'/'.$_GET['delete']);
	$kaPrivate->kaPrivate();
	}
elseif(isset($_POST['setPermissions'])) {
	$_POST['dir']=trim($_POST['dir']," ./");
	if(!isset($_POST['members'])) $_POST['members']=array();
	if(!isset($_POST['canWrite'])) $_POST['canWrite']=false;
	$kaPrivate->setPermissions($_POST['dir'],$_POST['permissions'],$_POST['members'],$_POST['permissionsw'],$_POST['membersw']);
	$kaPrivate->kaPrivate();
	}

/* END ACTIONS */

$dir=$kaPrivate->getDirContent($_GET['dir']);
?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?> / <?= str_replace("/"," / ",$dir['dirname']); ?>
	<small><?= $kaTranslate->translate('Private:'.$dir['permissions']['permissions']); ?><?= $dir['permissions']['inherited']?' <small>('.$kaTranslate->translate('Private:Inherited').')</small>':''; ?>
		<?
		if($dir['dirname']!="") { ?>
			<a onclick="kOpenIPopUp('ajax/changePermissions.php?dir=<?= urlencode(utf8_decode($dir['dirname'])); ?>','','900px','500px')" class="smallbutton"><?= $kaTranslate->translate('Private:Edit permissions'); ?></a>
			<? } ?>
		</small>
</h1>
<script type="text/javascript" src="./js/private.js"></script>
<br />
<?
if($dir['dirname']!="") { ?>
	<a href="?dir=<?= urlencode(utf8_decode($dir['parent'])); ?>" class="smallbutton"><?= $kaTranslate->translate('Private:Back to parent directory'); ?></a><br />
	<? }
?>
<br />
<table class="tabella">
<th></th><th></th><th><?= $kaTranslate->translate('Private:Size'); ?></th><th><?= $kaTranslate->translate('Private:Who can access'); ?></th>
<?
foreach($dir as $i=>$f) {
	if(is_numeric($i)) {
		?><tr class="<?= ($i%2==0?'even':'odd'); ?>"><?
		if(isset($f['dirname'])) { ?>
			<td class="dir">
			<a href="?dir=<?= urlencode($_GET['dir'].'/'.utf8_decode($f['dirname'])); ?>"><img src="img/folder<?= $f['permissions']['permissions'].($f['permissions']['writepermissions']==true?'w':''); ?>.png" width="16" height="16" alt="folder <?= $f['permissions']['permissions']; ?>" /> <?= $f['dirname']; ?></a></td>
			<td class="actions">
				<div>
					<a onclick="kOpenIPopUp('ajax/rename.php?dir=<?= urlencode($_GET['dir'].'/'.utf8_decode($f['dirname'])); ?>','','600px','400px')" class="smallbutton"><?= $kaTranslate->translate('Private:Rename'); ?></a>
					<a href="?dir=<?= urlencode($_GET['dir']); ?>&delete=<?= urlencode(utf8_decode($f['dirname'])); ?>" class="smallalertbutton" onclick="return confirm('<?= addslashes($kaTranslate->translate('Private:Are you sure? This operation is not reversible!')); ?>');"><?= $kaTranslate->translate('Private:Delete'); ?></a>
					</div>
				</td>
			<td class="size"><?
				if($f['size']['Kb']<1000) echo $f['size']['Kb'].' Kb';
				elseif($f['size']['Mb']<1000) echo $f['size']['Mb'].' Mb';
				else echo $f['size']['Gb'].' Gb';
				?></td>
			<td class="permissions">
				<?= $kaTranslate->translate('Private:'.$f['permissions']['permissions']); ?><?= $f['permissions']['inherited']?' <small>('.$kaTranslate->translate('Private:Inherited').')</small>':''; ?>
				<span><a onclick="kOpenIPopUp('ajax/changePermissions.php?dir=<?= urlencode($_GET['dir'].'/'.utf8_decode($f['dirname'])); ?>','','900px','500px')" class="smallbutton"><?= $kaTranslate->translate('Private:Edit'); ?></a></span>
				</td>
			<? }
		elseif(isset($f['filename'])) {
			$icon=file_exists('img/'.$f['extension'].'.png')?'img/'.$f['extension'].'.png':'img/_.png';
			?>
			<td class="file"><a href="?dir=<?= urlencode($_GET['dir'].'/'.utf8_decode($f['filename'])); ?>"><img src="<?= $icon; ?>" width="16" height="16" alt="folder" /> <?= $f['filename']; ?></a></td>
			<td class="actions">
				<div>
					<a onclick="kOpenIPopUp('ajax/rename.php?dir=<?= urlencode($_GET['dir'].'/'.utf8_decode($f['filename'])); ?>','','600px','400px')" class="smallbutton"><?= $kaTranslate->translate('Private:Rename'); ?></a>
					<a href="?dir=<?= urlencode($_GET['dir']); ?>&delete=<?= urlencode(utf8_decode($f['filename'])); ?>" class="smallalertbutton" onclick="return confirm('<?= addslashes($kaTranslate->translate('Private:Are you sure? This operation is not reversible!')); ?>');"><?= $kaTranslate->translate('Private:Delete'); ?></a>
					</div>
				</td>
			<td class="size"><?
				if($f['size']['Kb']<1000) echo $f['size']['Kb'].' Kb';
				elseif($f['size']['Mb']<1000) echo $f['size']['Mb'].' Mb';
				else echo $f['size']['Gb'].' Gb';
				?></td>
			<td class="permissions"><?= $kaTranslate->translate('Private:'.$f['permissions']['permissions']); ?><?= $f['permissions']['inherited']?' <small>('.$kaTranslate->translate('Private:Inherited').')</small>':''; ?></td>
			<? } ?>
			</tr><?
		}
	}
	?></table><br />
	<br />

<div class="submit">
	<a onclick="kOpenIPopUp('ajax/mkdir.php?dir=<?= urlencode($_GET['dir']); ?>','','900px','500px');" class="button"><?= $kaTranslate->translate('Private:New Folder'); ?></a>
	<a onclick="kOpenIPopUp('ajax/uploadFile.php?dir=<?= urlencode($_GET['dir']); ?>','','600px','400px');" class="button"><?= $kaTranslate->translate('Private:Upload one or more files'); ?></a>
	</div>


<?
include_once("../inc/foot.inc.php");
?>
