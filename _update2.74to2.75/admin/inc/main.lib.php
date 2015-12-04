<?php /******************************************************************************
* Roberto Pasini - info@kalamun.org
* Licenza GNU/GPL v.3
******************************************************************************/

/* functions to increase compatiblity on different configurations */
if(!function_exists('apache_request_headers'))
{
	function apache_request_headers()
	{
		$headers = array();
		foreach($_SERVER as $key => $value)
		{
			if(substr($key, 0, 5) == 'HTTP_') $headers[str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))))] = $value;
		}
		return $headers;
	}
}


/* orichalcum generic class */
class kaOrichalcum
{

	public function __construct()
	{
	}

	public function init($vars=array())
	{
		/* input vars
		x-frame-options -> deny
		*/
		if(!isset($vars['x-frame-options'])) $vars['x-frame-options']="deny";
		if(!isset($vars['check-permissions'])) $vars['check-permissions']=true;
		if(!isset($vars['check-session'])) $vars['check-session']=true;

		/* init default session */
		if((!isset($_SESSION['exists'])||$_SESSION['exists']!=true)||!isset($_SESSION['ll']))
		{
			session_start();
			$_SESSION['exists']=true;
		}

		/* prevent XSS */
		if($vars['x-frame-options']!="") header('X-Frame-Options: '.$vars['x-frame-options']);

		/* connect to db and set default constants */
		require_once("config.inc.php");
		if(!isset($db['id'])) require_once("connect.inc.php");
		require_once("main.lib.php");
		require_once("kalamun.lib.php");

		/* set default timezone in PHP and MySQL */
		$timezone=kaGetVar('timezone',1);
		if($timezone=="") $timezone='Europe/Rome';
		date_default_timezone_set($timezone);
		$query="SET time_zone='".date("P")."'";
		ksql_query($query);
		ksql_query("SET NAMES utf8");

		/* load setup variables */
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR."inc/log.lib.php");
		$GLOBALS['kaLog']=new kaLog();
		$GLOBALS['kaImpostazioni']=new kaImpostazioni();

		/* generate PAGE_ID and additional constants */
		if(!defined("PAGE_ID")) define("PAGE_ID",substr(dirname($_SERVER['PHP_SELF']),strpos(dirname($_SERVER['PHP_SELF']),"admin/")+6));
		if(!defined("RICH_EDITOR"))
		{
			if($GLOBALS['kaImpostazioni']->getVar('admin-editor',1,"*")=="true") define("RICH_EDITOR",true);
			else define("RICH_EDITOR",true);
		}

		/* load generic purpose classes */
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR."users/users.lib.php");
		$GLOBALS['kaUsers']=new kaUsers();
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR."inc/metadata.lib.php");
		$GLOBALS['kaMetadata']=new kaMetadata();

		/* manage language changes */
		if(isset($_GET['chg_lang']))
		{
			$_SESSION['ll']=$_GET['chg_lang'];
			$GLOBALS['kaImpostazioni']->kaImpostazioni();
		}

		/* manage session and access based on user permissions */
		include_once("sessionmanager.inc.php");
		$GLOBALS['kaTranslate']=new kaAdminTranslate();

		/* if user is not logged in, display login page */
		if($vars['check-session']==true && empty($_SESSION['username']))
		{
			include_once("login.inc.php");
			die();
		}

		/* if user are not allowed to access this page, display error */
		if($vars['check-permissions']==true&&!$GLOBALS['kaUsers']->canIUse())
		{
			?>
			<div class="alert"><h1><?= $GLOBALS['kaTranslate']->translate('UI:Forbidden'); ?></h1>
			<a href="<?= ADMINDIR; ?>"><?= $GLOBALS['kaTranslate']->translate('UI:Back to home'); ?></a></div>
			<?php 
			include_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR."inc/foot.inc.php");
			die();
		}
		
		/* embed additional classes */
		require('images.lib.php');
		$GLOBALS['kaImages']=new kaImages();

	}

}


/******************************************************************************
 HTMLIZE: converte una stringa in codice html
     b3_htmlize(string [,add/strip_slashes(true|false)]);
     utile per inserire dei testi già formattati in html all'interno di
     database o altro all'occorrenza.
     converte tutto in caratteri html tranne i tag che sono ammessi
******************************************************************************/

