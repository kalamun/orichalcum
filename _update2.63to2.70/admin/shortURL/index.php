<?php /* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","URL Brevi");
define("PAGE_LEVEL",1);
include_once("../inc/head.inc.php");

?><h1><?php echo PAGE_NAME; ?></h1>
	<br />

	<p>Puoi definire degli URL brevi (a livello di <em>root</em>) e collegarli ad una qualsiasi pagina <strong>interna</strong> del sito. Questo ti permette di ottenere un ranking migliore delle pagine pi&ugrave; importanti del tuo sito nei motori di ricerca.</p>
	<ul class="mainopt">
	<li><a href="new.php">Aggiungi un indirizzo breve</a></li>
	<li><a href="edit.php">Modifica un indirizzo breve</a></li>
	<li><a href="delete.php">Elimina un indirizzo breve</a></li>
	</ul>

<?php 
include_once("../inc/foot.inc.php");
