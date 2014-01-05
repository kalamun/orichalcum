<?php

/* (c)2013 Kalamun.org GPLv3 */

/* DB CONNECTION TEST */
error_reporting(0);

if(isset($_POST['mysqlcheck'])) {
	/* CONNETTI AL DATABASE */
	$id=mysql_connect($_POST['host'],$_POST['user'],$_POST['pass']);
	if(!$id) {
		echo 'Error connecting to the server. Check host, user and password then retry';
		}
	elseif(!mysql_select_db($_POST['dbname'],$id)) {
		echo 'The database don\'t exists. Please create it and retry.';
		}
	else {
		echo 'Yes! The database is well configured!';
		}
	die();
	}
?><!DOCTYPE html>
<html lang="IT">

<head>
<title>Orichalcum 2.50 Installer</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<style type="text/css" media="screen">
	body {
		color:#000;
		font: normal 10pt "Open Sans",Arial,Helvetica,DejaVuSans,sans-serif;
		background-color:#F4F4F4;
		margin:0;
		padding:0;
		line-height:1.4em;
		}

	#header {
		text-align:center;
		color:#D9DDDC;
		background-color:#343231;
		border-bottom:2px solid #343231;
		margin:0; 
		padding:20px 0 10px 0;
		}

	#inside {
		position:relative;
		width:800px; 
		margin:0 auto;
		}

	#container {
		width:800px;
		padding:20px;
		margin:0 auto;
		}

	table {
		width:100%;
		}
	th {
		font-weight:normal;
		font-size:.9em;
		color:#666;
		width:200px;
		text-align:left;
		vertical-align:middle;
		}
	h2,h3 {
		font-weight:normal;
		text-transform:uppercase;
		margin-top:50px;
		}
	h1,h2 {color:#4177a3;}
	h3 {color:#2a4d69; margin:0 0 5px; padding:0;}
	p { margin: 10px 0; padding:0;}
	a {color:#4177a3; text-decoration:none;}
	a:hover {color:#6197e3; text-decoration:underline;}
	input,select,textarea,.RichContainer iframe {
		font-family:"Open Sans",Arial,Helvetica,DejaVuSans,sans-serif;
		border:1px solid #ccc;
		font-size:1.2em;
		padding:10px;
		background-color:#fff;
		box-shadow:0 1px 0 0 rgba(255,255,255,1);
		border-radius:5px;
		width:95%;
		}
	input:focus,select:focus,textarea:focus,.RichContainer iframe:focus {
		box-shadow:0 1px 4px -1px rgba(0,0,0,.1);
		border-bottom:1px solid #298FDA;
		}

	.submit input {
		width:auto;
		display:inline-block;
		border:0;
		border-radius:5px;
		padding:10px 30px;
		color:#eee;
		font-size:1.6em;
		cursor:pointer;
		margin:1px;
		background-color:#343231;
		background:-moz-linear-gradient(center top , #33A0E8, #2180CE) repeat scroll 0 0 transparent;
		background:-webkit-linear-gradient(center top , #33A0E8, #2180CE) repeat scroll 0 0 transparent;
		background:linear-gradient(center top , #33A0E8, #2180CE) repeat scroll 0 0 transparent;
		border:1px solid;
		border-color:#2270AB #18639A #0F568B;
		box-shadow:0 1px 1px rgba(0, 0, 0, 0.3), 0 1px 0 #83C5F1 inset;
		text-shadow:0 1px 2px #355782;
		}
	.submit input:hover {
		background-color:#444241;
		color:#fff;
		box-shadow: 0 0 3px 3px #fff;
		}
	.submit {
		background-color:#ffc;
		text-align:center;
		padding:10px;
		border-radius:5px;
		margin-top:20px;
		}
	
	#header h1 {
		color:#fff;
		}
	.help {
		background-color:#ffc;
		text-align:center;
		padding:10px;
		}
	#mysqlCheckResults {
		font-size:.8em;
		padding:0 8px;
		}
	#mysqlCheckResults.alert {
		color:#ca0000;
		}
	#mysqlCheckButton {
		text-align:center;
		font-size:.8em;
		padding:2px 8px;
		}
	</style>
</head>

<body>
<div id="header">
<div id="inside">

<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="150px" height="120px" viewBox="0 0 200 160" xml:space="preserve">
<path d="M152.596,6.201c-3.354-0.125-119.745,7.292-121.389,7.024c-1.644-0.267,3.57-6.38,7.609-6.566
	c4.039-0.186,35.797-2.187,38.879-1.685c3.083,0.502,7.814,2.538,11.303,1.841c3.489-0.698,9.606-3.288,9.841-4.727
	S88.936-0.579,76.137,0.29c-12.798,0.87-38.839,0.637-43.696,3.011c-4.856,2.373-15.299,10.798-16.738,10.564
	c-1.438-0.234-5.543,0.363-7.451,3.006s-4.885,2.791-5.597,4.573c-0.712,1.782,4.913,4.808,7.006,6.204
	c2.093,1.396,7.168,3.91,12.029,3.857c4.861-0.052,20.977-1.858,23.992-0.945c3.016,0.913,2.609,11.184,5.772,15.074
	c3.164,3.89,9.826,9.616,13.63,10.869c3.804,1.252,7.23,2.232,7.579,3.977c0.349,1.744-0.073,26.357-0.073,26.357
	c-1.4,0.827-15.076-0.691-20.349,1.893c-5.273,2.584-18.349,11.635-21.303,18.115c-2.954,6.481-6.224,29.154-4.289,34.109
	c1.935,4.958,5.668,5.354,7.957,4.249c2.289-1.103,3.618-2.784,4.593-7.479c0.975-4.692,1.655-24.413,4.484-27.538
	c2.829-3.125,5.415-7.346,8.703-6.81c3.289,0.535,3.694,8.406,3.871,18.982c0.176,10.577-1.617,25.475,5.638,24.968
	c7.255-0.506,6.983-9.2,7.141-12.76c0.157-3.561-1.328-25.531,1.774-29.034c3.102-3.503,6.901-3.518,9.502-3.938
	c2.599-0.42,8.488-0.306,8.439,5.172c-0.047,5.478,0.879,24.402,1.008,31.386c0.128,6.981,2.575,11.388,7.049,8.53
	s7.992-14.098,8.006-19.37c0.015-5.271-1.915-18.032-1.925-20.564c-0.01-2.533,1.549-6.921,4.88-4.059
	c3.331,2.863,4.151,15.96,5.248,23.476c1.098,7.514,1.856,18.397,6.444,18.722c4.589,0.325,7.677-6.977,6.071-24.326
	c-1.605-17.348-2.016-17.416-3.454-26.721s-4.959-26.544-3.048-27.92s12.962-3.163,13.842-5.973c0.879-2.811-1.502-7.628-5.922-8.98
	c-4.421-1.353-9.348-0.89-10.481-1.707c-1.133-0.817-3.698-9.674-7.158-10.448c-3.46-0.774-5.568,3.102-5.554,6.901
	c0.014,3.799,2.451,5.673,1.701,6.395c-0.751,0.722-5.512,0.158-7.671-0.827c-2.161-0.985-7.198-2.438-6.108-3.948
	c1.09-1.51,5.121-6.325,2.771-11.499c-4.198-9.243-16.724-7.317-19.632,2.959c-1.38,4.874,4.501,8.004,4.095,9.204
	c-0.406,1.2-7.107,3.483-11.155,1.137s-9.816-7.083-9.873-13.21c-0.057-6.127-0.673-6.228,0.832-6.404
	c1.506-0.177,93.463-5.244,96.713-5.769c3.25-0.525,17.088-7.766,16.839-10.126C172.023,11.263,155.951,6.325,152.596,6.201z
	 M95.061,62.861c1.639-0.999,10.137,1.229,11.303,1.841c1.166,0.612,0.664,3.694,0.784,6.844c0.12,3.148-3.136,14.08-5.153,14.806
	c-2.017,0.727-4.928,0.463-7.13-2.849C92.661,80.19,93.638,63.728,95.061,62.861z" style="fill:#D9DDDC;" />
</svg>

	<h1>Orichalcum 2.50 installation</h1>
	<div style="clear:both;"></div>
	</div>
</div>

<div class="help">Do you need some help? <a href="http://help.orichalcum.it">Click here</a> for assistance.</div>

<div id="container">
<?php

function kCheckModRewrite() {
	if(function_exists("apache_get_modules")) {
		foreach(apache_get_modules() as $mod) {
			if($mod=="mod_rewrite") return true;
			}
		return false;
		}
	return true;
	}

/* check PHP */
if(phpversion()<5.3) {
	?><h2>Error: your PHP version is too old! You need at leas PHP 5.3</h2><?php
	}
/* check GD library */
elseif(!function_exists("gd_info")) {
	?><h2>Error: your PHP was compiled without GD libraries.</h2><?php
	}
/* check write permission */
elseif(!file_put_contents("delme.txt","This is only a write test. delete me please.")) {
	?><h2>Error: You don't have write permission on this directory!</h2><?php
	}
/* check short tags */
elseif(ini_get("short_open_tag")==0) {
	?><h2>Error: You need to turn on <em>short_open_tag</em> on your <strong>php.ini</strong>!</h2>
	<p>If you don't know what it is, start here: <a href="http://php.net/manual/en/ini.core.php">php.net/manual/en/ini.core.php</a>.</h2><?php
	}
/* check mod_rewrite support */
elseif(kCheckModRewrite()==false) {
	?><h2>Error: your webserver don't support <em>mod_rewrite</em>.</h2><?php
	}
/* FILE CHECK */
elseif(!file_exists('orichalcum.tar.gz')) {
	?><h2>The file <em>orichalcum.tar.gz</em> is missing.</h2><?php
	}

else {
	if(file_exists('delme.txt')) unlink('delme.txt');

	if(!isset($_POST['install'])) {
		?>


		<form action="" method="post">

		<script type="text/javascript">
			/* AJAX */
			kAjax=function() {
				var onSuccess=function(txt) {};;
				var onFail=function(txt) {};;
				var ajaxObj=null;
				var method="get";
				var uri="";
				var vars="";

				this.send=function(vmethod,vuri,vvars) {
					method=vmethod.toLowerCase();
					uri=vuri;
					vars=vvars;
					ajaxSend();
					}
				this.onSuccess=function(func) { onSuccess=func }
				this.onFail=function(func) { onFail=func; }

				function createXMLHttpRequest() {
					var XHR=null;
					if(typeof(XMLHttpRequest)==="function"||typeof(XMLHttpRequest)==="object") XHR=new XMLHttpRequest(); //browser standard
					else if(window.ActiveXObject&&!kBrowser.IE4) { //ie4, BLOCCATO
						if(kBrowser.IE5) XHR=new ActiveXObject("Microsoft.XMLHTTP"); //ie5.x: metodo diverso
						else XHR=new ActiveXObject("Msxml2.XMLHTTP"); //ie6: metodo diverso
						}
					return XHR;
					}
				function onStateChange() {
					if(ajaxObj.readyState===4) {
						if(ajaxObj.status==200) onSuccess(ajaxObj.responseText,ajaxObj.responseXML);
						else onFail(ajaxObj.status);
						}
					}
				function ajaxSend() {
					ajaxObj=createXMLHttpRequest();
					if(method=="get") {
						uri+="?"+vars;
						ajaxObj.open(method,uri,true);
						ajaxObj.onreadystatechange=onStateChange;
						ajaxObj.send(null);
						}
					else if(method=="post") {
						ajaxObj.open(method,uri,true);
						ajaxObj.setRequestHeader("content-type","application/x-www-form-urlencoded");
						ajaxObj.setRequestHeader("connection","close");
						ajaxObj.onreadystatechange=onStateChange;
						ajaxObj.send(vars);
						}
					delete ajaxObj;
					}	
				}
			
			function kCheckMysql() {
				host=document.getElementById('mysqlHost').value;
				if(host=='') host='localhost';
				dbname=document.getElementById('mysqlDbname').value;
				user=document.getElementById('mysqlUser').value;
				pass=document.getElementById('mysqlPass').value;
				var ajax=new kAjax;
				document.getElementById('mysqlCheckResults').innerHTML='Wait a second please...';
				ajax.onSuccess(function(html,xmlDoc) {
					document.getElementById('mysqlCheckResults').className=(html.substr(0,5)=="Error")?'alert':'';
					document.getElementById('mysqlCheckResults').innerHTML=html;
					});
				ajax.onFail(function() {
					document.getElementById('mysqlCheckResults').innerHTML='Impossible to perform this check now';
					});
				ajax.send("post",'install.php','&mysqlcheck=true&host='+escape(host)+'&dbname='+escape(dbname)+'&user='+escape(user)+'&pass='+escape(pass));
				}
			</script>


		<h2>Your website</h2>

		<table cellspacing="1" cellpadding="5">

		<tr><th>What's the name of your site?</th>
			<td><input type="text" name="site_name" value="" placeholder="Write the name here" tabindex="1"/></td>
			</tr>

		<tr><th>What's the address of your website</th>
			<td><input type="text" name="site_url" value="" placeholder="http://" tabindex="2"/></td>
			</tr>

		<tr><th>Your language</th>
		<td><select name="lang" tabindex="3">
				<option value="IT">Italiano</option>
				<option value="EN">English</option>
				<option value="FR">Français</option>
				</select></td>
			</tr>
		</table>
		
		<h2>Something about you</h2>

		<table cellspacing="1" cellpadding="5">
		<tr><th>Your full name</th>
			<td><input type="text" name="admin_name" value="" placeholder="Your full name" tabindex="4"/></td>
			</tr>

		<tr><th>Your e-mail address</th>
			<td><input type="text" name="admin_mail" value="" placeholder="your@email.tld" tabindex="5"/></td>
			</tr>

		<tr><th>Username</th>
			<td><input type="text" name="user_username" value="" placeholder="Choose a username" tabindex="6"/></td>
			</tr>

		<tr><th>Password</th>
			<td><input type="password" name="user_password" value="" placeholder="Something other than 123456" tabindex="7"/></td>
			</tr>

		</table>

		
		<h2>MySQL Database</h2>

		<table cellspacing="1" cellpadding="5">
		<tr><th>Host</th>
			<td><input type="text" id="mysqlHost" name="db_host" value="" placeholder="localhost" tabindex="8"/></td>
			</tr>

		<tr><th>Database name</th>
			<td><input type="text" id="mysqlDbname" name="db_name" value="" tabindex="9"/></td>
			</tr>

		<tr><th>Database username</th>
			<td><input type="text" id="mysqlUser" name="db_username" value="" tabindex="10"/></td>
			</tr>

		<tr><th>Database password</th>
			<td><input type="password" id="mysqlPass" name="db_password" value="" tabindex="11"/></td>
			</tr>

		<tr><td></td><td>
			<a href="javascript:kCheckMysql();" id="mysqlCheckButton">Check connection to the database</a>
			<div id="mysqlCheckResults"></div>
			<div style="clear:both;"></div>
			</td></tr>
		</table>
		
		<div class="submit">
			<input type="submit" value="Install" name="install" tabindex="12"/>
		</div>
		<br />
		<div style="text-align:center;">
			<small><strong>Orichalcum</strong> is proudly released under GPL v.3 license by Roberto <a href="http://www.kalamun.org" accesskey="a">Kalamun</a> Pasini</small>
			</div>
		<?php
		}

	else {
		function kGzDecode($data) {
			$flags=ord(substr($data,3,1));
			$headerlen=10;
			$extralen=0;
			$filenamelen=0;
			if($flags&4) {
				$extralen=unpack('v',substr($data,10,2));
				$extralen=$extralen[1];
				$headerlen+=2+$extralen;
				}
			if($flags&8) $headerlen=strpos($data,chr(0),$headerlen)+1;
			if($flags&16) $headerlen=strpos($data,chr(0),$headerlen)+1;
			if($flags&2) $headerlen+=2;
			$unpacked=gzinflate(substr($data,$headerlen));
			if($unpacked===FALSE) $unpacked=$data;
			return $unpacked;
			}
		function kTarExtract($file,$dest,$charset="ISO") {
			$dest=trim($dest,'/').'/';
			if(!isset($dest)) return false;

			$tar=array();
			$tar['size']=filesize($file);
			$tar['data']=file_get_contents($file);

			$offset=0;
			for($i=0;$offset<$tar['size'];$i++) {
				$file=array();
				$file['name']=trim(substr($tar['data'],$offset,100));
				if(substr($file['name'],-1)=="/") { //dir
					$file['size']=0;
					if(!file_exists($dest.$file['name'])) mkdir($dest.$file['name']);
					}
				else { //file
					$file['size']=OctDec(trim(substr($tar['data'],($offset+124),12)));
					$file['data']=substr($tar['data'],($offset+512),$file['size']);
					if($charset=="UTF-8") {
						//UTF8 dei file di testo
						$ext=substr($file['name'],-3);
						if($ext=="txt"|$ext=="php"|$ext=="html"|$ext=="xml"|$ext=="js") {
							$file['data']=utf8_decode(utf8_encode($file['data']));
							}
						}
					//Unix conversion dei file di testo
					$ext=substr($file['name'],-3);
					if($ext=="txt"|$ext=="php"|$ext=="html"|$ext=="xml") {
						$file['data']=str_replace("\r","",$file['data']);
						}
					if(!file_put_contents($dest.$file['name'],$file['data'])) return false;
					}
				$offset+=512+$file['size'];
				while(substr($tar['data'],$offset,1)==chr(0)) {
					$offset++;
					}
				}
			return true;
			}
		function kTgzExtract($file,$dest,$charset="ISO") {
			$tmpname='tmp'.date("YmdHis").'.tar';
			copy($file,$dest.'/'.$tmpname.'.gz');
			file_put_contents($dest.'/'.$tmpname,kGzDecode(file_get_contents($dest.'/'.$tmpname.'.gz')));
			unlink($dest.'/'.$tmpname.'.gz');
			$results=kTarExtract($dest.'/'.$tmpname,$dest,$charset);
			unlink($dest.'/'.$tmpname);
			if(!$results) return false;
			else return true;
			}
		function kRemoveDir($dir) {
			if(is_dir($dir)&&!is_link($dir)) {
				foreach(glob($dir.'/*') as $sf) {
					if(!rm_recurse($sf)) return false;
					}
				return rmdir($dir);
				}
			else {
				return unlink($dir);
				}
			}
		
		//languages
		$kLanguages=array('IT'=>array('Italiano','it_IT'),'EN'=>array('English','en_US'),'FR'=>array('Français','fr_FR'));
		$lang=$kLanguages[$_POST['lang']];
		
		//estraggo il tar.gz
		kTgzExtract("orichalcum.tar.gz",".");
		if(!file_exists('admin/inc/config.inc.php')) die('Something went wrong during the extraction of orichalcum.tar.gz <a href="install.php">Please retry</a>.');
		
		//aggiorno il config.inc.php
		$cnfg=file_get_contents('admin/inc/config.inc.php');
		if(substr($_POST['site_url'],0,7)!="http://"&&substr($_POST['site_url'],0,8)!="https://") $_POST['site_url']="http://".trim($_POST['site_url']);
		if($_POST['db_host']=="") $_POST['db_host']='localhost';
		$_POST['admin_name']=str_replace('"','\"',$_POST['admin_name']);

		$cnfg=str_replace('{VAR_ADMIN_NAME}',$_POST['site_name'],$cnfg);
		$cnfg=str_replace('{VAR_ADMIN_MAIL}',$_POST['admin_mail'],$cnfg);
		$cnfg=str_replace('{VAR_WEBMASTER_MAIL}',$_POST['admin_mail'],$cnfg);
		$cnfg=str_replace('{VAR_SITE_URL}',$_POST['site_url'],$cnfg);
		$cnfg=str_replace('{VAR_BASEDIR}',str_replace("//","/",dirname($_SERVER["REQUEST_URI"]).'/'),$cnfg);
		$cnfg=str_replace('{VAR_DB_HOST}',$_POST['db_host'],$cnfg);
		$cnfg=str_replace('{VAR_DB_NAME}',$_POST['db_name'],$cnfg);
		$cnfg=str_replace('{VAR_DB_USER}',$_POST['db_username'],$cnfg);
		$cnfg=str_replace('{VAR_DB_PASSWORD}',$_POST['db_password'],$cnfg);
		$cnfg=str_replace('{VAR_DEFAULT_LANG}',$_POST['lang'],$cnfg);
		if(!file_put_contents('admin/inc/config.inc.php',$cnfg)) die("Problemi durante l'aggiornamento del config.inc.php");
		
		//aggiorno htaccess
		$cnfg=file_get_contents('.htaccess');
		$cnfg=str_replace('{VAR_BASEDIR}',str_replace("//","/",dirname($_SERVER["REQUEST_URI"]).'/'),$cnfg);
		if(!file_put_contents('.htaccess',$cnfg)) die("Problemi durante l'aggiornamento di .htaccess");

		//connetto al db
		include('admin/inc/connect.inc.php');
		
		//importo sql
		$err="";
		foreach(explode(";\n",file_get_contents('orichalcum.sql')) as $query) {
			$query=trim($query);
			$query=str_replace('{VAR_USER_NAME}',mysql_real_escape_string($_POST['admin_name']),$query);
			$query=str_replace('{VAR_USER_USERNAME}',mysql_real_escape_string($_POST['user_username']),$query);
			$query=str_replace('{VAR_USER_EMAIL}',mysql_real_escape_string($_POST['admin_email']),$query);
			$query=str_replace('{VAR_USER_PASSWORD}',md5($_POST['user_password']),$query);
			$query=str_replace('{VAR_SITE_NAME}',mysql_real_escape_string($_POST['site_name']),$query);
			$query=str_replace('{VAR_DEFAULT_LANG}',mysql_real_escape_string($_POST['lang']),$query);
			$query=str_replace('{VAR_DEFAULT_LANGUAGE}',mysql_real_escape_string($lang[0]),$query);
			$query=str_replace('{VAR_DEFAULT_LANG_CODE}',mysql_real_escape_string($lang[1]),$query);
			if($query!="") {
				if(!mysql_query($query)) {
					$err.='<div style="font-size:x-small;">'.$query.'</div><hr />';
					}
				}
			}
		if($err!="") {
			echo "<h2>Some errors occurred while populating database</h2>";
			echo "<p>The following queries fail:</p>";
			echo $err;
			die();
			}
		
		//fine installazione
		echo "<h2>Well done! Your website is ready to rock!</h2><br />Now you can start using Orichalcum:<br />";
		echo '<a href="index.php">go to your brand new website</a><br />';
		echo '<a href="admin/">go to the control panel</a><br />';

		//cancello il tar.gz e l'sql
		unlink('orichalcum.tar.gz');
		unlink('orichalcum.sql');
		unlink('install.php');
		}
	}
	?>
</div>
</body>
</html>