function b3_htmlize($string,$slashes=true,$param="*") {
	
	if(get_magic_quotes_gpc()) $string=stripslashes($string);
	
	/***** HTML ENTITIES *** la funzione htmlentities ne supporta pochissimi, meglio che mi faccio i miei *****/
	$htmlentities=array();
	//$htmlentities[]=array("ent"=>"&","html"=>"&amp;");
	if($param!="*") $htmlentities[]=array("ent"=>"<","html"=>"&lt;");
	if($param!="*") $htmlentities[]=array("ent"=>">","html"=>"&gt;");
	$htmlentities[]=array("ent"=>"¡","html"=>"&iexcl;");
	$htmlentities[]=array("ent"=>"¤","html"=>"&curren;");
	$htmlentities[]=array("ent"=>"¢","html"=>"&cent;");
	$htmlentities[]=array("ent"=>"£","html"=>"&pound;");
	$htmlentities[]=array("ent"=>"¥","html"=>"&yen;");
	$htmlentities[]=array("ent"=>"¦","html"=>"&brvbar;");
	$htmlentities[]=array("ent"=>"§","html"=>"&sect;");
	$htmlentities[]=array("ent"=>"¨","html"=>"&uml;");
	$htmlentities[]=array("ent"=>"©","html"=>"&copy;");
	$htmlentities[]=array("ent"=>"ª","html"=>"&ordf;");
	$htmlentities[]=array("ent"=>"«","html"=>"&laquo;");
	$htmlentities[]=array("ent"=>"¬","html"=>"&not;");
	$htmlentities[]=array("ent"=>"­","html"=>"&ndash;"); //dovrebbe essere &shy; ma non esiste più
	$htmlentities[]=array("ent"=>"®","html"=>"&reg;");
	$htmlentities[]=array("ent"=>"™","html"=>"&trade;");
	$htmlentities[]=array("ent"=>"¯","html"=>"&macr;");
	$htmlentities[]=array("ent"=>"°","html"=>"&deg;");
	$htmlentities[]=array("ent"=>"±","html"=>"&plusmn;");
	$htmlentities[]=array("ent"=>"²","html"=>"&sup2;");
	$htmlentities[]=array("ent"=>"³","html"=>"&sup3;");
	$htmlentities[]=array("ent"=>"´","html"=>"&acute;");
	$htmlentities[]=array("ent"=>"µ","html"=>"&micro;");
	$htmlentities[]=array("ent"=>"¶","html"=>"&para;");
	$htmlentities[]=array("ent"=>"·","html"=>"&middot;");
	$htmlentities[]=array("ent"=>"¸","html"=>"&cedil;");
	$htmlentities[]=array("ent"=>"¹","html"=>"&sup1;");
	$htmlentities[]=array("ent"=>"º","html"=>"&ordm;");
	$htmlentities[]=array("ent"=>"»","html"=>"&raquo;");
	$htmlentities[]=array("ent"=>"¼","html"=>"&frac14;");
	$htmlentities[]=array("ent"=>"½","html"=>"&frac12;");
	$htmlentities[]=array("ent"=>"¾","html"=>"&frac34;");
	$htmlentities[]=array("ent"=>"¿","html"=>"&iquest;");
	$htmlentities[]=array("ent"=>"×","html"=>"&times;");
	$htmlentities[]=array("ent"=>"÷","html"=>"&divide;");
	$htmlentities[]=array("ent"=>"À","html"=>"&Agrave;");
	$htmlentities[]=array("ent"=>"Á","html"=>"&Aacute;");
	$htmlentities[]=array("ent"=>"Â","html"=>"&Acirc;");
	$htmlentities[]=array("ent"=>"Ã","html"=>"&Atilde;");
	$htmlentities[]=array("ent"=>"Ä","html"=>"&Auml;");
	$htmlentities[]=array("ent"=>"Å","html"=>"&Aring;");
	$htmlentities[]=array("ent"=>"Æ","html"=>"&AElig;");
	$htmlentities[]=array("ent"=>"Ç","html"=>"&Ccedil;");
	$htmlentities[]=array("ent"=>"È","html"=>"&Egrave;");
	$htmlentities[]=array("ent"=>"É","html"=>"&Eacute;");
	$htmlentities[]=array("ent"=>"Ê","html"=>"&Ecirc;");
	$htmlentities[]=array("ent"=>"Ë","html"=>"&Euml;");
	$htmlentities[]=array("ent"=>"Ì","html"=>"&Igrave;");
	$htmlentities[]=array("ent"=>"Í","html"=>"&Iacute;");
	$htmlentities[]=array("ent"=>"Î","html"=>"&Icirc;");
	$htmlentities[]=array("ent"=>"Ï","html"=>"&Iuml;");
	$htmlentities[]=array("ent"=>"Ð","html"=>"&ETH;");
	$htmlentities[]=array("ent"=>"Ñ","html"=>"&Ntilde;");
	$htmlentities[]=array("ent"=>"Ò","html"=>"&Ograve;");
	$htmlentities[]=array("ent"=>"Ó","html"=>"&Oacute;");
	$htmlentities[]=array("ent"=>"Ô","html"=>"&Ocirc;");
	$htmlentities[]=array("ent"=>"Õ","html"=>"&Otilde;");
	$htmlentities[]=array("ent"=>"Ö","html"=>"&Ouml;");
	$htmlentities[]=array("ent"=>"Ø","html"=>"&Oslash;");
	$htmlentities[]=array("ent"=>"Ù","html"=>"&Ugrave;");
	$htmlentities[]=array("ent"=>"Ú","html"=>"&Uacute;");
	$htmlentities[]=array("ent"=>"Û","html"=>"&Ucirc;");
	$htmlentities[]=array("ent"=>"Ü","html"=>"&Uuml;");
	$htmlentities[]=array("ent"=>"Ý","html"=>"&Yacute;");
	$htmlentities[]=array("ent"=>"Þ","html"=>"&THORN;");
	$htmlentities[]=array("ent"=>"ß","html"=>"&szlig;");
	$htmlentities[]=array("ent"=>"à","html"=>"&agrave;");
	$htmlentities[]=array("ent"=>"á","html"=>"&aacute;");
	$htmlentities[]=array("ent"=>"â","html"=>"&acirc;");
	$htmlentities[]=array("ent"=>"ã","html"=>"&atilde;");
	$htmlentities[]=array("ent"=>"ä","html"=>"&auml;");
	$htmlentities[]=array("ent"=>"å","html"=>"&aring;");
	$htmlentities[]=array("ent"=>"æ","html"=>"&aelig;");
	$htmlentities[]=array("ent"=>"ç","html"=>"&ccedil;");
	$htmlentities[]=array("ent"=>"è","html"=>"&egrave;");
	$htmlentities[]=array("ent"=>"é","html"=>"&eacute;");
	$htmlentities[]=array("ent"=>"ê","html"=>"&ecirc;");
	$htmlentities[]=array("ent"=>"ë","html"=>"&euml;");
	$htmlentities[]=array("ent"=>"ì","html"=>"&igrave;");
	$htmlentities[]=array("ent"=>"í","html"=>"&iacute;");
	$htmlentities[]=array("ent"=>"î","html"=>"&icirc;");
	$htmlentities[]=array("ent"=>"ï","html"=>"&iuml;");
	$htmlentities[]=array("ent"=>"ð","html"=>"&eth;");
	$htmlentities[]=array("ent"=>"ñ","html"=>"&ntilde;");
	$htmlentities[]=array("ent"=>"ò","html"=>"&ograve;");
	$htmlentities[]=array("ent"=>"ó","html"=>"&oacute;");
	$htmlentities[]=array("ent"=>"ô","html"=>"&ocirc;");
	$htmlentities[]=array("ent"=>"õ","html"=>"&otilde;");
	$htmlentities[]=array("ent"=>"ö","html"=>"&ouml;");
	$htmlentities[]=array("ent"=>"ø","html"=>"&oslash;");
	$htmlentities[]=array("ent"=>"ù","html"=>"&ugrave;");
	$htmlentities[]=array("ent"=>"ú","html"=>"&uacute;");
	$htmlentities[]=array("ent"=>"û","html"=>"&ucirc;");
	$htmlentities[]=array("ent"=>"ü","html"=>"&uuml;");
	$htmlentities[]=array("ent"=>"ý","html"=>"&yacute;");
	$htmlentities[]=array("ent"=>"þ","html"=>"&thorn;");
	$htmlentities[]=array("ent"=>"ÿ","html"=>"&yuml;");
	$htmlentities[]=array("ent"=>"Œ","html"=>"&OElig;");
	$htmlentities[]=array("ent"=>"œ","html"=>"&oelig;");
	$htmlentities[]=array("ent"=>"Š","html"=>"&Scaron;");
	$htmlentities[]=array("ent"=>"š","html"=>"&scaron;");
	$htmlentities[]=array("ent"=>"Ÿ","html"=>"&Yuml;");
	$htmlentities[]=array("ent"=>"ˆ","html"=>"&circ;");
	$htmlentities[]=array("ent"=>"˜","html"=>"&tilde;");
	$htmlentities[]=array("ent"=>"–","html"=>"&ndash;");
	$htmlentities[]=array("ent"=>"—","html"=>"&mdash;");
	$htmlentities[]=array("ent"=>"‘","html"=>"&lsquo;");
	$htmlentities[]=array("ent"=>"’","html"=>"&rsquo;");
	$htmlentities[]=array("ent"=>"‚","html"=>"&sbquo;");
	$htmlentities[]=array("ent"=>"“","html"=>"&ldquo;");
	$htmlentities[]=array("ent"=>"”","html"=>"&rdquo;");
	$htmlentities[]=array("ent"=>"„","html"=>"&bdquo;");
	$htmlentities[]=array("ent"=>"†","html"=>"&dagger;");
	$htmlentities[]=array("ent"=>"‡","html"=>"&Dagger;");
	$htmlentities[]=array("ent"=>"…","html"=>"&hellip;");
	$htmlentities[]=array("ent"=>"‰","html"=>"&permil;");
	$htmlentities[]=array("ent"=>"‹","html"=>"&lsaquo;");
	$htmlentities[]=array("ent"=>"›","html"=>"&rsaquo;");
	$htmlentities[]=array("ent"=>"€","html"=>"&euro;");
	$htmlentities[]=array("ent"=>"","html"=>"’");
	$htmlentities[]=array("ent"=>"","html"=>"‘");
	$htmlentities[]=array("ent"=>"","html"=>"“");
	$htmlentities[]=array("ent"=>"","html"=>"”");
	$htmlentities[]=array("ent"=>"","html"=>"&ndash;");
	$htmlentities[]=array("ent"=>" ","html"=>"&ensp;");
	$htmlentities[]=array("ent"=>" ","html"=>"&emsp;");
	$htmlentities[]=array("ent"=>"E’","html"=>"&Egrave;");
	$htmlentities[]=array("ent"=>"A’","html"=>"&Agrave;");
	$htmlentities[]=array("ent"=>"O’","html"=>"&Ograve;");
	$htmlentities[]=array("ent"=>"I’","html"=>"&Igrave;");
	$htmlentities[]=array("ent"=>"U’","html"=>"&Ugrave;");

	$string=stripslashes($string);
	$string=trim($string," \n\r"); //tolgo tutti gli spazi dagli estremi
	$string=str_replace("\r","",$string); //tolgo tutti gli accapi windows
	$string=b3_closetag($string); //chiudo i tag aperti
	
	for($i=0;isset($htmlentities[$i]['ent']);$i++) {
		$string=str_replace($htmlentities[$i]['ent'],$htmlentities[$i]['html'],$string);
		}

	$string=preg_replace("/&#([0-9]{2,5});/","&#$1;",$string); //ripristino i caratteri speciali scritti nel formato &#nnnn;

	if($param!="*"&&$param!="") {
		//ricavo i tag ammessi
		$allow=explode(",",$param);
		
		// ripristino i tag ammessi
		$replace_string="";
		for($i=0;isset($allow[$i]);$i++) {
			if($allow[$i]=="a") $allow_a=true;
			elseif($allow[$i]=="table") $allow_table=true;
			elseif($allow[$i]=="img") $allow_img=true;
			elseif($allow[$i]=="abbr") $allow_abbr=true;
			elseif($allow[$i]=="acronym") $allow_acronym=true;
			elseif($allow[$i]=="cite") $allow_cite=true;
			elseif($allow[$i]=="div") $allow_div=true;
			elseif($allow[$i]=="flash") $allow_flash=true;
			elseif($allow[$i]=="b") $string=preg_replace("/(&lt;\/?)b|B(&gt;)/","$1strong$2",$string);
			elseif($allow[$i]=="i") $string=preg_replace("/(&lt;\/?)(i|I)(&gt;)/","$1em$3",$string);
			else { $replace_string.=$allow[$i]."|"; }
			if($allow[$i]=="p") { $allow_p=true; }
			
			$string=preg_replace('#(&lt;\/?)'.strtoupper($allow[$i]).'(&gt;)#','$1'.$allow[$i].'$2',$string);
			}
		$string=preg_replace("/&lt;(\/?(".rtrim($replace_string,"|")."))&gt;/","<$1>",$string);

		$string=" ".$string." "; //aggiungo uno spazio a inizio e uno alla fine senno' ci sono dei problemi col detect degli url scritti all'inizio o alla fine... trimmo alla fine

		if(isset($allow_table)&&$allow_table==true) {
			/* AUTODETECT DELLE TABELLE */
			$find=array(
				'/&lt;table(.*?)&gt;(.*?)&lt;\/table&gt;/i',
				'/\s*?&lt;thead(.*?)&gt;(.*?)&lt;\/thead&gt;\s*?/i',
				'/\s*?&lt;tbody(.*?)&gt;(.*?)&lt;\/tbody&gt;\s*?/i',
				'/\s*?&lt;tr(.*?)&gt;(.*?)&lt;\/tr&gt;\s*?/i',
				'/\s*?&lt;th(.*?)&gt;(.*?)&lt;\/th&gt;\s*?/i',
				'/\s*?&lt;td(.*?)&gt;(.*?)&lt;\/td&gt;\s*?/i',
				'/\s*?&lt;colgroup(.*?)&gt;(.*?)&lt;\/colgroup&gt;\s*?/i',
				'/\s*?&lt;col(.*?)&gt;\s*?/i'
				);
			$replace=array(
				"<table$1>$2</table>",
				"<thead$1>$2</thead>",
				"<tbody$1>$2</tbody>",
				"<tr$1>$2</tr>",
				"<th$1>$2</th>",
				"<td$1>$2</td>",
				"<colgroup$1>$2</colgroup>",
				"<col$1>$2</col>"
				);
			$string=preg_replace($find,$replace,$string);
			}

		if(isset($allow_img)&&$allow_img==true) {
			/* AUTODETECT DELLE IMMAGINI */
			$find=array(
				'/&lt;img (.*?)&gt;/i',
				);
			$replace=array(
				"<img $1>",
				);
			$string=preg_replace($find,$replace,$string);
			}

		if(isset($allow_div)&&$allow_div==true) {
			$find=array(
				'/&lt;div(.*?)&gt;(.*)&lt;\/div&gt;/i',
				);
			$replace=array(
				"<div$1>$2</div>",
				);
			$string=preg_replace($find,$replace,$string);
			}

		/* acronym */
		if(isset($allow_acronym)&&$allow_acronym==true) {
			$find=array('/&lt;acronym title="(.*?)"&gt;(.*?)&lt;\/acronym&gt;/');
			$replace=array("<acronym title=\"$1\">$2</acronym>");
			$string=preg_replace($find,$replace,$string);
			}

		/* abbr */
		if(isset($allow_abbr)&&$allow_abbr==true) {
			$find=array('/&lt;abbr title="(.*?)"&gt;(.*?)&lt;\/abbr&gt;/');
			$replace=array("<abbr title=\"$1\">$2</abbr>");
			$string=preg_replace($find,$replace,$string);
			}

		/* cite */
		if(isset($allow_abbr)&&$allow_abbr==true) {
			$find=array('/&lt;cite( title=".*?")?&gt;(.*?)&lt;\/cite&gt;/');
			$replace=array("<cite$1>$2</cite>");
			$string=preg_replace($find,$replace,$string);
			}

		/* embed flash */
		if(isset($allow_flash)&&$allow_flash==true) {
			$find=array('/&lt;iframe(.*?src=".*?)&gt;&lt;\/iframe&gt;/','/&lt;(\/?)param(.*?)&gt;/','/&lt;embed(.*?)&gt;/','/&lt;(\/?)object(.*?)&gt;/');
			$replace=array("<iframe$1></iframe>","<$1param$2>","<embed$1>","<$1object$2>");
			$string=preg_replace($find,$replace,$string);
			}

		//workaround per gli ul ed ol
		$listfind=array("/<(ul|ol)>/","/<\/(ul|ol)>/");
		$listreplace=array("</p><span><$1>","</$1></span><p>");
		$string=preg_replace($listfind,$listreplace,$string);

		}
		
	if($param=="*"||(isset($allow_a)&&$allow_a==true)) {
		/* AUTODETECT DI INDIRIZZI INTERNET ED E-MAIL */
		$find=array(
			'/&lt;a href="(http:|https:|ftp:)?(\/\/[[:alnum:]|\.|\-|_]+[[:alpha:]]{2,4}[^"]*?)"((?: +[[:alpha:]]*="[^"]*")*)&gt;(.*?)&lt;\/a&gt;/si',
			'/(?<!="|=\'|=)(http:|https:|ftp:)(\/\/[[:alnum:]|\.|\-|_]+[[:alpha:]]{2,4}\/?[[:alnum:]|\/|\-|_|\.|\?|=|#|&|;|:]*)/si',
			'/(?<!\/\/|%2F%2F|">)(www\.[[:alnum:]|\.|\-|_]+[[:alpha:]]{2,4}\/?[[:alnum:]|\/|\-|_|\.|\?|=|#|&|;|:]*)/si',
			'/&lt;a href="([[:punct:][:alnum:]]*?)"(.*?)&gt;(.*?)&lt;\/a&gt;/si',
			'/&lt;a name="([[:punct:][:alnum:]]*?)"&gt;&lt;\/a&gt;/si',
			'/&lt;a href="mailto:([[:alnum:]|\.|\-|_]+@[[:alnum:]|\.|\-|_]+[A-Z]{2,4})"([^&]*)&gt;(.*?)&lt;\/a&gt;/si',
			'/(?<!mailto:|="|=\'|=)(\b[A-Z0-9\._%+\-]+@[A-Z0-9\.\-]+\.[A-Z]{2,4}\b)/si'
			);
		$replace=array(
			"<a href=\"$1$2\"$3>$4</a>",
			"<a href=\"$1$2$3\">$1$2$3</a>",
			"<a href=\"http://$1\">$1</a>",
			"<a href=\"$1\"$2>$3</a>",
			"<a name=\"$1\"></a>",
			"<a href=\"mailto:$1\"$2>$3</a>",
			"<a href=\"mailto:$1\">$1</a>",
			);
		$string=preg_replace($find,$replace,$string);
		}
	
	$string=trim($string," ");
	$string=preg_replace('/<a href="[^"]*"((?: +[[:alpha:]]*="[^"]*")*)>\s*<\/a>/s',"",$string); //remove empty links
	$string=preg_replace("/<br( \/)?>\s?<br( \/)?>/s","</p><p>",$string);
	$string=str_replace("<br />\n","<br />",$string);
	$string=str_replace("</p>\n\n","</p>",$string);
	$string=preg_replace("#^<p></p>#","",$string);
	$string=preg_replace("#<p></p>$#","",$string);
	$string=preg_replace("#<p><br /></p>$#","",$string);
	$string=str_replace("<br />","<br />\n",$string);
	$string=str_replace("</p>","</p>\n\n",$string);
	$string=trim($string," ");

	// tolgo tutti gli spazi multipli
	$string=preg_replace("/ +/"," ",$string);

	if($slashes==true) { $string=addslashes($string); }

	//rilascio la stringa elaborata
	return $string;
	}


