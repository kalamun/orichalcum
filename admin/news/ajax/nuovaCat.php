<?php 
require_once('../../inc/main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("check-permissions"=>false, "x-frame-options"=>"") );

if(isset($_SESSION['iduser'])) {
	include('../../inc/categorie.lib.php');

	$kaLog=new kaLog();
	$kaCategorie=new kaCategorie();

	$dir=strtolower($_POST['categoria']);
	$dir=preg_replace("/[^\w]/","-",$dir);
	$dir=preg_replace("/-+/","-",$dir);
	$log=$kaCategorie->add($_POST['categoria'],$dir,TABLE_NEWS);
	if($log==false) {
		$kaLog->add("ERR",'Errore durante la creazione della categoria <em>'.b3_htmlize($_POST['categoria'],true,"").'</em> nelle News');
		}
	else {
		$kaLog->add("INS",'Creata la categoria <em>'.b3_htmlize($_POST['categoria'],true,"").'</em> nelle News');
		}
	}
