<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Elimina un URL breve");
include_once("../inc/head.inc.php");


/* AZIONI */
if(isset($_GET['delete'])) {
	$log="";
	$query="SELECT * FROM ".TABLE_SHORTURL." WHERE idurl=".$_GET['delete']." LIMIT 1";
	$results=ksql_query($query);
	$row=ksql_fetch_array($results);
	$old=$row;
	
	$query="DELETE FROM ".TABLE_SHORTURL." WHERE idurl=".$_GET['delete'];
	if(!ksql_query($query)) $log="Problemi durante le modifica";

	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Errore nell\'eliminazione dell\'URL breve <em>'.$old['urlfrom'].'</em>');
		}
	else {
		echo '<div id="MsgSuccess">Modifiche salvate con successo</div>';
		$kaLog->add("DEL",'Eliminato l\'URL breve: <a href="'.BASEDIR.$old['urlfrom'].'">'.$old['urlfrom'].'</a>');
		}
	}
/* FINE AZIONI */


?><h1><?= PAGE_NAME; ?></h1>
<br />
<p>Questi sono gli URL brevi impostati nel sito; scegli quale eliminare:</p>
<table class="tabella">
<tr><th>URL</th><th>Destinazione</th><th>Azioni</th></tr><?php 
$query="SELECT * FROM ".TABLE_SHORTURL." ORDER BY `urlfrom`";
$results=ksql_query($query);
while($row=ksql_fetch_array($results)) {
	echo '<tr>';
	echo '<td><a href="?delete='.$row['idurl'].'" onclick="return confirm(\'Sei proprio sicuro di voler eliminare questo URL breve?\');"><strong>'.SITE_URL.'/'.$row['urlfrom'].'</strong></a></td>';
	echo '<td style="background-color:#fbf7c5;">'.SITE_URL.'/'.$row['urlto'].'</a></td>';
	echo '<td style="background-color:#fbf7c5;"><a href="?delete='.$row['idurl'].'" class="smallbutton" onclick="return confirm(\'Sei proprio sicuro di voler eliminare questo URL breve?\');">Elimina</a></td>';
	echo '</tr>';
	}
?></table>
	
<?php 	
include_once("../inc/foot.inc.php");
