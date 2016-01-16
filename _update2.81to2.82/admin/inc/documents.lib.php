<?php /* (c) Kalamun.org - GNU/GPL 3 */

// gestione del caricamento e del ridimensionamento delle immagini

class kaDocuments {
	protected $img,$thumb;

	function __construct() {
		}

	function countList($tabella=false,$id=false,$conditions='') {
		if(!defined("TABLE_DOCS")|!defined("DIR_DOCS")) return false;
		$output=array();

		$query="SELECT count(*) AS tot FROM ".TABLE_DOCS." WHERE iddoc>0 ";
		if($tabella!="") $query.=" AND tabella='".$tabella."' ";
		if($id!="") $query.=" AND id='".$id."' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";

		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row['tot'];
		}

	function getList($tabella=false,$id=false,$orderby='ordine',$conditions='',$offset=false,$rowcount=false) {
		if(!defined("TABLE_DOCS")|!defined("DIR_DOCS")) return false;
		$output=array();

		$query="SELECT * FROM ".TABLE_DOCS." WHERE iddoc>0 ";
		if($tabella!="") $query.=" AND tabella='".$tabella."' ";
		if($id!="") $query.=" AND id='".$id."' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		if($orderby!="") $query.=" ORDER BY ".$orderby." ";
		if($offset==false) $offset=0;
		if($offset!=""||$rowcount!="") {
			$query.=" LIMIT ".$offset;
			if($rowcount!="") $query.=",".$rowcount;
			}

		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++) {
			$output[$i]=$row;
			if(trim($output[$i]['alt'])=="") $output[$i]['alt']=$output[$i]['filename'];
			if($row['filename']==""&&$row['hotlink']!="") {
				$output[$i]['filename']=basename($output[$i]['hotlink']);
				$output[$i]['url']=$output[$i]['hotlink'];
				$output[$i]['hotlink']=true;
				}
			else {
				$output[$i]['url']=DIR_DOCS.$row['iddoc'].'/'.$row['filename'];
				$output[$i]['hotlink']=false;
				}
			}
		return $output;
		}

	function getDocument($iddoc) {
		if(!defined("TABLE_DOCS")|!defined("DIR_DOCS")) return false;
		$output=array();

		$query="SELECT * FROM ".TABLE_DOCS." WHERE iddoc=".$iddoc." LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$output=$row;
		if(trim($output['alt'])=="") $output['alt']=$output['filename'];
		if($row['filename']==""&&$row['hotlink']!="") {
			$output['filename']=basename($row['hotlink']);
			$output['url']=$row['hotlink'];
			$output['hotlink']=true;
			}
		else {
			$output['url']=DIR_DOCS.$row['iddoc'].'/'.$row['filename'];
			$output['hotlink']=false;
			}
		return $output;
		}

	function updateAlt($iddoc,$alt) {
		$query="UPDATE ".TABLE_DOCS." SET alt='".b3_htmlize($alt,true,"strong,em,u,a,acronym")."' WHERE iddoc=".$iddoc;
		if(!ksql_query($query)) return false;
		return true;
		}
	
	function updateHotlink($iddoc,$hotlink) {
		$query="UPDATE ".TABLE_DOCS." SET filename='',hotlink='".b3_htmlize($hotlink,true,"")."' WHERE iddoc=".$iddoc;
		if(!ksql_query($query)) return false;
		return true;
		}
	
	function upload($file,$filename,$tabella,$id,$alt) {
		if(!defined("TABLE_DOCS")|!defined("DIR_DOCS")) return false;
		$filename=preg_replace("/([^A-Za-z0-9\._-])+/i",'_',$filename);
		if($filename!=""&&substr(strtolower($filename),-4)!='.php'&&substr(strtolower($filename),-4)!='.php3') {
			$id=intval($id);

			/* indice dell'ordine */
			$query="SELECT ordine FROM ".TABLE_DOCS." WHERE tabella='".$tabella."' AND id='".$id."' ORDER BY ordine DESC LIMIT 0,1";
			$results=ksql_query($query);
			$row=ksql_fetch_array($results);
			if($row['ordine']==false) $row['ordine']=0;
			$ordine=++$row['ordine'];
			
			$query="INSERT INTO ".TABLE_DOCS." (filename,hotlink,tabella,id,alt,ordine) VALUES('".addslashes($filename)."','','".$tabella."','".$id."','".b3_htmlize($alt,true,"strong,em,u,acronym")."',".$ordine.")";
			if(ksql_query($query)) { $iddoc=ksql_insert_id(); }
			else { return false; }
			
			mkdir(BASERELDIR.DIR_DOCS.$iddoc);
			//copio nella dir assegnata
			if(!copy($file,BASERELDIR.DIR_DOCS.$iddoc.'/'.utf8_decode($filename))) return false;

			return $iddoc;
			}
		else return false;
		}
	
	function setHotlink($filename,$tabella,$id,$alt) {
		$id=intval($id);
		$query="SELECT ordine FROM ".TABLE_DOCS." WHERE tabella='".$tabella."' AND id='".$id."' ORDER BY ordine DESC LIMIT 0,1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		if($row['ordine']==false) $row['ordine']=0;
		$ordine=++$row['ordine'];
		
		$query="INSERT INTO ".TABLE_DOCS." (filename,hotlink,tabella,id,alt,ordine) VALUES('','".b3_htmlize($filename,true,"")."','".$tabella."','".$id."','".b3_htmlize($alt,true,"strong,em,u,acronym")."',".$ordine.")";
		if(ksql_query($query)) { $iddoc=ksql_insert_id(); }
		else { return false; }
		
		mkdir(BASERELDIR.DIR_DOCS.$iddoc);

		return $iddoc;
		}

	function update($iddoc,$file,$filename,$alt=null) {
		if(!defined("TABLE_DOCS")|!defined("DIR_DOCS")) return false;
		
		$filename=preg_replace("/([^A-Za-z0-9\._-])+/i",'_',$filename);
		if($filename!=""&&substr(strtolower($filename),-4)!='.php'&&substr(strtolower($filename),-4)!='.php3') { //aggiornamento dell'alt e del documento
			$query="SELECT filename FROM ".TABLE_DOCS." WHERE iddoc=".intval($iddoc);
			$results=ksql_query($query);
			$row=ksql_fetch_array($results);
			$query="UPDATE ".TABLE_DOCS." SET filename='".addslashes($filename)."'";
			if($alt!=null) $query.=",alt='".b3_htmlize($alt,true,"strong,em,u,acronym")."'";
			$query.=" WHERE iddoc=".$iddoc;
			if(!ksql_query($query)) return false;
			
			if(!file_exists(BASERELDIR.DIR_DOCS.$iddoc)) mkdir(BASERELDIR.DIR_DOCS.$iddoc);
			@unlink(BASERELDIR.DIR_DOCS.$iddoc.'/'.$row['filename']); //elimino il vecchio documento
	
			//copio nella dir assegnata
			if(!copy($file,BASERELDIR.DIR_DOCS.$iddoc.'/'.utf8_decode($filename))) return false;
			}

		else { //aggiornamento solo dell'alt
			$query="UPDATE ".TABLE_DOCS." SET alt='".b3_htmlize($alt,true,"strong,em,u,acronym")."' WHERE iddoc=".$iddoc;
			if(!ksql_query($query)) return false;
			}
		
		return $iddoc;
		}

	function delete($iddoc) {
		if(!defined("TABLE_DOCS")|!defined("DIR_DOCS")) return false;
		
		$query="SELECT * FROM ".TABLE_DOCS." WHERE iddoc=".$iddoc;
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
			$query="DELETE FROM ".TABLE_DOCS." WHERE iddoc=".$iddoc;
			if(!ksql_query($query)) return false;
			
			if($row['filename']!="") {
				unlink(BASERELDIR.DIR_DOCS.$iddoc.'/'.$row['filename']); //elimino la vecchia immagine
				rmdir(BASERELDIR.DIR_DOCS.$iddoc); //elimino la dir
				}
			}
		
		return true;
		}

	function usage($iddoc) {
		$output=array();
		$id=array(TABLE_CONFIG=>"idconf",TABLE_USERS=>"iduser",TABLE_BANNER=>"idbanner",TABLE_PAGINE=>"idpag",TABLE_LANDINGPAGE=>"idlp",TABLE_LANDINGPAGE_T=>"idlpt",TABLE_THANKYOUPAGE=>"idtyp",TABLE_NEWS=>"idnews");
		$type=array(TABLE_CONFIG=>"Configurazione",TABLE_USERS=>"Utenti",TABLE_BANNER=>"Banner",TABLE_PAGINE=>"Pagina",TABLE_LANDINGPAGE=>"Landing-page",TABLE_LANDINGPAGE_T=>"Landing page",TABLE_THANKYOUPAGE=>"Conversion page",TABLE_NEWS=>"News");

		$query="SELECT * FROM ".TABLE_DOCS." WHERE iddoc=".$iddoc;
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$descr=$type[$row['tabella']].' ';
		$query2="SELECT * FROM ".$row['tabella']." WHERE ".$id[$row['tabella']]."=".$row['id']." LIMIT 1";
		$results2=ksql_query($query2);
		$row2=ksql_fetch_array($results2);
		if(!isset($row2['ll'])) $row2['ll']="";
		if(!isset($row2['dir'])) $row2['dir']="";
		$descr.='<strong>'.strtolower($row2['ll']).'/'.($row['tabella']==TABLE_NEWS?'news/':'').$row2['dir'].'</strong>';
		$url=BASEDIR.strtolower($row2['ll']).'/'.($row['tabella']==TABLE_NEWS?'news/':'').$row2['dir'];

		$output[]=array("table"=>$row['tabella'],"id"=>$row['id'],"descr"=>$descr,"url"=>$url);
		
		$search=array();
		$search[]=array(TABLE_PAGINE,'anteprima');
		$search[]=array(TABLE_PAGINE,'testo');
		$search[]=array(TABLE_LANDINGPAGE,'testo');
		$search[]=array(TABLE_LANDINGPAGE_T,'testo');
		$search[]=array(TABLE_THANKYOUPAGE,'testo');
		$search[]=array(TABLE_NEWS,'anteprima');
		$search[]=array(TABLE_NEWS,'testo');
		
		foreach($search as $s) {
			$query2="SELECT * FROM ".$s[0]." WHERE ".$s[1]." LIKE '%id=\"doc".$iddoc."\"%' LIMIT 1";
			$results2=ksql_query($query2);
			while($row2=ksql_fetch_array($results2)) {
				$descr=$type[$s[0]].' <strong>'.strtolower($row2['ll']).'/'.($s[0]==TABLE_NEWS?'news/':'').$row2['dir'].'</strong>';
				$url=BASEDIR.strtolower($row2['ll']).'/'.($s[0]==TABLE_NEWS?'news/':'').$row2['dir'];
				if($url!=$output[0]['url']) $output[]=array("table"=>$s[0],"id"=>$row2[$id[$s[0]]],"descr"=>$descr,"url"=>$url);
				}
			}
		
		return $output;
		}

	}
