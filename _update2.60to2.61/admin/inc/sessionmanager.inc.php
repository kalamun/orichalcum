<?
/* (c) Kalamun.org - GNU/GPL 3 */
/* this file manage the user login/logout and permissions on pages */

if(session_id()=="") session_start();

//if user are autenthicating from log-in form
if(isset($_POST['login'])) {
	$admin_username=b3_htmlize($_POST['orichalcum_admin_username'],false,"");
	$admin_password=md5($_POST['orichalcum_admin_password']);
	$entermode='login';
	}
//if cookies are valid, try to authenticate with them
elseif(isset($_COOKIE['admin_username'])&&isset($_COOKIE['admin_password'])) {
	$admin_username=$_COOKIE['admin_username'];
	$admin_password=$_COOKIE['admin_password'];
	$entermode='cookie';
	}
//else try to get user/pass from session
else {
	if(!isset($_SESSION['username'])) $_SESSION['username']="";
	if(!isset($_SESSION['password'])) $_SESSION['password']="";
	$admin_username=$_SESSION['username'];
	$admin_password=$_SESSION['password'];
	}

$query="SELECT * FROM `".TABLE_USERS."` WHERE `username`='".mysql_real_escape_string($admin_username)."' AND `password`='".mysql_real_escape_string($admin_password)."' LIMIT 1";
$results=mysql_query($query);
if($row=mysql_fetch_array($results)) {
	/*login*/
	$_SESSION['iduser']=$row['iduser'];
	$_SESSION['name']=$row['name'];
	$_SESSION['username']=$row['username'];
	$_SESSION['password']=$row['password'];
	$_SESSION['email']=$row['email'];
	$_SESSION['permissions']=$row['permissions'];
	if(!isset($_SESSION['ll'])||$_SESSION['ll']=='') $_SESSION['ll']=DEFAULT_LANG;

	if(!isset($_SESSION['loggedin'])) {
		$q="SELECT * FROM `".TABLE_USERS_PROP."` WHERE `iduser`='".intval($row['iduser'])."'";
		$rs=mysql_query($q);
		while($r=mysql_fetch_array($rs)) {
			if(!isset($_SESSION[$r['family']])) $_SESSION[$r['family']]=array();
			$_SESSION[$r['family']][$r['param']]=$r['value'];
			}

		if(isset($entermode)) { //aggiorno la data di ultimo ingresso
			$query="UPDATE `".TABLE_USERS."` SET `lastlogin`=NOW() WHERE `iduser`=".mysql_real_escape_string($row['iduser']);
			mysql_query($query);
			}
		if(isset($_POST['orichalcum_admin_remember'])) { //setto i cookie se richiesto con scadenza tra 10 anni
			setcookie("admin_username",$admin_username,time()+315360000,BASEDIR);
			setcookie("admin_password",$admin_password,time()+315360000,BASEDIR);
			}
		elseif(isset($entermode)&&$entermode=='login') { //altrimenti se fanno il login senza volere i cookie, li rimuovo
			if(isset($_COOKIE['admin_username'])) setcookie("admin_username",$admin_username,time()-3600,BASEDIR);
			if(isset($_COOKIE['admin_password'])) setcookie("admin_password",$admin_password,time()-3600,BASEDIR);
			}

		$_SESSION['loggedin']=true;
		$GLOBALS['kaLog']->add("GEN","Log-in"); //report log-in in the events archive
		}
	}
else {
	$_GET['logout']=true;
	sleep(5);
	}

// log out
if(isset($_GET['logout'])) {
	if(isset($_SESSION['iduser'])) $GLOBALS['kaLog']->add("GEN","Log-out");
	session_unset();
	if(isset($_COOKIE['admin_username'])) setcookie("admin_username",$admin_username,time()-3600,BASEDIR);
	if(isset($_COOKIE['admin_password'])) setcookie("admin_password",$admin_password,time()-3600,BASEDIR);
	}

if(!isset($_SESSION['ll'])||$_SESSION['ll']=='') $_SESSION['ll']=DEFAULT_LANG;
if($_SESSION['ll']=='') $_SESSION['ll']='EN';
?>