<?php 
include('../../inc/connect.inc.php');
if(!isset($_POST['url'])||trim($_POST['url'])=="") echo 'false';
elseif(preg_match('/^http:\/\//',$_POST['url'])) echo 'true';
else {
	if(ltrim(substr($_POST['url'],0,strrpos($_POST['url'],"/")+1),"./")=="p/") $_POST['url']=substr($_POST['url'],strrpos($_POST['url'],"/")+1);
	$query="SELECT count(*) AS tot FROM ".TABLE_PAGINE." WHERE dir='".$_POST['url']."'";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
	if($row['tot']>0) echo 'true';
	else echo 'false';
	}
