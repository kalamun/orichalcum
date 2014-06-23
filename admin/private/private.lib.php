<?
/* (c) Kalamun.org - GNU/GPL 3 */

/*

possible permissions:
	 -> Inherit
	private -> nobody can access
	restricted -> only specified members
	members -> any member
	public -> anyone

permissions are ereditary. in case of inherited permissions, the "inherited" field is valued as true

*/

class kaPrivate {
	protected $permissions,$members,$privatedir;
	
	public function kaPrivate() {
		// load members list
		require_once($_SERVER['DOCUMENT_ROOT'].ADMINDIR.'members/members.lib.php');
		$kaMembers=new kaMembers();
		$this->members=$kaMembers->getUsersList(true);
		
		$this->privatedir=kaGetVar('dir_private',1);

		// load permissions list
		$this->permissions=$this->getPermissionsList();
		}
	
	public function getDirContent($dir) {
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
			$output['dirname']=$dir;
			$output['permissions']=$this->getPermissions(utf8_decode($dir));
			$output['parent']=substr($dir,0,strrpos($dir,"/"));
			$output['permalink']=SITE_URL.BASEDIR.strtolower($_SESSION['ll']).'/'.$this->privatedir.'/'.urlencode($dir);
			$output['size']=0;
			foreach(scandir($fulldir) as $file) {
				$file=trim($file," ./");
				if($file!="") {
					if(is_dir($fulldir.'/'.$file)) {
						$tmp=$this->getDirContent($dir.'/'.$file);
						$tmp['dirname']=mb_convert_encoding($file,"UTF-8");
						$tmp['abslocation']=$fulldir.'/'.$file;
						$tmp['permalink']=SITE_URL.BASEDIR.strtolower($_SESSION['ll']).'/'.$this->privatedir.'/'.urlencode($dir).'/'.urlencode($file);
						$tmp['permissions']=$this->getPermissions($dir.'/'.$file);
						for($f=strtolower($file);isset($dirs[$f]);$f+='-') {}
						$dirs[$f]=$tmp;
						if(isset($tmp['size'])) $output['size']+=$tmp['size']['b'];
						}
					else {
						$tmp=array();
						$tmp['filename']=mb_convert_encoding($file,"UTF-8");
						$tmp['extension']=substr($file,strrpos($file,".")+1);
						$tmp['location']=$fulldir.'/'.$file;
						$tmp['permalink']=SITE_URL.BASEDIR.strtolower($_SESSION['ll']).'/'.$this->privatedir.'/'.urlencode($dir).'/'.urlencode($file);
						$tmp['permissions']=$this->getPermissions($dir.'/'.$file);
						$size=filesize($fulldir.'/'.$file);
						$tmp['size']=array("b"=>$size,
											"Kb"=>number_format($size/(1024),2),
											"Mb"=>number_format($size/(1024*1024),2),
											"Gb"=>number_format($size/(1024*1024*1024),2));
						$tmp['datecreation']=date(kaGetVar('timezone',2),filectime($fulldir.'/'.$file));
						$tmp['datemodification']=date(kaGetVar('timezone',2),filemtime($fulldir.'/'.$file));
						for($f=strtolower($file);isset($dirs[$f]);$f+='-') {}
						$files[$f]=$tmp;
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
			return $output;
			}
		return false;
		}
	
	public function setPermissions($dir,$permissions,$armembers=array(),$writepermissions,$armembersw=array()) {
		$dir=trim($dir," ./");
		$dir=str_replace("../","",$dir);
		$members=",";
		foreach($armembers as $idmember=>$val) {
			$members.=$idmember.",";
			}
		if($permissions!="public"&&$permissions!="private"&&$permissions!="restricted"&&$permissions!="members") $permissions="";
		$writemembers=",";
		foreach($armembersw as $idmember=>$val) {
			$writemembers.=$idmember.",";
			}
		if($writepermissions!="private"&&$writepermissions!="restricted"&&$writepermissions!="members") $writepermissions="";

		if($permissions==""&&$writepermissions=="") {
			//if inherit is set, delete any previous entries
			$query="DELETE FROM ".TABLE_PRIVATE." WHERE `dir`='".mysql_real_escape_string($dir)."'";
			}
		else {
			//else set the new entry
			$query="SELECT * FROM ".TABLE_PRIVATE." WHERE `dir`='".mysql_real_escape_string($dir)."' LIMIT 1";
			$results=mysql_query($query);
			if($row=mysql_fetch_array($results)) $query="UPDATE ".TABLE_PRIVATE." SET `permissions`='".$permissions."',`members`='".mysql_real_escape_string($members)."',`writepermissions`='".$writepermissions."',`writemembers`='".mysql_real_escape_string($writemembers)."' WHERE `idprivate`=".$row['idprivate']." LIMIT 1";
			else $query="INSERT INTO ".TABLE_PRIVATE." (`dir`,`permissions`,`members`,`writepermissions`,`writemembers`) VALUES('".mysql_real_escape_string($dir)."','".$permissions."','".mysql_real_escape_string($members)."','".$writepermissions."','".mysql_real_escape_string($writemembers)."')";
			}
		if(mysql_query($query)) return true;
		else return false;		
		}
	private function getPermissionsList() {
		$output=array(""=>array("permissions"=>"public","members"=>$this->members,"writepermissions"=>"private","writemembers"=>array(),"inherited"=>false));
		$query="SELECT * FROM ".TABLE_PRIVATE;
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$m=explode(",",trim($row['members'],","));
			$m=array_flip($m);
			$mw=explode(",",trim($row['writemembers'],","));
			$mw=array_flip($mw);
			$row['members']=array();
			$row['writemembers']=array();
			foreach($this->members as $idmember=>$member) {
				if(isset($m[$idmember])||$row['permissions']=='public'||$row['permissions']=='members') $row['members'][$idmember]=$member;
				if(isset($mw[$idmember])||$row['writepermissions']=='public'||$row['writepermissions']=='members') $row['writemembers'][$idmember]=$member;
				}
			$output[$row['dir']]=$row;
			}
		return $output;
		}
	public function getPermissions($dir) {
		$dir=utf8_encode($dir);
		$dir=trim($dir," ./");
		$dir=str_replace("../","",$dir);
		if(!isset($this->permissions[''])) return false; //basedir permission is missing!
		$refdir="";
		//check if parent dirs has more restrictive permissions of current dir... in case, inherit it
		$val=array("private"=>1,"restricted"=>2,"members"=>3,"public"=>4,""=>5);
		for($d=$dir;$d!="";$d=trim(dirname($d)," ./")) {
			if($refdir==""&&isset($this->permissions[$d])) $refdir=$d;
			if(isset($this->permissions[$d])&&$val[$this->permissions[$d]['permissions']]<$val[$this->permissions[$refdir]['permissions']]) $refdir=$d;
			}
		$perm=$this->permissions[$refdir];
		$perm['inherited']=($refdir==$dir?false:true);
		return $perm;
		}
	
	public function mkdir($dir,$permissions,$armembers=array(),$writepermissions=false,$armembersw=array()) {
		$dir=trim($dir," ./");
		$dir=str_replace("../","",$dir);
		if($dir=="") return false;
		if(file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$dir)) return false;

		if(mkdir($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$dir)) {
			$this->setPermissions($dir,$permissions,$armembers,$writepermissions,$armembersw);
			return true;
			}
		return false;
		}
	
