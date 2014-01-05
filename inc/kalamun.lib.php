<?php
/* (c) Kalamun.org - GNU/GPL 3 */

function kInit() {
	if(!defined("BASERELDIR")||!defined("ADMINDIR")) {
		$reldir="";
		$dirname=dirname($_SERVER['PHP_SELF']);
		$chars=count_chars(substr($dirname,strlen(BASEDIR)),1);
		if(!isset($chars[47])) $chars[47]=0;
		for($i=1;$i<=$chars[47];$i++) { $reldir.='../'; }
		if(!defined("BASERELDIR")) define("BASERELDIR",$reldir);
		if(!defined("ADMINDIR")) define("ADMINDIR",BASEDIR."admin/");
		}
	
	function kTxtLog($log) {
		if(defined("MYSQL_LOG")&&MYSQL_LOG==true) {
			$log="\n                      ".date("Y-m-d H:i:s")." ------------------------------\n".number_format($GLOBALS['microseconds'],6)."   ".$log."\n";
			file_put_contents($_SERVER['DOCUMENT_ROOT'].BASEDIR.'log.txt',$log,FILE_APPEND);
			}
		}
	}
kInit();

/* utili per i testi */
class kText {
	function formatText($string) {
		global $__template;

		//<a>
		$code=array();
		$tpl=$__template->getSubTemplate('a');
		if($tpl!="") {
			$offset=0;
			while(strpos($string,"<a ",$offset)!==false) {
				$id=count($code);
				$code[$id]['start']=strpos($string,"<a ",$offset);
				$code[$id]['end']=strpos($string,"</a>",$code[$id]['start'])+4;
				$tmp=substr($string,$code[$id]['start'],$code[$id]['end']-$code[$id]['start']);
				if(strpos($tmp," ")!==false) {
					$attr_offset=0;
					while(strpos($tmp," ",$attr_offset)!==false) {
						$attr_start=strpos($tmp," ",$attr_offset);
						$attr_stop=strpos($tmp,"=",$attr_start);
						$attribute=trim(substr($tmp,$attr_start,$attr_stop-$attr_start));
						$value_start=$attr_stop+1;
						if(substr($tmp,$value_start,1)=='"') $value_start++;
						$value_stop=strpos($tmp,'"',$value_start);
						$value=substr($tmp,$value_start,$value_stop-$value_start);
						while(substr($tmp,$value_start-1,2)=='\"') {
							$value_stop=strpos($tmp,"\"",$value_stop+1);
							}
						$code[$id]['contents'][$attribute]=$value;
						$code[$id]['contents']['contents']=preg_replace('/^.*?>(.*?)<\/a>.*/',"$1",$tmp);
						$attr_offset+=$value_stop+1;
						if($attr_offset>strlen($tmp)) $attr_offset=strlen($tmp);
						}
					}
				$offset=$code[$id]['end'];
				}
			for($i=count($code)-1;$i>=0;$i--) {
				$__template->contents=$code[$i]['contents'];
				$tpl=$__template->getSubTemplate('a');
				$string=substr_replace($string,$tpl,$code[$i]['start'],$code[$i]['end']-$code[$i]['start']);
				}
			}

		//<code>
		$code=array();
		$tpl=$__template->getSubTemplate('code');
		if($tpl!="") {
			$offset=0;
			while(strpos($string,"<code>",$offset)!==false) {
				$id=count($code);
				$code[$id]['start']=strpos($string,"<code>",$offset);
				$code[$id]['end']=strpos($string,"</code>",$code[$id]['start']);
				$code[$id]['contents']=substr($string,$code[$id]['start']+6,$code[$id]['end']-$code[$id]['start']-6);
				$offset=$code[$id]['end'];
				}
			for($i=count($code)-1;$i>=0;$i--) {
				$__template->contents=$code[$i]['contents'];
				$tpl=$__template->getSubTemplate('code');
				$string=substr_replace($string,$tpl,$code[$i]['start'],$code[$i]['end']-$code[$i]['start']);
				}
			}

		return $string;
		}

