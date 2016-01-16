<?php 
/* (c)2011 Kalamun.org - GPL 3 */

class kaBanner {
	protected $kaDocuments;
	
	function __construct() {
		require_once('../inc/documents.lib.php');
		$this->kaDocuments=new kaDocuments;
		}
	
	function add($banner,$title,$description,$url,$categoria,$ll=null) {
		if($ll==null) $ll=$_SESSION['ll'];
		$log="";
		$query="SELECT ordine FROM `".TABLE_BANNER."` WHERE categoria='".ksql_real_escape_string($categoria)."' ORDER BY `ordine` DESC LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$ordine=$row['ordine']+1;
		
		$query="SELECT MIN(`views`) AS `min` FROM `".TABLE_BANNER."` WHERE `categoria`='".ksql_real_escape_string($categoria)."'";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$offset=$row['min'];

		$query="INSERT INTO `".TABLE_BANNER."` (`online`,`title`,`description`,`url`,`categoria`,`views`,`offset`,`clicks`,`ordine`,`ll`) VALUES('s','".b3_htmlize($title,true,"")."','".b3_htmlize($description,true)."','".b3_htmlize($url,true,"")."','".intval($categoria)."','0','".intval($offset)."','0',".$ordine.",'".$ll."')";
		if(!ksql_query($query)) { $log="Problemi durante l'inserimento nel database"; } else { $id=ksql_insert_id(); }
		
		//upload del file del banner
		if($log==""&&$banner['tmp_name']!="") {
			if(!$this->kaDocuments->upload($banner['tmp_name'],$banner['name'],TABLE_BANNER,$id,$title)) $log.="Errore durante il caricamento del file ".$banner['name'].".<br />";
			}
		if($log!="") return false;
		return $id;
		}

	function update($idbanner,$title,$description,$url,$idcat,$online) {
		if($online!='s') $online='n';

		$query="SELECT `ordine`,`categoria` FROM `".TABLE_BANNER."` WHERE `idbanner`='".intval($idbanner)."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
			$query="UPDATE `".TABLE_BANNER."` SET `title`='".b3_htmlize($title,true,"")."',`description`='".b3_htmlize($description,true)."',`url`='".b3_htmlize($url,true,"")."',`online`='".$online."' WHERE `idbanner`='".intval($idbanner)."' LIMIT 1";
			if(!ksql_query($query)) return false;
			
			if($row['categoria']!=$idcat) {
				$query="SELECT `ordine` FROM `".TABLE_BANNER."` WHERE `categoria`='".intval($idcat)."' ORDER BY `ordine` LIMIT 1";
				$results=ksql_query($query);
				$r=ksql_fetch_array($results);
				if(!isset($r['ordine'])) $r['ordine']=0;
				$r['ordine']++;
				
				$query="UPDATE `".TABLE_BANNER."` SET `ordine`='".$r['ordine']."',`categoria`='".intval($idcat)."' WHERE `idbanner`='".intval($idbanner)."' LIMIT 1";
				if(!ksql_query($query)) return false;
				
				$query="UPDATE `".TABLE_BANNER."` SET `ordine`=`ordine`-1 WHERE `categoria`='".intval($row['categoria'])."' AND `ordine`>'".intval($row['ordine'])."'";
				if(!ksql_query($query)) return false;
				
				}
			return true;
			}
		return false;
		}
	function updateFile($idbanner,$file,$title=null,$width=0,$height=0,$resize="") {
		$log="";
		$banner=$this->get($idbanner);
		if($log==""&&$file['tmp_name']!=""&&$banner['idbanner']!="") {
			$ext=strtolower(substr($file['name'],strrpos($file['name'],".")+1));
			if(isset($banner['banner']['iddoc'])) {
				if(!$this->kaDocuments->update($banner['banner']['iddoc'],$file['tmp_name'],$file['name'],$title)) $log.="Errore durante il caricamento del file ".$file['name'].".<br />";
				}
			else {
				if(!$this->kaDocuments->upload($file['tmp_name'],$file['name'],TABLE_BANNER,$banner['idbanner'],$title)) $log.="Errore durante il caricamento del file ".$file['name'].".<br />";
				}
			// if image, resize
			if(($ext=="jpg"||$ext=="png"||$ext=="gif")&&$log==""&&$width>0&&$height>0) {
				require_once('../inc/images.lib.php');
				$this->kaImages=new kaImages;
				if($resize=="") $resize="inside";
				$docs=$this->kaDocuments->getList(TABLE_BANNER,$banner['idbanner']);
				if(isset($docs[0])) {
					$this->kaImages->resize(BASERELDIR.'/'.$docs[0]['url'],$width,$height,100,$resize);
					}
				}
			}
		if($log!="") return false;
		else return true;
		}

	function delete($idbanner) {
		$log="";
		$banner=$this->get($idbanner);
		
		$query="DELETE FROM ".TABLE_BANNER." WHERE idbanner=".intval($idbanner)." LIMIT 1";
		if(!ksql_query($query)) { $log="Problemi durante l'eliminazione dal database"; } else { $id=$idbanner; }

		$query="UPDATE ".TABLE_BANNER." SET ordine=ordine-1 WHERE categoria='".$banner['categoria']."' AND ordine>".$banner['ordine'];
		if(!ksql_query($query)) $log="Problemi durante il riordino del database";

		if($log==""&&isset($banner['banner']['iddoc'])) {
			if(!$this->kaDocuments->delete($banner['banner']['iddoc'])) $log.="Errore durante la rimozione del file ".$banner['banner']['name'].".<br />";
			}

		if($log!="") return false;
		else return true;
		}
	
	function get($idbanner) {
		$output=array();
		$query="SELECT * FROM ".TABLE_BANNER." WHERE `idbanner`='".intval($idbanner)."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
			$output=$row;
			}
		if(!isset($output['idbanner'])) return false;
		$banners=$this->kaDocuments->getList(TABLE_BANNER,$output['idbanner']);
		$output['banner']=isset($banners[0])?$banners[0]:array();
		return $output;
		}

	function getList($idcat) {
		$output=array();
		$query="SELECT * FROM ".TABLE_BANNER." WHERE categoria='".intval($idcat)."' AND ll='".$_SESSION['ll']."' ORDER BY ordine";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$output[]=$row;
			}
		return $output;
		}

	function sort($order,$lang=false) {
		$output=true;
		if($lang==false) $lang=$_SESSION['ll'];
		for($i=0;isset($order[$i]);$i++) {
			$query="UPDATE ".TABLE_BANNER." SET ordine=".($i+1)." WHERE idbanner=".$order[$i]." ";
			$query.=" AND ll='".$lang."' LIMIT 1";
			if(!ksql_query($query)) $output=false;
			}
		return $output;
		}	
	
	}

