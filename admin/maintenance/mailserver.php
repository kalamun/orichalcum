<?php 
/* (c) Kalamun.org - GNU/GPL 3 */


define("PAGE_NAME","Test funzionamento mailserver");
include_once("../inc/head.inc.php");

?>

<h1><?php  echo PAGE_NAME; ?></h1>
<br />

<form method="get" action="">
	<label for="from">Mittente</label> <input type="text" value="<?= ( !empty($_GET['from']) ? $_GET['from'] : ADMIN_MAIL ); ?>" name="from" id="from"><br>
	<label for="to">Destinatario</label> <input type="text" value="<?= ( !empty($_GET['to']) ? $_GET['to'] : ADMIN_MAIL ); ?>" name="to" id="to"><br>
	<input type="submit" name="send" value="Invia e-mail di test" class="smallbutton"><br>
</form>
<br><br>
<?php

/* AZIONI */
if( !empty($_GET['send']) )
{

	require_once("../../inc/tplshortcuts.lib.php");
	kInitBettino("../../");
	if( !defined("VERBOSE") ) define("VERBOSE", true);

	$subject = "Orichalcum - Mailserver Test";
	$message = "<p>My dear little girl</p>
				<p>For a long time I’ve been wanting to write to you in the evening 
				after one of those outings with friends that I will soon be describing 
				in “A Defeat,” the kind when the world is ours. I wanted to bring you my
				 conqueror’s joy and lay it at your feet, as they did in the Age of the 
				Sun King. And then, tired out by all the shouting, I always simply went 
				to bed. Today I’m doing it to feel the pleasure you don’t yet know, of 
				turning abruptly from friendship to love, from strength to tenderness. 
				Tonight I love you in a way that you have not known in me: I am neither 
				worn down by travels nor wrapped up in the desire for your presence. I 
				am mastering my love for you and turning it inwards as a constituent 
				element of myself. This happens much more often than I admit to you, but
				 seldom when I’m writing to you. Try to understand me: I love you while 
				paying attention to external things. At Toulouse I simply loved you. 
				Tonight I love you on <em>a spring evening</em>. I love you with the 
				window open. You are mine, and things are mine, and my love alters the 
				things around me and the things around me alter my love.</p>
				<p>My dear little girl, as I’ve told you, what you’re lacking is friendship. But <em>now is the time</em> for more practical advice. Couldn’t you find <em>a woman friend</em>?
				 How can Toulouse fail to contain one intelligent young woman worthy of 
				you*? But you wouldn’t have to love her. Alas, you’re always ready to 
				give your love, it’s the easiest thing to get from you. I’m not talking 
				about your love for me, which is well beyond that, but you are lavish 
				with little secondary loves, like that night in Thiviers when you loved 
				that peasant walking downhill in the dark, whistling away, who turned 
				out to be me. Get to know the feeling, free of tenderness, that comes 
				from being two. It’s hard, because all friendship, even between two 
				red-blooded men, has its moments of love. I have only to console my 
				grieving friend to love him; it’s a feeling easily weakened and 
				distorted. But you’re capable of it, and you <em>must</em> experience 
				it. And so, despite your fleeting misanthropy, have you imagined what a 
				lovely adventure it would be to search Toulouse for a woman who would be
				 worthy of you and whom you wouldn’t be in love with? Don’t bother with 
				the physical side or the social situation. And search honestly. And if 
				you find nothing, turn Henri Pons, whom you scarcely love anymore, into <em>a friend</em>.</p>
				<p>I love you with all my heart and soul.</p>
				<p align=\"right\"><em>Jean-Paul</em></p>";

	/* SEND EMAIL */
	$results = kSendEmail($_GET['from'], $_GET['to'], $subject, $message);
	if($results === true) $success .= "<br>E-mail HTML inviata";
	else $alert="Si è verificato un errore di invio dell'email: il mailserver non funziona correttamente";
	
}


if(!isset($alert)&&isset($success)) echo '<div id="MsgSuccess">'.$success.'</div>';
elseif(isset($alert)) echo '<div id="MsgAlert">'.$alert.'</div>';
/* FINE AZIONI */

?>



<?php 
include_once("../inc/foot.inc.php");
