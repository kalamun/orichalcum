<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

/* UTENTI */

class kUsers {
	protected $inited;
	protected $publicUsers;
	public $userDB;
	
	public function __construct() {
		$this->inited=false;
		}
		
	public function init() {
		$this->inited=true;
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."admin/inc/connect.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/tplshortcuts.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/images.lib.php");

		$kImages=new kImages();

		$up=kGetVar('utenti_pubblici',1);
		$iduser=explode(",",trim($up,",")); //array di utenti abilitati
		$iduser=array_flip($iduser);

		$this->publicUsers=array();
		$tmpPublicUsers=array();
		$query="SELECT * FROM `".TABLE_USERS."` WHERE ";
		foreach($iduser as $id=>$u) {
			$query.=" `iduser`='".intval($id)."' OR ";
			}
		$query.=" `iduser`=0 ORDER BY `name`";
		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$tmpPublicUsers[$row['iduser']]=$row;
			$tmpPublicUsers[$row['iduser']]['summary']="";
			$tmpPublicUsers[$row['iduser']]['description']="";
			
			$q="SELECT * FROM ".TABLE_USERS_PROP." WHERE family='info' AND iduser=".$row['iduser'];
			$rs=mysql_query($q);
			while($r=mysql_fetch_array($rs)) {
				$tmpPublicUsers[$row['iduser']][$r['param']]=$r['value'];
				}

			$tmpPublicUsers[$row['iduser']]['imgs']=($row['featuredimage']>0 ? $kImages->getImage($row['featuredimage']) : array());
			$tmpPublicUsers[$row['iduser']]['permalink']=BASEDIR.strtolower(LANG).'/'.kGetVar('dir_users',1).'/'.$row['username'];
			}
		foreach($iduser as $k=>$v) {
			if(isset($tmpPublicUsers[$k])) $this->publicUsers[]=$tmpPublicUsers[$k];
			}
		}
	
	public function getMetaData($username=false,$ll=false) {
		if(!$this->inited) $this->init();
		if($username==false) $username=$GLOBALS['__subdir__'];
		$query="SELECT name FROM ".TABLE_USERS." WHERE username='".b3_htmlize($username,true,"")."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$output=array();
		$output['titolo']=$row['name'];
		$output['traduzioni']="";
		$output['template']="";
		$output['layout']="";
		return $output;
		}
	public function userExists($username=false) {
		if(!$this->inited) $this->init();
		if($username==false) $username=trim($GLOBALS['__subdir__']);
		foreach($this->publicUsers as $user) {
			if($user['username']==$username) return true;
			}
		return false;
		}

	public function getUsers() {
		if(!$this->inited) $this->init();
		return $this->publicUsers;
		}

	public function getUser($username) {
		if(!$this->inited) $this->init();
		foreach($this->publicUsers as $u) {
			if($u['username']==$username) {
				return $u;
				}
			}
		return false;
		}
	public function getUserById($iduser) {
		if(!$this->inited) $this->init();
		foreach($this->publicUsers as $u) {
			if($u['iduser']==$iduser) {
				return $u;
				}
			}
		return false;
		}
	public function kGetUserByUsername($username=false) {
		if(!$this->inited) $this->init();
		if($username==false) $username=trim($GLOBALS['__subdir__']);
		foreach($this->publicUsers as $user) {
			if($user['username']==$username) return $user;
			}
		return false;
		}
	public function kSetUserByUsername($username=false) {
		if(!$this->inited) $this->init();
		if($username==false) $username=trim($GLOBALS['__subdir__']);
		foreach($this->publicUsers as $user) {
			if($user['username']==$username) $this->userDB=$user;
			}
		}

	}

class kMembers {
	protected $inited;
	public $userDB;
	
	public function __construct() {
		$this->inited=false;
		}

	public function init() {
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."admin/inc/connect.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."admin/inc/main.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/tplshortcuts.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/images.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/documents.lib.php");
		$this->imgallery=new kImgallery();
		$this->docgallery=new kDocgallery();
		$this->inited=true;
		}
		
