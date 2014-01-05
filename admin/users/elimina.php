<?
define("PAGE_NAME","Users:Delete user");
include_once("../inc/head.inc.php");
require_once("./users.lib.php");
$kaUsers=new kaUsers();

/* AZIONI */
if(isset($_GET['iduser'])&&is_numeric($_GET['iduser'])) {
	$log="";
	$u=$kaUsers->getUserFromId($_GET['iduser']);
	if(!$kaUsers->del($_GET['iduser'])) $log="Errore durante l'eliminazione dell'utente";
	
	if($log=="") {
		echo '<div id="MsgSuccess">Utente eliminato con successo</div>';
		$kaLog->add("DEL",'Users: Deleted user <em>'.$u['name'].'</em> - '.$u['username'].' (<em>ID: '.$u['iduser'].'</em>)');
		}
	else {
		$kaLog->add("ERR",'Users: Error deleting <em>'.$u['name'].'</em> - '.$u['username'].' (<em>ID: '.$u['iduser'].'</em>)');
		echo '<div id="MsgAlert">'.$log.'</div>';
		}
	}
/* FINE AZIONI */

?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<script type="text/javascript">
	function selectMenuRef(useUser) {
		document.getElementById('useUser').value=useUser;
		k_openIframeWindow(ADMINDIR+"inc/selectMenuRef.inc.php","450px","500px");
		}
	function selectElement(id,where) {
		var useUser=document.getElementById('useUser').value;
		var get="";
		if(String(window.location).indexOf("search=")>-1) {
			get=String(window.location);
			get=get.replace(/.*search=/,"");
			get="search="+get.replace(/^[[^\d]*].*/,"");
			}
		var url=String(window.location).replace(/\?.*/,"");
		window.location=url+'?useUser='+useUser+'&addtomenu='+id+','+where+'&'+get;
		}
	</script>

<div class="topset">
	<input type="hidden" id="useUser" />
	<table class="tabella">
	<tr><th><?= $kaTranslate->translate('Users:User'); ?></th><th><?= $kaTranslate->translate('Users:Username'); ?></th><th><?= $kaTranslate->translate('Users:Created on'); ?> / <?= $kaTranslate->translate('Users:Last log-in'); ?></th></tr><?php
	foreach($kaUsers->getUsersList() as $ka=>$v) {
		if($_SESSION['iduser']==$v['iduser']) { ?>
			<tr>
			<td><h2><?= $v['name']; ?></h2>
				<small><?= $kaTranslate->translate('Users:You can\'t delete yourself!'); ?></small>
				</td>
			<td class="percorso"><?= $v['username']; ?></td>
			<td class="percorso"><?= $v['created_leggibile']; ?><br /><?= $v['lastlogin_leggibile']; ?></td>
			</tr>
			<? }
		else {
			?>
			<tr>
			<td><h2><a href="?iduser=<?= $v['iduser']; ?>" onclick="return confirm('<?= $kaTranslate->translate('Users:Are you sure you want to permanently delete this user?'); ?>');"><?= $v['name']; ?></a></h2>
				<small class="actions"><a href="?iduser=<?= $v['iduser']; ?>" onclick="return confirm('<?= $kaTranslate->translate('Users:Are you sure you want to permanently delete this user?'); ?>');"><?= $kaTranslate->translate('Users:Delete'); ?></a></small>
				</td>
			<td class="percorso"><a href="?iduser=<?= $v['iduser']; ?>" onclick="return confirm('<?= $kaTranslate->translate('Users:Are you sure you want to permanently delete this user?'); ?>');"><?= $v['username']; ?></a></td>
			<td class="percorso"><a href="?iduser=<?= $v['iduser']; ?>" onclick="return confirm('<?= $kaTranslate->translate('Users:Are you sure you want to permanently delete this user?'); ?>');"><?= $v['created_leggibile']; ?><br /><?= $v['lastlogin_leggibile']; ?></a></td>
			</tr>
			<? }
		}
	?></table>
	</div>
	<?

include_once("../inc/foot.inc.php");
?>
