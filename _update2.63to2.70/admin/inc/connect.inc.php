<?php 
/* (c) Kalamun.org - GNU/GPL 3 */
global $__db;

require_once('config.inc.php');

/* CONNETTI AL DATABASE */
$__db['id']=mysql_connect($__db['host'],$__db['user'],$__db['password']);
if(!$__db['id']) {
	$message="Si e' verificato un errore di connessione al database.\nSito: ".SITE_URL."\nPagina: ".$_SERVER['PHP_SELF']."\n\nQuesto e' un messaggio generato automaticamente.";
	//mail(WEBMASTER_MAIL,"[".SITE_URL."] Errore di connessione al database",$message,'From: '.ADMIN_MAIL);
	die('<h1>Errore di connessione al database.</h1><p>A causa di un errore di connessione al database risulta impossibile utilizzare il sito internet. Siamo spiacenti dell\'inconveniente.<br />Il webmaster &egrave; stato avvisato via e-mail del problema.</p>');
	}
if(!mysql_select_db($__db['name'],$__db['id'])) {
	$message="Si e' verificato un errore di selezione del database.\nSito: ".SITE_URL."\nPagina: ".$_SERVER['PHP_SELF']."\nDB: ".$__db['name']."\n\nQuesto e' un messaggio generato automaticamente.";
	//mail(WEBMASTER_MAIL,"[".SITE_URL."] Errore di connessione al database",$message,'From: '.ADMIN_MAIL);
	die('<h1>Errore durante la selezione del database.</h1><p>A causa di un errore di selezione del database risulta impossibile utilizzare il sito internet. Siamo spiacenti dell\'inconveniente.<br />Il webmaster &egrave; stato avvisato via e-mail del problema.</p>');
	}
mysql_query("SET NAMES 'UTF8'"); 

