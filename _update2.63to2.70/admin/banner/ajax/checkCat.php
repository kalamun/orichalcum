<?php session_start();
include('../../inc/connect.inc.php');
if(!isset($_POST['url'])||trim($_POST['url'])=="") die('false');

if(get_magic_quotes_gpc()) $_POST['url']=stripslashes($_POST['url']);

$query="SELECT count(*) AS tot FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_BANNER."' AND dir='".mysql_real_escape_string($_POST['url'])."' AND ll='".mysql_real_escape_string($_SESSION['ll'])."'";
$results=mysql_query($query);
$row=mysql_fetch_array($results);
if($row['tot']>0) echo 'true';
else echo 'false';

