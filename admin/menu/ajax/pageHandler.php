<?php
session_start();
if(!isset($_SESSION['exists'])) die();

require_once("../../inc/config.inc.php");
if(!isset($db['id'])) require_once("../../inc/connect.inc.php");
require_once("../../inc/kalamun.lib.php");
require_once("../../inc/main.lib.php");

require_once('../../pages/pages.lib.php');
$kaPages=new kaPages();

/* if page list is requested */
if(isset($_POST['getSuggestions'])&&$_POST['getSuggestions']!="") {
	$params=array("match"=>$_POST['getSuggestions'],"start"=>0,"limit"=>10);
	if(strlen($_POST['ll'])==2) $params['ll']=$_POST['ll'];
	if(substr($_POST['ll'],0,1)=="-") $params['exclude_ll']=substr($_POST['ll'],1);
	foreach($kaPages->getQuickList($params) as $page) {
		echo $page['ll']."\t".$page['idpag']."\t".$page['dir']."\t".utf8_decode(b3_unhtmlize($page['titolo']))."\n";
		}
	}
?>