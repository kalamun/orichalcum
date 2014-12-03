<?php 
/* (c) Kalamun.org - GPL 3 */

/* autodetect current language */
function kDetectLang() {
	$codes=array();
	$query="SELECT * FROM `".TABLE_LINGUE."` WHERE `online`='s' ORDER BY `ordine`";
	$results=ksql_query($query);
	while($row=ksql_fetch_array($results)) {
		$codes[$row['ll']]=$row['code'];
		}
	if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
		foreach($codes as $ll=>$code) {
			if(strpos(strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']),$code)!==false) return strtoupper($ll);
			}
		foreach($codes as $ll=>$code) {
			$p=strtolower($ll);
			if(strpos(strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']),$p)!==false) return strtoupper($ll);
			}
		}
	return strtoupper(DEFAULT_LANG);
	}

// if language is set by URL (eg. /en/)
if(isset($_GET['lang']) && $_GET['lang']!="") {
	define("LANG",strtoupper($_GET['lang']));
	if(!headers_sent())
	{
		setcookie("lang",LANG,0,"/");
		$_COOKIE['lang']=LANG;
	}

// if language is set by cookie
} elseif(isset($_COOKIE['lang'])) {
	// if short permalink for default language is active, prevent to detect when no specified
	if(kGetVar('short_permalink_default_lang',1,"*")=="true") define("LANG",DEFAULT_LANG);
	else define("LANG",$_COOKIE['lang']);

// if language is not set, auto-detect it
} else {
	define("LANG",kDetectLang());
	if(!headers_sent())
	{
		setcookie("lang",LANG,0,"/");
		$_COOKIE['lang']=LANG;
	}
}

// set locale to current language
$query="SELECT * FROM `".TABLE_LINGUE."` WHERE `online`='s' AND `ll`='".ksql_real_escape_string(LANG)."' LIMIT 1";
$results=ksql_query($query);
$row=ksql_fetch_array($results);
setlocale(LC_TIME,$row['code']);