	public function register($username,$password=false,$name,$email,$affiliation="",$expiration=false) {
		if(!$this->inited) $this->init();
		if(trim($username)=="") return false;
		$status='act';
		$email=strtolower($email);
		if($email!=""&&!preg_match("/.*@.*\..*/",$email)) $email="";
		if($password=="") $password=$this->generatePassword();
		if(strlen($expiration)==10) $expiration.=" ".date("H:i:s");
		if($expiration!=""&&preg_match("/\d{4}.\d{2}.\d{2}.\d{2}.\d{2}.\d{2}/",$expiration)) $expiration=substr($expiration,0,4).'-'.substr($expiration,5,2).'-'.substr($expiration,8,2).' '.substr($expiration,11,2).':'.substr($expiration,14,2).':'.substr($expiration,17,2);
		else $expiration="0000-00-00 00:00:00";
		$query="SELECT * FROM ".TABLE_MEMBERS." WHERE username='".b3_htmlize($username,true,"")."' AND affiliation='".b3_htmlize($affiliation,true,"")."' AND status<>'del' LIMIT 1";
		$results=mysql_query($query);
		if(!mysql_fetch_array($results)) {
			$query="INSERT INTO ".TABLE_MEMBERS." (name,email,username,password,affiliation,created,lastlogin,expiration,status,newsletter_lists) VALUES('".b3_htmlize($name,true,"")."','".b3_htmlize($email,true,"")."','".b3_htmlize($username,true,"")."','".mysql_real_escape_string($password)."','".mysql_real_escape_string($affiliation)."',NOW(),NOW(),'".mysql_real_escape_string($expiration)."','".mysql_real_escape_string($status)."',',')";
			$results=mysql_query($query);
			if($results) return mysql_insert_id();
			}
		return false;
		}
	public function replaceMetadata($username,$param,$value,$affiliation=false) {
		if(!$this->inited) $this->init();
		$query="SELECT * FROM ".TABLE_MEMBERS." WHERE `username`='".b3_htmlize($username,true,"")."' AND ";
		if($affiliation!=false) $query.=" `affiliation`='".b3_htmlize($affiliation,true,"")."' AND ";
		$query.=" status<>'del' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if(isset($row['idmember'])) {
			$query2="SELECT * FROM ".TABLE_METADATA." WHERE `tabella`='".TABLE_MEMBERS."' AND `id`='".$row['idmember']."' AND `param`='".b3_htmlize($param,true,"")."' LIMIT 1";
			$results2=mysql_query($query2);
			if($row2=mysql_fetch_array($results2)) {
				$query3="UPDATE ".TABLE_METADATA." SET `value`='".b3_htmlize($value,true,"")."' WHERE `tabella`='".TABLE_MEMBERS."' AND `id`='".$row['idmember']."' AND `param`='".b3_htmlize($param,true,"")."' LIMIT 1";
				$results3=mysql_query($query3);
				if($results3) return mysql_insert_id();
				}
			else {
				$query3="INSERT INTO ".TABLE_METADATA." (`tabella`,`id`,`param`,`value`) VALUES('".TABLE_MEMBERS."','".$row['idmember']."','".b3_htmlize($param,true,"")."','".b3_htmlize($value,true,"")."')";
				$results3=mysql_query($query3);
				if($results3) return mysql_insert_id();
				}
			}
		return false;		
		}
	public function memberExists($username,$affiliation="") {
		if(!$this->inited) $this->init();
		$query="SELECT * FROM ".TABLE_MEMBERS." WHERE username='".b3_htmlize($username,true,"")."' AND affiliation='".b3_htmlize($affiliation,true,"")."' AND status<>'del' LIMIT 1";
		$results=mysql_query($query);
		if(!mysql_fetch_array($results)) return false;
		else return true;
		}

	public function getList($all=false,$conditions="") {
		if(!$this->inited) $this->init();
		$output=array();
		$query="SELECT * FROM ".TABLE_MEMBERS." WHERE idmember>0 ";
		if($all==false) $query.=" AND (expiration>NOW() OR expiration='0000-00-00 00:00:00') AND status='act' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$row['metadata']=$this->getMetadataByIdmember($row['idmember']);
			$row['imgs']=$this->imgallery->getList(TABLE_MEMBERS,$row['idmember']);
			$row['docs']=$this->docgallery->getList(TABLE_MEMBERS,$row['idmember']);
			$output[]=$row;
			}
		return $output;
		}
	public function getById($idmember,$all=false,$conditions="") {
		if(!$this->inited) $this->init();
		$query="SELECT * FROM ".TABLE_MEMBERS." WHERE idmember='".intval($idmember)."' ";
		if($all==false) $query.=" AND (expiration>NOW() OR expiration='0000-00-00 00:00:00') AND status='act' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		$query.=" LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			$row['metadata']=$this->getMetadataByIdmember($row['idmember']);
			$row['imgs']=$this->imgallery->getList(TABLE_MEMBERS,$row['idmember']);
			$row['docs']=$this->docgallery->getList(TABLE_MEMBERS,$row['idmember']);
			return $row;
			}
		else return false;
		}
	public function getByUsername($username,$all=false,$conditions="") {
		if(!$this->inited) $this->init();
		$query="SELECT * FROM ".TABLE_MEMBERS." WHERE username='".b3_htmlize($username,true,"")."' ";
		if($all==false) $query.=" AND (expiration>NOW() OR expiration='0000-00-00 00:00:00') AND status='act' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		$query.=" LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			$row['metadata']=$this->getMetadataByIdmember($row['idmember']);
			$row['imgs']=$this->imgallery->getList(TABLE_MEMBERS,$row['idmember']);
			$row['docs']=$this->docgallery->getList(TABLE_MEMBERS,$row['idmember']);
			return $row;
			}
		else return false;
		}
	public function getByEmail($email,$all=false,$conditions="") {
		if(!$this->inited) $this->init();
		$query="SELECT * FROM ".TABLE_MEMBERS." WHERE email='".mysql_real_escape_string($email)."' ";
		if($all==false) $query.=" AND (expiration>NOW() OR expiration='0000-00-00 00:00:00') AND status='act' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		$query.=" LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			$row['metadata']=$this->getMetadataByIdmember($row['idmember']);
			$row['imgs']=$this->imgallery->getList(TABLE_MEMBERS,$row['idmember']);
			$row['docs']=$this->docgallery->getList(TABLE_MEMBERS,$row['idmember']);
			return $row;
			}
		else return false;
		}
	public function getMetadataByIdmember($idmember) {
		if(!$this->inited) $this->init();
		$output=array();
		$query="SELECT * FROM ".TABLE_METADATA." WHERE tabella='".TABLE_MEMBERS."' AND id='".intval($idmember)."'";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$output[$row['param']]=$row['value'];
			}
		return $output;
		}
	public function subscribeToNewsletter($idmember,$idnl) {
		$query="UPDATE `".TABLE_MEMBERS."` SET `newsletter_lists`=CONCAT(`newsletter_lists`,'".intval($idnl).",') WHERE `idmember`=".intval($idmember)." AND `newsletter_lists` NOT LIKE '%,".intval($idnl).",%' LIMIT 1";
		return mysql_query($query);
		}
	public function unsubscribeFromNewsletter($idmember,$idnl) {
		$query="UPDATE `".TABLE_MEMBERS."` SET `newsletter_lists`=REPLACE(`newsletter_lists`,',".intval($idnl).",',',') WHERE `idmember`=".intval($idmember)." AND `newsletter_lists` LIKE '%,".intval($idnl).",%' LIMIT 1";
		return mysql_query($query);
		}

	public function isLogged() {
		if(!$this->inited) $this->init();
		if(isset($_SESSION['member']['idmember'])) {
			$m=$this->getById($_SESSION['member']['idmember']);
			if(isset($m['idmember'])&&$m['idmember']!=false) return true;
			else return false;
			}
		else return false;
		}
	public function logIn($username,$password) {
		if(!$this->inited) $this->init();
		$m=$this->getByUsername($username);
		if($m['password']==$password) {
			$query="UPDATE ".TABLE_MEMBERS." SET lastlogin=NOW() WHERE idmember=".$m['idmember']." LIMIT 1";
				$results=mysql_query($query);
					$_SESSION['member']=$m;
			return true;
			}
		else return false;
		}
	public function logOut() {
		if(!$this->inited) $this->init();
		if(isset($_SESSION['member'])) {
			unset($_SESSION['member']);
			return true;
			}
		else return false;
		}
	
	public function getVar($param) {
		if(isset($_SESSION['member'][$param])) return $_SESSION['member'][$param];
		else return false;
		}
	public function getMetadata($param) {
		if(isset($_SESSION['member']['metadata'][$param])) return $_SESSION['member']['metadata'][$param];
		else return false;
		}

	private function generatePassword($length=8,$charset=null) {
		$password="";
		if($charset==null) $charset="qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890";
		for($i=1;$i<=$length;$i++) {
			$password.=$charset{rand(0,strlen($charset)-1)};
			}
		return $password;
		}

	public function passwordReset($username) {
		$u=$this->getByUsername($username);
		if($u==false) return false;
		if($u['email']=="") return false;
		
		$token=md5($u['idmember'].$u['username'].$u['password'].$u['created'].$u['lastlogin']);
		$url=kGetSiteURL().kGetBaseDir().'admin/member_password_reset.php?t='.urlencode(base64_encode($username.'|'.$token));

		$subject=kTranslate("Password Reset");
		$message=kTranslate("Someone (probably you) asked to reset the password of your user.")."<br>";
		$message.=kTranslate("To proceed please").' <a href="'.$url.'">'.kTranslate("click here").'</a>';
		
		$from=ADMIN_MAIL;
		$to=$u['email'];

		kSendEmail($from,$to,$subject,$message);
		}
	}
