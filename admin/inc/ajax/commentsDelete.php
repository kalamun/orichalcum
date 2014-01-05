<?
session_start();
if(isset($_SESSION['iduser'])&&isset($_POST['idcomm'])&&is_numeric($_POST['idcomm'])) {
	include_once('../connect.inc.php');
	include_once('../kalamun.lib.php');

	/* set default timezone in PHP and MySQL */
	$timezone=kaGetVar('timezone',1);
	if($timezone!="") {
		date_default_timezone_set($timezone);
		$query="SET time_zone='".date("P")."'";
		mysql_query($query);
		}

	require_once("../log.lib.php");
	$kaLog=new kaLog();

	$log="";
	$query="DELETE FROM ".TABLE_COMMENTI." WHERE idcomm='".$_POST['idcomm']."'";
	if(!mysql_query($query)) {
		$kaLog->add("ERR",'Errore nella cancellazione del commento numero <em>'.$_POST['idcomm'].'</em>');
		echo "Error removing comment";
		}
	else {
		$kaLog->add("UPD",'Cancellato il commento numero <em>'.$_POST['idcomm'].'</em>');
		}
	}
?>
