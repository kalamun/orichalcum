<?php 
/* (c) Kalamun.org - GNU/GPL 3 */


define("PAGE_NAME","Test funzionamento mailserver");
include_once("../inc/head.inc.php");

/* AZIONI */
if(isset($_GET['sendtestemail'])) {
	require_once("../../inc/tplshortcuts.lib.php");
	kInitBettino("../../");

	$subject='Orichalcum - Mailserver Test';
	$message="It works!";
	$headers='From: '.ADMIN_MAIL;

	/* PLAIN */
	if(mail($_GET['sendtestemail'],$subject,$message,$headers))
	{
		$success="E-mail plain/text inviata";
		
		/* HTML */
		$results=kSendEmail(ADMIN_MAIL,$_GET['sendtestemail'],$subject,$message);
		if($results==true) $success.="<br>E-mail HTML inviata";
		else $alert="<br>Errore di invio dell'email HTML";
	}
	else $alert="Si è verificato un errore di invio dell'email: il mailserver non funziona correttamente";
	
	}


if(!isset($alert)&&isset($success)) echo '<div id="MsgSuccess">'.$success.'</div>';
elseif(isset($alert)) echo '<div id="MsgAlert">'.$alert.'</div>';
/* FINE AZIONI */

?>
<h1><?php  echo PAGE_NAME; ?></h1>
<br />

<form method="get" action="">
	<label for="sendtestemail">Destinatario</label> <input type="text" value="<?= (isset($_GET['sendtestemail'])?$_GET['sendtestemail']:ADMIN_MAIL); ?>" name="sendtestemail" id="sendtestemail" />
	<input type="submit" value="Invia e-mail di test" class="smallbutton">
	</form>


<?php 
include_once("../inc/foot.inc.php");
