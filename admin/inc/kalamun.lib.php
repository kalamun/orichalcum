<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

function kaInit() {
	$reldir="";
	$dirname=dirname($_SERVER['PHP_SELF']).'/';
	$chars=count_chars(substr($dirname,strlen(BASEDIR)),1);
	for($i=1;$i<=$chars[47];$i++) { $reldir.='../'; }
	define("BASERELDIR",$reldir);
	if(preg_match("/.*\/admin.*/",dirname($_SERVER['PHP_SELF']))) {
		define("ADMINDIR",BASEDIR."admin/");
		$reldir="";
		$dirname=dirname($_SERVER['PHP_SELF']).'/';
		$chars=count_chars(substr($dirname,strlen(ADMINDIR)),1);
		if(isset($chars[47])) for($i=1;$i<=$chars[47];$i++) { $reldir.='../'; }
		define("ADMINRELDIR",$reldir);
		}
	}
kaInit();

function kaGenericErrorHandler($errorNumber,$errorString,$errorFile="",$errorLine="",$errorContext="") {
	switch ($errorNumber) {
	case E_ERROR||E_CORE_ERROR||E_COMPILE_ERROR||E_PARSE||E_USER_ERROR:
	  	?>
	  	<div id="MsgAlert" style="display:block;">
	  	<strong>FATAL ERROR:</strong> [<?= $errorNumber; ?>] <?= $errorString; ?><br />
		<small>File <?= $errorFile; ?> - Line <?= $errorLine; ?></small>
		</div>
		<?php 
		if(defined("ADMINRELDIR")&&isset($__db)) include(ADMINRELDIR.'inc/foot.inc.php');
		die();
		break;
	case E_WARNING||E_CORE_WARNING||E_COMPILE_WARNING||E_USER_WARNING:
		echo "<strong>ERROR</strong> [$errorNumber] $errorString<br>\n";
		break;
	case E_NOTICE||E_STRICT||E_USER_NOTICE:
		echo "<strong>WARNING</strong> [$errorNumber] $errorString<br>\n";
		break;
	default:
		echo "Generic Error: [$errorNumber] $errorString<br>\n";
		break;
		}
    }
set_error_handler("kaGenericErrorHandler");


/* translate strings based on files inside "locale" dirs */
class kaAdminTranslate
{
	protected $dictionary;
	
	public function __construct()
	{
		$this->dictionary=array();
		
		// always load admin/inc/locale/
		$this->import('inc');
		
		// load dictionary from current module
		if(defined("PAGE_ID")) $this->import(PAGE_ID);
		
		// load translations from current template
		$dir=rtrim($_SERVER['DOCUMENT_ROOT'],"/") . BASEDIR . DIR_TEMPLATE . $GLOBALS['kaImpostazioni']->getVar('template_default',1) . '/admin';
		if(file_exists($dir)) $this->import($dir);
	}

	// import locale file from a given directory
	public function import($dir=null)
	{
		if($dir==null&&defined(PAGE_ID)) $dir=PAGE_ID;
		if($dir==null||$dir=="") $dir="inc";
		$dir=rtrim($dir,"/ ");

		if(empty($_SESSION['ui']['lang']))
		{
			$query="SELECT * FROM ".TABLE_LINGUE." WHERE ll='".DEFAULT_LANG."' LIMIT 1";
			$results=ksql_query($query);
			$row=ksql_fetch_array($results);
			$_SESSION['ui']['lang']=$row['code'];
		}

		$file="";
		if(substr($dir, 0, strlen($_SERVER['DOCUMENT_ROOT']))!=$_SERVER['DOCUMENT_ROOT']) $file.=ADMINRELDIR;
		$file.=$dir.'/locale/'.$_SESSION['ui']['lang'].'.txt';
		if(file_exists($file))
		{
			$diz=file($file);
			if($diz)
			{
				foreach($diz as $line)
				{
					if(trim($line)!=""&&substr($line,0,2)!="//")
					{
						$line=trim($line);
						$line=preg_replace("/(\t+)/","\t",$line);
						$elm=explode("\t",$line);
						if(isset($elm[1])) $this->dictionary[trim($elm[0])]=trim($elm[1]);
					}
				}
			}
		}
	}

