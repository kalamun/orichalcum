<?php 
/* (c) Kalamun.org - GPL v3 */

class kaImpostazioni
{
	protected $ll='';
	
	public function __construct()
	{
		// set the current language
		$this->ll = !isset($_SESSION['ll']) ? DEFAULT_LANG : $_SESSION['ll'];
	}

	/* check if given parameter exists in database */
	public function paramExists($param,$ll=false)
	{
		if($ll==false) $ll=$this->ll;
		$query="SELECT * FROM `".TABLE_CONFIG."` WHERE `param`='".ksql_real_escape_string($param)."' AND `ll`='".ksql_real_escape_string($ll)."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) return true;
		else return false;
	}

	/* add a new paramenter */
	public function addParam($param,$value1,$value2,$ll=false)
	{
		if($ll==false) $ll=$this->ll;
		$query="INSERT INTO `".TABLE_CONFIG."` (`param`,`value1`,`value2`,`ll`) VALUES('".ksql_real_escape_string($param)."','".ksql_real_escape_string($value1)."','".ksql_real_escape_string($value2)."','".ksql_real_escape_string($ll)."')";
		if(ksql_query($query)) return true;
		else return false;
	}

	/* update a parameter that already exists in db */
	public function updateParam($param,$value1,$value2,$ll=false)
	{
		if($ll==false) $ll=$this->ll;
		$query="UPDATE `".TABLE_CONFIG."` SET `value1`='".ksql_real_escape_string($value1)."',`value2`='".ksql_real_escape_string($value2)."' WHERE `param`='".ksql_real_escape_string($param)."' AND `ll`='".ksql_real_escape_string($ll)."' LIMIT 1";
		if(ksql_query($query)) return true;
		else return false;
	}
	
	/* delete a parameter from db */
	public function deleteParam($param,$ll=false)
	{
		if($ll==false) $ll=$this->ll;
		$query="DELETE FROM `".TABLE_CONFIG."` WHERE `param`='".ksql_real_escape_string($param)."' AND `ll`='".ksql_real_escape_string($ll)."' LIMIT 1";
		if(ksql_query($query)) return true;
		else return false;
	}
	
	/* update a parameter if it already exists, or add it */
	public function replaceParam($param,$value1,$value2,$ll=false)
	{
		if($this->paramExists($param,$ll)) return $this->updateParam($param,$value1,$value2,$ll);
		else return $this->addParam($param,$value1,$value2,$ll);
	}
	public function setParam($param,$value1,$value2,$ll=false)
	{
		return $this->replaceParam($param,$value1,$value2,$ll);
	}

	/* returns the param array as in db: param, value1, value2, language */
	public function getParam($param,$ll=false)
	{
		if($ll==false) $ll=$this->ll;
		$query="SELECT * FROM `".TABLE_CONFIG."` WHERE `param`='".ksql_real_escape_string($param)."' AND `ll`='".ksql_real_escape_string($ll)."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) return $row;
		else return false;
	}

	/* returns a list of params for the given search */
	public function getParamsList($param=false, $ll=false)
	{
		if(empty($ll)) $ll = $this->ll;
		$output = array();
		
		$query = "SELECT * FROM `".TABLE_CONFIG."` WHERE ";
		if(!empty($param)) $query .= "`param` LIKE '".ksql_real_escape_string($param)."' AND ";
		$query .= "`ll`='".ksql_real_escape_string($ll)."' ORDER BY `param`";

		$results = ksql_query($query);
		while($row = ksql_fetch_array($results))
		{
			$output[$row['param']] = $row;
		}
		return $output;
	}

	/* return only the requested value (1 or 2) for the given param */
	public function getVar($param,$num,$ll=false)
	{
		if($ll==false) $ll=$this->ll;
		$query="SELECT `value".$num."` FROM `".TABLE_CONFIG."` WHERE `param`='".ksql_real_escape_string($param)."' AND ll='".ksql_real_escape_string($ll)."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) return $row['value'.$num];
		else return false;
	}