/******************************************************************************
 LMTHIZE: converte del codice html in stringa
     utile per inserire del testo html all'interno delle textarea e degli input
     b3_lmthize(string [,type("textarea"|"input")]);
******************************************************************************/

function b3_lmthize($string,$type="textarea") {
	$string=stripslashes($string);
	$string=trim($string,"\n");
	
	//sostituzione degli URL autodetectati
	$find=array(
		'/<a href="(http|ftp)(:\/\/)([[:alnum:][:punct:]]*\.[[:alnum:]]{2,4}[\/\.*]?[^<>\s]*)">(\1?\2?\3)<\/a>/',
		'/<a href="mailto:([[:alnum:][:punct:]]*@[[:alnum:][:punct:]]*\.[[:alnum:]]{2,4}[^<>\s]*)">\1<\/a>/',
		);
	$replace=array(
		'$4',
		'$1',
		);
	$string=preg_replace($find,$replace,$string);
	
	if($type=="input") { $string=str_replace('"','&quot;',$string); }
	elseif($type=="textarea") {
		$string=str_replace('&','&amp;',$string);
		$string=str_replace('<','&lt;',$string);
		$string=str_replace('>','&gt;',$string);
		}
	
	return $string;
	}


/******************************************************************************
UNHTMLIZE: converte gli html special chars in caratteri UTF8
     v.1.0

     history
******************************************************************************/

