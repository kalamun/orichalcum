<?php
/* (c) Kalamun.org - GNU/GPL 3 */

/* PAGINE */
class kPages {
	protected $inited;
	protected $imgs,$docgallery,$kText,$loadedPage,$page,$currentConversion,$type;

	public function __construct() {
		$this->inited=false;
		}
		
	public function init() {
		$this->inited=true;
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR."admin/inc/connect.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR."admin/inc/main.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR."inc/images.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR."inc/documents.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR."inc/kalamun.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR."inc/tplshortcuts.lib.php");
		$this->kText=new kText();
		$this->imgs=new kImages();
		$this->docgallery=new kDocgallery();
		}

	function pageExists($dir=false,$ll=false) {
		if(!$this->inited) $this->init();
		if($dir==false) $dir=trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__'],"/");
		if($ll==false) $ll=LANG;
		$query="SELECT * FROM `".TABLE_PAGINE."` WHERE (`dir`='".b3_htmlize($dir,true,"")."' OR `dir`='".mysql_real_escape_string($dir)."') AND ll='".$ll."' LIMIT 1";
		$results=mysql_query($query);
		if(!mysql_fetch_array($results)) return false;
		else return true;
		}

	public function getMetadata($dir=null,$ll=false) {
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		if($dir==null) $dir=trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__']," /");
		$metadata=array();
		$metadata['titolo']=$dir;
		$metadata['traduzioni']="";
		foreach(kGetLanguages() as $code=>$lang) { $metadata['traduzioni'].=$code."|".kGetVar('dir_home',1,$code)."\n"; }
		$metadata['template']=kGetVar('template',1);
		$metadata['layout']="";
		if(isset($dir)&&$dir!="") {
			$query="SELECT `idpag`,`titolo`,`traduzioni`,`template`,`layout`,`featuredimage` FROM `".TABLE_PAGINE."` WHERE (`dir`='".b3_htmlize($dir,true,'')."' OR `dir`='".mysql_real_escape_string($dir)."') AND ll='".mysql_real_escape_string($ll)."' LIMIT 1";
			$results=mysql_query($query);
			$row=mysql_fetch_array($results);
			$metadata['titolo']=$row['titolo'];
			$metadata['traduzioni']=$row['traduzioni'];
			$metadata['template']=$row['template'];
			$metadata['layout']=$row['layout'];
			$metadata['featuredimage']=($row['featuredimage']>0 ? $this->imgs->getImage($row['featuredimage']) : array());
			$idpag=$row['idpag'];
			}
		if(isset($idpag)) {
			$query="SELECT * FROM `".TABLE_METADATA."` WHERE `tabella`='".TABLE_PAGINE."' AND `id`='".$idpag."'";
				$results=mysql_query($query);
					while($row=mysql_fetch_array($results)) {
				$metadata[$row['param']]=$row['value'];
				}
			}
		return $metadata;
		}

