<?php session_start();
if(!isset($_SESSION['username'])) die();

include('../../inc/connect.inc.php');
if(!isset($_POST['username'])||trim($_POST['username'])=="") echo 'false';
else {
	$query="SELECT count(*) AS tot FROM ".TABLE_USERS." WHERE username='".$_POST['username']."'";
	$results=ksql_query($query);
	$row=ksql_fetch_array($results);
	if($row['tot']>0) echo 'true';
	}
