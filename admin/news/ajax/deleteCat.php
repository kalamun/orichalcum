<?php 
require_once('../../inc/main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("check-permissions"=>false, "x-frame-options"=>"") );

if(isset($_SESSION['iduser'])) {
	include('../../inc/categorie.lib.php');

	$kaLog=new kaLog();
	$kaCategorie=new kaCategorie();

	$cat=$kaCategorie->get($_POST['idcat']);
	$log=$kaCategorie->del($_POST['idcat'],TABLE_NEWS);
	if($log==false) {
		$kaLog->add("ERR",'Errore durante l\'eliminazione della categoria <em>'.b3_htmlize($cat['categoria'],true,"").'</em> dalle News');
		}
	else {
		$kaLog->add("INS",'Eliminata la categoria <em>'.b3_htmlize($cat['categoria'],true,"").'</em> dalle News');
		}
	}
