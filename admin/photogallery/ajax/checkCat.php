<?php session_start();
include('../../inc/connect.inc.php');
if(!isset($_POST['url'])||trim($_POST['url'])=="") echo 'false';
else {
	$query="SELECT count(*) AS tot FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_PHOTOGALLERY."' AND dir='".$_POST['url']."' AND ll='".$_SESSION['ll']."'";
	$results=ksql_query($query);
	$row=ksql_fetch_array($results);
	if($row['tot']>0) echo 'true';
	else echo 'false';
	}
