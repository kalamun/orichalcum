<?php 
session_start();
if(!isset($_SESSION['iduser'])) die();
if(!isset($_POST['t'])) die();
if(!isset($_POST['id'])) die();
if(!isset($_POST['p'])) die();
if(!isset($_POST['v'])) die();

require_once('../main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("check-permissions"=>false, "x-frame-options"=>"") );

if(get_magic_quotes_gpc()) $_POST['v']=stripslashes($_POST['v']);
if(get_magic_quotes_gpc()) $_POST['p']=stripslashes($_POST['p']);

$kaMetadata->set($_POST['t'],$_POST['id'],$_POST['p'],$_POST['v']);
