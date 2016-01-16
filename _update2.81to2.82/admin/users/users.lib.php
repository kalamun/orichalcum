<?php 
/* 2012 (c) Roberto Kalamun Pasini - GPLv3 */
/* user management: create new users, change password, change properties, delete... */

class kaUsers {
	
	public function __construct() {
		}

	/* create a new user */	
	/* $permissions is an array containing the PAGE_ID (more or less they are the same of admin directories)
	   of the modules that user can access */
	public function add($name,$email,$username,$password,$permissions=false) {
		//check if user exists
		$query="SELECT * FROM ".TABLE_USERS." WHERE `username`='".b3_htmlize($username,true,"")."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) return false;
		else {
			//if not, create it
			if($permissions==false||!is_array($permissions)) $permissions=array();
			$perm=",.,profilo,./.,"; // access to home, profile and exit are granted to all
			foreach($permissions as $p) {
				$perm.=ksql_real_escape_string($p).",";
				}
			$query="INSERT INTO `".TABLE_USERS."` (`name`,`email`,`username`,`password`,`featuredimage`,`permissions`,`created`,`lastlogin`) VALUES('".b3_htmlize($name,true,"")."','".b3_htmlize($email,true,"")."','".b3_htmlize($username,true,"")."','".md5($password)."',0,'".$perm."',NOW(),NOW())";
			return ksql_query($query)?true:false;
			}
		}

	/* update user */
	public function update($iduser,$name,$email,$username,$permissions=false,$featuredimage=0) {
		if($permissions!=false) {
			$perm=",";
			foreach($permissions as $p) {
				$perm.=$p.",";
				}
			}
		$query="UPDATE `".TABLE_USERS."` SET `name`='".b3_htmlize($name,true,"")."',`email`='".b3_htmlize($email,true,"")."',`username`='".b3_htmlize($username,true,"")."'";
		if(isset($perm)) $query.=",`permissions`='".ksql_real_escape_string($perm)."'";
		$query.=", `featuredimage`='".intval($featuredimage)."' WHERE `iduser`=".ksql_real_escape_string($iduser)." LIMIT 1";
		return ksql_query($query)?true:false;
		}

	/* change the password of specified user */
	public function password($iduser,$password) {
		$query="UPDATE `".TABLE_USERS."` SET `password`='".md5($password)."' WHERE `iduser`=".ksql_real_escape_string($iduser)." LIMIT 1";
		return ksql_query($query)?true:false;
		}

	/* delete user */
	public function del($iduser) {
		$log=true;
		$query="DELETE FROM `".TABLE_USERS."` WHERE iduser='".$iduser."' LIMIT 1";
		if(!ksql_query($query)) $log=false;
		else {
			$query="DELETE FROM `".TABLE_USERS_PROP."` WHERE iduser='".$iduser."'";
			if(!ksql_query($query)) $log=false;
			}
		return $log;
		}
	
