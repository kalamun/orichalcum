<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Statistiche");
include_once("../inc/head.inc.php");
define("PARAM","google_analytics");
define("VALUE1TYPE","code");
define("VALUE2TYPE","code");

/* AZIONI */
if(isset($_SESSION['iduser'])&&isset($_POST['update'])) {
	function b3_code($string) {
		$string=addslashes(stripslashes($string));
		return $string;
		}

	$query="SELECT count(*) AS tot FROM ".TABLE_CONFIG." WHERE param='".PARAM."' LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);

	if(VALUE1TYPE=="textarea") $_POST['value1']=b3_htmlize($_POST['value1'],true);
	elseif(VALUE1TYPE=="input") $_POST['value1']=b3_htmlize($_POST['value1'],true,"");
	elseif(VALUE1TYPE=="code") $_POST['value1']=b3_code($_POST['value1']);
	if(VALUE2TYPE=="textarea") $_POST['value2']=b3_htmlize($_POST['value2'],true);
	elseif(VALUE2TYPE=="input") $_POST['value2']=b3_htmlize($_POST['value2'],true,"");
	elseif(VALUE1TYPE=="code") $_POST['value2']=b3_code($_POST['value2']);
	
	if($row['tot']>0) $query="UPDATE ".TABLE_CONFIG." SET value1='".$_POST['value1']."',value2='".$_POST['value2']."' WHERE param='".PARAM."'";
	else $query="INSERT INTO ".TABLE_CONFIG." (param,value1,value2) VALUES('".PARAM."','".$_POST['value1']."','".$_POST['value2']."')";
	if(mysql_query($query)) {
		echo '<div id="MsgSuccess">Configurazione salvata con successo</div>';
		$kaLog->add("CFG","Modificate impostazioni di Statistiche esterne");
		}
	else {
		echo '<div class="MsgAlert">Attenzione! Problemi durante il salvataggio del parametro di configurazione</div>';
		$kaLog->add("ERR","Problemi durante la modifica delle impostazioni di Statistiche esterne");
		}
	}
/* FINE AZIONI */

?>
<h1><? echo PAGE_NAME; ?></h1>
<br />
<? include('./statistichemenu.php'); ?>
<p>Incollare qui il codice Javascript necessario ad attivare un motore di statistiche esterno (es. <em>Google Analytics</em> o <em>StatCounter</em>) sul proprio sito internet.</p>
<?
$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='".PARAM."' LIMIT 1";
$results=mysql_query($query);
$row=mysql_fetch_array($results);
?>
<form action="?" method="post">
	<textarea name="value1" style="width:100%;height:300px;font-family:'Courier New',fixed;"><?
		echo b3_lmthize(str_replace("<br />","&lt;br /&gt;",$row['value1']));
		?></textarea>
	<input type="hidden" name="value2" value="" />
	<br /><br />
	<div class="submit"><input type="submit" name="update" value="Salva" class="button"></div>
</form></div><br /><br />
<?

include_once("../inc/foot.inc.php");
?>
