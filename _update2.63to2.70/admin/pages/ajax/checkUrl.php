<?php session_start();
include('../../inc/connect.inc.php');
if(!isset($_POST['url'])||trim($_POST['url'])=="") echo 'false';
else {
	$query="SELECT count(*) AS tot FROM ".TABLE_PAGINE." WHERE dir='".$_POST['url']."' AND ll='".$_SESSION['ll']."'";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
	if($row['tot']>0) echo 'true';
	else {
		$query="SELECT count(*) AS tot FROM ".TABLE_LANDINGPAGE." WHERE dir='".$_POST['url']."' AND ll='".$_SESSION['ll']."'";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if($row['tot']>0) echo 'true';
		else {
			$query="SELECT count(*) AS tot FROM ".TABLE_THANKYOUPAGE." WHERE dir='".$_POST['url']."' AND ll='".$_SESSION['ll']."'";
			$results=mysql_query($query);
			$row=mysql_fetch_array($results);
			if($row['tot']>0) echo 'true';
			else echo 'false';
			}
		}
	}
