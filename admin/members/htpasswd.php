<?
/* 2010 (c) Roberto Kalamun Pasini - GPLv3 */

define("PAGE_NAME","Aggiorna manualmente .htpasswd");
define("PAGE_LEVEL",1);
include_once("../inc/head.inc.php");
include_once("./members.lib.php");
$kaMembers=new kaMembers();

/* AZIONI */
if(isset($_POST['update'])) {
	$log="";
	$kaMembers->refreshHtpasswd();
	echo '<div id="MsgSuccess"><em>.htpasswd</em> aggiornato</div>';
	}
/* FINE AZIONI */

?>

	<h1><?= PAGE_NAME; ?></h1>
	<br />
	<form action="" method="post">
	<div class="submit"><input type="submit" name="update" value="Aggiorna" class="button"></div>
	</form>

<?
	
include_once("../inc/foot.inc.php");
?>