	// translate a string
	public function translate($param)
	{
		$args=func_get_args();
		array_shift($args);
		$param=trim($param,"{}");

		if(isset($this->dictionary[$param])) return vsprintf($this->dictionary[$param], $args);
		if(strpos($param,":")!==false) return vsprintf( substr($param,strpos($param,":")+1) ,$args);
	}
}

class kaAdminMenu {
	protected $menu,$fullmenu,$sel,$kaTranslate;
	
	function __construct() {
		$this->menu=array();
		$this->sel=array();
		global $kaTranslate;
		if(isset($kaTranslate)) $this->kTranslate=$kaTranslate;
		else $this->kTranslate=new kaAdminTranslate();
		$menu=array();
		$fullmenu=array();
		
		/* get and parse main menu */
		$xml=file_get_contents(ADMINRELDIR."inc/menu.xml");

		//parsing
		$xml=preg_replace('/<?xml[^>]*>/','',$xml);
		$xml=preg_replace('/<[^ ]*>/','',$xml);
		$xml=preg_replace('/<\/[^>]*>/','',$xml);
		$xml=explode('<mainNode',trim($xml));
		for($i=0;isset($xml[$i]);$i++) {
			if($xml[$i]!="") {
				$xml[$i]=explode('<subNode',$xml[$i]);
				for($j=0;isset($xml[$i][$j]);$j++) {
					$tmp=$xml[$i][$j];
					$xml[$i][$j]=array();
					
					// get the title
					preg_match('/title="(.*?)"/', $tmp, $match);
					$xml[$i][$j]['title'] = !empty($match[1]) ? $this->kTranslate->translate($match[1]) : '';

					// get the id (the directoty of the correspondant module)
					preg_match('/id="(.*?)"/', $tmp, $match);
					$xml[$i][$j]['id'] = !empty($match[1]) ? $match[1] : '';
					
					// get the url (optional)
					preg_match('/url="(.*?)"/', $tmp, $match);
					$xml[$i][$j]['url'] = !empty($match[1]) ? $match[1] : '';
					
					// get the icon
					preg_match('/icon="(.*?)"/', $tmp, $match);
					$xml[$i][$j]['icon'] = !empty($match[1]) ? $match[1] : '';
					
		
					// populate an array with only active elements for the current user
					if($j==0) $menu[] = array_merge($xml[$i][$j], array("submenu"=>array()));
					elseif(strpos($_SESSION['permissions'], ",".$xml[$i][$j]['id'].",") !== false) $menu[(count($menu)-1)]['submenu'][] = $xml[$i][$j];
					
					// populate an array with all the elements, indipendently of permissions
					if($j==0) $fullmenu[] = array_merge($xml[$i][$j], array("submenu"=>array()));
					else $fullmenu[(count($fullmenu)-1)]['submenu'][] = $xml[$i][$j];
				}
				if(count($menu[(count($menu)-1)]['submenu'])==0) unset($menu[(count($menu)-1)]);
			}
		}

		/* get and parse add-ons menu (if exists) */
		if(file_exists(ADMINRELDIR."addons/menu.xml"))
		{
			$xml=file_get_contents(ADMINRELDIR."addons/menu.xml");

			//parsing
			$xml=preg_replace('/<?xml[^>]*>/','',$xml);
			$xml=preg_replace('/<[^ ]*>/','',$xml);
			$xml=preg_replace('/<\/[^>]*>/','',$xml);
			$xml=explode('<mainNode',trim($xml));
			for($i=0;isset($xml[$i]);$i++)
			{
				if($xml[$i]!="")
				{
					$xml[$i]=explode('<subNode',$xml[$i]);
					
					for($j=0;isset($xml[$i][$j]);$j++)
					{
						$tmp=$xml[$i][$j];
						$xml[$i][$j]=array();
						// get the title
						preg_match('/title="(.*?)"/', $tmp, $match);
						$xml[$i][$j]['title'] = !empty($match[1]) ? $match[1] : '';

						// get the id (the directoty of the correspondant module)
						preg_match('/id="(.*?)"/', $tmp, $match);
						if($match[1] == '{SITE_URL}') $match[1] = SITE_URL;
						$xml[$i][$j]['id'] = !empty($match[1]) ? $match[1] : '';
						
						// get the url (optional)
						preg_match('/url="(.*?)"/', $tmp, $match);
						$xml[$i][$j]['url'] = !empty($match[1]) ? $match[1] : '';
						
						// get the icon
						preg_match('/icon="(.*?)"/', $tmp, $match);
						$xml[$i][$j]['icon'] = !empty($match[1]) ? $match[1] : '';
			
						// populate an array with only active elements for the current user
						if($j==0) $menu[] = array_merge($xml[$i][$j], array("submenu"=>array()));
						elseif(strpos($_SESSION['permissions'], ",".$xml[$i][$j]['id'].",") !== false) $menu[(count($menu)-1)]['submenu'][] = $xml[$i][$j];
						
						// populate an array with all the elements, indipendently of permissions
						if($j==0) $fullmenu[] = array_merge($xml[$i][$j], array("submenu"=>array()));
						else $fullmenu[(count($fullmenu)-1)]['submenu'][] = $xml[$i][$j];
					}
					if(count($menu[(count($menu)-1)]['submenu'])==0) unset($menu[(count($menu)-1)]);
				}
			}
		}
		$this->menu=$menu;
		$this->fullmenu=$fullmenu;
	}
	
