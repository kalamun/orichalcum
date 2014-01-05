<?php
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Nuovo indirizzo breve");
define("PAGE_LEVEL",1);

include_once("../inc/head.inc.php");

/* AZIONI */
if(isset($_POST['save'])) {
	$log="";
	
	if(strlen($_POST['urlfrom'])==2||strpos($_POST['urlfrom'],"/")==2) $log="L'URL non pu&ograve; essere di due lettere";

	if($log=="") {
		$query="INSERT INTO ".TABLE_SHORTURL." (`urlfrom`,`urlto`) VALUES('".b3_htmlize($_POST['urlfrom'],true,"")."','".b3_htmlize($_POST['urlto'],true,"")."')";
		if(!mysql_query($query)) $log="Problemi durante il salvataggio";
		else $id=mysql_insert_id();
		}
	
	if($log!="") {
		echo '<div id="MsgAlert">'.$log.'</div>';
		$kaLog->add("ERR",'Errore nell\'aggiunta dell\'URL breve <em>'.b3_htmlize($_POST['urlfrom'],true,"").'</em>');
		}
	else {
		echo '<div id="MsgSuccess">Indirizzo breve inserito con successo.</div>';
		$kaLog->add("INS",'Aggiunto URL breve: <a href="'.BASEDIR.b3_htmlize($_POST['urlfrom'],true,"").'">'.b3_htmlize($_POST['urlfrom'],true,"").'</a>');
		}
	}
/***/

?><h1><?php echo PAGE_NAME; ?></h1>
	<br />
	
	<form action="" method="post">
	<?php
	echo 'URL breve:<br />';
	echo b3_create_input("urlfrom","text",SITE_URL."/","","400px",250).'<br /><br />';
	echo 'Pagina di destinazione:<br />';
	echo b3_create_input("urlto","text",SITE_URL."/",strtolower($_SESSION['ll']).'/',"400px",250).'<br /><br />';
	?>
	
	<div class="submit" id="submit">
		<input type="submit" name="save" class="button" value="Salva" />
		</div>
	</form>

<?php	
include_once("../inc/foot.inc.php");
?>
