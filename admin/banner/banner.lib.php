<?php 
/* (c)2011 Kalamun.org - GPL 3 */

class kaBanner {
	protected $kaDocuments;
	
	function __construct()
	{
	}
	
	/* insert a new banner */
	function add($vars)
	{
		if(empty($vars['ll'])) $vars['ll'] = $_SESSION['ll'];
		if(empty($vars['url'])) $vars['url'] = "";
		if(empty($vars['featuredimage'])) $vars['featuredimage'] = 0;
		$log = "";

		$query = "SELECT `ordine` FROM `".TABLE_BANNER."` WHERE `categoria` = '".ksql_real_escape_string($vars['idcat'])."' ORDER BY `ordine` DESC LIMIT 1";
		$results = ksql_query($query);
		$row = ksql_fetch_array($results);
		$orderby = $row['ordine']+1;
		
		$query = "SELECT MIN(`views`) AS `min` FROM `".TABLE_BANNER."` WHERE `categoria`='".ksql_real_escape_string($vars['idcat'])."'";
		$results = ksql_query($query);
		$row = ksql_fetch_array($results);
		$offset = $row['min'];

		$query = "INSERT INTO `".TABLE_BANNER."` (`online`,`type`,`title`,`description`,`url`,`categoria`,`featuredimage`,`views`,`offset`,`clicks`,`ordine`,`ll`) "
				. " VALUES('s', '".ksql_real_escape_string($vars['type'])."', '".b3_htmlize($vars['title'],true,"")."', '". ($vars['type'] == "code" ? ksql_real_escape_string($vars['description']) : b3_htmlize($vars['description'],true)) ."', '".b3_htmlize($vars['url'],true,"")."','".intval($vars['idcat'])."', '".$vars['featuredimage']."', '0', '".intval($offset)."', '0', '".$orderby."', '".$vars['ll']."')";
		
		if(!ksql_query($query))
		{
			trigger_error("An error occured while saving the banner into the database");
			$log = "Error saving db";
			
		} else {
			$id = ksql_insert_id();
		}
		
		if($log != "") return false;
		return $id;
	}

	/* update a banner */
	function update($idbanner,$vars)
	{
		if($vars['online']!='s') $vars['online']='n';

		// check if banner exists
		$query="SELECT `ordine`,`categoria` FROM `".TABLE_BANNER."` WHERE `idbanner`='".intval($idbanner)."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results))
		{
			$query="UPDATE `".TABLE_BANNER."` SET ";
			
			if(isset($vars['type'])) $query.=" `type`='".b3_htmlize($vars['type'],true,"")."',";
			if(isset($vars['title'])) $query.=" `title`='".b3_htmlize($vars['title'],true,"")."',";
			if(isset($vars['description'])) $query.=" `description`='".b3_htmlize($vars['description'],true)."',";
			if(isset($vars['url'])) $query.=" `url`='".ksql_real_escape_string($vars['url'])."',";
			if(isset($vars['featuredimage'])) $query.=" `featuredimage`='".ksql_real_escape_string($vars['featuredimage'])."',";
			if(isset($vars['idcat'])) $query.=" `categoria`='".ksql_real_escape_string($vars['idcat'])."',";
			if(isset($vars['online'])) $query.=" `online`='".ksql_real_escape_string($vars['online'])."',";
			
			$query = rtrim($query, ",");
			$query .= " WHERE `idbanner`='".intval($idbanner)."' LIMIT 1";

			if(!ksql_query($query)) return false;
			
			if($row['categoria']!=$vars['idcat'])
			{
				$query="SELECT `ordine` FROM `".TABLE_BANNER."` WHERE `categoria`='".intval($vars['idcat'])."' ORDER BY `ordine` LIMIT 1";
				$results=ksql_query($query);
				$r=ksql_fetch_array($results);
				if(!isset($r['ordine'])) $r['ordine']=0;
				$r['ordine']++;
				
				$query="UPDATE `".TABLE_BANNER."` SET `ordine`='".$r['ordine']."',`categoria`='".intval($vars['idcat'])."' WHERE `idbanner`='".intval($idbanner)."' LIMIT 1";
				if(!ksql_query($query)) return false;
				
				$query="UPDATE `".TABLE_BANNER."` SET `ordine`=`ordine`-1 WHERE `categoria`='".intval($row['categoria'])."' AND `ordine`>'".intval($row['ordine'])."'";
				if(!ksql_query($query)) return false;
				
			}
			return true;
		}
		return false;
	}

	/* delete a banner */
	function delete($idbanner)
	{
		$log="";
		$banner=$this->get($idbanner);
		
		$query="DELETE FROM ".TABLE_BANNER." WHERE idbanner=".intval($idbanner)." LIMIT 1";
		if(!ksql_query($query)) { $log="Problemi durante l'eliminazione dal database"; } else { $id=$idbanner; }

		$query="UPDATE ".TABLE_BANNER." SET ordine=ordine-1 WHERE categoria='".$banner['categoria']."' AND ordine>".$banner['ordine'];
		if(!ksql_query($query)) $log="Problemi durante il riordino del database";

		if($log==""&&isset($banner['banner']['iddoc']))
		{
			if(!$this->kaDocuments->delete($banner['banner']['iddoc'])) $log.="Errore durante la rimozione del file ".$banner['banner']['name'].".<br />";
		}

		if($log!="") return false;
		else return true;
	}
	
	/* get a single banner */
	function get($idbanner)
	{
		$output=array();
		$query="SELECT * FROM ".TABLE_BANNER." WHERE `idbanner`='".intval($idbanner)."' LIMIT 1";
		$results=ksql_query($query);
		
		if($row=ksql_fetch_array($results))
		{
			$output = $row;
		} else return false;

		if(!empty($output['featuredimage']))
		{
			$kaImages = new kaImages();
			$output['banner'] = $kaImages->getImage($output['featuredimage']);
			
		} else $output['banner'] = array();

		return $output;
	}

	function getList($idcat, $order=false)
	{
		// if order is not defined, get the default order for category
		if($order==false)
		{
			require_once(ADMINRELDIR.'inc/metadata.lib.php');
			$kaMetadata=new kaMetadata();
			$order = $kaMetadata->get(TABLE_CATEGORIE, $idcat, 'orderby');
			$order = $order['value'];
		}
		if(empty($order)) $order = 'ordine';

		$output=array();
		$query="SELECT * FROM `".TABLE_BANNER."` WHERE `categoria`='".intval($idcat)."' AND `ll`='".$_SESSION['ll']."' ORDER BY `".ksql_real_escape_string($order)."`, `ordine`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			$output[]=$row;
		}
		return $output;
	}

	/* save the manual sorting of banners */
	function sort($order, $lang=false)
	{
		$output=true;
		if($lang==false) $lang=$_SESSION['ll'];
		
		for($i=0;isset($order[$i]);$i++)
		{
			$query="UPDATE `".TABLE_BANNER."` SET `ordine`=".($i+1)." WHERE `idbanner`=".intval($order[$i])." ";
			$query.=" AND ll='".ksql_real_escape_string($lang)."' LIMIT 1";
			if(!ksql_query($query)) $output=false;
		}

		return $output;
	}	
	
}