function b3_unhtmlize($string) {
	/***** HTML ENTITIES *** la funzione htmlentities ne supporta pochissimi, meglio che mi faccio i miei *****/
	$htmlentities=array();
	$htmlentities[]=array("ent"=>"&","html"=>"&amp;");
	//$htmlentities[]=array("ent"=>'"',"html"=>"&quot;");
	//$htmlentities[]=array("ent"=>"'","html"=>"&apos;");
	$htmlentities[]=array("ent"=>"<","html"=>"&lt;");
	$htmlentities[]=array("ent"=>">","html"=>"&gt;");
	$htmlentities[]=array("ent"=>"¡","html"=>"&iexcl;");
	$htmlentities[]=array("ent"=>"¤","html"=>"&curren;");
	$htmlentities[]=array("ent"=>"¢","html"=>"&cent;");
	$htmlentities[]=array("ent"=>"£","html"=>"&pound;");
	$htmlentities[]=array("ent"=>"¥","html"=>"&yen;");
	$htmlentities[]=array("ent"=>"¦","html"=>"&brvbar;");
	$htmlentities[]=array("ent"=>"§","html"=>"&sect;");
	$htmlentities[]=array("ent"=>"¨","html"=>"&uml;");
	$htmlentities[]=array("ent"=>"©","html"=>"&copy;");
	$htmlentities[]=array("ent"=>"ª","html"=>"&ordf;");
	$htmlentities[]=array("ent"=>"«","html"=>"&laquo;");
	$htmlentities[]=array("ent"=>"¬","html"=>"&not;");
	$htmlentities[]=array("ent"=>"­","html"=>"&shy;");
	$htmlentities[]=array("ent"=>"®","html"=>"&reg;");
	$htmlentities[]=array("ent"=>"™","html"=>"&trade;");
	$htmlentities[]=array("ent"=>"¯","html"=>"&macr;");
	$htmlentities[]=array("ent"=>"°","html"=>"&deg;");
	$htmlentities[]=array("ent"=>"±","html"=>"&plusmn;");
	$htmlentities[]=array("ent"=>"²","html"=>"&sup2;");
	$htmlentities[]=array("ent"=>"³","html"=>"&sup3;");
	$htmlentities[]=array("ent"=>"´","html"=>"&acute;");
	$htmlentities[]=array("ent"=>"µ","html"=>"&micro;");
	$htmlentities[]=array("ent"=>"¶","html"=>"&para;");
	$htmlentities[]=array("ent"=>"·","html"=>"&middot;");
	$htmlentities[]=array("ent"=>"¸","html"=>"&cedil;");
	$htmlentities[]=array("ent"=>"¹","html"=>"&sup1;");
	$htmlentities[]=array("ent"=>"º","html"=>"&ordm;");
	$htmlentities[]=array("ent"=>"»","html"=>"&raquo;");
	$htmlentities[]=array("ent"=>"¼","html"=>"&frac14;");
	$htmlentities[]=array("ent"=>"½","html"=>"&frac12;");
	$htmlentities[]=array("ent"=>"¾","html"=>"&frac34;");
	$htmlentities[]=array("ent"=>"¿","html"=>"&iquest;");
	$htmlentities[]=array("ent"=>"×","html"=>"&times;");
	$htmlentities[]=array("ent"=>"÷","html"=>"&divide;");
	$htmlentities[]=array("ent"=>"À","html"=>"&Agrave;");
	$htmlentities[]=array("ent"=>"Á","html"=>"&Aacute;");
	$htmlentities[]=array("ent"=>"Â","html"=>"&Acirc;");
	$htmlentities[]=array("ent"=>"Ã","html"=>"&Atilde;");
	$htmlentities[]=array("ent"=>"Ä","html"=>"&Auml;");
	$htmlentities[]=array("ent"=>"Å","html"=>"&Aring;");
	$htmlentities[]=array("ent"=>"Æ","html"=>"&AElig;");
	$htmlentities[]=array("ent"=>"Ç","html"=>"&Ccedil;");
	$htmlentities[]=array("ent"=>"È","html"=>"&Egrave;");
	$htmlentities[]=array("ent"=>"É","html"=>"&Eacute;");
	$htmlentities[]=array("ent"=>"Ê","html"=>"&Ecirc;");
	$htmlentities[]=array("ent"=>"Ë","html"=>"&Euml;");
	$htmlentities[]=array("ent"=>"Ì","html"=>"&Igrave;");
	$htmlentities[]=array("ent"=>"Í","html"=>"&Iacute;");
	$htmlentities[]=array("ent"=>"Î","html"=>"&Icirc;");
	$htmlentities[]=array("ent"=>"Ï","html"=>"&Iuml;");
	$htmlentities[]=array("ent"=>"Ð","html"=>"&ETH;");
	$htmlentities[]=array("ent"=>"Ñ","html"=>"&Ntilde;");
	$htmlentities[]=array("ent"=>"Ò","html"=>"&Ograve;");
	$htmlentities[]=array("ent"=>"Ó","html"=>"&Oacute;");
	$htmlentities[]=array("ent"=>"Ô","html"=>"&Ocirc;");
	$htmlentities[]=array("ent"=>"Õ","html"=>"&Otilde;");
	$htmlentities[]=array("ent"=>"Ö","html"=>"&Ouml;");
	$htmlentities[]=array("ent"=>"Ø","html"=>"&Oslash;");
	$htmlentities[]=array("ent"=>"Ù","html"=>"&Ugrave;");
	$htmlentities[]=array("ent"=>"Ú","html"=>"&Uacute;");
	$htmlentities[]=array("ent"=>"Û","html"=>"&Ucirc;");
	$htmlentities[]=array("ent"=>"Ü","html"=>"&Uuml;");
	$htmlentities[]=array("ent"=>"Ý","html"=>"&Yacute;");
	$htmlentities[]=array("ent"=>"Þ","html"=>"&THORN;");
	$htmlentities[]=array("ent"=>"ß","html"=>"&szlig;");
	$htmlentities[]=array("ent"=>"à","html"=>"&agrave;");
	$htmlentities[]=array("ent"=>"á","html"=>"&aacute;");
	$htmlentities[]=array("ent"=>"â","html"=>"&acirc;");
	$htmlentities[]=array("ent"=>"ã","html"=>"&atilde;");
	$htmlentities[]=array("ent"=>"ä","html"=>"&auml;");
	$htmlentities[]=array("ent"=>"å","html"=>"&aring;");
	$htmlentities[]=array("ent"=>"æ","html"=>"&aelig;");
	$htmlentities[]=array("ent"=>"ç","html"=>"&ccedil;");
	$htmlentities[]=array("ent"=>"è","html"=>"&egrave;");
	$htmlentities[]=array("ent"=>"é","html"=>"&eacute;");
	$htmlentities[]=array("ent"=>"ê","html"=>"&ecirc;");
	$htmlentities[]=array("ent"=>"ë","html"=>"&euml;");
	$htmlentities[]=array("ent"=>"ì","html"=>"&igrave;");
	$htmlentities[]=array("ent"=>"í","html"=>"&iacute;");
	$htmlentities[]=array("ent"=>"î","html"=>"&icirc;");
	$htmlentities[]=array("ent"=>"ï","html"=>"&iuml;");
	$htmlentities[]=array("ent"=>"ð","html"=>"&eth;");
	$htmlentities[]=array("ent"=>"ñ","html"=>"&ntilde;");
	$htmlentities[]=array("ent"=>"ò","html"=>"&ograve;");
	$htmlentities[]=array("ent"=>"ó","html"=>"&oacute;");
	$htmlentities[]=array("ent"=>"ô","html"=>"&ocirc;");
	$htmlentities[]=array("ent"=>"õ","html"=>"&otilde;");
	$htmlentities[]=array("ent"=>"ö","html"=>"&ouml;");
	$htmlentities[]=array("ent"=>"ø","html"=>"&oslash;");
	$htmlentities[]=array("ent"=>"ù","html"=>"&ugrave;");
	$htmlentities[]=array("ent"=>"ú","html"=>"&uacute;");
	$htmlentities[]=array("ent"=>"û","html"=>"&ucirc;");
	$htmlentities[]=array("ent"=>"ü","html"=>"&uuml;");
	$htmlentities[]=array("ent"=>"ý","html"=>"&yacute;");
	$htmlentities[]=array("ent"=>"þ","html"=>"&thorn;");
	$htmlentities[]=array("ent"=>"ÿ","html"=>"&yuml;");
	$htmlentities[]=array("ent"=>"Œ","html"=>"&OElig;");
	$htmlentities[]=array("ent"=>"œ","html"=>"&oelig;");
	$htmlentities[]=array("ent"=>"Š","html"=>"&Scaron;");
	$htmlentities[]=array("ent"=>"š","html"=>"&scaron;");
	$htmlentities[]=array("ent"=>"Ÿ","html"=>"&Yuml;");
	$htmlentities[]=array("ent"=>"ˆ","html"=>"&circ;");
	$htmlentities[]=array("ent"=>"˜","html"=>"&tilde;");
	$htmlentities[]=array("ent"=>"–","html"=>"&ndash;");
	$htmlentities[]=array("ent"=>"—","html"=>"&mdash;");
	$htmlentities[]=array("ent"=>"'","html"=>"&lsquo;");
	$htmlentities[]=array("ent"=>"'","html"=>"&rsquo;");
	$htmlentities[]=array("ent"=>"‚","html"=>"&sbquo;");
	$htmlentities[]=array("ent"=>'"',"html"=>"&ldquo;");
	$htmlentities[]=array("ent"=>'"',"html"=>"&rdquo;");
	$htmlentities[]=array("ent"=>"„","html"=>"&bdquo;");
	$htmlentities[]=array("ent"=>"†","html"=>"&dagger;");
	$htmlentities[]=array("ent"=>"‡","html"=>"&Dagger;");
	$htmlentities[]=array("ent"=>"…","html"=>"&hellip;");
	$htmlentities[]=array("ent"=>"‰","html"=>"&permil;");
	$htmlentities[]=array("ent"=>"‹","html"=>"&lsaquo;");
	$htmlentities[]=array("ent"=>"›","html"=>"&rsaquo;");
	$htmlentities[]=array("ent"=>"€","html"=>"&euro;");
	
	foreach($htmlentities as $ent) {
		$string=str_replace($ent['html'],$ent['ent'],$string);
		}

	$string=strip_tags($string,"<a><strong><em><br>");
	return $string;
	}

	
	
