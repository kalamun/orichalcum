<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Statistiche");
define("PAGE_LEVEL",2);
include_once("../inc/head.inc.php");
define("PARAM","stats_expiration");
define("VALUE1TYPE","input");
define("VALUE2TYPE","input");

/* AZIONI */
if(isset($_POST['update'])) {
	$kaImpostazioni->setParam('stats_expiration',intval($_POST['stats_expiration'][1]),"",'*');
	$kaImpostazioni->setParam('log_expiration',intval($_POST['log_expiration'][1]),"",'*');
	echo '<div id="MsgSuccess">Configurazione salvata con successo</div>';
	}
/* FINE AZIONI */

$stats_expiration=$kaImpostazioni->getParam('stats_expiration','*');
$log_expiration=$kaImpostazioni->getParam('log_expiration','*');


?>
<h1><? echo PAGE_NAME; ?></h1>
<br />
<?
include('statistichemenu.php');

echo '<form action="?" method="post">';
	echo '<h3>Statistiche sui visitatori</h3>';
	echo "Rimuovi le statistiche più vecchie di ";
	echo b3_create_input("stats_expiration[1]","text","",b3_lmthize($stats_expiration['value1'],"input"),"30px",4);
	echo ' giorni<br />';
	echo '<small>se scrivi "<em>0</em>" o lasci vuoto, non scadranno mai.</small><br /><br />';

	echo '<h3>Registro delle attivit&agrave;</h3>';
	echo "Rimuovi i record più vecchi di ";
	echo b3_create_input("log_expiration[1]","text","",b3_lmthize($log_expiration['value1'],"input"),"30px",4);
	echo ' giorni<br />';
	echo '<small>se scrivi "<em>0</em>" o lasci vuoto, non scadranno mai.</small><br /><br />';

	echo '<div class="submit"><input type="submit" name="update" value="Salva" class="button"></div>';
echo '</form></div><br /><br />';


include_once("../inc/foot.inc.php");
?>
