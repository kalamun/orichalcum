<?
session_start();
if(!isset($_SESSION['iduser'])) die();
$title=isset($_POST['subject'])?$_POST['subject']:'Preview';
?>

<div id="iPopUpHeader">
	<h1><?= $title; ?>&nbsp;</h1>
	<a href="javascript:kCloseIPopUp();" class="closeWindow"><img src="../img/closeWindow.gif" alt="Close" width="9" height="9" /></a>
	</div>

<div style="position:absolute; top:28px; left:0; right:0; bottom:0; overflow:hidden;">
	<iframe src="ajax/preview.php" style="width:100%;height:100%;" >
	</div>