	function embedImg($string) {
		global $__template;
		$kImage=new kImages;
		$images=array();

		//detect delle immagini
		$offset=0;
		while(strpos($string,"<img ",$offset)!==false) {
			$id=count($images);
			$images[$id]['start']=strpos($string,"<img ",$offset);
			$images[$id]['end']=strpos($string,">",$images[$id]['start'])+1;
			$images[$id]['html']=substr($string,$images[$id]['start'],$images[$id]['end']-$images[$id]['start']);
			if(preg_match("/^.*id=[\"']?(thumb|img)(\d+)[\"'].*$/",$images[$id]['html'])) {
				$images[$id]['type']=preg_replace("/^.*id=[\"']?(thumb|img)(\d+)[\"'].*$/","$1",$images[$id]['html']);
				$images[$id]['idimg']=preg_replace("/^.*id=[\"']?(thumb|img)(\d+)[\"'].*$/","$2",$images[$id]['html']);
				}
			strpos($images[$id]['html'],' width=')!==false?$images[$id]['width']=preg_replace("/^.*width=[\"']?(\d+)[\"'].*$/","$1",$images[$id]['html']):$images[$id]['width']="";
			strpos($images[$id]['html'],' height=')!==false?$images[$id]['height']=preg_replace("/^.*height=[\"']?(\d+)[\"'].*$/","$1",$images[$id]['html']):$images[$id]['height']="";
			if($images[$id]['width']=="") strpos($images[$id]['html'],'width:')!==false?$images[$id]['width']=preg_replace("/^.*width: ?([^;]+);.*$/","$1",$images[$id]['html']):$images[$id]['width']="";
			if($images[$id]['height']=="") strpos($images[$id]['html'],'height:')!==false?$images[$id]['height']=preg_replace("/^.*height: ?([^;]+);.*$/","$1",$images[$id]['html']):$images[$id]['height']="";
			$offset=$images[$id]['end']+1;
			}

		$embeddedimages=array();
		for($i=count($images)-1;$i>=0;$i--) {
			if(isset($images[$i]['idimg'])) {
				$__template->imgDB=$kImage->getImage($images[$i]['idimg']);
				if($images[$i]['width']!="") $__template->imgDB['width']=$images[$i]['width'];
				if($images[$i]['height']!="") $__template->imgDB['height']=$images[$i]['height'];
				$embeddedimages[]=$__template->imgDB;
				if($images[$i]['type']=="img") $tpl=$__template->getSubTemplate('image');
				else $tpl=$__template->getSubTemplate('thumbnail');
				$string=substr_replace($string,$tpl,$images[$i]['start'],$images[$i]['end']-$images[$i]['start']);
				}
			}

		return array($string,$embeddedimages);
		}

	function embedDocs($string) {
		global $__template;
		$kDocuments=new kDocuments;
		$documents=array();

		//detect delle immagini
		$offset=0;
		while(strpos($string,"<a ",$offset)!==false) {
			$id=count($documents);
			$documents[$id]['start']=strpos($string,"<a ",$offset);
			$documents[$id]['end']=strpos($string,"</a>",$documents[$id]['start'])+4;
			$documents[$id]['html']=substr($string,$documents[$id]['start'],$documents[$id]['end']-$documents[$id]['start']);
			if(preg_match("/^.*id=[\"']?doc(\d+)[\"'].*$/",$documents[$id]['html'])) {
				$documents[$id]['iddoc']=preg_replace("/^.*id=[\"']?doc(\d+)[\"'].*$/","$1",$documents[$id]['html']);
				$documents[$id]['caption']=preg_replace("/^.*id=[\"']?doc\d+[\"'].*?>(.*)<\/a>$/","$1",$documents[$id]['html']);
				}
			$offset=$documents[$id]['end'];
			}


		$embeddeddocs=array();
		for($i=count($documents)-1;$i>=0;$i--) {
			if(isset($documents[$i]['iddoc'])) {
				$__template->docDB=$kDocuments->getDocument($documents[$i]['iddoc']);
				$__template->docDB['caption']=$documents[$i]['caption'];
				$embeddeddocs[]=$__template->docDB;
				$tpl=$__template->getSubTemplate('document');
				$string=substr_replace($string,$tpl,$documents[$i]['start'],$documents[$i]['end']-$documents[$i]['start']);
				}
			}

		return array($string,$embeddeddocs);
		}

	function embedMedia($string) {
		global $__template;
		require_once('media.lib.php');
		$kMedia=new kMedia;
		$media=array();

		//detect delle immagini
		$offset=0;
		while(strpos($string,"<img ",$offset)!==false) {
			$id=count($media);
			$media[$id]['start']=strpos($string,"<img ",$offset);
			$media[$id]['end']=strpos($string,">",$media[$id]['start'])+1;
			$media[$id]['html']=substr($string,$media[$id]['start'],$media[$id]['end']-$media[$id]['start']);
			if(preg_match("/^.*id=[\"']?media(\d+)[\"'].*$/",$media[$id]['html'])) {
				$media[$id]['idmedia']=preg_replace("/^.*id=[\"']?media(\d+)[\"'].*$/","$1",$media[$id]['html']);
				}
			strpos($media[$id]['html'],' width=')!==false?$media[$id]['width']=preg_replace("/^.*width=[\"']?(\d+)[\"'].*$/","$1",$media[$id]['html']):$media[$id]['width']="";
			strpos($media[$id]['html'],' height=')!==false?$media[$id]['height']=preg_replace("/^.*height=[\"']?(\d+)[\"'].*$/","$1",$media[$id]['html']):$media[$id]['height']="";
			$offset=$media[$id]['end'];
			}

		$embeddedmedia=array();
		for($i=count($media)-1;$i>=0;$i--) {
			if(isset($media[$i]['idmedia'])) {
				$__template->mediaDB=$kMedia->getMedia($media[$i]['idmedia']);
				if($media[$i]['width']!="") $__template->mediaDB['width']=$media[$i]['width'];
				if($media[$i]['height']!="") $__template->mediaDB['height']=$media[$i]['height'];
				$embeddedmedia[]=$__template->mediaDB;
				$tpl=$__template->getSubTemplate('media');
				$string=substr_replace($string,$tpl,$media[$i]['start'],$media[$i]['end']-$media[$i]['start']);
				}
			}

		return array($string,$embeddedmedia);
		}

