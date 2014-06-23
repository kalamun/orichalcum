<?php
/* (c) Kalamun.org - GNU/GPL 3 */

require_once("inc/tplshortcuts.lib.php");
kInitBettino();

/* generazione pagine e template */
if(!isset($GLOBALS['__dir__'])||$GLOBALS['__dir__']=="") {
	if(kGetVar('home_page',1)!="") {
		header('Location:'.SITE_URL.BASEDIR.strtolower(LANG)."/".kGetVar('home_page',1));
		die();
		}
	else die(kTranslate('Under Construction'));
	}
$__template->loadMenu();

/* news */
if($GLOBALS['__dir__']==rtrim($__template->getVar('dir_news',1),"/")) {
	kSetNewsTemplate();
	$__template->setMetaData($__news->getMetaData());
	if(kGetVar('news-commenti',1)=='s'&&isset($_POST['commentSubmit'])&&isset($_POST['commentName'])&&isset($_POST['commentText'])) kAddComment(array("table"=>TABLE_NEWS,"id"=>kGetNewsId(),"name"=>$_POST['commentName'],"email"=>$_POST['commentEmail'],"text"=>$_POST['commentText'],"public"=>kGetVar('news-commenti',2)=="s"?"n":"s"));
	if($__template->getVar('news-template',2)!="") $__template->setLayout($__template->getVar('news-template',2));
	$__template->get($__template->getVar('news-template',1));
	}
elseif($GLOBALS['__dir__']==rtrim($__template->getVar('dir_feed',1),"/")) {
	$__template->setMetaData($__news->getMetaData());
	$__template->get();
	}
/* private area */
elseif($GLOBALS['__dir__']==rtrim($__template->getVar('dir_private',1),"/")) {
	$__template->setMetaData($__shop->getMetaData());
	if($__template->getVar('private-template',2)!="") $__template->setLayout($__template->getVar('private-template',2));
	$__template->get($__template->getVar('private-template',1));
	}
/* shop */
elseif($GLOBALS['__dir__']==rtrim($__template->getVar('dir_shop',1),"/")) {
	if($GLOBALS['__subdir__']==$__template->getVar('dir_shop_manufacturers'))
	{
		$__template->setMetaData($__shop->getManufacturerMetaData());
		if(kGetVar('shop-commenti',1)=='s'&&isset($_POST['commentSubmit'])&&isset($_POST['commentName'])&&isset($_POST['commentText'])) {
			kSetShopManufacturerByDir();
			kAddComment(array("table"=>TABLE_SHOP_MANUFACTURERS,"id"=>kGetShopManufacturerId(),"name"=>$_POST['commentName'],"email"=>$_POST['commentEmail'],"text"=>$_POST['commentText'],"public"=>kGetVar('shop-commenti',2)=="s"?"n":"s"));
			}
		if($__template->getVar('shop-template',2)!="") $__template->setLayout($__template->getVar('shop-template',2));
		$__template->get($__template->getVar('shop-template',1));
	} else {
		$__template->setMetaData($__shop->getMetaData());
		if(kGetVar('shop-commenti',1)=='s'&&isset($_POST['commentSubmit'])&&isset($_POST['commentName'])&&isset($_POST['commentText'])) {
			kSetShopItemByDir();
			kAddComment(array("table"=>TABLE_SHOP_ITEMS,"id"=>kGetShopItemId(),"name"=>$_POST['commentName'],"email"=>$_POST['commentEmail'],"text"=>$_POST['commentText'],"public"=>kGetVar('shop-commenti',2)=="s"?"n":"s"));
			}
		if($__template->getVar('shop-template',2)!="") $__template->setLayout($__template->getVar('shop-template',2));
		$__template->get($__template->getVar('shop-template',1));
	}
	}
/* photogallery */
elseif($GLOBALS['__dir__']==rtrim($__template->getVar('dir_photogallery',1),"/")) {
	$__template->setMetaData($__photogallery->getMetaData());
	if(kGetVar('photogallery-commenti',1)=='s'&&isset($_POST['commentSubmit'])&&isset($_POST['commentName'])&&isset($_POST['commentText'])) kAddComment(array("table"=>TABLE_PHOTOGALLERY,"id"=>kGetPhotogalleryId(),"name"=>$_POST['commentName'],"email"=>$_POST['commentEmail'],"text"=>$_POST['commentText'],"public"=>kGetVar('photogallery-commenti',2)=="s"?"n":"s"));
	$__template->get($__template->getVar('photogallery-template',1));
	}
/* users */
elseif($GLOBALS['__dir__']==rtrim($__template->getVar('dir_users',1),"/")) {
	$__template->setMetaData($__users->getMetaData());
	$__template->get();
	}
/* search */
elseif($GLOBALS['__dir__']==rtrim($__template->getVar('dir_search',1),"/")) {
	$__template->get();
	}
/* pagine */
else {
	kSetPageTemplate();
	kSetPageByDir();
	$__template->setMetaData($__pages->getMetaData());
	if(kGetVar('pages-commenti',1)=='s'&&isset($_POST['commentSubmit'])&&isset($_POST['commentName'])&&isset($_POST['commentText'])) kAddComment(array("table"=>TABLE_PAGINE,"id"=>kGetPageId(),"name"=>$_POST['commentName'],"email"=>$_POST['commentEmail'],"text"=>$_POST['commentText'],"public"=>kGetVar('pages-commenti',2)=="s"?"n":"s"));
	$__template->get();
	}

kStatistiche();
?>
