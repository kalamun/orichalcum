<?php session_start();
include('../../inc/connect.inc.php');
if(!isset($_POST['url'])||trim($_POST['url'])=="") die('false');

if(get_magic_quotes_gpc()) $_POST['url']=stripslashes($_POST['url']);

$query="SELECT count(*) AS tot FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_BANNER."' AND dir='".ksql_real_escape_string($_POST['url'])."' AND ll='".ksql_real_escape_string($_SESSION['ll'])."'";
$results=ksql_query($query);
$row=ksql_fetch_array($results);
if($row['tot']>0) echo 'true';
else echo 'false';

