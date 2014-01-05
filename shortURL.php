<?
/* (c) Kalamun.org - GNU/GPL 3 */

require_once("inc/tplshortcuts.lib.php");
kInitBettino();

if($_GET['url']=="") $_GET['url']=kGetVar('home_page',1);
elseif($_GET['url']=="sitemap.xml") {
	$GLOBALS['__template']->get();
	die();
	}

$query="SELECT * FROM ".TABLE_SHORTURL." WHERE `urlfrom`='".b3_htmlize($_GET['url'],true,"")."' LIMIT 1";
$results=mysql_query($query);
if($row=mysql_fetch_array($results)) {
	//requested url is defined in the database
	//if it's local, redirect to local
	if(substr($row['urlto'],0,4)!='http'&&substr($row['urlto'],0,3)!='ftp') $url=SITE_URL.BASEDIR.$row['urlto'];
	else $url=$row['urlto'];
	}
else {
	$url=SITE_URL.BASEDIR.strtolower(LANG)."/".$_GET['url'];
	}

header('Location:'.$url);
?>