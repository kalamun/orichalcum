<?php
session_start();
if(!isset($_SESSION['iduser'])) die("You don't have permissions to access this informations");

require_once('../../inc/connect.inc.php');
require_once('../../inc/kalamun.lib.php');
require_once('../../inc/sessionmanager.inc.php');
require_once('../../users/users.lib.php');
require_once('../../inc/main.lib.php');

/* set default timezone in PHP and MySQL */
$timezone=kaGetVar('timezone',1);
if($timezone!="") {
	date_default_timezone_set($timezone);
	$query="SET time_zone='".date("P")."'";
	mysql_query($query);
	}

$kaUsers=new kaUsers();
if(!$kaUsers->canIUse('members')) die("You don't have permissions to access this informations");

require_once('../members.lib.php');
$kaMembers=new kaMembers();

if(isset($_GET['csv'])) {
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private",false);
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"members_".date("Y-m-d").".csv\";");
	header("Content-Transfer-Encoding: Binary"); 
	
	echo "Name\t";
	echo "E-mail\t";
	echo "Username\t";
	echo "Password\t";
	echo "Affiliation\n";
	
	foreach($kaMembers->getUsersList() as $m) {
		echo $m['name']."\t";
		echo $m['email']."\t";
		echo $m['username']."\t";
		echo $m['password']."\t";
		echo $m['affiliation']."\n";
		}
	
	}
?>