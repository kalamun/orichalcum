<?php session_start();
require_once('../../inc/main.lib.php');
$kaOrichalcum=new kaOrichalcum();
$kaOrichalcum->init( array("check-permissions"=>false, "x-frame-options"=>"") );

$kaTranslate->import('setup');

require_once('../../shop/shop.lib.php');
$kaShop=new kaShop();

if(isset($_GET['csv'])) {
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private",false);
	header("Content-Type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"coupons_".($_GET['valid']==1?'valid':'used').'_'.date("Y-m-d").".csv\";");
	header("Content-Transfer-Encoding: Binary"); 
	
	foreach($kaShop->getCouponCodesList(array('idscoup'=>$_GET['idscoup'],'valid'=>$_GET['valid'])) as $m) {
		echo $m['code']."\n";
		}
	
	}
