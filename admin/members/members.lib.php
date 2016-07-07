<?php 
/* 2010 (c) Roberto Kalamun Pasini - GPLv3 */

class kaMembers {
	protected $kaMetadata;
	
	public function __construct()
	{
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/metadata.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/imgallery.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.ADMINDIR.'inc/docgallery.lib.php');
		$this->kaMetadata=new kaMetadata();
		$this->kaImgallery=new kaImgallery();
		$this->kaDocgallery=new kaDocgallery();
	}
	
	/* add a new member to the database */
	public function add($name, $email, $username, $password, $affiliation="", $expiration="", $status="act")
	{
		/* clean inputs */
		$email = strtolower($email);
		$email = preg_replace("/[^\w@\.-]/", "", $email);
		if($email!="" && !preg_match("/[^@]+@[^.]+\..*/", $email)) $email = "";
	
		if(trim($name)=="" && $email!="") $name = $email;
		if(strlen($name)>64) $name = substr($name, 0, 64);
		$name = mb_convert_encoding($name, "UTF-8", mb_detect_encoding($name));
		
		if(strlen($username)>64) $username = substr($username, 0, 64);
		$username = mb_convert_encoding($username, "UTF-8", mb_detect_encoding($username));
		
		
		if($password=="") $password = $this->generatePassword();
		if(strlen($expiration)==10) $expiration.=" ".date("H:i:s");
		if($expiration!=""&&preg_match("/\d{4}.\d{2}.\d{2}.\d{2}.\d{2}.\d{2}/",$expiration)) $expiration=substr($expiration,0,4).'-'.substr($expiration,5,2).'-'.substr($expiration,8,2).' '.substr($expiration,11,2).':'.substr($expiration,14,2).':'.substr($expiration,17,2);
		else $expiration="0000-00-00 00:00:00";
		
		$query="SELECT * FROM `".TABLE_MEMBERS."` WHERE `username`='".ksql_real_escape_string($username)."' ";
		if($email!="") $query.=" OR `email`='".ksql_real_escape_string($email)."' ";
		$query.=" LIMIT 1";
		
		$results=ksql_query($query);
		if(!ksql_fetch_array($results))
		{
			$query = "INSERT INTO ".TABLE_MEMBERS." (`name`,`email`,`username`,`password`,`affiliation`,`created`,`lastlogin`,`expiration`,`status`,`newsletter_lists`) VALUES('".ksql_real_escape_string($name)."','".ksql_real_escape_string($email)."','".ksql_real_escape_string($username)."','".ksql_real_escape_string($password)."','".ksql_real_escape_string($affiliation)."',NOW(),NOW(),'".ksql_real_escape_string($expiration)."','".ksql_real_escape_string($status)."',',')";
			if(ksql_query($query)) return ksql_insert_id();
		}
		
		return false;
	}
	
	public function addMass($qty=0,$prefix="",$affiliation="",$expiration="") {
		$success=true;
		for($i=0;$i<=$qty;$i++) {
			$username=$prefix.$this->generatePassword(8);
			while($this->getUserByUsername($username,$affiliation)!=false) {
				$username=$prefix.$this->generatePassword(8);
				}
			$password=$this->generatePassword(8);
			if($this->add($username,"",$username,$password,$affiliation,$expiration)==false) $success=false;
			}
		return $success;
		}

	public function update($idmember,$name,$email,$username,$password,$affiliation="",$expiration="",$status="act") {
		$email=strtolower($email);
		if($email!=""&&!preg_match("/.*@.*\..*/",$email)) $email="";
		if($password=="") $password=$this->generatePassword();
		if(strlen($expiration)==10) $expiration.=" ".date("H:i:s");
		if($expiration!=""&&preg_match("/\d{4}.\d{2}.\d{2}.\d{2}.\d{2}.\d{2}/",$expiration)) $expiration=substr($expiration,0,4).'-'.substr($expiration,5,2).'-'.substr($expiration,8,2).' '.substr($expiration,11,2).':'.substr($expiration,14,2).':'.substr($expiration,17,2);
		else $expiration="0000-00-00 00:00:00";
		$query="SELECT * FROM ".TABLE_MEMBERS." WHERE idmember='".intval($idmember)."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
			$query="UPDATE ".TABLE_MEMBERS." SET name='".b3_htmlize($name,true,"")."',email='".b3_htmlize($email,true,"")."',username='".b3_htmlize($username,true,"")."',password='".addslashes(stripslashes($password))."',affiliation='".b3_htmlize($affiliation,true,"")."',expiration='".$expiration."',status='".$status."' WHERE idmember=".intval($idmember);
			if(ksql_query($query)) return $row['idmember'];
			}
		return false;
		}

	public function updateNewsletter($idmember,$newsletter_lists)
	{
		$query="UPDATE ".TABLE_MEMBERS." SET `newsletter_lists`='".ksql_real_escape_string($newsletter_lists)."' WHERE idmember=".intval($idmember);
		if(ksql_query($query)) return true;
		else return false;
	}

	public function updateEmail($idmember,$email)
	{
		$query="UPDATE ".TABLE_MEMBERS." SET `email`='".ksql_real_escape_string($email)."' WHERE idmember=".intval($idmember);
		if(ksql_query($query)) return true;
		else return false;
	}

	public function password($idmember,$password)
	{
		$query="UPDATE ".TABLE_MEMBERS." SET `password`='".ksql_real_escape_string($password)."' WHERE `idmember`=".ksql_real_escape_string($idmember)." LIMIT 1";
		if(ksql_query($query)) {
			if(isset($_SESSION['idmember'])&&$idmember==$_SESSION['idmember']) $_SESSION['password']=$password;
			return true;
			}
		else return false;
	}
	
	public function del($idmember,$affiliation=null)
	{
		$log=true;
		$query="UPDATE ".TABLE_MEMBERS." SET status='del' WHERE idmember='".intval($idmember)."' ";
		if($affiliation!=null) $query.=" AND affiliation='".ksql_real_escape_string($affiliation)."'";
		$query.=" LIMIT 1";
		if(!ksql_query($query)) $log=false;
		return $log;
	}

	public function delAll($affiliation=null) {
		$log=true;
		$query="UPDATE ".TABLE_MEMBERS." SET status='del' ";
		if($affiliation!=null) $query.=" WHERE affiliation='".ksql_real_escape_string($affiliation)."'";
		if(!ksql_query($query)) $log=false;
		return $log;
		}
	
	/* GET A LIST OF USERS, USING SOME FILTERS */
	public function getUsersList($vars=array())
	{
		if(!is_array($vars)) $vars=array('idmemberAsKey'=>$vars);
		
		$output=array();
		$query="SELECT * FROM `".TABLE_MEMBERS."` WHERE ";

		// filter by email
		if(isset($vars['email']))
		{
			$email = strtolower($vars['email']);
			$email = preg_replace("/[^\w@\.-]/", "", $email);
			$query.="`email`='".ksql_real_escape_string($email)."' AND ";
		}
		
		// filter by given array of list ids
		if(isset($vars['lists']))
		{
			$query.="(";
			foreach($vars['lists'] as $idlista)
			{
				$query.="`newsletter_lists` LIKE '%,".ksql_real_escape_string($idlista).",%' OR ";
			}
			$query.="idmember=0) AND ";
		}

		//mandatory fields
		if(isset($vars['mandatary']))
		{
			foreach($vars['mandatary'] as $field)
			{
				$query.="`".ksql_real_escape_string($field)."`<>'' AND ";
			}
		}

		//custom conditions
		if(isset($vars['conditions'])&&$vars['conditions']!="")
		{
			$query.=" (".$vars['conditions'].") AND ";
		}

		$query.=" `idmember`>0 ";

		// group by specified field
		if(isset($vars['groupby']))
		{
			$query.=" GROUP BY `".ksql_real_escape_string($vars['groupby'])."` ";
		}

		$query.=" ORDER BY ";
		if(isset($vars['orderby'])) $query.=$vars['orderby'];
		else $query.="`name`,`username`,`created`";
		$results=ksql_query($query);
		if(!$results) return false;
		while($row=ksql_fetch_array($results))
		{
			isset($vars['idmemberAsKey'])&&$vars['idmemberAsKey']==true?$i=$row['idmember']:$i=count($output);
			$output[$i]=$row;
			$output[$i]['created_friendly']=preg_replace("/(\d{4}).(\d{2}).(\d{2}).(\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 h$4:$5",$row['created']);
			$output[$i]['lastlogin_friendly']=preg_replace("/(\d{4}).(\d{2}).(\d{2}).(\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 h$4:$5",$row['lastlogin']);
			$output[$i]['expiration_friendly']=trim($row['expiration'],"0- :")==""?'-':preg_replace("/(\d{4}).(\d{2}).(\d{2}).(\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 h$4:$5",$row['expiration']);
		}
		return $output;
	}
	
	public function countUsers($vars=array()) {
		if(!is_array($vars)) $vars=array('idmemberAsKey'=>$vars);
		
		$output=array();
		$query="SELECT count(*) AS tot FROM `".TABLE_MEMBERS."` WHERE ";

		// filter by email
		if(isset($vars['email'])) {
			$query.="`email`='".ksql_real_escape_string($vars['email'])."' AND ";
			}
		
		// filter by given array of list ids
		if(isset($vars['lists'])) {
			$query.="(";
			foreach($vars['lists'] as $idlista) {
				$query.="`newsletter_lists` LIKE '%,".ksql_real_escape_string($idlista).",%' OR ";
				}
			$query.="idmember=0) AND ";
			}

		//mandatory fields
		if(isset($vars['mandatary'])) {
			foreach($vars['mandatary'] as $field) {
				$query.="`".ksql_real_escape_string($field)."`<>'' AND ";
				}
			}
		
		//custom conditions
		if(isset($vars['conditions'])&&$vars['conditions']!="") {
			$query.="(".$vars['conditions'].") AND ";
			}

		$query.=" `idmember`>0 ";

		// group by specified field
		if(isset($vars['groupby'])) {
			$query.=" GROUP BY `".ksql_real_escape_string($vars['groupby'])."` ";
			}

		$results=ksql_query($query);
		if(!$results) return false;
		$row=ksql_fetch_array($results);
		return $row['tot'];
		}
	
	public function getUserById($idmember) {
		$query="SELECT * FROM ".TABLE_MEMBERS." WHERE idmember='".intval($idmember)."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
			$row['created_leggibile']=preg_replace("/(\d{4}).(\d{2}).(\d{2}).(\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 h$4:$5",$row['created']);
			$row['lastlogin_leggibile']=preg_replace("/(\d{4}).(\d{2}).(\d{2}).(\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 h$4:$5",$row['lastlogin']);
			$row['metadata']=$this->kaMetadata->getList(TABLE_MEMBERS,$row['idmember']);
			$row['imgallery']=$this->kaImgallery->getList(TABLE_MEMBERS,$row['idmember']);
			$row['docgallery']=$this->kaDocgallery->getList(TABLE_MEMBERS,$row['idmember']);
			return $row;
			}
		return false;
		}

	public function getUserByUsername($username,$affiliation=null) {
		$query="SELECT * FROM ".TABLE_MEMBERS." WHERE username='".ksql_real_escape_string($username)."' ";
		if($affiliation!=null) $query.=" AND affiliation='".ksql_real_escape_string($affiliation)."' ";
		$query.=" ORDER BY status LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
			$row['created_leggibile']=preg_replace("/(\d{4}).(\d{2}).(\d{2}).(\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 h$4:$5",$row['created']);
			$row['lastlogin_leggibile']=preg_replace("/(\d{4}).(\d{2}).(\d{2}).(\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 h$4:$5",$row['lastlogin']);
			$row['metadata']=$this->kaMetadata->getList(TABLE_MEMBERS,$row['idmember']);
			return $row;
			}
		return false;
		}
	
	private function generatePassword($length=8,$charset=null) {
		$password="";
		if($charset==null) $charset="qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890powivdslwieufdsjnvkcwug568726387fhUVHISDUVQGIFJDSC";
		for($i=1;$i<=$length;$i++) {
			$password.=$charset{rand(0,strlen($charset)-1)};
			}
		return $password;
		}
	
	public function refreshHtpasswd() {
		$htpasswd=$_SERVER['DOCUMENT_ROOT'].BASEDIR.'members/.htpasswd';
		$htpasswdbkup=$_SERVER['DOCUMENT_ROOT'].BASEDIR.'members/.htpasswd_bkup';
		if(file_exists($htpasswdbkup)) unlink($htpasswdbkup);
		if(file_exists($htpasswd)) rename($htpasswd,$htpasswdbkup);
		file_put_contents($htpasswd,"# last update ".date("d-m-Y H:i:s")." #\n");
		foreach($this->getUsersList() as $u) {
			if($u['status']=="act"&&(trim($u['expiration'],"0- :")==""||mktime(substr($u['expiration'],11,2),substr($u['expiration'],14,2),substr($u['expiration'],17,2),substr($u['expiration'],5,2),substr($u['expiration'],8,2),substr($u['expiration'],0,4))>time())) file_put_contents($htpasswd,$u['username'].":".crypt($u['password'],base64_encode($u['password']))."\n",FILE_APPEND);
			}
		}
	}

