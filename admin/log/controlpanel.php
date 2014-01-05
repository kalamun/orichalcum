<?
define("PAGE_NAME","Log delle attivit&agrave;");
define("PAGE_LEVEL",1);
include_once("../inc/head.inc.php");

/* AZIONI */
if(isset($_POST['clear'])) {
	$log="";
	if(!$kaLog->clear("all")) $log="Problemi durante la rimozione dal database dei dati di log";

	if($log!="") echo '<div id="MsgAlert">'.$log.'</div>';
	else echo '<div id="MsgSuccess">Registro svuotato con successo</div>';
	}
/* FINE AZIONI */

if(isset($_GET['clear'])) {
	?>
	<h1><? echo PAGE_NAME; ?></h1>
	<br />
	<p>
	Sei proprio sicuro di voler cancellare tutti i dati presenti nel registro delle attivit&agrave; del pannello di controllo?
	</p>

	<form action="?" method="post">
	<div class="submit"><input name="clear" type="submit" class="button" value="S&igrave; svuota tutto" /> <input type="submit" value="Annulla" class="button" /></div>
	</form>
	<br /><br />
	<?
	}
else {
	?>
	<h1><? echo PAGE_NAME; ?></h1>
	<br />

	<table border="0" cellpadding="2" cellspacing="1" class="tabella">
	<tr>
		<th>Utente</th>
		<th>Data</th>
		<th>Ora</th>
		<th>Lingua</th>
		<th>Azione</th>
		<th>Descrizione</th>
		</tr>
	<?
	$log=$kaLog->get();
	foreach($log as $ka=>$v) {
		echo '<tr>';
		echo '<td>'.$v['username'].'</td>';
		echo '<td>'.$v['dataleggibile'].'</td>';
		echo '<td>'.$v['oraleggibile'].'</td>';
		echo '<td>'.$v['ll'].'</td>';
		echo '<td>'.$v['azione'].'</td>';
		echo '<td>'.$v['descr'].'</td>';
		echo '</tr>';
		}

	?>
	</table><br />
	<form action="?clear" method="post">
	<div class="submit"><input type="submit" class="button" value="Svuota il log" /></div>
	</form>
	<br /><br />
	<?
	}

include_once("../inc/foot.inc.php");
?>