	/* crea delle ancore html dentro al testo in corrispondenza del tag desiderato */	
/*	function getAnchors($string,$tag) {
		$offset=0;
		$index=array();
		for($i=0;strpos($string,$tag,$offset)!==false;$i++) {
			$offset=strpos($string,$tag,$offset);
			$anchor='<a name="ilink'.$i.'"></a>';
			$index[$i]=substr($string,$offset+strlen($tag),strpos($string,'</'.trim($tag,"<> ").'>',$offset)-$offset+strlen($tag)-strlen($tag)-strlen('</'.trim($tag,"<> ").'>')+1);
			$string=substr($string,0,$offset).$anchor.substr($string,$offset);
			$offset+=strlen($anchor)+strlen($tag);
			if(strpos($string,$tag,$offset)!==false) $offset=strpos($string,$tag,$offset);
			else $offset=strlen($string);
			}
		return array("string"=>$string,"index"=>$index);
		}
	*/
	}


/* statistiche */
function kStatistiche() {
	/* sposto la roba vecchia in archivio */
	$q="INSERT INTO ".TABLE_STATS_ARCHIVE." (`ip`,`date`,`url`,`referer`,`system`,`contacts`,`ll`) SELECT `ip`,`date`,`url`,`referer`,`system`,`contacts`,`ll` FROM ".TABLE_STATISTICHE." WHERE `date`<'".date("Y-m-d H:i",time()-3600)."'";
	/**/ $GLOBALS['microseconds']=microtime();
	if(mysql_query($q)) {
		/**/ kTxtLog($q);
		$q="DELETE FROM ".TABLE_STATISTICHE." WHERE `date`<'".date("Y-m-d H:i",time()-3600)."'";
		mysql_query($q);
		/**/ kTxtLog($q);
		}

	/* cattura indirizzo */
	$url=trim(BASEDIR,"/ ").'/';
	if(isset($_GET['lang'])) $url.=$_GET['lang'].'/';
	if(isset($GLOBALS['__dir__'])) $url.=$GLOBALS['__dir__'].'/';
	if(isset($GLOBALS['__subdir__'])) $url.=$GLOBALS['__subdir__'].'/';
	if(isset($GLOBALS['__subsubdir__'])) $url.=$GLOBALS['__subsubdir__'].'/';
	$url=trim($url,"/ ").'?';
	foreach($_GET as $k=>$v) {
		if($k!='lang'&&$k!='dir'&&$k!='subdir'&&$k!='subsubdir') $url.=$k.'='.urlencode($v).'&';
		}
	$url=trim($url,"&?");

	isset($_SERVER['HTTP_REFERER'])?$referer=addslashes($_SERVER['HTTP_REFERER']):$referer="";
	$system=$_SERVER['HTTP_USER_AGENT'];
	$ip=$_SERVER['REMOTE_ADDR'];

	/* filters: record only knowed browsers */
	if(strpos($system,"MSIE")!==false
		||strpos($system,"Firefox")!==false
		||strpos($system,"Chrome")!==false
		||strpos($system,"Safari")!==false
		||strpos($system,"Opera")!==false
		) {

		/* verifico se e' una nuova visita */
		$q="SELECT count(*) AS tot FROM ".TABLE_STATISTICHE." WHERE ip='".$ip."' AND system='".mysql_real_escape_string($system)."'";
		kTxtLog($q);
		$p=mysql_query($q);
		$r=mysql_fetch_array($p);
		if($r['tot']==0) {
			//nuova visita
			$q="INSERT INTO ".TABLE_STATISTICHE." (`ip`,`date`,`url`,`referer`,`system`,`contacts`,`ll`) VALUES('".$ip."','".date("Y-m-d H:i",time())."','".mysql_real_escape_string($url)."','".mysql_real_escape_string($referer)."','".mysql_real_escape_string($system)."',1,'')";
				mysql_query($q);
				/**/ kTxtLog($q);
			}
		else {
			//nuovo contatto
			$q="UPDATE ".TABLE_STATISTICHE." SET `date`='".date("Y-m-d H:i",time())."',`url`=CONCAT(url,'"."\n".$url."'),`contacts`=`contacts`+1 WHERE `ip`='".$ip."' AND system='".mysql_real_escape_string($system)."'";
				$results=mysql_query($q);
				/**/ kTxtLog($q);
			}
		}
	}


?>