	public function rename($fromdir,$todir) {
		$fromdir=trim($fromdir," ./");
		$fromdir=str_replace("../","",$fromdir);
		if($fromdir=="") return false;
		$todir=trim($todir," ./");
		$todir=str_replace("../","",$todir);
		if($todir=="") return false;
		if(file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$fromdir)&&!file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$todir)) {
			if(rename($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$fromdir,$_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$todir)) {
				//update permission db records
				$query="UPDATE ".TABLE_PRIVATE." SET `dir`='".mysql_real_escape_string(utf8_encode($todir))."' WHERE `dir`='".mysql_real_escape_string(utf8_encode($fromdir))."'";
				if(mysql_query($query)) return true;
				else return false;
				}
			}
		return false;
		}

	public function rmdir($dir) {
		$dir=trim($dir," ./");
		$dir=str_replace("../","",$dir);
		if($dir=="") return false; //can't delete root dir

		//remove dir recursively
		if(file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$dir)) {
			if(is_dir($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$dir)) {
				foreach($this->getDirContent($dir) as $i=>$f) {
					if(is_numeric($i)) {
						if(isset($f['dirname'])) $this->rmdir($dir.'/'.$f['dirname']);
						elseif(isset($f['filename'])) $this->deleteFile($dir.'/'.utf8_decode($f['filename']));
						}
					}
				if(!rmdir($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$dir)) return false;

				//remove permission db records recursively
				$query="DELETE FROM ".TABLE_PRIVATE." WHERE `dir`='".mysql_real_escape_string($dir)."'";
				mysql_query($query);
				}
			}
		}
	
	public function uploadFile($from,$to) {
		$to=trim($to," ./");
		$to=str_replace("../","",$to);
		$to=$_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$to;
		
		//forbid to upload php files
		if(substr(strtolower($to),-4)=='.php'||substr(strtolower($to),-5)=='.php3') return false;
		
		if(move_uploaded_file($from,$to)) return true;
		else return false;
		}
	
	public function deleteFile($file) {
		$file=trim($file," ./");
		$file=str_replace("../","",$file);
		if(file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$file)) {
			if(!is_dir($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$file)) unlink($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$file);
			}
		}
	
	public function delete($file) {
		$file=trim($file," ./");
		$file=str_replace("../","",$file);
		if(file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$file)) {
			if(is_dir($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_PRIVATE.$file)) $this->rmdir($file);
			else $this->deleteFile($file);
			}
		}

	public function isFile($filename) {
		$filename=trim($filename," ./");
		$filename=$_SERVER["DOCUMENT_ROOT"].BASEDIR.DIR_PRIVATE.$filename;
		if(!file_exists($filename)) return false;
		if(is_dir($filename)) return false;
		return true;
		}
	
	public function forceDownload($filename) {
		$filename=trim($filename," ./");
		$filename=$_SERVER["DOCUMENT_ROOT"].BASEDIR.DIR_PRIVATE.$filename;
		
		//check if requested file exists and that it isn't a dir
		if(!file_exists($filename)) return false;
		if(is_dir($filename)) return false;
		
		header('Content-Description: File Transfer');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="'.basename($filename).'"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: '.filesize($filename));
		ob_get_level() and ob_clean();
		flush();
		$this->readfile_chunked($filename);
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

?>