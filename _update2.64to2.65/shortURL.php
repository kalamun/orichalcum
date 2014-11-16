<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

require_once("inc/tplshortcuts.lib.php");
kInitBettino();

// if the url is empty, load the home page for the autodetected language
if($_GET['url']=="") $_GET['url']=kGetVar('home_page',1);
elseif($_GET['url']=="sitemap.xml")
{
	$GLOBALS['__template']->get();
	die();
}

// search for short URLs
$query="SELECT * FROM `".TABLE_SHORTURL."` WHERE `urlfrom`='".b3_htmlize($_GET['url'],true,"")."' OR `urlfrom`='".mysql_real_escape_string($_GET['url'])."' LIMIT 1";
$results=mysql_query($query);
if($row=mysql_fetch_array($results))
{
	// requested url is defined in the database
	// if it's local, redirect to local, else redirect to url
	if(substr($row['urlto'],0,4)!='http'&&substr($row['urlto'],0,3)!='ftp') $url=SITE_URL.BASEDIR.$row['urlto'];
	else $url=$row['urlto'];
	header('Location:'.$url);

}

// prevent redirect for default language if this option is turned on in Settings > General Settings
if(LANG==DEFAULT_LANG && kGetVar('short_permalink_default_lang',1,"*")=="true") {
	include("index.php");
	die();
}

$url=SITE_URL.BASEDIR.strtolower(LANG)."/".$_GET['url'];
header('Location:'.$url);



