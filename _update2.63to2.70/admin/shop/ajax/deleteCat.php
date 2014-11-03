<?php 
session_start();
if(isset($_SESSION['iduser'])) {
	include('../../inc/connect.inc.php');
	include('../../inc/main.lib.php');
	include('../../inc/log.lib.php');
	include('../../inc/categorie.lib.php');

	$kaLog=new kaLog();
	$kaCategorie=new kaCategorie();

	$cat=$kaCategorie->get($_POST['idcat']);
	$log=$kaCategorie->del($_POST['idcat'],TABLE_SHOP_ITEMS);
	if($log==false) {
		$kaLog->add("ERR",'Errore durante l\'eliminazione della categoria <em>'.b3_htmlize($cat['categoria'],true,"").'</em> dalle News');
		}
	else {
		$kaLog->add("INS",'Eliminata la categoria <em>'.b3_htmlize($cat['categoria'],true,"").'</em> dalle News');
		}
	}
