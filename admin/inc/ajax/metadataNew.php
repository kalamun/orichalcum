<?
session_start();
if(!isset($_SESSION['iduser'])) die();

include('../../inc/connect.inc.php');
include('../../inc/kalamun.lib.php');
include('../../inc/main.lib.php');
include('../../inc/metadata.lib.php');

$kaMetadata=new kaMetadata();
?>

<div id="iPopUpHeader">
	<h1>Nuovo meta-dato</h1>
	<a href="javascript:kCloseIPopUp();" class="closeWindow"><img src="<?= ADMINDIR; ?>img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	</div>

<div style="padding:20px;">
	<label for="newMDparam">Parametro</label> <input type="text" name="newMDparam" id="newMDparam" value="" style="width:200px;" maxlength="124" /><br />
	<br />
	<label for="newMDvalue">Valore</label><br />
	<textarea name="newMDvalue" id="newMDvalue" style="width:100%;height:180px;"></textarea><br />
	<br />
	<div class="submit"><input type="button" value="Salva" class="button" onclick="kaMetadataSave('<?= $_POST['t']; ?>',<?= $_POST['id']; ?>,document.getElementById('newMDparam').value,document.getElementById('newMDvalue').value)" /></div>
	</div>
</table>