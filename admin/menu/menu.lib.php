<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

class kaMenu {
	protected $menu,$collection;

	public function kaMenu()
	{
		$this->collection='';
	}
	
	public function addElement($vars)
	{
		/*
		$vars is an array:
			label -> the element label
			dir -> the page url
			idmenu -> the id of the reference menu
			where -> before, inside or after the reference menu?
		*/
		if($vars['where']=="after")
		{
			// select the reference page (included placeholders for first inserts)
			$query="SELECT `ordine`,`ref`,`collection` FROM `".TABLE_MENU."` WHERE `idmenu`='".intval($vars['idmenu'])."' AND (`ll`='".$_SESSION['ll']."' OR `ll`='##') LIMIT 1";
			$results=ksql_query($query);
			$page=ksql_fetch_array($results);
			
			if(!isset($page['ordine'])) $page['ordine']=0;
			if(!isset($page['ref'])) $page['ref']=0;
			if(!isset($page['collection'])) $page['collection']="";			
			$order=$page['ordine']+1;
			$ref=$page['ref'];
			$collection=$page['collection'];
			
		} elseif($vars['where']=="inside") {
			$query="SELECT `ordine`,`ref` FROM `".TABLE_MENU."` WHERE `ref`=".intval($vars['idmenu'])." AND (`ll`='".ksql_real_escape_string($_SESSION['ll'])."' OR `ll`='##') ORDER BY `ordine` DESC LIMIT 1";
			$results=ksql_query($query);
			$page=ksql_fetch_array($results);

			$order=$page['ordine']+1;
			$ref=$vars['idmenu'];
			
			$query="SELECT `collection` FROM `".TABLE_MENU."` WHERE `idmenu`=".intval($vars['idmenu'])." AND (`ll`='".ksql_real_escape_string($_SESSION['ll'])."' OR `ll`='##') LIMIT 1";
			$results=ksql_query($query);
			$page=ksql_fetch_array($results);
			$collection=$page['collection'];

		} elseif($vars['where']=="before") {
			$query="SELECT `ordine`,`ref`,`collection` FROM `".TABLE_MENU."` WHERE `idmenu`='".intval($vars['idmenu'])."' AND (`ll`='".ksql_real_escape_string($_SESSION['ll'])."' OR `ll`='##') LIMIT 1";
			$results=ksql_query($query);
			$page=ksql_fetch_array($results);

			$order=$page['ordine'];
			$ref=$page['ref'];
			$collection=$page['collection'];

		}

		$query="INSERT INTO `".TABLE_MENU."` (`label`,`url`,`ref`,`ordine`,`ll`,`collection`,`photogallery`) VALUES('".ksql_real_escape_string($vars['title'])."','".ksql_real_escape_string($vars['dir'])."','".intval($ref)."','".intval($order)."','".ksql_real_escape_string($_SESSION['ll'])."','".ksql_real_escape_string($collection)."','')";
		if(!ksql_query($query)) return false;
		
		// successfully inserted
		$idmenu=ksql_insert_id();
		
		// update the order of other elements
		if($vars['where']=="after")
		{
			$query="UPDATE `".TABLE_MENU."` SET `ordine`=`ordine`+1 WHERE `ref`='".intval($ref)."' AND `ordine`>='".intval($order)."' AND `idmenu`<>'".$idmenu."' AND `collection`='".ksql_real_escape_string($collection)."' AND `ll`='".ksql_real_escape_string($_SESSION['ll'])."'";
			ksql_query($query);

		} elseif($vars['where']=="before") {
			$query="UPDATE `".TABLE_MENU."` SET `ordine`=`ordine`+1 WHERE `ref`='".intval($ref)."' AND `ordine`>='".intval($order)."' AND `idmenu`<>'".$idmenu."' AND `collection`='".ksql_real_escape_string($collection)."' AND `ll`='".ksql_real_escape_string($_SESSION['ll'])."'";
			ksql_query($query);
		}
		
		return $idmenu;
	}

