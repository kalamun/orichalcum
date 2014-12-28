<?php 
session_start();
if(!isset($_SESSION['iduser'])) die();

require_once('../main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("check-permissions"=>false, "x-frame-options"=>"") );

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
