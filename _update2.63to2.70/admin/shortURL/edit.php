<?php 
/* (c) Kalamun.org - GNU/GPL 3 */
define("PAGE_NAME","Modifica Pagina");
include_once("../inc/head.inc.php");


/* AZIONI */
if(isset($_POST['update'])) {
	$log="";
	$query="UPDATE ".TABLE_SHORTURL." SET `urlfrom`='".$_POST['urlfrom']."',`urlto`='".$_POST['urlto']."' WHERE idurl=".$_POST['idurl'];
	if(!mysql_query($query)) $log="Problemi durante le modifica";

	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Errore nell\'aggiornamento dell\'URL breve <em>'.b3_htmlize($_POST['urlfrom'],true,"").'</em>');
		}
	else {
		echo '<div id="MsgSuccess">Modifiche salvate con successo</div>';
		$kaLog->add("UPD",'Modificato l\'URL breve: <a href="'.BASEDIR.b3_htmlize($_POST['urlfrom'],true,"").'">'.b3_htmlize($_POST['urlfrom'],true,"").'</a>');
		}
	}
/* FINE AZIONI */


if(!isset($_GET['idurl'])) {
	?><h1><?= PAGE_NAME; ?></h1>
		<br />
		<p>Questi sono gli URL brevi impostati nel sito; scegli quale modificare:</p>
		<table class="tabella">
		<tr><th>URL</th><th>Destinazione</th><th>Azioni</th></tr><?php 
		$query="SELECT * FROM ".TABLE_SHORTURL." ORDER BY `urlfrom`";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			echo '<tr>';
			echo '<td><a href="?idurl='.$row['idurl'].'"><strong>'.SITE_URL.'/'.$row['urlfrom'].'</strong></a></td>';
			echo '<td style="background-color:#fbf7c5;">'.SITE_URL.'/'.$row['urlto'].'</a></td>';
			echo '<td style="background-color:#fbf7c5;"><a href="?idurl='.$row['idurl'].'" class="smallbutton">Modifica</a></td>';
			echo '</tr>';
			}
		?></table>
	<?php  }

else {

	echo '<h1>'.PAGE_NAME.'</h1>';
	?>
	<br />
	<?php 
	$query="SELECT * FROM ".TABLE_SHORTURL." WHERE idurl='".$_GET['idurl']."' LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);

	echo '<form action="?" method="post" enctype="multipart/form-data">';
		echo b3_create_input("idurl","hidden","",$row['idurl'],"");
		echo 'URL breve:<br />';
		echo b3_create_input("urlfrom","text",SITE_URL.'/',b3_lmthize($row['urlfrom'],"input"),"400px",150);
		echo '<br /><br />';
		echo 'Pagina di destinazione:<br />';
		echo b3_create_input("urlto","text",SITE_URL.'/',b3_lmthize($row['urlto'],"input"),"400px",150);
		echo '<br /><br />';

		echo '<div class="submit"><input type="submit" name="update" class="button" value="Salva le modifiche" /></div>';
	?></div><?php 
	echo '</form>';



	}

include_once("../inc/foot.inc.php");
