<?
/* (c) Kalamun.org - GNU/GPL 3 */

class kaMenu {
	protected $menu,$collection;

	public function kaMenu() {
		$this->collection='';
		}

	public function getCollections() {
		$output=array();
		$query="SELECT `collection` FROM ".TABLE_MENU." GROUP BY `collection` ORDER BY `collection`";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
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
		$query="SELECT * FROM ".TABLE_MENU." WHERE `collection`='".mysql_real_escape_string($this->collection)."' AND `ll`='".mysql_real_escape_string($ll)."'";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
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
		$query="SELECT * FROM `".TABLE_MENU."` WHERE `collection`='".mysql_real_escape_string($this->collection)."' AND `ll`='".mysql_real_escape_string($ll)."' AND `ref`='".mysql_real_escape_string($ref)."' ORDER BY `ordine`";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$menu[$row['ordine']]=$this->getSubMenuStructure($row['idmenu']);
			$menu[$row['ordine']]['data']=$row['idmenu'];
			}
		return $menu;
		}

	public function updateDirAndLabel($idmenu,$newdir,$newlabel=false,$ll=false) {
		if($ll==false) $ll=$_SESSION['ll'];

		$query="UPDATE `".TABLE_MENU."` SET `url`='".mysql_real_escape_string($newdir)."' ";
		if($newlabel!=false) $query.=", `label`='".b3_htmlize($newlabel,true,"")."' ";
		$query.=" WHERE `idmenu`='".mysql_real_escape_string($idmenu)."' AND `collection`='".mysql_real_escape_string($this->collection)."' AND `ll`='".mysql_real_escape_string($ll)."'";
		if(mysql_query($query)) return true;
		else return false;
		}

	public function getMenuElementsByUrl($vars=array()) {
		/* it returns an array of elements selected by the url */
		if(!isset($vars['url'])) return false;
		if(!isset($vars['ll'])) $vars['ll']=$_SESSION['ll'];

		$output=array();
		$query="SELECT * FROM `".TABLE_MENU."` WHERE `collection`='".mysql_real_escape_string($this->collection)."' AND `ll`='".mysql_real_escape_string($vars['ll'])."' AND `url`='".mysql_real_escape_string($vars['url'])."' OR `url` LIKE '%/".mysql_real_escape_string($vars['url'])."' ORDER BY `ordine`";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$output[$row['ordine']]=$row;
			}
		return $output;

		}
	
	public function deleteElement($vars=array()) {
		$log="";
		if(!isset($vars['idmenu'])) return false;
		//if(!isset($vars['ll'])) $vars['ll']=$_SESSION['ll'];
		$query="SELECT ordine,ref FROM ".TABLE_MENU." WHERE `collection`='".mysql_real_escape_string($this->collection)."' AND `idmenu`=".$vars['idmenu'];
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			$ordine=$row['ordine'];
			$ref=$row['ref'];
			}
		else $log="Errore durante la selezione dell'ordinamento";
		
		if($log=="") {
			$query="DELETE FROM ".TABLE_MENU." WHERE `collection`='".mysql_real_escape_string($this->collection)."' AND `idmenu`=".$vars['idmenu'];
			if(!mysql_query($query)) $log="Problemi durante l'eliminazione della voce";
			$query="DELETE FROM ".TABLE_MENU." WHERE `collection`='".mysql_real_escape_string($this->collection)."' AND `ref`=".$vars['idmenu'];
			if(!mysql_query($query)) $log="Problemi durante l'eliminazione della voce";
			}
		
		if($log=="") {
			$query="UPDATE ".TABLE_MENU." SET `ordine`=`ordine`-1 WHERE `ordine`>".$ordine." AND `ref`=".$ref." AND `collection`='".mysql_real_escape_string($this->collection)."' AND `ll`='".$_SESSION['ll']."'";
			if(!mysql_query($query)) $log="Problemi durante l'eliminazione della voce";
			}
		
		if($log=="") return true;
		else return false;
		}

	}
	
?>