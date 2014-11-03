<?php 
/* (c) Kalamun.org - GPL v3 */

class kaCategorie {

	function kaCategorie() {
		require_once('connect.inc.php');
		require_once('main.lib.php');
		}

	function add($categoria,$dir,$tabella,$lang=false) {
		if($lang==false&&isset($_SESSION['ll'])) $lang=$_SESSION['ll'];
		elseif($lang==false&&defined(LANG)) $lang=LANG;
		else $lang=DEFAULT_LANG;

		$query="SELECT ordine FROM ".TABLE_CATEGORIE." WHERE tabella='".mysql_real_escape_string($tabella)."' AND ll='".mysql_real_escape_string($lang)."' ORDER BY ordine DESC LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$ordine=$row['ordine']+1;
		
		//check if dir still exists
		$query="SELECT * FROM ".TABLE_CATEGORIE." WHERE `dir`='".b3_htmlize($dir,true,"")."' AND `tabella`='".mysql_real_escape_string($tabella)."' AND ll='".mysql_real_escape_string($lang)."' ORDER BY `ordine` DESC LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			$dir.=date("YmdHsi");
			}
		
		$query="INSERT INTO ".TABLE_CATEGORIE." (`categoria`,`dir`,`tabella`,`photogallery`,`ordine`,`ll`) VALUES('".b3_htmlize($categoria,true,"")."','".b3_htmlize($dir,true,"")."','".mysql_real_escape_string($tabella)."',',','".$ordine."','".mysql_real_escape_string($lang)."')";
		if(mysql_query($query)) return mysql_insert_id();
		else return false;
		}
	
	function get($idcat,$tabella=false,$lang=false) {
		if($lang==false&&isset($_SESSION['ll'])) $lang=$_SESSION['ll'];
		elseif($lang==false&&defined(LANG)) $lang=LANG;
		else $lang=DEFAULT_LANG;
		$query="SELECT * FROM ".TABLE_CATEGORIE." WHERE ";
		if($tabella!=false) $query.=" tabella='".$tabella."' AND ";
		$query.=" idcat='".$idcat."' AND ll='".$lang."'";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $row;
		}

	function getList($tabella,$lang=false,$ref=-1)
	{
		$output=array();
		if($lang==false&&isset($_SESSION['ll'])) $lang=$_SESSION['ll'];
		elseif($lang==false&&defined(LANG)) $lang=LANG;
		else $lang=DEFAULT_LANG;
		$query="SELECT * FROM `".TABLE_CATEGORIE."` WHERE `tabella`='".mysql_real_escape_string($tabella)."' ";
		if($ref>=0) $query.=" AND `ref`='".intval($ref)."' ";
		$query.=" AND `ll`='".mysql_real_escape_string($lang)."' ORDER BY `ordine`";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results))
		{
			$output[]=$row;
			//if ref is specified, goes recursively
			if($ref>=0 && $row['idcat']>0) $output=array_merge($output,$this->getList($tabella,$lang,$row['idcat']));
		}
		return $output;
	}
	
	public function getStructuredList($tabella,$ref=0,$lang=false) {
		$output=array();
		if($lang==false&&isset($_SESSION['ll'])) $lang=$_SESSION['ll'];
		elseif($lang==false&&defined(LANG)) $lang=LANG;
		elseif($lang==false) $lang=DEFAULT_LANG;
		$query="SELECT * FROM `".TABLE_CATEGORIE."` WHERE `tabella`='".$tabella."' AND `ll`='".$lang."' AND `ref`='".$ref."' ORDER BY `ordine`";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$id=count($output);
			$output[$id]=$this->getStructuredList($tabella,$row['idcat'],$lang);
			$output[$id]['data']=$row;
			}
		return $output;
		}
	
	function update($idcat,$vars,$dir=false,$tabella=false,$lang=false) {
		if(!is_array($vars))
		{
			$categoria=$vars;
			$vars=array();
			$vars['categoria']=$categoria;
			if($dir!=false) $vars['dir']=$dir;
			if($tabella!=false) $vars['tabella']=$tabella;
			if($lang!=false) $vars['lang']=$lang;
		}

		if(empty($idcat)) return false;
		if(empty($vars)) return true;
		if(empty($vars['lang'])) $vars['lang']=$_SESSION['ll'];

		$query="UPDATE `".TABLE_CATEGORIE."` SET ";
		if(isset($vars['categoria'])) $query.=" `categoria`='".b3_htmlize($vars['categoria'],true,"")."', ";
		if(isset($vars['dir'])) $query.=" `dir`='".mysql_real_escape_string($vars['dir'])."', ";
		if(isset($vars['photogallery'])) $query.=" `photogallery`='".mysql_real_escape_string($vars['photogallery'])."', ";
		$query.="`ll`='".mysql_real_escape_string($vars['lang'])."' WHERE `idcat`=".intval($idcat)." ";
		if(isset($vars['tabella'])) $query.=" AND `tabella`='".$vars['tabella']."' ";
		$query.=" AND `ll`='".mysql_real_escape_string($vars['lang'])."' LIMIT 1";
		
		if(mysql_query($query)) return $idcat;
		else return false;
		}

	function del($idcat,$tabella=false,$lang=false)
	{
		if($lang==false&&isset($_SESSION['ll'])) $lang=$_SESSION['ll'];
		elseif($lang==false&&defined(LANG)) $lang=LANG;
		else $lang=DEFAULT_LANG;
		$query="SELECT `tabella`,`ordine` FROM `".TABLE_CATEGORIE."` WHERE `idcat`=".intval($idcat)." ";
		if($tabella!=false) $query.=" AND tabella='".mysql_real_escape_string($tabella)."' ";
		$query.=" AND ll='".mysql_real_escape_string($lang)."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if(!isset($row['ordine'])) return false;

		$order=$row['ordine'];
		$table=$row['tabella'];
		
		// delete all contained categories
		foreach($this->getList($table,$lang,$idcat) as $cat)
		{
			$query="DELETE FROM `".TABLE_CATEGORIE."` WHERE `idcat`=".intval($cat['idcat'])." LIMIT 1";
			mysql_query($query);
		}
		
		$query="SELECT `ordine` FROM `".TABLE_CATEGORIE."` WHERE `ref`=".intval($idcat)." ";
		if($tabella!=false) $query.=" AND tabella='".mysql_real_escape_string($tabella)."' ";
		$query.=" AND ll='".mysql_real_escape_string($lang)."' LIMIT 1";
		
		$query="DELETE FROM `".TABLE_CATEGORIE."` WHERE `idcat`=".intval($idcat)." ";
		if($tabella!=false) $query.=" AND `tabella`='".mysql_real_escape_string($tabella)."' ";
		$query.=" AND ll='".mysql_real_escape_string($lang)."'";
		if(!mysql_query($query)) return false;
		else {
			$query="UPDATE ".TABLE_CATEGORIE." SET `ordine`=`ordine`-1 WHERE `ordine`>".intval($order)." ";
			if($tabella!=false) $query.=" AND tabella='".mysql_real_escape_string($tabella)."' ";
			$query.=" AND `ll`='".mysql_real_escape_string($lang)."' LIMIT 1";
			mysql_query($query);
			return $idcat;
		}
	}

	function sort($order,$tabella,$lang=false) {
		$output=true;
		if($lang==false&&isset($_SESSION['ll'])) $lang=$_SESSION['ll'];
		elseif($lang==false&&defined(LANG)) $lang=LANG;
		else $lang=DEFAULT_LANG;
		if(!is_array($order)) {
			$order=array();
			foreach($this->getList($tabella,$lang) as $cat) {
				$order[]=$cat['idcat'];
				}
			}
		for($i=0;isset($order[$i]);$i++) {
			$query="UPDATE ".TABLE_CATEGORIE." SET ordine=".($i+1)." WHERE idcat=".$order[$i]." ";
			if($tabella!=false) $query.=" AND tabella='".$tabella."' ";
			$query.=" AND ll='".$lang."' LIMIT 1";
			if(!mysql_query($query)) $output=false;
			}
		return $output;
		}

	function sortby($orderby,$tabella,$lang=false,$ref=0) {
		$output=true;
		if($lang==false&&isset($_SESSION['ll'])) $lang=$_SESSION['ll'];
		elseif($lang==false&&defined(LANG)) $lang=LANG;
		elseif($lang==false) return false;

		$i=1;
		$q="SELECT * FROM `".TABLE_CATEGORIE."` WHERE `tabella`='".mysql_real_escape_string($tabella)."' AND `ll`='".mysql_real_escape_string($lang)."' AND `ref`='".intval($ref)."' ORDER BY ".$orderby."";
		$rs=mysql_query($q);
		while($r=mysql_fetch_array($rs))
		{
			$query="UPDATE ".TABLE_CATEGORIE." SET `ordine`=".($i+1)." WHERE `idcat`=".$r['idcat']." LIMIT 1";
			if(!mysql_query($query)) $output=false;
			if($this->sortby($orderby,$tabella,$lang,$r['idcat'])==false) $output=false;
			$i++;
		}
		return $output;
		}

	public function updateOrder($idcat,$ordine,$ref,$tabella) {
		$query="UPDATE `".TABLE_CATEGORIE."` SET `ordine`='".intval($ordine)."',`ref`='".intval($ref)."' WHERE `idcat`='".intval($idcat)."' AND `tabella`='".mysql_real_escape_string($tabella)."' LIMIT 1";
		if(mysql_query($query)) return true;
		else return false;
		}
	}