	function getStructure()
	{
		return $this->menu;
	}
	
	function getFullStructure()
	{
		return $this->fullmenu;
	}

	function getLanguages()
	{
		$output=array();
		$query="SELECT * FROM `".TABLE_LINGUE."` ORDER BY `ordine`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			$output[]=$row;
		}
		return $output;
	}

	function get()
	{
		$output="";
		if(!defined("PAGE_ID")) define("PAGE_ID",substr(dirname($_SERVER['PHP_SELF']),strrpos(dirname($_SERVER['PHP_SELF']),"/")+1));

		$output.='<div id="menu">';

		/****** LANGUAGES *******/
		$languages=$this->getLanguages();
		if(!defined("TRANSLATIONS")) define("TRANSLATIONS",true);

		$output.='<div class="languages">';
		$output.=$this->kTranslate->translate('Menu:Languages').' <ul>';
		
		//maintain GET variables
		$append_var=$_SERVER['QUERY_STRING'];
		foreach($_GET as $kaey => $value)
		{
			if($kaey=="chg_lang"||$kaey=="delete"||$kaey=="confirm")
			{
				$append_var=preg_replace("/".$kaey."=?[^&]*&?/","",$append_var);
			}
		}

		foreach($languages as $row)
		{
			$output.='<a href="'.ADMINDIR.PAGE_ID.'/'.basename($_SERVER['PHP_SELF']).'?chg_lang='.$row['ll'].'&'.$append_var.'" class="lingua';
			if($row['ll']==$_SESSION['ll']) { $output.=' sel'; }
			$output.='">';
			if(file_exists(BASERELDIR.'img/lang/'.strtolower($row['ll']).'.gif')) $output.='<img src="'.BASERELDIR.'img/lang/'.strtolower($row['ll']).'.gif'.'" width="16" height="11" title="'.$row['lingua'].'" /> ';
			$output.=$row['ll'].'</a>';
		}

		$output.='</ul></div>';
		if(!defined("TRANSLATIONS")) define("TRANSLATIONS",false);
		
		// get selected language
		$this->sel=array('parent'=>'','id'=>'','url'=>'','title'=>'','perm'=>'');
		foreach($this->menu as $ka=>$v)
		{
			for($i=0;isset($v['submenu'][$i]['id']);$i++)
			{
				if(PAGE_ID==$v['submenu'][$i]['id'])
				{
					$this->sel=$v['submenu'][$i];
					$this->sel['parent']=$ka;
					break(2);
				}
			}
		}

		//create menu
		$output.='<ul>';
		foreach($this->menu as $ka=>$v)
		{
			$output.='<li><a '.($ka==$this->sel['parent']?' class="sel"':'').'>'.$v['title'].'</a>';
			$output.='<ul>';

			for($i=0;isset($v['submenu'][$i]['title']);$i++)
			{
				if(empty($v['submenu'][$i]['url'])) $v['url'][$i] = "";
				
				$url = $v['submenu'][$i]['id']=='{SITE_URL}' ? SITE_URL.BASEDIR : ADMINDIR.$v['submenu'][$i]['id'].'/'.$v['submenu'][$i]['url'];
				$output .= '<li><a href="'.$url.'"';
				if(PAGE_ID==$v['submenu'][$i]['id']) $output .= ' class="sel"';
				$output.= '>';
				if(!empty($v['submenu'][$i]['icon'])) $output .= '<span class="icon">'.$v['submenu'][$i]['icon'].'</span>';
				$output.= $v['submenu'][$i]['title'].'</a></li>';
			}
			$output.='</ul>';
			$output.='</li>';
		}
		$output.='</ul>';
		$output.='</div>';

		return $output;
	}
	
	function getSelected($ref=false) {
		if(!$ref) return $this->sel;
		else return $this->sel[$ref];
		}

	function getSelectedSubmenu() {
		$output='<ul>';
		foreach($this->menu[$this->sel['parent']]['submenu'] as $k=>$v) {
			if(!isset($v['url'])) $v['url']="";
			$output.='<li';
			if(PAGE_ID==$v['id']) $output.=' class="sel"';
			$output.='><a href="'.ADMINDIR.$v['id'].'/'.$v['url'].'"';
			if(PAGE_ID==$v['id']) $output.=' class="sel"';
			$output.='>'.$v['title'].'</a>';
			$output.='</li>';
			}
		$output.='</ul>';
		return $output;
		}

	}

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
				if($ext=="txt"|$ext=="php"|$ext=="html"|$ext=="xml") {
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

/* remove a directory recursively */
function kRemoveDir($dir)
{
	if(!file_exists($dir)) return false;
	
	if(is_dir($dir) && !is_link($dir))
	{
		if(glob($dir.'/*') != false)
		{
			foreach(glob($dir.'/*') as $sf)
			{
				if(!kRemoveDir($sf)) return false;
			}
		}
		return rmdir($dir);

	} else {
		return unlink($dir);
	}
}


function kDirCopy($source,$dest,$options=array('folderPermission'=>0755,'filePermission'=>0755)) {
	$result=false;
	if(is_file($source)) {
		if ($dest[strlen($dest)-1]=='/') {
			if(!file_exists($dest)) {
				cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
				}
			$__dest=$dest."/".basename($source);
			}
		else {
			$__dest=$dest;
			}
		$result=copy($source, $__dest);
		chmod($__dest,$options['filePermission']);
		}
	elseif(is_dir($source)) {
		if($dest[strlen($dest)-1]=='/') {
			if ($source[strlen($source)-1]=='/') {
				//Copy only contents
				}
			else {
				//Change parent itself and its contents
				$dest=$dest.basename($source);
				@mkdir($dest);
				chmod($dest,$options['filePermission']);
				}
			}
		else {
			if($source[strlen($source)-1]=='/') {
				//Copy parent directory with new name and all its content
				@mkdir($dest,$options['folderPermission']);
				chmod($dest,$options['filePermission']);
				}
			else {
				//Copy parent directory with new name and all its content
				@mkdir($dest,$options['folderPermission']);
				chmod($dest,$options['filePermission']);
				}
			}

		$dirHandle=opendir($source);
		while($file=readdir($dirHandle)) {
			if($file!="." && $file!="..") {
				 if(!is_dir($source."/".$file)) {
					$__dest=$dest."/".$file;
					}
				else {
					$__dest=$dest."/".$file;
					}
				$result=kDirCopy($source."/".$file, $__dest, $options);
				}
			}
		closedir($dirHandle);
		}
	else {
		$result=false;
		}
	return $result;
	} 

function kaGetVar($param,$num,$ll=false) {
		if($ll==false) {
			if(isset($_SESSION['ll'])) $ll=$_SESSION['ll'];
			else $ll=DEFAULT_LANG;
			}
		$query="SELECT value".$num." FROM ".TABLE_CONFIG." WHERE param='".$param."' AND ll='".$ll."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) return $row['value'.$num];
		else return false;
		}