/******************************************************************************
 CLOSETAG: chiude i tag che sono aperti in una stringa
     utile per quando si tronca una stringa con substr() e magari qualche tag
     rimane aperto nella parte tronca: in questo modo si chiudono.
     v.1.0
******************************************************************************/

function b3_closetag($string) {
	//array dei tag supportati
	$o_tag=array("<a href","<b>","<i>","<u>","<em>","<strong>","<ol>","<ul>","<p>");
	$c_tag=array("</a>","</b>","</i>","</u>","</em>","</strong>","</ol>","</ul>","</p>");

	for($i=0;isset($o_tag[$i]);$i++) {
		// su queste due variabili conto i tag aperti e chiusi
		$opentag=substr_count($string,$o_tag[$i]);
		$closetag=substr_count($string,$c_tag[$i]);
		//aggiungo in coda alla stringa i tag di chiusura dei rispettivi tag aperti
		for(;$closetag<$opentag;$closetag++) {
			$string.=$c_tag[$i];
			}
		}

	return $string;
	}


/******************************************************************************
TITLE2DIR: converte i titoli in directory
     v.1.0
******************************************************************************/

function title2dir($titolo) {
	$titolo=preg_replace('/&(.).{0,5}?;/','$1',$titolo);
	$titolo=preg_replace('/&/','e',$titolo);
	$titolo=preg_replace('/%/','percento',$titolo);
	$titolo=preg_replace('/[ _$#\/]/','-',$titolo);
	$titolo=str_replace('à','a',$titolo);
	$titolo=str_replace('è','e',$titolo);
	$titolo=str_replace('é','e',$titolo);
	$titolo=str_replace('ì','i',$titolo);
	$titolo=str_replace('ò','o',$titolo);
	$titolo=str_replace('ù','u',$titolo);
	$titolo=preg_replace('/[^A-Za-z]/','',$titolo);
	if(strlen($titolo)>64) $titolo=substr($titolo,0,64);
	return $titolo;
	}


