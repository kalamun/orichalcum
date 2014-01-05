<ul><?php
include('../../inc/connect.inc.php');
if(!isset($_GET['ll'])||trim($_GET['ll'])=="") echo '<li><a href="javascript:setVal(\'\',\'\');">Errore nella scelta della lingua</a></li>';
else {
	$query="SELECT * FROM ".TABLE_NEWS." WHERE ll='".$_GET['ll']."'";
	$results=mysql_query($query);
	for($i=0;$row=mysql_fetch_array($results);$i++) {
		echo '<li><a href="javascript:setVal(\''.$_GET['ll'].'\',\''.$row['dir'].'\');"><strong>'.$row['titolo'].'</strong><br />/'.$row['dir'].'</a></li>';
		}
	if($i==0) echo '<li><a href="javascript:setVal(\''.$_GET['ll'].'\',\'\');">Nessuna pagina disponibile</a></li>';
	}
?></ul>