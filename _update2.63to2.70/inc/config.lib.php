<?php 
/* (c) Kalamun.org - GPL v3 */

class kImpostazioni {
	protected $ll='';
	
	public function __construct() {
		// imposto la lingua in uso
		defined('LANG')?$this->ll=LANG:$this->ll=$_SESSION['ll'];
		}

	public function paramExists($param,$ll=false) {
		// controllo l'esistenza di un parametro
		if($ll==false) $ll=$this->ll;
		if($ll==$this->ll) {
			if(!isset($GLOBALS['__template'])) return false;
			if(!isset($GLOBALS['__template']->config[$param])) return false;
			return true;
			}
		else {
			$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='".$param."' AND ll='".$ll."' LIMIT 1";
				$results=mysql_query($query);
					if($row=mysql_fetch_array($results)) return true;
			else return false;
			}
		}

	public function addParam($param,$value1,$value2,$sistema='n',$ll=false) {
		// aggiungo un parametro
		if($ll==false) $ll=$this->ll;
		$query="INSERT INTO ".TABLE_CONFIG." (param,value1,value2,sistema,ll) VALUES('".$param."','".$value1."','".$value2."','".$sistema."','".$ll."')";
		$results=mysql_query($query);
		if($results) return true;
		else return false;
		}
		
	public function updateParam($param,$value1,$value2,$sistema='n',$ll=false) {
		// aggiungo un parametro
		if($ll==false) $ll=$this->ll;
		$query="UPDATE ".TABLE_CONFIG." SET value1='".$value1."',value2='".$value2."',sistema='".$sistema."' WHERE param='".$param."' AND ll='".$ll."' LIMIT 1";
		$results=mysql_query($query);
		if($results) return true;
		else return false;
		}
		
	public function setParam($param,$value1,$value2,$sistema='n',$ll=false) {
		// se esiste il parametro lo sovrascrive, altrimenti lo crea
		if($this->paramExists($param,$ll)) $this->updateParam($param,$value1,$value2,$sistema,$ll);
		else $this->addParam($param,$value1,$value2,$sistema,$ll);
		}

	public function getParam($param,$ll=false) {
		if($ll==false) $ll=$this->ll;
		if($ll==$this->ll) {
			if(!isset($GLOBALS['__template'])) return false;
			if(!isset($GLOBALS['__template']->config[$param])) return false;
			return $GLOBALS['__template']->config[$param];
			}
		else {
			$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='".$param."' AND ll='".$ll."' LIMIT 1";
				$results=mysql_query($query);
					if($row=mysql_fetch_array($results)) return $row;
			else return false;
			}
		}

	}