/************************/
/* GENERAZIONE DEI FORM */
/************************/

/******************************************************************************
 CREATE_INPUT: crea degli input accessibili
	esempio:         name    type   label                value     width  maxl  etc...   random
	b3_create_input("testo","text","Inserisci il testo","Bla bla","100%","255","checked",true)
******************************************************************************/
function b3_create_input($name,$type,$label,$value,$width="auto",$maxlength=false,$add="",$random_mode=false) {
	$string="";
	$random_mode?$rand=rand(1,999):$rand="";
	if($label!=""&&$type!="checkbox"&&$type!="radio") { $string.='<label for="'.preg_replace("/\[|\]/","",$name).$rand.'">'.$label.'</label>'; }
	$string.='<input type="'.$type.'" name="'.$name.'" id="'.preg_replace("/\[|\]/","",$name).$rand.'"';
	if($width!=""&&$type!="file") { $string.=' style="width:'.$width.';"'; }
	elseif($width!=""&&$type=="file") { $string.=' size="'.$width.'"'; }
	$string.=' value="'.str_replace('"','&quot;',$value).'"';
	if($maxlength!=false) { $string.=' maxlength="'.$maxlength.'"'; }
	if($add!="") { $string.=' '.$add; }
	$string.=' />';
	if($label!=""&&($type=="checkbox"|$type=="radio")) { $string.='<label for="'.preg_replace("/\[|\]/","",$name).$rand.'">'.$label.'</label>'; }
	return $string;
	}