	public function search($keywords) {
		if(!$this->inited) $this->init();
		/* search for one or more terms, passed as array */
		$output=array();
		if(!is_array($keywords)) $keywords=array($keywords);
		
		$query="SELECT `titolo`,`dir`,`anteprima` FROM `".TABLE_PAGINE."` WHERE ";
		foreach($keywords as $k) {
			$k=trim($k);
			if($k!=""&&strlen($k)>3) {
				$k=b3_htmlize($k,true,"");
				$query.="(`titolo` LIKE '%".mysql_real_escape_string($k)."%' OR `sottotitolo` LIKE '%".mysql_real_escape_string($k)."%' OR `anteprima` LIKE '%".mysql_real_escape_string($k)."%' OR `testo` LIKE '%".mysql_real_escape_string($k)."%') AND ";
				}
			}
		if(substr($query,-6)=="WHERE ") return $output; //if no valid keywords return the empty array (prevent to return all pages)

		$query.="`ll`='".LANG."' AND `riservata`='n' ORDER BY `modified`";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$output[]=array(
				"title"=>$row['titolo'],
				"permalink"=>BASEDIR.strtolower(LANG).'/'.$row['dir'],
				"excerpt"=>$row['anteprima']
				);
			}
		return $output;
		}

	public function setPageByDir($dir=false,$ll=false) {
		if(!$this->inited) $this->init();
		if($dir==false) $dir=trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__'],"/");
		if($ll==false) $ll=LANG;
		$this->loadedPage=$this->getPage($dir,$ll);
		}
	public function getPageVar($var) {
		if(!$this->inited) $this->init();
		return $this->loadedPage[$var];
		}
	public function getPageList($ll=null)
	{
		if(!$this->inited) $this->init();
		if($ll==null) $ll=LANG;
		$output=array();
		$query="SELECT `dir` FROM `".TABLE_PAGINE."` WHERE `ll`='".$ll."' AND `riservata`='n' ORDER BY `titolo`";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results))
		{
			$output[] = $this->row2output($row);
		}
		return $output;
	}

	public function getQuickList($cat=false,$from=0,$limit=10,$conditions="",$options="",$orderby="",$ll=null)
	{
		if(!$this->inited) $this->init();
		
		$vars=array(
			'photogallery'=>false,
			'documentgallery'=>false,
			'comments'=>false,
			'translations'=>false
			);

		if($from=="") $from=0;
		if($limit=="") $limit=10;
		if($orderby=="") $orderby="titolo";
		if($ll==null) $ll=LANG;

		$output=array();
		$query="SELECT * FROM `".TABLE_PAGINE."` WHERE `ll`='".$ll."' ";
		if($conditions!="") $query.="AND (".$conditions.") ";
		if($cat!=false)
		{
			$query.="AND `categorie` LIKE '%,".mysql_real_escape_string($cat).",%' ";
		}
		if($options!="") $query.=" ".$options." ";
		$query.=" AND `riservata`='n' ORDER BY `".$orderby."` DESC,`idpag` DESC LIMIT ".intval($from).",".intval($limit)."";
		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++)
		{
			$output[$i]=$this->row2output($row,$vars);
		}
		return $output;
	}

	function getPage($dir=false,$ll=false) {
		if(!$this->inited) $this->init();
		if($dir==false) $dir=trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__'],"/");
		if($ll==false) $ll=LANG;

		$output=array();

		$query="SELECT * FROM `".TABLE_PAGINE."` WHERE (`dir`='".b3_htmlize($dir,true,'')."' OR `dir`='".mysql_real_escape_string($dir)."') AND `riservata`='n' AND `ll`='".mysql_real_escape_string($ll)."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results))
		{
			$output = $this->row2output($row);

		} else {
			$query="DESCRIBE ".TABLE_PAGINE;
			$results=mysql_query($query);
			while($row=mysql_fetch_array($results))
			{
				$output[$row['Field']]="";
			}
			$output['imgs']=array();
			$output['docs']=array();
			$output['commenti']=array();
			$output['permalink']="";
			$output['categorie']=array();
			$output['conversions']=array();
			$output['embeddedimgs']=array();
			$output['embeddeddocs']=array();
			$output['embeddedmedias']=array();
		}

		return $output;
	}
	
	public function getPermalinkById($idpag) {
		if(!$this->inited) $this->init();
		$query="SELECT `ll`,`dir` FROM `".TABLE_PAGINE."` WHERE `idpag`='".intval($idpag)."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return BASEDIR.strtolower($row['ll']).'/'.$row['dir'];
		}

	public function getCatByName($name,$ll=false) {
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		$query="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_PAGINE."' AND categoria='".b3_htmlize($name,true,"")."' AND ll='".strtoupper($ll)."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) return $row;
		else return false;
		}
	public function getCatById($idcat) {
		if(!$this->inited) $this->init();
		$query="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_PAGINE."' AND idcat='".$idcat."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) return $row;
		else return false;
		}

	public function addComment($name,$email,$text,$idpag,$public="n") {
		if(!$this->inited) $this->init();
		if($public!="s") $public="n";
		$query="INSERT INTO ".TABLE_COMMENTI." (ip,data,tabella,id,autore,email,testo,public) VALUES('".$_SERVER['REMOTE_ADDR']."',NOW(),'".TABLE_PAGINE."','".$idpag."','".b3_htmlize($name,true,"")."','".b3_htmlize($email,true,"")."','".b3_htmlize($text,true,"")."','".$public."')";
		mysql_query($query);
		$idcomm=mysql_insert_id();
		return $idcomm;
		}
	
	public function getComments($idnews) {
		if(!$this->inited) $this->init();
		$output=array();
		$query="SELECT * FROM ".TABLE_COMMENTI." WHERE tabella='".TABLE_PAGINE."' AND id='".$idnews."' AND public='s' ORDER BY data";
		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$output[$i]=$row;
			$output[$i]['dataleggibile']=preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).*/","$3-$2-$1 $4:$5",$row['data']);
			}
		return $output;
		}

	private function row2output($row,$vars=array())
	{
		if(!$this->inited) $this->init();

		if(!isset($vars['photogallery'])) $vars['photogallery']=true;
		if(!isset($vars['documentgallery'])) $vars['documentgallery']=true;
		if(!isset($vars['comments'])) $vars['comments']=true;
		if(!isset($vars['translations'])) $vars['translations']=true;

		$output=$row;
		$output['embeddedimgs']=array();
		$output['embeddeddocs']=array();
		$output['embeddedmedias']=array();
		$output['anteprima']=$this->kText->formatText($output['anteprima']);
		$tmp=$this->kText->embedImg($output['anteprima']);
			$output['anteprima']=$tmp[0];
			$output['embeddedimgs']=array_merge($output['embeddedimgs'],$tmp[1]);
		$tmp=$this->kText->embedDocs($output['anteprima']);
			$output['anteprima']=$tmp[0];
			$output['embeddeddocs']=array_merge($output['embeddeddocs'],$tmp[1]);
		$tmp=$this->kText->embedMedia($output['anteprima']);
			$output['anteprima']=$tmp[0];
			$output['embeddedmedias']=array_merge($output['embeddedmedias'],$tmp[1]);
		$output['testo']=$this->kText->formatText($output['testo']);
		$tmp=$this->kText->embedImg($output['testo']);
			$output['testo']=$tmp[0];
			$output['embeddedimgs']=array_merge($output['embeddedimgs'],$tmp[1]);
		$tmp=$this->kText->embedDocs($output['testo']);
			$output['testo']=$tmp[0];
			$output['embeddeddocs']=array_merge($output['embeddeddocs'],$tmp[1]);
		$tmp=$this->kText->embedMedia($output['testo']);
			$output['testo']=$tmp[0];
			$output['embeddedmedias']=array_merge($output['embeddedmedias'],$tmp[1]);

		if($row['featuredimage']==0) $output['featuredimage']=false;
		else $output['featuredimage']=$this->imgs->getImage($row['featuredimage']);
		
		// get photogallery in the correct order
		$output['imgs']=array();
		if($vars['photogallery']==true)
		{
			$conditions="";
			foreach(explode(",",trim($row['photogallery'],",")) as $idimg)
			{
				$conditions.="`idimg`='".intval($idimg)."' OR ";
			}
			$conditions.="`idimg`='0'";
			
			$imgs=$this->imgs->getList(false,false,false,$conditions);
			
			foreach(explode(",",trim($row['photogallery'],",")) as $idimg)
			{
				foreach($imgs as $img)
				{
					if($img['idimg']==$idimg) $output['imgs'][]=$img;
				}
			}
		}
		
		
		$output['docs']=array();
		if($vars['documentgallery']==true)
		{
			$output['docs']=$this->docgallery->getList(TABLE_PAGINE,$row['idpag']);
		}
		
		$output['commenti']=array();
		if($vars['comments']==true)
		{
			$output['commenti']=$this->getComments($row['idpag']);
		}
		
		$output['traduzioni']=array();
		if($vars['translations']==true)
		{
			$output['traduzioni']=array();
			foreach(explode("|",trim($row['traduzioni'],"|")) as $trad)
			{
				if(substr($trad,0,2)!="") $output['traduzioni'][substr($trad,0,2)]=$this->getPermalinkById(substr($trad,3));
			}
		}
		
		$output['permalink']=BASEDIR.strtolower($ll).'/'.$row['dir'];

		$output['categorie']=array();
		if(strpos(kGetVar('admin-page-layout',1),",categories,")!==false)
		{
			$row['categorie']=trim($row['categorie'],",");
			foreach(explode(",",$row['categorie']) as $cat)
			{
				$output['categorie'][]=$this->getCatById($cat);
			}
		}
		
		//substitute {CAPTCHA} with the reCaptcha output
		if(strpos($output['testo'],'{CAPTCHA}')!==false || strpos($output['anteprima'],'{CAPTCHA}')!==false)
		{
			require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR.'inc/recaptcha/recaptchalib.php');
			$output['anteprima']=str_replace('{CAPTCHA}',recaptcha_get_html(kGetVar('captcha',1)),$output['anteprima']);
			$output['testo']=str_replace('{CAPTCHA}',recaptcha_get_html(kGetVar('captcha',1)),$output['testo']);
		}

		$output['conversions']=array();
		if($output['allowconversions']==1&&(!isset($GLOBALS['conversionstracker'][$output['idpag']])||$GLOBALS['conversionstracker'][$output['idpag']]==false))
		{
			//register of conversions, in order to apply conversions just one time for page
			if(!isset($GLOBALS['conversionstracker'])) $GLOBALS['conversionstracker']=array();
			$GLOBALS['conversionstracker'][$output['idpag']]=true;
			
			//populate conversions array
			$output['conversions']=$this->getConversions($output['idpag']);
			//process conversions
			$this->processConversions($output);
		}

		return $output;
	}

	/* CONVERSIONS */
	public function getConversions($vars) {
		if(!$this->inited) $this->init();
		if(!is_array($vars)) $vars=array("idpag"=>$vars);

		$query="SELECT * FROM `".TABLE_CONVERSIONS."` WHERE ";
		if(isset($vars['idpag'])) $query.=" `idpag`='".mysql_real_escape_string($vars['idpag'])."' AND ";
		$query.=" `idpag`>0 LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if($row==false) {
			//if there are no records for this page, get an empty array
			$row=array();
			$query="DESCRIBE `".TABLE_CONVERSIONS."`";
			$results=mysql_query($query);
			while($r=mysql_fetch_array($results)) {
				$row[$r['Field']]="";
				}
			}
		$output=$row;

		$output['variables']=array();
		foreach(explode("\n",trim($row['variables'])) as $line) {
			if(trim($line)!="") {
				$line=explode("\t",trim($line));
				$output['variables'][]=array("variable_name"=>$line[0],"correspondence"=>$line[1],"mandatary"=>$line[2]);
				}
			}

		//array of newsletter ids to subscribe and unsubscribe.
		//I do a foreach cycle as workaround to the explosion of empty string that create an array with one empty element
		$output['newsletters_add']=array();
		foreach(explode(",",trim($row['newsletters_add'],",")) as $nl) {
			if(trim($nl)!="") $output['newsletters_add'][]=$nl;
			}
		$output['newsletters_remove']=array();
		foreach(explode(",",trim($row['newsletters_remove'],",")) as $nl) {
			if(trim($nl)!="") $output['newsletters_remove'][]=$nl;
			}

		$output['create_member_username']="";
		$output['create_member_password']="";
		$output['create_member_expiration']="";
		$output['create_member_affiliation']="";
		foreach(explode("\n",$output['create_member_config']) as $l) {
			if(substr($l,0,2)=="u:") $output['create_member_username']=substr($l,2);
			if(substr($l,0,2)=="p:") $output['create_member_password']=substr($l,2);
			if(substr($l,0,2)=="e:") $output['create_member_expiration']=substr($l,2);
			if(substr($l,0,2)=="a:") $output['create_member_affiliation']=substr($l,2);
			}

		return $output;
		}

	/* PROCESS CONVERSIONS OF THE INPUT VARIABLES */
	public function processConversions($page=null) {
		if(!$this->inited) $this->init();
		if($page==null) $page=$this->loadedPage;
		$conv=$page['conversions'];
		$GLOBALS['__template']->currentConversion['conversion_code']=isset($page['conversions']['conversion_code'])?$page['conversions']['conversion_code']:'';
		$GLOBALS['__template']->currentConversion['fail_code']=isset($page['conversions']['fail_code'])?$page['conversions']['fail_code']:'';
		$correspondence=array("name"=>"","surname"=>"","username"=>"","password"=>"","email"=>"","expiration"=>"","affiliation"=>"");

		/* CHECK IF THERE ARE A VALID SUBMISSION; IF NOT, RETURN FALSE */
		if(isset($conv['variables'])&&is_array($conv['variables'])) {
			foreach($conv['variables'] as $var) {
				if($var['mandatary']==true&&!isset($_POST[$var['variable_name']])) return false;
				//in the meanwhile, track relationships beetween variables and fields
				foreach($correspondence as $k=>$v) {
					if($var['correspondence']==$k) $correspondence[$k]=$var['variable_name'];
					}
				}
			}
		$GLOBALS['__template']->currentConversion['result']="fail"; // ok, is valid... if something happens before the end, it remains on fail

		/* IN CASE OF MODERATION */
		//admin moderation
		if($conv['moderate']==1) {
			//not implemented yet
			}
		//double opt-in
		elseif($conv['moderate']==2) {
			//not implemented yet
			}
		//captcha
		elseif($conv['moderate']==3) {
			require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR.'inc/recaptcha/recaptchalib.php');
			$resp=recaptcha_check_answer(kGetVar('captcha',2),$_SERVER["REMOTE_ADDR"],$_POST["recaptcha_challenge_field"],$_POST["recaptcha_response_field"]);
			if(!$resp->is_valid) {
				return false;
				//$resp->error
				}
			}

		/* OPERATIONS TO DO ONLY IF A USER IS DEFINED */
		//check if all the vars needed for a user creation was passed: username or name or surname or email
		if(($correspondence['username']!=""&&isset($_POST[$correspondence['username']]))||
			($correspondence['email']!=""&&isset($_POST[$correspondence['email']]))||
			($correspondence['name']!=""&&isset($_POST[$correspondence['name']]))||
			($correspondence['surname']!=""&&isset($_POST[$correspondence['surname']]))||
			($correspondence['password']!=""&&isset($_POST[$correspondence['password']]))) {
			
			/* COLLECT DATA */

			//email
			if($correspondence['email']!=""&&isset($_POST[$correspondence['email']])) $m_email=$_POST[$correspondence['email']];
			else $m_email="";
			
			//name
			if($correspondence['name']!=""&&isset($_POST[$correspondence['name']])) $m_name=$_POST[$correspondence['name']];
			elseif($correspondence['username']!=""&&isset($_POST[$correspondence['username']])) $m_name=$_POST[$correspondence['username']];
			elseif($m_email!="") $m_name=substr($m_email,0,strpos($m_email,"@"));
			else $m_name="";
			
			//surname
			if($correspondence['surname']!=""&&isset($_POST[$correspondence['surname']])) $m_surname=$_POST[$correspondence['surname']];
			else $m_surname="";
			
			//fullname
			$m_fullname=trim($m_name.' '.$m_surname);
			
			//username
			$m_username="";
			if($correspondence['username']!=""&&isset($_POST[$correspondence['username']])) $m_username=$_POST[$correspondence['username']];
			elseif($conv['create_member_username']!="") {
				$m_username=$conv['create_member_username'];
				$m_username=str_replace("%n",$correspondence['name']!=""?strtolower($_POST[$correspondence['name']]):'',$m_username);
				$m_username=str_replace("%N",$correspondence['name']!=""?strtolower(substr($_POST[$correspondence['name']],0,1)):'',$m_username);
				$m_username=str_replace("%u",$correspondence['surname']!=""?strtolower($_POST[$correspondence['surname']]):'',$m_username);
				$m_username=str_replace("%U",$correspondence['surname']!=""?strtolower(substr($_POST[$correspondence['surname']],0,1)):'',$m_username);
				$m_username=str_replace("%e",$correspondence['email']!=""?strtolower($_POST[$correspondence['email']]):'',$m_username);
				$allowedchars="qwertyuiopasdfghjklzxcvbnm";
				while(strpos($m_username,"%d")!==false) {
					$m_username=preg_replace("/%d/",rand(0,9),$m_username,1);
					}
				while(strpos($m_username,"%s")!==false) {
					$m_username=preg_replace("/%s/",substr($allowedchars,rand(0,strlen($allowedchars)-1),1),$m_username,1);
					}
				}
			elseif($correspondence['name']!=""&&isset($_POST[$correspondence['name']])) {
				$m_username=$_POST[$correspondence['name']];
				if($correspondence['surname']!=""&&isset($_POST[$correspondence['surname']])) $m_username.=$_POST[$correspondence['surname']];
				$m_username=strtolower(str_replace(" ","",$m_username));
				}
			elseif($m_email!="") $m_username=$m_email;
			else $m_username=rand(10000000,99999999);
			//check if username exists yet. in case, add a random number at the end
			while($GLOBALS['__members']->getByUsername($m_username,true)!=false) {
				$m_username.=rand(0,9);
				}
			
			//password
			if($correspondence['password']!=""&&isset($_POST[$correspondence['password']])) $m_password=$_POST[$correspondence['password']];
			elseif($conv['create_member_password']!="") {
				$m_password=$conv['create_member_password'];
				$m_password=str_replace("%n",$correspondence['name']!=""?strtolower($_POST[$correspondence['name']]):'',$m_password);
				$m_password=str_replace("%N",$correspondence['name']!=""?strtolower(substr($_POST[$correspondence['name']],0,1)):'',$m_password);
				$m_password=str_replace("%u",$correspondence['surname']!=""?strtolower($_POST[$correspondence['surname']]):'',$m_password);
				$m_password=str_replace("%U",$correspondence['surname']!=""?strtolower(substr($_POST[$correspondence['surname']],0,1)):'',$m_password);
				$m_password=str_replace("%e",$correspondence['email']!=""?strtolower($_POST[$correspondence['email']]):'',$m_password);
				$allowedchars="qwertyuiopasdfghjklzxcvbnm";
				while(strpos($m_password,"%d")!==false) {
					$m_password=preg_replace("/%d/",rand(0,9),$m_password,1);
					}
				while(strpos($m_password,"%s")!==false) {
					$m_password=preg_replace("/%s/",substr($allowedchars,rand(0,strlen($allowedchars)-1),1),$m_password,1);
					}
				}
			else $m_password=false;
			
			//affiliation
			if($correspondence['affiliation']!=""&&isset($_POST[$correspondence['affiliation']])) $m_affiliation=$_POST[$correspondence['affiliation']];
			elseif($conv['create_member_affiliation']!="") $m_affiliation=$conv['create_member_affiliation'];
			else $m_affiliation="";
			
			//expiration
			if($correspondence['expiration']!=""&&isset($_POST[$correspondence['expiration']])) $m_expiration=$_POST[$correspondence['expiration']];
			elseif($conv['create_member_expiration']!="") $m_expiration=date("Y-m-d",time()+($conv['create_member_expiration']*86400));
			else $m_expiration="";

			/* CREATE A MEMBER */
			if($conv['create_member']==1||count($conv['newsletters_add'])>0||$conv['private_dir']==1) {

				//registration
				require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR."inc/utenti.lib.php");
				$idmember=$GLOBALS['__members']->register($m_username,$m_password,$m_fullname,$m_email,$m_affiliation,$m_expiration);
				if($idmember==false) {
					$member=$GLOBALS['__members']->getByEmail($m_email,$m_affiliation);
					$idmember==$member['idmember'];
					if($idmember==false) return false;
					}
				$member=$GLOBALS['__members']->getById($idmember);
				$m_username=$member['username'];
				$m_password=$member['password'];
				
				//save custom vars as metadata
				foreach($conv['variables'] as $var) {
					if(isset($_POST[$var['variable_name']])) $GLOBALS['__members']->replaceMetadata($m_username,$var['variable_name'],$_POST[$var['variable_name']],$m_affiliation);
					}

				/* CREATE A PRIVATE DIR CALLED AS THE USERNAME */
				if($conv['private_dir']==1&&$member['username']!="") {
					$dir=str_replace("/","",$member['username']);
					$GLOBALS['__private']->mkdir($dir,"members",array($member['idmember']=>true));
					}


				/* SUBSCRIBE TO NEWSLETTERS */
				foreach($conv['newsletters_add'] as $idnl) {
					$GLOBALS['__members']->subscribeToNewsletter($idmember,$idnl);
					}

				}

			/* UNSUBSCRIBE FROM NEWSLETTERS */
			if(isset($_POST[$correspondence['email']])&&count($conv['newsletters_remove'])>0) {
				$member=$GLOBALS['__members']->getByEmail($_POST[$correspondence['email']]);
				$idmember=$member['idmember'];
				if($idmember!="") {
					foreach($conv['newsletters_remove'] as $idnl) {
						$GLOBALS['__members']->unsubscribeFromNewsletter($idmember,$idnl);
						}
					}
				}
			}


		/* NOTIFICATIONS */
		if(trim($conv['notification_emails'])!="") {
			$to=explode("\n",$conv['notification_emails']);

			if($conv['notification_from']=="self"&&$correspondence['email']!="") $from=$_POST[$correspondence['email']];
			elseif($conv['notification_from']=="admin") $from=ADMIN_NAME.' <'.ADMIN_mail.'>';
			else $from=$conv['notification_from'];

			$fullname="";
			if($correspondence['name']!="") $fullname.=$_POST[$correspondence['name']];
			if($correspondence['surname']!="") $fullname.=' '.$_POST[$correspondence['surname']];
			$fullname=trim($fullname);
			$subject=$conv['notification_subject'];
			if($subject=="") $subject="New form subscription from your website!";
			$message=$conv['notification_text'];
			if($message=="") {
				foreach($_POST as $k=>$v) {
					$message.="<h3>".$k."</h3>";
					$message.=nl2br(trim($v)).'<hr />';
					}
				}
			
			foreach($correspondence as $k=>$v) {
				if($k!=""&&$v!="") {
					$subject=str_replace('{'.strtoupper($k).'}',$_POST[$v],$subject);
					$message=str_replace('{'.strtoupper($k).'}',$_POST[$v],$message);
					}
				}
			foreach($conv['variables'] as $var) {
				if($var['variable_name']!=""&&isset($_POST[$var['variable_name']])) {
					$subject=str_replace("{".$var['variable_name']."}",$_POST[$var['variable_name']],$subject);
					$subject=str_replace("{".strtoupper($var['variable_name'])."}",$_POST[$var['variable_name']],$subject);
					$message=str_replace("{".$var['variable_name']."}",$_POST[$var['variable_name']],$message);
					$message=str_replace("{".strtoupper($var['variable_name'])."}",$_POST[$var['variable_name']],$message);
					}
				}
			$subject=str_replace("{NAME}",$m_name,$subject);
			$subject=str_replace("{SURNAME}",$m_surname,$subject);
			$subject=str_replace("{USERNAME}",$m_username,$subject);
			$subject=str_replace("{PASSWORD}",$m_password,$subject);
			$message=str_replace("{NAME}",$m_name,$message);
			$message=str_replace("{SURNAME}",$m_surname,$message);
			$message=str_replace("{USERNAME}",$m_username,$message);
			$message=str_replace("{PASSWORD}",$m_password,$message);
			
			foreach($to as $sendto) {
				$GLOBALS['__emails']->send($from,$sendto,$subject,$message);
				}

			}

		/* FOLLOW-UP */
		if(trim(strip_tags($conv['followup_text'],"<img>"))!=""&&$correspondence['email']!="") {
			$to=$_POST[$correspondence['email']];

			$from=$conv['followup_from'];
			if($from=="") $from=ADMIN_NAME.' <'.ADMIN_mail.'>';

			$subject=$conv['followup_subject'];

			$message=$conv['followup_text'];
			foreach($correspondence as $k=>$v) {
				if($k!=""&&$v!="") {
					$subject=str_replace('{'.strtoupper($k).'}',$_POST[$v],$subject);
					$message=str_replace('{'.strtoupper($k).'}',$_POST[$v],$message);
					}
				}
			foreach($conv['variables'] as $var) {
				if($var['variable_name']!=""&&isset($_POST[$var['variable_name']])) {
					$subject=str_replace("{".$var['variable_name']."}",$_POST[$var['variable_name']],$subject);
					$subject=str_replace("{".strtoupper($var['variable_name'])."}",$_POST[$var['variable_name']],$subject);
					$message=str_replace("{".$var['variable_name']."}",$_POST[$var['variable_name']],$message);
					$message=str_replace("{".strtoupper($var['variable_name'])."}",$_POST[$var['variable_name']],$message);
					}
				}
			$subject=str_replace("{NAME}",$m_name,$subject);
			$subject=str_replace("{SURNAME}",$m_surname,$subject);
			$subject=str_replace("{USERNAME}",$m_username,$subject);
			$subject=str_replace("{PASSWORD}",$m_password,$subject);
			$message=str_replace("{NAME}",$m_name,$message);
			$message=str_replace("{SURNAME}",$m_surname,$message);
			$message=str_replace("{USERNAME}",$m_username,$message);
			$message=str_replace("{PASSWORD}",$m_password,$message);

			$GLOBALS['__emails']->send($from,$to,$subject,$message);
			}

		/* "DISPLAY" CONVERSION CODE */
		$message=$GLOBALS['__template']->currentConversion['conversion_code'];
		foreach($correspondence as $k=>$v) {
			if($k!=""&&$v!="") {
				$message=str_replace('{'.strtoupper($k).'}',$_POST[$v],$message);
				}
			}
		foreach($conv['variables'] as $var) {
			if($var['variable_name']!=""&&isset($_POST[$var['variable_name']])) {
				$message=str_replace("{".$var['variable_name']."}",$_POST[$var['variable_name']],$message);
				$message=str_replace("{".strtoupper($var['variable_name'])."}",$_POST[$var['variable_name']],$message);
				}
			}
		$message=str_replace("{NAME}",$m_name,$message);
		$message=str_replace("{SURNAME}",$m_surname,$message);
		$message=str_replace("{USERNAME}",$m_username,$message);
		$message=str_replace("{PASSWORD}",$m_password,$message);
		$GLOBALS['__template']->currentConversion['conversion_code']=$message;

		$GLOBALS['__template']->currentConversion['result']="success";
		}
	
	/* returns conversions results: if $boolean==true, it returns true or false, otherwise it returns the code inserted trought the admin panel ("conversion code" tab) */
	public function getConversionsResults($boolean=false) {
		if(!$this->inited) $this->init();
		if(isset($GLOBALS['__template']->currentConversion['result'])&&$GLOBALS['__template']->currentConversion['result']=="success") {
			if($boolean==true) return true;
			else return $GLOBALS['__template']->currentConversion['conversion_code'];
			}
		elseif(isset($GLOBALS['__template']->currentConversion['result'])&&$GLOBALS['__template']->currentConversion['result']=="fail") {
			if($boolean==true) return false;
			else return $GLOBALS['__template']->currentConversion['fail_code'];
			}
		}
	}


class kEmptyPage {

	function kEmptyPage() {
		}

	function getPage($dir) {
		$contents['titolo']="Under Construction";
		$contents['anteprima']="";
		$contents['testo']="";
		}

	}

?>
