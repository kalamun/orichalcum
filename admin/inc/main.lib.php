<?php
/******************************************************************************
* *************************************************************************** *
* * MAIN.LIB.PHP                                                            * *
* * Libreria di funzioni PHP di utilita' generale.                          * *
* *                                                                         * *
* * autore: Roberto Pasini - info@kalamun.org                               * *
* * Licenza GNU/GPL v.3                                                     * *
* *************************************************************************** *
******************************************************************************/

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
			'/&lt;a href="(http|https|ftp)(:\/\/[[:alnum:]|\.|-|_]+[[:alpha:]]{2,4}[^"]*?)"((?: +[[:alpha:]]*="[^"]*")*)&gt;(.*?)&lt;\/a&gt;/si',
			'/(?<!="|=)(http|https|ftp)(:\/\/[[:alnum:]|\.|-|_]+[[:alpha:]]{2,4}\/?[[:alnum:]|\/|-|_|\.|\?|=|#|&|;|:]*)/si',
			'/(?<!:\/\/|%2F%2F|">)(www\.[[:alnum:]|\.|-|_]+[[:alpha:]]{2,4}\/?[[:alnum:]|\/|-|_|\.|\?|=|#|&|;|:]*)/si',
			'/&lt;a href="([[:punct:][:alnum:]]*?)"(.*?)&gt;(.*?)&lt;\/a&gt;/si',
			'/&lt;a name="([[:punct:][:alnum:]]*?)"&gt;&lt;\/a&gt;/si',
			'/&lt;a href="mailto:([A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4})"([^&]*)&gt;(.*?)&lt;\/a&gt;/si',
			'/(?<!mailto:)(\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b)/si'
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
	//if($param=="*"||(isset($allow_p)&&$allow_p==true)) if(substr($string,0,2)!="<") $string="<p>".$string."</p>";
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
		$string=str_replace($ent['html'],utf8_encode($ent['ent']),$string);
		}

	//$string=str_replace("<br />","\n",$string);
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

function b3_create_textarea($name,$label,$value,$width="200px",$height="50px",$rich=false,$random_mode=false,$mediatable="",$mediaid="",$kaeys=false) {
	//check the custom height of the user
	if(isset($GLOBALS['kaUsers'])&&isset($_SESSION['iduser'])) {
		$tmpheight=$GLOBALS['kaUsers']->propGetValue($_SESSION['iduser'],'editor',$name.'_height');
		if($tmpheight>0) $height=$tmpheight.'px';
		}

	// update filenames with the real one recorded on db (for images, docs and media), in case the files was changed
	preg_match_all('/&lt;img .*?id="img(\d*)".*?&gt;/',$value,$matches);
	foreach($matches[1] as $k=>$v) {
		$query="SELECT * FROM ".TABLE_IMG." WHERE `idimg`='".$v."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			if($row['filename']==""&&$row['hotlink']!="") $row['filename']=$row['hotlink'];
			$value=preg_replace('/(&lt;img .*?src=")([^"]*?)(" [^&]*?id="img'.$v.'"[^&]*?&gt;)/','$1'.BASEDIR.DIR_IMG.$row['idimg'].'/'.$row['filename'].'$3',$value);
			}
		}
	preg_match_all('/&lt;img .*?id="thumb(\d*)".*?&gt;/',$value,$matches);
	foreach($matches[1] as $k=>$v) {
		$query="SELECT * FROM ".TABLE_IMG." WHERE `idimg`='".$v."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			if($row['thumbnail']==""&&$row['hotlink']!="") $row['thumbnail']=$row['hotlink'];
			$value=preg_replace('/(&lt;img .*?src=")([^"]*?)(" [^&]*?id="thumb'.$v.'"[^&]*?&gt;)/','$1'.BASEDIR.DIR_IMG.$row['idimg'].'/'.$row['thumbnail'].'$3',$value);
			}
		}
	preg_match_all('/&lt;a .*?id="doc(\d*)".*?&gt;/',$value,$matches);
	foreach($matches[1] as $k=>$v) {
		$query="SELECT * FROM ".TABLE_DOCS." WHERE `iddoc`='".$v."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			$value=preg_replace('/(&lt;a .*?href=")([^"]*)(" .*?id="doc'.$v.'".*?&gt;)/','$1'.BASEDIR.DIR_DOCS.$row['iddoc'].'/'.$row['filename'].'$3',$value);
			}
		}
	preg_match_all('/&lt;img .*?id="media(\d*)".*?&gt;/',$value,$matches);
	foreach($matches[1] as $k=>$v) {
		$query="SELECT * FROM ".TABLE_MEDIA." WHERE `idmedia`='".$v."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			if($row['thumbnail']!="") $url=BASEDIR.DIR_MEDIA.$row['idmedia'].'/'.$row['thumbnail'];
			else $url=ADMINDIR.'img/media_placeholder.png';
			$value=preg_replace('/(&lt;img .*?src=")([^"]*)(" .*?id="media'.$v.'".*?&gt;)/','$1'.$url.'$3',$value);
			}
		}

	$string="";
	$random_mode?$rand=rand(1,666):$rand="";
	$id=preg_replace("/\[|\]/","",$name).$rand;
	if($label!="") { $string.='<label for="'.preg_replace("/\[|\]/","",$name).$rand.'">'.$label.'</label>'; }
	$string.='<div class="RichContainer" mediatable="'.$mediatable.'" mediaid="'.$mediaid.'" style="width:'.$width.'">'
			.'<textarea name="'.$name.'" id="'.$id.'" style="width:100%;height:'.$height.';resize:none;">'.$value.'</textarea>'
			.'</div>';
	$string.='<script type="text/javascript">if(!kTxtArea) var kTxtArea=Array(); kTxtArea[\''.$id.'\']=kRichTextOn(\''.$id.'\','.($rich?'true':'false').','.($kaeys?"'".$kaeys."'":"false").');</script>';
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
?>