function b3_create_textarea($name,$label,$value,$width="200px",$height="50px",$rich=false,$random_mode=false,$mediatable="",$mediaid="",$kaeys=false)
{
	//check the custom textarea height in the user preferences
	if(isset($GLOBALS['kaUsers'])&&isset($_SESSION['iduser']))
	{
		$tmpheight=$GLOBALS['kaUsers']->propGetValue($_SESSION['iduser'],'editor',$name.'_height');
		if($tmpheight>0) $height=$tmpheight.'px';
	}

	// update filenames with the real one recorded into db (for images, docs and media), in case the files was changed
	preg_match_all('/&lt;img .*?data-orichalcum-id="img(\d*)".*?&gt;/',$value,$matches);
	foreach($matches[1] as $k=>$v)
	{
		$query="SELECT * FROM ".TABLE_IMG." WHERE `idimg`='".$v."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results))
		{
			if($row['filename']==""&&$row['hotlink']!="") $row['filename']=$row['hotlink'];
			if($row['filetype']==1)
			{
				// for the images, display the full-size image
				$value=preg_replace('/(&lt;img .*?src=")([^"]*?)(" [^&]*?data-orichalcum-id="img'.$v.'"[^&]*?&gt;)/','$1'.BASEDIR.DIR_IMG.$row['idimg'].'/'.$row['filename'].'$3',$value);
			} elseif($row['filetype']==2) {
				// for the videos, display the thumbnail
				$value=preg_replace('/(&lt;img .*?src=")([^"]*?)(" [^&]*?data-orichalcum-id="img'.$v.'"[^&]*?&gt;)/','$1'.BASEDIR.DIR_MEDIA.$row['idimg'].'/'.$row['thumbnail'].'$3',$value);
			}
		}
	}
	preg_match_all('/&lt;img .*?data-orichalcum-id="thm(\d*)".*?&gt;/',$value,$matches);
	foreach($matches[1] as $k=>$v)
	{
		$query="SELECT * FROM ".TABLE_IMG." WHERE `idimg`='".$v."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
			if($row['thumbnail']==""&&$row['hotlink']!="") $row['thumbnail']=$row['hotlink'];
			$value=preg_replace('/(&lt;img .*?src=")([^"]*?)(" [^&]*?data-orichalcum-id="thm'.$v.'"[^&]*?&gt;)/','$1'.BASEDIR.DIR_IMG.$row['idimg'].'/'.$row['thumbnail'].'$3',$value);
		}
	}
	
	preg_match_all('/&lt;a .*?id="doc(\d*)".*?&gt;/',$value,$matches);
	foreach($matches[1] as $k=>$v)
	{
		$query="SELECT * FROM ".TABLE_DOCS." WHERE `iddoc`='".$v."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results))
		{
			$value=preg_replace('/(&lt;a .*?href=")([^"]*)(" .*?id="doc'.$v.'".*?&gt;)/','$1'.BASEDIR.DIR_DOCS.$row['iddoc'].'/'.$row['filename'].'$3',$value);
		}
	}


	$string="";
	$random_mode?$rand=rand(1,666):$rand="";
	$id=preg_replace("/\[|\]/","",$name).$rand;
	if($label!="") { $string.='<label for="'.preg_replace("/\[|\]/","",$name).$rand.'">'.$label.'</label>'; }
	
	$editor= $rich==false ? "none" : "kzen";
	$string.='<textarea editor="'.$editor.'" name="'.$name.'" id="'.$id.'" style="width:'.$width.';height:'.$height.';">'.$value.'</textarea>';
	return $string;
}

