<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

/*

possible permissions:
	 -> Inherit
	private -> nobody can access
	restricted -> only specified members
	members -> any member
	public -> anyone

permissions are ereditary. in case of Inherited permissions, the "Inherited" field is valued as true

*/

class kPrivate {
	protected $inited;
	protected $permissions,$members;

	public function __construct() {
		$this->inited=false;
		}
	
	public function init() {
		$this->inited=true;
		
		if($GLOBALS['__members']->isLogged()) {
			// load only the current user
			$this->members[]=$GLOBALS['__members']->getById($GLOBALS['__members']->getVar('idmember'));
			}
		else {
			// load members list
			//$this->members=$GLOBALS['__members']->getList(true,"");
			$this->members=array();
			}

		// load permissions list
		$this->permissions=$this->getPermissionsList();

		}

	public function mkdir($dir,$permissions,$armembers=array()) {
		$dir=trim($dir," ./");
		$dir=str_replace("../","",$dir);
		if($dir=="") return false;

		if(file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$dir)) return false;

		if(mkdir($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$dir)) {
			$this->setPermissions($dir,$permissions,$armembers);
			return true;
			}
		return false;
		}

	public function getDirContent($dir) {
		if(!$this->inited) $this->init();
		$dir=utf8_encode(trim($dir," ./"));
		$dir=str_replace("../","",$dir);

		//check different encodes
		$fulldir=$_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$dir;
		if(!file_exists($fulldir)) {
			$fulldir=$_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.utf8_decode($dir);
			}

		$output=array();
		$files=array();
		$dirs=array();
		if(file_exists($fulldir)&&is_dir($fulldir)) {
			$perm=$this->getPermissions($dir);
			//check permissions
			if($perm['permissions']=='public'
				||($perm['permissions']=='members'&&$GLOBALS['__members']->isLogged())
				||($perm['permissions']=='restricted'&&$GLOBALS['__members']->isLogged()&&isset($perm['members'][$GLOBALS['__members']->getVar('idmember')]))
				) {

				//read dir
				$output['dirname']=$dir;
				$output['permissions']=$this->getPermissions(utf8_decode($dir));
				$output['permalink']=SITE_URL.BASEDIR.$GLOBALS['__template']->getLanguageURI(kGetLanguage()).$GLOBALS['__template']->getVar('dir_private',1).'/'.$dir;
				$output['parent']=dirname($dir);
				$output['size']=0;

				foreach(scandir($fulldir) as $file) {
					if(trim($file,".")!="") {
						if(is_dir(rtrim($fulldir,"/").'/'.$file)) {
							$tmp=$this->getDirContent($dir.'/'.$file);
							if(count($tmp)>0) {
								$tmp['dirname']=mb_convert_encoding($file,"UTF-8");
								$tmp['abslocation']=rtrim($fulldir,"/").'/'.$file;
								$tmp['permalink']=SITE_URL.BASEDIR.$GLOBALS['__template']->getLanguageURI(kGetLanguage()).$GLOBALS['__template']->getVar('dir_private',1).'/'.str_replace("%2F","/",urlencode(utf8_decode($dir))).($dir!=""?'/':'').urlencode($file);
								$tmp['permissions']=$this->getPermissions($dir.'/'.$file);
								$dirs[$file]=$tmp;
								$output['size']+=$tmp['size']['b'];
								}
							}
						else {
							$tmp=array();
							$tmp['filename']=mb_convert_encoding($file,"UTF-8");
							$tmp['extension']=substr($file,strrpos($file,".")+1);
							$tmp['location']=$fulldir.'/'.$file;
							$tmp['permalink']=SITE_URL.BASEDIR.$GLOBALS['__template']->getLanguageURI(kGetLanguage()).$GLOBALS['__template']->getVar('dir_private',1).'/'.str_replace("%2F","/",urlencode(utf8_decode($dir))).($dir!=""?'/':'').urlencode($file);
							$size=filesize($fulldir.'/'.$file);
							$tmp['size']=array("b"=>$size,
												"Kb"=>number_format($size/(1024),2),
												"Mb"=>number_format($size/(1024*1024),2),
												"Gb"=>number_format($size/(1024*1024*1024),2));
							$tmp['datecreation']=date(kGetVar('timezone',2),filectime($fulldir.'/'.$file));
							$tmp['datemodification']=date(kGetVar('timezone',2),filemtime($fulldir.'/'.$file));
							$files[$file]=$tmp;
							$output['size']+=$size;
							}
						}
					}

				$output['size']=array("b"=>$output['size'],
									"Kb"=>number_format($output['size']/(1024),2),
									"Mb"=>number_format($output['size']/(1024*1024),2),
									"Gb"=>number_format($output['size']/(1024*1024*1024),2));
				ksort($dirs);
				ksort($files);
				foreach($dirs as $d) {
					$output[]=$d;
					}
				foreach($files as $f) {
					$output[]=$f;
					}
				}

			return $output;
			}
		return false;
		}
	
	private function getPermissionsList() {
		if(!$this->inited) $this->init();
		$output=array(""=>array("permissions"=>"public","members"=>$this->members,"inherited"=>false,"writepermissions"=>"private","writemembers"=>array()));
		$query="SELECT * FROM ".TABLE_PRIVATE;
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$m=explode(",",trim($row['members'],","));
			$m=array_flip($m);
			$mw=explode(",",trim($row['writemembers'],","));
			$mw=array_flip($mw);
			$row['members']=array();
			foreach($this->members as $ordine=>$member) {
				if(isset($m[$member['idmember']])||$row['permissions']=='public'||$row['permissions']=='members') $row['members'][$member['idmember']]=$member;
				}
			$row['writemembers']=array();
			foreach($this->members as $ordine=>$member) {
				if(isset($mw[$member['idmember']])||$row['writepermissions']=='public'||$row['writepermissions']=='members') $row['writemembers'][$member['idmember']]=$member;
				}
			$output[$row['dir']]=$row;
			}
		return $output;
		}
	public function getPermissions($dir) {
		if(!$this->inited) $this->init();
		$dir=trim($dir," ./");
		$dir=str_replace("../","",$dir);
		if(!isset($this->permissions[''])) return false; //basedir permission is missing!
		$refdir="";
		//check if parent dirs has more restrictive permissions of current dir... in case, Inherit it
		$val=array("private"=>1,"restricted"=>2,"members"=>3,"public"=>4,""=>5);
		for($d=$dir;$d!="";$d=trim(dirname($d)," ./")) {
			if($refdir==""&&isset($this->permissions[$d])) $refdir=$d;
			if(isset($this->permissions[$d])&&$val[$this->permissions[$d]['permissions']]<$val[$this->permissions[$refdir]['permissions']]) $refdir=$d;
			}
		$perm=$this->permissions[$refdir];
		$perm['inherited']=($refdir==$dir?false:true);
		return $perm;
		}
	
	public function dirExists($dir) {
		if(!$this->inited) $this->init();
		$dir=trim($dir," ./");
		$dir=str_replace("../","",$dir);
		if(!isset($this->permissions[''])) return false; //basedir permission is missing!
		$fullpath=$_SERVER["DOCUMENT_ROOT"].BASEDIR.DIR_PRIVATE.$dir;
		return file_exists($fullpath);
		}

	public function dirIsWritable($dir) {
		if(!$this->inited) $this->init();
		$dir=trim($dir," ./");
		$dir=str_replace("../","",$dir);
		if(!isset($this->permissions[''])) return false; //basedir permission is missing!
		$refdir="";
		//check if parent dirs has more restrictive permissions of current dir... in case, Inherit it
		for($d=$dir;$d!="";$d=trim(dirname($d)," ./")) {
			if($refdir==""&&isset($this->permissions[$d])) $refdir=$d;
			if(isset($this->permissions[$d])&&!isset($this->permissions[$dir])) $refdir=$d;
			}
		$perm=$this->permissions[$refdir];
		if($perm['writepermissions']!="private"&&isset($perm['writemembers'][kGetMemberId()])) return true;
		return false;
		}
	
	public function isFile($filename) {
		$filename=trim($filename," ./");
		$filename=str_replace("../","",$filename);
		$filename=$_SERVER["DOCUMENT_ROOT"].BASEDIR.DIR_PRIVATE.$filename;
		if(!file_exists($filename)) return false;
		if(is_dir($filename)) return false;
		return true;
		}
	
	public function canIDownload($filename) {
		if(!$this->inited) $this->init();
		//if absolute path arrives, trim it
		$path=$_SERVER["DOCUMENT_ROOT"].BASEDIR.DIR_PRIVATE;
		if(substr($filename,0,strlen($path))==$path) $filename=substr($filename,strlen($path));
		$filename=trim($filename," ./");
		$filename=str_replace("../","",$filename);
		$perm=$this->getPermissions($filename);
		if($perm['permissions']=='public') return true;
		if($perm['permissions']=='members'&&$GLOBALS['__members']->isLogged()) return true;
		if($perm['permissions']=='restricted'&&$GLOBALS['__members']->isLogged()&&isset($perm['members'][$GLOBALS['__members']->getVar('idmember')])) return true;
		return false;
		}
	
	public function forceDownload($filename) {
		if(!$this->inited) $this->init();
		$filename=trim($filename," ./");
		
		//check if requested file exists and that it isn't a dir
		if(!$this->isFile($filename)) return false;
		
		//check permissions
		if(!$this->canIDownload($filename)) return false;
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($filename).'"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: '.filesize($_SERVER["DOCUMENT_ROOT"].BASEDIR.DIR_PRIVATE.$filename));
		ob_clean();
		flush();
		$this->readfile_chunked($_SERVER["DOCUMENT_ROOT"].BASEDIR.DIR_PRIVATE.$filename);
		//$this->log($_SESSION['magazzino']['iduser'],$_SESSION['magazzino']['name'],"MDW","[".$dir."] ".$filename);
		//$this->notify($dir,"L'utente ".$_SESSION['magazzino']['name']." ha scaricato il file ".$filename." dallo spazio ".$mag['name']);
		exit(0);
		}

	private function readfile_chunked($filename,$retbytes=true) {
		$chunksize=1*(1024*1024); // how many bytes per chunk
		$buffer='';
		$cnt=0;
		$handle=fopen($filename,'rb');
		if($handle===false) {
			return false;
			}
		while(!feof($handle)) {
			$buffer=fread($handle,$chunksize);
			echo $buffer;
			ob_flush();
			flush();
			if($retbytes) $cnt+=strlen($buffer);
			}
		$status=fclose($handle);
		if($retbytes&&$status) {
			return $cnt; // return num bytes delivered like readfile() does.
			}
		return $status;
		} 


	}

