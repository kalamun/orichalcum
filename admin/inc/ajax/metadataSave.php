<?
session_start();
if(!isset($_SESSION['iduser'])) die();
if(!isset($_POST['t'])) die();
if(!isset($_POST['id'])) die();
if(!isset($_POST['p'])) die();
if(!isset($_POST['v'])) die();

include('../../inc/connect.inc.php');
include('../../inc/kalamun.lib.php');
include('../../inc/main.lib.php');
include('../../inc/metadata.lib.php');

/* set default timezone in PHP and MySQL */
$timezone=kaGetVar('timezone',1);
if($timezone!="") {
	date_default_timezone_set($timezone);
	$query="SET time_zone='".date("P")."'";
	mysql_query($query);
	}

if(get_magic_quotes_gpc()) $_POST['v']=stripslashes($_POST['v']);
if(get_magic_quotes_gpc()) $_POST['p']=stripslashes($_POST['p']);

$kaMetadata=new kaMetadata();
$kaMetadata->set($_POST['t'],$_POST['id'],$_POST['p'],$_POST['v']);
?>