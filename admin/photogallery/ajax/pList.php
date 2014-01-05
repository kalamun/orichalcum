<ul><?php
include('../../inc/connect.inc.php');
include('../../inc/kalamun.lib.php');
include('../photogallery.lib.php');
$kaPhotogallery=new kaPhotogallery();

if(!isset($_GET['ll'])||trim($_GET['ll'])=="") echo '<li><a href="javascript:setVal(\'\',\'\');">Errore nella scelta della lingua</a></li>';
else {
	$list=$kaPhotogallery->getList("",$_GET['ll']);
	$i=0;
	foreach($list as $row) {
		echo '<li><a href="javascript:setVal(\''.$_GET['ll'].'\',\''.$row['dir'].'\');"><strong>'.$row['titolo'].'</strong><br />/'.$row['dir'].'</a></li>';
		$i++;
		}
	if($i==0) echo '<li><a href="javascript:setVal(\''.$_GET['ll'].'\',\'\');">Nessuna pagina disponibile</a></li>';
	}
?></ul>