<?php 
session_start();
if(!isset($_SESSION['iduser'])) die();

include('../../inc/connect.inc.php');
include('../../inc/kalamun.lib.php');
include('../../inc/main.lib.php');
include('../../inc/metadata.lib.php');

/* set default timezone in PHP and MySQL */
$timezone=kaGetVar('timezone',1);
if($timezone!="") {
	date_default_timezone_set($timezone);
	$query="SET time_zone='".date("P")."'";
	ksql_query($query);
	}

$kaMetadata=new kaMetadata();
$p=$kaMetadata->get($_POST['t'],$_POST['id'],$_POST['p']);
?>

<div id="iPopUpHeader">
	<h1>Modifica meta-dato</h1>
	<a href="javascript:kCloseIPopUp();" class="closeWindow"><img src="<?= ADMINDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	</div>

<div style="padding:20px;">
	<label for="newMDparam">Parametro</label> <?= $_POST['p']; ?><br />
	<br />
	<label for="newMDvalue">Valore</label><br />
	<textarea name="newMDvalue" id="newMDvalue" style="width:100%;height:180px;"><?= $p['value']; ?></textarea><br />
	<br />
	<div class="submit"><input type="button" value="Salva" class="button" onclick="kaMetadataSave('<?= $_POST['t']; ?>',<?= $_POST['id']; ?>,'<?= addslashes($_POST['p']); ?>',document.getElementById('newMDvalue').value)" /></div>
	</div>