function b3_create_select($name,$label,$labels,$values,$selected="",$width="auto",$random_mode=false,$add="") {
	$string="";
	$random_mode?$rand=rand(1,666):$rand="";
	if($label!="") { $string.='<label for="'.preg_replace("/\[|\]/","",$name).$rand.'">'.$label.'</label>'; }
	$string.='<select name="'.$name.'" id="'.preg_replace("/\[|\]/","",$name).$rand.'" style="width:'.$width.';" ';
	if($add!="") $string.=$add;
	$string.='>';
	for($i=0;isset($values[$i]);$i++) {
		if(!isset($labels[$i])) $labels[$i]="";
		$string.='<option value="'.$values[$i].'"';
		if($values[$i]==$selected) { $string.=' selected'; }
		$string.='>'.$labels[$i].'</option>';
		}
	$string.='</select>';
	return $string;
	}

	
	
/***************************************************
* TAR GZ Archive Manager
***************************************************/

class kTarGz
{
	protected $filehandler, $basedir, $exclude;

	public function __construct()
	{
		$this->exclude=array();
	}

	// archive and compress a dir or a file
	public function targz($gzfile, $dir)
	{
		$this->tarencode(str_replace(".gz",".tar",$gzfile), $dir);
		$this->gzcompress($gzfile, str_replace(".gz",".tar",$gzfile));
	}
	
	public function gzcompress($gzfile, $file)
	{
		if(!file_exists($file) || !is_file($file)) return false;
		
		$string=file_get_contents($file);
		$gz=gzopen($gzfile,'w9');
		gzwrite($gz, $string);
		gzclose($gz);
	}
	
	public function tarencode($tarfile, $basedir)
	{
		$this->tarOpen($tarfile);
		if($this->filehandler!=false)
		{
			$this->tarSetBaseDir($basedir);
			tarRecursiveAdd($basedir);
		}
		$this->tarClose();
	}
	
	// open file handler
	public function tarOpen($tarfile)
	{
		$this->filehandler=fopen($tarfile, "x");
		return $this->filehandler;
	}
	
	// set base dir
	public function tarSetBaseDir($basedir)
	{
		$this->basedir=rtrim($basedir,"/ ")."/";
	}

	// set dirs and files to exclude
	public function tarExclude($dir)
	{
		$dir=rtrim($dir,"/ ");
		$this->exclude[$dir]=true;
	}

	// add each file to tar, recursively
	public function tarRecursiveAdd($dir)
	{
		$dir=rtrim($dir,"/ ");
		if(!file_exists($dir)) return false;
		if(isset($this->exclude[$dir])) return false;

		if(is_dir($dir))
		{
			foreach(scandir($dir) as $file)
			{
				if(trim($file,".")!="") $this->tarRecursiveAdd($dir."/".$file);
			}

		} else {
			$this->tarAddHeader($dir);
			$this->tarWriteContents($dir);
		}

	}
	
	// Adds file header to the tar file, it is used before adding file content.
	public function tarAddHeader($dir)
	{
		$archfn=substr($dir, strlen($this->basedir));
		$info=stat($dir);
		$ouid=sprintf("%6s ", decoct($info[4]));
		$ogid=sprintf("%6s ", decoct($info[5]));
		$omode=sprintf("%6s ", decoct(fileperms($dir)));
		$omtime=sprintf("%11s", decoct(filemtime($dir)));
		if (@is_dir($dir))
		{
			 $type="5";
			 $osize=sprintf("%11s ", decoct(0));
		}
		else
		{
			 $type='';
			 $osize=sprintf("%11s ", decoct(filesize($dir)));
			 clearstatcache();
		}
		$dmajor = '';
		$dminor = '';
		$gname = '';
		$linkname = '';
		$magic = '';
		$prefix = '';
		$uname = '';
		$version = '';
		$chunkbeforeCS=pack("a100a8a8a8a12A12",$archfn, $omode, $ouid, $ogid, $osize, $omtime);
		$chunkafterCS=pack("a1a100a6a2a32a32a8a8a155a12", $type, $linkname, $magic, $version, $uname, $gname, $dmajor, $dminor ,$prefix,'');

		$checksum = 0;
		for ($i=0; $i<148; $i++) $checksum+=ord(substr($chunkbeforeCS,$i,1));
		for ($i=148; $i<156; $i++) $checksum+=ord(' ');
		for ($i=156, $j=0; $i<512; $i++, $j++)    $checksum+=ord(substr($chunkafterCS,$j,1));

		fwrite($this->filehandler,$chunkbeforeCS,148);
		$checksum=sprintf("%6s ",decoct($checksum));
		$bdchecksum=pack("a8", $checksum);
		fwrite($this->filehandler,$bdchecksum,8);
		fwrite($this->filehandler,$chunkafterCS,356);
		return true;
	}

	// Writes file content to the tar file must be called after a TarAddHeader
	public function tarWriteContents($dir)
	{
		if (@is_dir($dir))
		{
			return;
		}
		else
		{
			$size=filesize($dir);
			$padding=$size % 512 ? 512-$size%512 : 0;
			$f2=fopen($dir,"rb");
			while (!feof($f2)) fwrite($this->filehandler,fread($f2,1024*1024));
			$pstr=sprintf("a%d",$padding);
			fwrite($this->filehandler,pack($pstr,''));
		}
	}

	// Adds 1024 byte footer at the end of the tar file
	public function tarAddFooter()
	{
		fwrite($this->filehandler,pack('a1024',''));
	}
	
	public function tarClose()
	{
		$this->tarAddFooter();
		fclose($this->filehandler);
		$this->filehandler=false;
	}

}