	/* return an array with users' data */
	public function getUsersList() {
		$output=array();
		$query="SELECT * FROM `".TABLE_USERS."` ORDER BY `name`,`username`,`created`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$i=count($output);
			$output[$i]=$row;
			$output[$i]['created_leggibile']=preg_replace("/(\d{4}).(\d{2}).(\d{2}).(\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 h$4:$5",$row['created']);
			$output[$i]['lastlogin_leggibile']=preg_replace("/(\d{4}).(\d{2}).(\d{2}).(\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 h$4:$5",$row['lastlogin']);
			}
		return $output;
		}
	
	public function getUserFromId($iduser) {
		$query="SELECT * FROM `".TABLE_USERS."` WHERE `iduser`='".intval($iduser)."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$row['created_leggibile']=preg_replace("/(\d{4}).(\d{2}).(\d{2}).(\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 h$4:$5",$row['created']);
		$row['lastlogin_leggibile']=preg_replace("/(\d{4}).(\d{2}).(\d{2}).(\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 h$4:$5",$row['lastlogin']);
		$row=array_merge($row,$this->propGetList($row['iduser']));
		return $row;
		}
	
	public function getPermissionsList() {
		require_once('../inc/kalamun.lib.php');
		$kaAdminMenu=new kaAdminMenu();
		$menu=$kaAdminMenu->getFullStructure();
		$id=count($menu);
		$menu[$id]=array("title"=>"Altre opzioni","submenu"=>array());
		$menu[$id]['submenu'][]=array("title"=>"Aggiorna","id"=>"upgrade");
		return $menu;
		}
	
	public function canIUse($id=false,$iduser=false) {
		if($iduser==false) $iduser=$_SESSION['iduser'];
		if(!defined("PAGE_ID")) define("PAGE_ID",substr(dirname($_SERVER['PHP_SELF']),strrpos(dirname($_SERVER['PHP_SELF']),"/")+1));
		if($id==false) $id=PAGE_ID;
		if($iduser==$_SESSION['iduser']) {
			if(strpos($_SESSION['permissions'],",".$id.",")!==false) return true;
			else return false;
			}
		else {
			$query="SELECT `permissions` FROM `".TABLE_USERS."` WHERE `iduser`='".ksql_real_escape_string($iduser)."' AND `permissions` LIKE '%,".ksql_real_escape_string($id).",%' LIMIT 1";
			$results=ksql_query($query);
			if($row=ksql_fetch_array($results)) return true;
			else return false;
			}
		}
	
	
	public function propGetList($iduser) {
		$output=array();
		$query="SELECT * FROM `".TABLE_USERS_PROP."` WHERE `iduser`='".intval($iduser)."'";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			if(!isset($output[$row['family']])) $output[$row['family']]=array();
			$output[$row['family']][$row['param']]=$row['value'];
			}
		return $output;
		}
	public function propGet($iduser,$family,$param) {
		$query="SELECT * FROM ".TABLE_USERS_PROP." WHERE iduser='".ksql_real_escape_string($iduser)."' AND family='".ksql_real_escape_string($family)."' AND param='".ksql_real_escape_string($param)."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row;
		}
	public function propGetValue($iduser,$family,$param) {
		$query="SELECT * FROM ".TABLE_USERS_PROP." WHERE iduser='".ksql_real_escape_string($iduser)."' AND family='".ksql_real_escape_string($family)."' AND param='".ksql_real_escape_string($param)."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row['value'];
		}
	public function propGetById($iduprop) {
		$query="SELECT * FROM ".TABLE_USERS_PROP." WHERE iduprop='".ksql_real_escape_string($iduprop)."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row;
		}
	public function propExists($iduser,$family,$param) {
		$query="SELECT * FROM ".TABLE_USERS_PROP." WHERE iduser='".ksql_real_escape_string($iduser)."' AND family='".ksql_real_escape_string($family)."' AND param='".ksql_real_escape_string($param)."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) return $row;
		else return false;
		}
	public function propAdd($iduser,$family,$param,$value) {
		$query="INSERT INTO ".TABLE_USERS_PROP." (iduser,family,param,value) VALUES('".ksql_real_escape_string($iduser)."','".ksql_real_escape_string($family)."','".ksql_real_escape_string($param)."','".ksql_real_escape_string($value)."')";
		if(ksql_query($query)) {
			if($iduser==$_SESSION['iduser']) $_SESSION[$family][$param]=$value;
			return ksql_insert_id();
			}
		else return false;
		}
	public function propUpdate($iduser,$family,$param,$value) {
		$query="UPDATE ".TABLE_USERS_PROP." SET value='".ksql_real_escape_string($value)."' WHERE iduser='".ksql_real_escape_string($iduser)."' AND family='".ksql_real_escape_string($family)."' AND param='".ksql_real_escape_string($param)."' LIMIT 1";
		if(ksql_query($query)) {
			if($iduser==$_SESSION['iduser']) $_SESSION[$family][$param]=$value;
			return true;
			}
		else return false;
		}
	public function propReplace($iduser,$family,$param,$value) {
		$log=true;
		if(!$this->propExists($iduser,$family,$param)) {
			if(!$this->propAdd($iduser,$family,$param,$value)) $log=false;
			}
		else {
			if(!$this->propUpdate($iduser,$family,$param,$value)) $log=false;
			}
		return $log;
		}
	public function propDel($iduser,$family,$param) {
		$query="DELETE FROM ".TABLE_USERS_PROP." WHERE iduser='".ksql_real_escape_string($iduser)."' AND family='".ksql_real_escape_string($family)."' AND param='".ksql_real_escape_string($param)."'";
		if(ksql_query($query)) return true;
		else return false;
		}
	public function propDelById($idprop) {
		$query="DELETE FROM ".TABLE_USERS_PROP." WHERE idprop=".ksql_real_escape_string($idprop);
		if(ksql_query($query)) return true;
		else return false;
		}

	public function getLanguages($mode="") {
		$l=array();
		if($mode=='codes') {
			$l[]='it_IT';
			$l[]='en_US';
			$l[]='fr_FR';
			}
		elseif($mode=='labels') {
			$l[]='Italiano';
			$l[]='English';
			$l[]='Français';
			}
		else {
			$l['it_IT']='Italiano';
			$l['en_US']='English';
			$l['fr_FR']='Français';
			}
		return $l;
		}
	}