	/* return an array with the list of the templates */
	public function getTemplateList()
	{
		$output=array();
		if($handle=opendir(BASERELDIR.DIR_TEMPLATE))
		{
			while(false!==($file=readdir($handle)))
			{
				if(is_dir(BASERELDIR.DIR_TEMPLATE.$file) && trim($file,".")!="")
				{
					$output[]=$file;
				}
			}
			closedir($handle);
		}
		return $output;
	}

	/* return an array with the list of layouts of the given template */
	public function getLayoutList($tpl="")
	{
		if($tpl=="") $tpl=$this->getVar('template_default',1);
		$output=array();
		
		if(file_exists(BASERELDIR.DIR_TEMPLATE.$tpl.'/layouts') && $handle=opendir(BASERELDIR.DIR_TEMPLATE.$tpl.'/layouts'))
		{
			while(false!==($file=readdir($handle)))
			{
				if(trim($file,".")!="")
				{
					$output[]=$file;
				}
			}
			closedir($handle);
		}
		return $output;
	}

}


/* manage the admin/inc/config.inc.php file */
class kaConfigInc
{
	protected $filename,$contents,$log;

	public function __construct()
	{
		$this->log="";
		$this->filename=ADMINRELDIR."inc/config.inc.php";
		if($this->read()==false) return false;
		else return true;
	}

	public function getFilename()
	{
		return $this->filename;
	}

	public function read()
	{
		if(file_exists($this->filename))
		{
			$this->contents=file_get_contents($this->filename);
			return $this->contents;
		}
		else return false;
	}
	
	public function write($contents)
	{
		$this->log="";
		if(is_writable($this->filename))
		{
			if (!$handle=fopen($this->filename.'.tmp.php','w'))
			{
				$this->log = "Config.inc: ".$this->filename.".tmp.php not found";
				exit;
			}
			
			// create a backup copy
			if(file_exists($this->filename.'.bkup.php')) unlink($this->filename.'.bkup.php');
			copy($this->filename,$this->filename.'.bkup.php');

			if(fwrite($handle,$contents)===FALSE)
			{
				$this->log = "Config.inc: Error while trying to write ".$this->filename.".tmp.php ... check permissions";
				exit;
			}
			
			fclose($handle);
			unlink($this->filename);
			rename($this->filename.'.tmp.php',$this->filename);

		} else {
			$this->log = "Config.inc: You are not allowed to write the file ".$this->filename." ... check permissions";
		}

		if($this->__construct()==false)
		{
			$this->log = "Config.inc: Error while saving the file config.inc.php";
		}

		if($this->log=="") return true;
		else {
			if(isset($GLOBALS['kaLog'])) $GLOBALS['kaLog']->add("ERR", $this->log);
			trigger_error($this->log);
			return false;
		}
	}
	
	/* recover backup */
	public function recover()
	{
		rename($this->filename,$this->filename.'.tmp.php');
		rename($this->filename.'.bkup.php',$this->filename);
		rename($this->filename.'.tmp.php',$this->filename.'.bkup.php');
	}
	
	public function addLine($value,$num=false)
	{
		$tmpcontents=array();
		$contents=explode("\n",$this->contents);
		if($num!=false&&count($contents)>$num)
		{
			foreach($contents as $ka=>$line)
			{
				if($ka==$num) $tmpcontents[]=$value;
				$tmpcontents[]=$line;
			}

		} else {
			$tmpcontents=$contents;
			$tmpcontents[]=$value;
		}
		$contents=implode($contents);
		$this->write($contents);
	}
	
	public function delLine($num)
	{
		$contents=explode("\n",$this->contents);
		unset($contents[$num]);
		$contents=implode($contents);
		$this->write($contents);
	}
	
	public function updateLine($num,$value)
	{
		$contents=explode("\n",$this->contents);
		$contents[$num]=$value;
		$contents=implode($contents);
		$this->write($contents);
	}

	public function findLine($searchkey)
	{
		$output=array();
		foreach(explode("\n",$this->contents) as $ka=>$line)
		{
			if(strpos($line,$searchkey)!==false) $output[$ka]=$line;
		}
		return $output;
	}

	public function getError()
	{
		return $this->log;
	}
}
	
