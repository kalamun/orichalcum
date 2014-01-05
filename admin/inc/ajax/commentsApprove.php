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
	$query="SELECT public FROM ".TABLE_COMMENTI." WHERE idcomm='".$_POST['idcomm']."' LIMIT 1";
	$results=mysql_query($query);
	if($row=mysql_fetch_array($results)) {
		$query="UPDATE ".TABLE_COMMENTI." SET public='".($row['public']=='n'?'s':'n')."' WHERE idcomm='".$_POST['idcomm']."' LIMIT 1";
		if(!mysql_query($query)) {
			$kaLog->add("ERR",'Errore nella modifica dello stato di approvazione del commento numero <em>'.$_POST['idcomm'].'</em>');
			echo "Error approving comment";
			}
		else {
			$kaLog->add("UPD",'Approvato il commento numero <em>'.$_POST['idcomm'].'</em>');
			echo ($row['public']=='n'?'s':'n');
			}
		}
	}
?>
