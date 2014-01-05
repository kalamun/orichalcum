<?
/* (c) Kalamun.org - GPL v3 */

class kaImpostazioni {
	protected $ll='';
	
	public function kaImpostazioni() {
		// imposto la lingua in uso
		!isset($_SESSION['ll'])?$this->ll=DEFAULT_LANG:$this->ll=$_SESSION['ll'];
		}

	public function paramExists($param,$ll=false) {
		// controllo l'esistenza di un parametro
		if($ll==false) $ll=$this->ll;
		$query="SELECT * FROM `".TABLE_CONFIG."` WHERE `param`='".mysql_real_escape_string($param)."' AND `ll`='".mysql_real_escape_string($ll)."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) return true;
		else return false;
		}

	public function addParam($param,$value1,$value2,$ll=false) {
		// aggiungo un parametro
		if($ll==false) $ll=$this->ll;
		$query="INSERT INTO `".TABLE_CONFIG."` (`param`,`value1`,`value2`,`ll`) VALUES('".mysql_real_escape_string($param)."','".mysql_real_escape_string($value1)."','".mysql_real_escape_string($value2)."','".mysql_real_escape_string($ll)."')";
		if(mysql_query($query)) return true;
		else return false;
		}
		
	public function updateParam($param,$value1,$value2,$ll=false) {
		// aggiungo un parametro
		if($ll==false) $ll=$this->ll;
		$query="UPDATE `".TABLE_CONFIG."` SET `value1`='".mysql_real_escape_string($value1)."',`value2`='".mysql_real_escape_string($value2)."' WHERE `param`='".mysql_real_escape_string($param)."' AND `ll`='".mysql_real_escape_string($ll)."' LIMIT 1";
		if(mysql_query($query)) return true;
		else return false;
		}
		
	public function replaceParam($param,$value1,$value2,$ll=false) {
		if($this->paramExists($param,$ll)) return $this->updateParam($param,$value1,$value2,$ll);
		else return $this->addParam($param,$value1,$value2,$ll);
		}
		
	public function setParam($param,$value1,$value2,$ll=false) {
		// se esiste il parametro lo sovrascrive, altrimenti lo crea
		if($this->paramExists($param,$ll)) return $this->updateParam($param,$value1,$value2,$ll);
		else return $this->addParam($param,$value1,$value2,$ll);
		}

	public function getParam($param,$ll=false) {
		if($ll==false) $ll=$this->ll;
		$query="SELECT * FROM `".TABLE_CONFIG."` WHERE `param`='".mysql_real_escape_string($param)."' AND `ll`='".mysql_real_escape_string($ll)."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) return $row;
		else return false;
		}

	public function getVar($param,$num,$ll=false) {
		if($ll==false) $ll=$this->ll;
		$query="SELECT `value".$num."` FROM `".TABLE_CONFIG."` WHERE `param`='".mysql_real_escape_string($param)."' AND ll='".mysql_real_escape_string($ll)."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) return $row['value'.$num];
		else return false;
		}

	public function getTemplateList() {
		$output=array();
		if($handle=opendir(BASERELDIR.DIR_TEMPLATE)) {
			while(false!==($file=readdir($handle))) {
				if(is_dir(BASERELDIR.DIR_TEMPLATE.$file)&&trim($file,".")!="") {
					$output[]=$file;
					}
				}
			closedir($handle);
			}
		return $output;
		}

	public function getLayoutList($tpl="") {
		if($tpl=="") $tpl=$this->getVar('template_default',1);
		$output=array();
		if(file_exists(BASERELDIR.DIR_TEMPLATE.$tpl.'/layouts')&&$handle=opendir(BASERELDIR.DIR_TEMPLATE.$tpl.'/layouts')) {
			while(false!==($file=readdir($handle))) {
				if(trim($file,".")!="") {
					$output[]=$file;
					}
				}
			closedir($handle);
			}
		return $output;
		}

	}

class kaConfigInc {
	protected $filename,$contents,$log;

	public function kaConfigInc() {
		$this->log="";
		$this->filename=ADMINRELDIR."inc/config.inc.php";
		if($this->read()==false) return false;
		else return true;
		}

	public function getFilename() {
		return $this->filename;
		}

	public function read() {
		if(file_exists($this->filename)) {
			$this->contents=file_get_contents($this->filename);
			return $this->contents;
			}
		else return false;
		}
	
	public function write($contents) {
		$this->log="";
		if(is_writable($this->filename)) {
			if (!$handle=fopen($this->filename.'.tmp.php','w')) {
				$this->log="Impossibile aprire ".$this->filename.'.tmp.php';
				exit;
				}
			//se sono arrivato fin qui, faccio una copia di bkup
			if(file_exists($this->filename.'.bkup.php')) unlink($this->filename.'.bkup.php');
			copy($this->filename,$this->filename.'.bkup.php');

			if(fwrite($handle,$contents)===FALSE) {
				$this->log="Impossibile scrivere sul file ".$this->filename.'.tmp.php';
				exit;
				}
			fclose($handle);
			unlink($this->filename);
			rename($this->filename.'.tmp.php',$this->filename);
			}
		else {
			$this->log="Non hai i permessi per scrivere su ".$this->filename;
			}
		if($this->kaConfigInc()==false) {
			$this->log="Errore nel salvataggio del file";
			}
		if($this->log=="") return true;
		else return false;
		}
	
	public function recover() {
		rename($this->filename,$this->filename.'.tmp.php');
		rename($this->filename.'.bkup.php',$this->filename);
		rename($this->filename.'.tmp.php',$this->filename.'.bkup.php');
		}
	
	public function addLine($value,$num=false) {
		$tmpcontents=array();
		$contents=explode("\n",$this->contents);
		if($num!=false&&count($contents)>$num) {
			foreach($contents as $ka=>$line) {
				if($ka==$num) $tmpcontents[]=$value;
				$tmpcontents[]=$line;
				}
			}
		else {
			$tmpcontents=$contents;
			$tmpcontents[]=$value;
			}
		$contents=implode($contents);
		$this->write($contents);
		}
	
	public function delLine($num) {
		$contents=explode("\n",$this->contents);
		unset($contents[$num]);
		$contents=implode($contents);
		$this->write($contents);
		}
	
	public function updateLine($num,$value) {
		$contents=explode("\n",$this->contents);
		$contents[$num]=$value;
		$contents=implode($contents);
		$this->write($contents);
		}

	public function findLine($searchkey) {
		$output=array();
		foreach(explode("\n",$this->contents) as $ka=>$line) {
			if(strpos($line,$searchkey)!==false) $output[$ka]=$line;
			}
		return $output;
		}

	public function getError() {
		return $this->log;
		}
	}
	
?>