	public function getCollections() {
		$output=array();
		$query="SELECT `collection` FROM ".TABLE_MENU." GROUP BY `collection` ORDER BY `collection`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$output[]=$row['collection'];
			}
		return $output;
		}
	public function setCollection($collection) {
		$this->collection=$collection;
		}

	public function getMenuContents($ll=false) {
		$menu=array();
		if($ll==false) $ll=$_SESSION['ll'];
		$query="SELECT * FROM ".TABLE_MENU." WHERE `collection`='".ksql_real_escape_string($this->collection)."' AND `ll`='".ksql_real_escape_string($ll)."'";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$menu[$row['idmenu']]=$row;
			}
		return $menu;
		}
		
	public function getMenuStructure($ref=0,$ll=false) {
		$this->menu=$this->getSubMenuStructure($ref);
		return $this->menu;
		}

	private function getSubMenuStructure($ref=0,$ll=false) {
		$menu=array();
		if($ll==false) $ll=$_SESSION['ll'];
		$query="SELECT * FROM `".TABLE_MENU."` WHERE `collection`='".ksql_real_escape_string($this->collection)."' AND `ll`='".ksql_real_escape_string($ll)."' AND `ref`='".ksql_real_escape_string($ref)."' ORDER BY `ordine`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$menu[$row['ordine']]=$this->getSubMenuStructure($row['idmenu']);
			$menu[$row['ordine']]['data']=$row['idmenu'];
			}
		return $menu;
		}

	public function getMenuPlaceholder($ll=false) {
		$menu=array();
		if($ll==false) $ll=$_SESSION['ll'];
		$query="SELECT * FROM ".TABLE_MENU." WHERE `label`='###placeholder###' AND `collection`='".ksql_real_escape_string($this->collection)."' AND `ll`='##'";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row;
		}

	public function updateDirAndLabel($idmenu,$newdir,$newlabel=false,$ll=false) {
		if($ll==false) $ll=$_SESSION['ll'];

		$query="UPDATE `".TABLE_MENU."` SET `url`='".ksql_real_escape_string($newdir)."' ";
		if($newlabel!=false) $query.=", `label`='".b3_htmlize($newlabel,true,"")."' ";
		$query.=" WHERE `idmenu`='".ksql_real_escape_string($idmenu)."' AND `collection`='".ksql_real_escape_string($this->collection)."' AND `ll`='".ksql_real_escape_string($ll)."'";
		if(ksql_query($query)) return true;
		else return false;
		}

	public function getMenuElementsByUrl($vars=array()) {
		/* it returns an array of elements selected by the url */
		if(!isset($vars['url'])) return false;
		if(!isset($vars['ll'])) $vars['ll']=$_SESSION['ll'];

		$output=array();
		$query="SELECT * FROM `".TABLE_MENU."` WHERE `collection`='".ksql_real_escape_string($this->collection)."' AND `ll`='".ksql_real_escape_string($vars['ll'])."' AND `url`='".ksql_real_escape_string($vars['url'])."' OR `url` LIKE '%/".ksql_real_escape_string($vars['url'])."' ORDER BY `ordine`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$output[$row['ordine']]=$row;
			}
		return $output;

		}
	
	public function deleteElement($vars=array()) {
		$log="";
		if(!isset($vars['idmenu'])) return false;
		//if(!isset($vars['ll'])) $vars['ll']=$_SESSION['ll'];
		$query="SELECT ordine,ref FROM ".TABLE_MENU." WHERE `collection`='".ksql_real_escape_string($this->collection)."' AND `idmenu`=".$vars['idmenu'];
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
			$ordine=$row['ordine'];
			$ref=$row['ref'];
			}
		else $log="Errore durante la selezione dell'ordinamento";
		
		if($log=="") {
			$query="DELETE FROM ".TABLE_MENU." WHERE `collection`='".ksql_real_escape_string($this->collection)."' AND `idmenu`=".$vars['idmenu'];
			if(!ksql_query($query)) $log="Problemi durante l'eliminazione della voce";
			$query="DELETE FROM ".TABLE_MENU." WHERE `collection`='".ksql_real_escape_string($this->collection)."' AND `ref`=".$vars['idmenu'];
			if(!ksql_query($query)) $log="Problemi durante l'eliminazione della voce";
			}
		
		if($log=="") {
			$query="UPDATE ".TABLE_MENU." SET `ordine`=`ordine`-1 WHERE `ordine`>".$ordine." AND `ref`=".$ref." AND `collection`='".ksql_real_escape_string($this->collection)."' AND `ll`='".$_SESSION['ll']."'";
			if(!ksql_query($query)) $log="Problemi durante l'eliminazione della voce";
			}
		
		if($log=="") return true;
		else return false;
		}

	}
	
