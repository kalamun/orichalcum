<?
/* (c) Kalamun.org - GPL 3 */
/*autorilevamento lingua*/
require($_SERVER['DOCUMENT_ROOT'].BASEDIR.'admin/inc/connect.inc.php');

function kDetectLang() {
	$codes=array();
	$query="SELECT * FROM `".TABLE_LINGUE."` WHERE `online`='s' ORDER BY `ordine`";
	$results=mysql_query($query);
	while($row=mysql_fetch_array($results)) {
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

if(isset($_GET['lang'])&&$_GET['lang']!="") {
	define("LANG",strtoupper($_GET['lang']));
	if(!headers_sent()) {
		setcookie("lang",LANG,0,"/");
		$_COOKIE['lang']=LANG;
		}
	}
elseif(isset($_COOKIE['lang'])) define("LANG",$_COOKIE['lang']);
else {
	define("LANG",kDetectLang());
	if(!headers_sent()) {
		setcookie("lang",LANG,0,"/");
		$_COOKIE['lang']=LANG;
		}
	}

$query="SELECT * FROM `".TABLE_LINGUE."` WHERE `online`='s' AND `ll`='".mysql_real_escape_string(LANG)."' LIMIT 1";
$results=mysql_query($query);
$row=mysql_fetch_array($results);
setlocale(LC_TIME,$row['code']);

?>