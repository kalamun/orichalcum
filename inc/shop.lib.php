<?
/* visualizzazione dello shop */
class kShop {
	protected $inited;
	protected $cats,$allowedcats,$allthecats,$kText,$imgallery,$docgallery,$loadedItem,$delivererDB,$paymentDB,$payPalBusinessId,$virtualPayBusinessId,$virtualPayABI,$virtualPayKEY,$orderDB,$customFields,$zone;

	public function __construct() {
		$this->inited=false;
		}
	
	public function init() {
		$this->inited=true;
		global $__template;
		global $__users;
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."admin/inc/connect.inc.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."admin/inc/main.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/images.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/documents.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/kalamun.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/utenti.lib.php");
		$this->kText=new kText();
		$this->imgallery=new kImgallery();
		$this->docgallery=new kDocgallery();
		$this->loadedItem=array();
		$this->cats=array();
		$this->allowedcats=array();
		$this->allthecats=array();
		$this->delivererDB=array();
		$this->paymentDB=array();
		$this->orderDB=array();
		$this->zone="";
		$this->payPalBusinessId="";
		$this->virtualPayBusinessId="";
		$this->virtualPayABI="";
		$this->virtualPayKEY="";
		$tmp=trim($__template->getVar('shop',2),",");
		if($tmp!="*") {
			foreach(explode(",",trim($tmp,",")) as $cat) {
				$this->allowedcats[]=$cat;
				}
			}
		$query="SELECT * FROM ".TABLE_CATEGORIE." WHERE `tabella`='".TABLE_SHOP_ITEMS."' AND `ll`='".mysql_real_escape_string(LANG)."' ORDER BY `ref`,`ordine`";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$row['categoria']=mb_convert_encoding($row['categoria'],"UTF-8");
			$this->allthecats[$row['idcat']]=$row;
			$this->allthecats[$row['idcat']]['permalink']=BASEDIR.strtolower(LANG).'/'.$__template->getVar('dir_shop',1).'/'.$row['dir'];
			$this->allthecats[$row['idcat']]['imgs']=$this->imgallery->getList(TABLE_CATEGORIE,$row['idcat']);
			if($tmp=="*") $this->allowedcats[$row['idcat']]=true;
			if(array_search($row['idcat'],$this->allowedcats)!==false) $this->cats[$row['idcat']]=true;
			}
		unset($tmp);

		$this->customFields=array();
		$query="SELECT * FROM ".TABLE_SHOP_CUSTOMFIELDS." WHERE `categories`='' ";
		foreach($this->allthecats as $cat) {
			$query.=" OR `categories` LIKE '%".$cat['idcat']."%' ";
			}
		$query.=" ORDER BY `order`";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$this->customFields[]=$row;
			}

		}
	
	public function shopExists($dir=null,$ll=null) {
		if($ll==null) $ll=LANG;
		if($dir==null) $dir=array($GLOBALS['__dir__']);
		else $dir=explode("/",trim($dir,"/"));
		if($dir[0]!=kGetVar('dir_shop')) return false;
		return true;
		}

	public function shopItemExists($dir=null,$ll=null) {
		if($ll==null) $ll=LANG;
		if($dir==null) $dir=array($GLOBALS['__dir__'],$GLOBALS['__subdir__'],$GLOBALS['__subsubdir__']);
		else $dir=explode("/",$dir);
		if($dir[0]!=kGetVar('dir_shop')) return false;
		if(!isset($dir[2])) $dir[2]="";

		$query="SELECT * FROM `".TABLE_SHOP_ITEMS."` WHERE `dir`='".mysql_real_escape_string($dir[2])."' AND ll='".mysql_real_escape_string(strtoupper($ll))."' AND online='y' ";
		if(!isset($_GET['preview'])||$_GET['preview']!=md5(ADMIN_MAIL)) $query.="AND public<=NOW() ";
		//if($expired=="nascondi") $query.="AND expired<=NOW() ";
		$query.=" LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) return true;
		else return false;
		}

	public function getCountries($zone=false) {
		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_COUNTRIES."` ";
		if($zone!=false) $query.="WHERE `zone`='".intval($zone)."' ";
		$query.="ORDER BY `country`";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$output[]=$row;
			}
		return $output;
		}
	public function getZoneByCountry($country) {
		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_COUNTRIES."` WHERE `ll`='".mysql_real_escape_string($country)."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			return $row['zone'];
			}
		return false;
		}
	public function getPaymentsByZone($zone,$ll=null) {
		if($ll==null) $ll=LANG;
		$output=array();
		$query="SELECT * FROM ".TABLE_SHOP_PAYMENTS." WHERE zones LIKE '%,".intval($zone).",%' AND ll='".$ll."' ORDER BY ordine";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$output[]=$row;
			}
		return $output;
		}
	public function getPaymentById($idspay) {
		$query="SELECT * FROM ".TABLE_SHOP_PAYMENTS." WHERE idspay='".mysql_real_escape_string($idspay)."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			return $row;
			}
		else return false;
		}
	public function setPaymentById($idspay) {
		if(!$this->inited) $this->init();
		$this->paymentDB=$this->getPaymentById($idspay);
		return true;
		}
	public function getDeliverersByZone($zone=false) {
		if($zone==false) $zone=$this->zone;
		$output=array();
		$query="SELECT * FROM ".TABLE_SHOP_DELIVERERS." WHERE zones LIKE '%,".intval($zone).",%' ORDER BY ordine";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$output[]=$row;
			}
		return $output;
		}
	public function getDelivererById($iddel) {
		$query="SELECT * FROM ".TABLE_SHOP_DELIVERERS." WHERE iddel='".mysql_real_escape_string($iddel)."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			$output=$row;
			$output['prices']=array();
			$query="SELECT * FROM ".TABLE_SHOP_DEL_PRICES." WHERE iddel='".mysql_real_escape_string($iddel)."' ORDER BY maxweight";
				$results=mysql_query($query);
					while($row=mysql_fetch_array($results)) {
				$prices=explode(",",rtrim($row['prices'],","));
				$output['prices'][$row['maxweight']]=$prices;
				}
			return $output;
			}
		else return false;
		}
	public function setDelivererById($iddel) {
		if(!$this->inited) $this->init();
		$this->delivererDB=$this->getDelivererById($iddel);
		if($this->delivererDB!=false) return true;
		else return false;
		}
	public function getDelivererPriceByKg($kg,$zone=false) {
		if(!$this->inited) $this->init();
		if($zone==false) $zone=$this->zone;
		if(!isset($this->delivererDB['prices'])||!is_array($this->delivererDB['prices'])) return 0;
		foreach($this->delivererDB['prices'] as $k=>$p) {
			if($k>$kg) {
				return isset($p[$zone])?$p[$zone]:false;
				}
			}
		return false;
		}

	public function getItemList($from=0,$limit=10,$conditions="",$options="",$orderby="",$ll=null) {
		if(!$this->inited) $this->init();
		if($from=="") $from=0;
		if($limit=="") $limit=10;
		if($orderby=="") $orderby=$GLOBALS['__template']->getVar('shop-order',1);
		if($orderby=="") $orderby="public";
		if($orderby=="created"||$orderby=="public"||$orderby=="expired") $dataRef=$orderby; else $dataRef='titolo';
		$expired=$GLOBALS['__template']->getVar('shop-order',2);
		if($ll==null) $ll=LANG;

		$output=array();
		$query="SELECT * FROM ".TABLE_SHOP_ITEMS." WHERE ll='".$ll."' AND online='y' AND public<=NOW() ";
		if($expired=="nascondi") $query.="AND expired<NOW() ";
		if($conditions!="") $query.="AND (".$conditions.") ";
		if(count($this->cats)>0) {
			$query.="AND (categorie=',' ";
			foreach($this->cats as $cat=>$true) {
				$query.="OR categorie LIKE '%,".$cat.",%' ";
				}
			$query.=") ";
			}
		if($options!="") $query.=" ".$options." ";
		$query.="ORDER BY ".$orderby.",idsitem DESC LIMIT ".$from.",".$limit."";
		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$output[$i]=$this->row2output($row,$orderby);
			}
		return $output;
		}

	public function getItemQuickList($vars) {
		if(!$this->inited) $this->init();
		if(!isset($vars['orderby'])||$vars['orderby']=="") $vars['orderby']=$GLOBALS['__template']->getVar('shop-order',1);
		if($vars['orderby']=="") $orderby="public";
		$vars['orderby']=="created"||$vars['orderby']=="public"||$vars['orderby']=="expired" ? $dataRef=$vars['orderby'] : $dataRef='public';
		
		if(!isset($vars['expired'])||$vars['expired']=="") $vars['expired']=$GLOBALS['__template']->getVar('shop-order',2);
		if(!isset($vars['ll'])||$vars['ll']=="") $vars['ll']=LANG;

		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_ITEMS."` WHERE `ll`='".mysql_real_escape_string($vars['ll'])."' AND `online`='y' AND `public`<=NOW() ";
		if($vars['expired']=="nascondi") $query.=" AND expired<NOW() ";
		if(isset($vars['conditions'])&&$vars['conditions']!="") $query.=" AND (".$vars['conditions'].") ";
		
		if(isset($vars['category'])) $query.=" AND categorie LIKE '%,".intval($vars['category']).",%'";
		elseif(count($this->cats)>0) {
			$query.="AND (categorie=',' ";
			foreach($this->cats as $cat=>$true) {
				$query.="OR categorie LIKE '%,".$cat.",%' ";
				}
			$query.=") ";
			}

		if(isset($vars['options'])&&$vars['options']!="") $query.=" ".$vars['options']." ";
		$query.="ORDER BY ".$vars['orderby'].",idsitem DESC ";
		if(isset($vars['offset'])&&isset($vars['limit'])) $query.=" LIMIT ".$vars['offset'].",".$vars['limit']."";
		elseif(isset($vars['offset'])) $query.=" LIMIT ".$vars['offset'].",9999";
		elseif(isset($vars['limit'])) $query.=" LIMIT ".$vars['limit'];

		$results=mysql_query($query);

		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$output[$i]=$row;
			$output[$i]['categories']=array();
			$subdir="";
			foreach($this->cats as $cat=>$true) {
				if(strpos($row['categorie'],','.$cat.',')!==false) {
					$output[$i]['categories'][]=$cat;
					if($GLOBALS['__dir__']==$GLOBALS['__template']->getVar('dir_shop',1)&&$GLOBALS['__subdir__']==$this->allthecats[$cat]['dir']) $subdir=$GLOBALS['__subdir__'];
					elseif($subdir=="") $subdir=$this->allthecats[$cat]['dir'];
					}
				}
			if($subdir==""&&isset($output[$i]['categories'][0])) $subdir=$output[$i]['categories'][0]['dir'];
			$output[$i]['permalink']=BASEDIR.strtolower(LANG).'/'.$GLOBALS['__template']->getVar('dir_shop',1).'/'.$subdir.'/'.$row['dir'];
			$output[$i]['catpermalink']=BASEDIR.strtolower(LANG).'/'.$GLOBALS['__template']->getVar('dir_shop',1).'/'.$subdir;
			}

		return $output;
		}

	public function getCategoryByDir($dir) {
		if(!$this->inited) $this->init();
		if(count($this->allthecats)>0) {
			foreach($this->allthecats as $cat) {
				if($cat['dir']==$dir) return $cat;
				}
			}
		return false;
		}

	public function setItemByDir($dir,$ll=false) {
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		$this->loadedItem=$this->getItemByDir($dir,$ll=false);
		}

	public function getItemByDir($dir,$ll=false) {
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		$dir=mysql_real_escape_string($dir);
		$expired=$GLOBALS['__template']->getVar('shop-order',2);

		$query="SELECT * FROM ".TABLE_SHOP_ITEMS." WHERE online='y' AND dir='".$dir."' AND ll='".$ll."' ";
		if(!isset($_GET['preview'])||$_GET['preview']!=md5(ADMIN_MAIL)) {
			$query.="AND public<='".date("Y-m-d H:i:s")."' ";
			if($expired=="nascondi") $query.="AND expired>'".date("Y-m-d H:i:s")."' ";
			}
		if(count($this->cats)>0) {
			$query.="AND (categorie=',' ";
			foreach($this->cats as $cat=>$true) {
				$query.="OR categorie LIKE '%,".$cat.",%' ";
				}
			$query.=") ";
			}
		$query.=' LIMIT 1';
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $this->row2output($row);
		}
	public function getItemById($idsitem,$ll=false) {
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		$idsitem=intval($idsitem);
		$expired=$GLOBALS['__template']->getVar('shop-order',2);

		$query="SELECT * FROM ".TABLE_SHOP_ITEMS." WHERE online='y' AND idsitem='".$idsitem."' AND ll='".$ll."' ";
		if(!isset($_GET['preview'])||$_GET['preview']!=md5(ADMIN_MAIL)) {
			$query.="AND public<=NOW() ";
			if($expired=="nascondi") $query.="AND expired>NOW() ";
			}
		if(count($this->cats)>0) {
			$query.="AND (categorie=',' ";
			foreach($this->cats as $cat=>$true) {
				$query.="OR categorie LIKE '%,".$cat.",%' ";
				}
			$query.=") ";
			}
		$query.=' LIMIT 1';
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $this->row2output($row);
		}
	
	public function getPayPalBusinessId() {
		if(!$this->inited) $this->init();
		global $__template;
		if($this->payPalBusinessId!="") return $this->payPalBusinessId;
		else return $__template->getVar('shop-paypal',1);
		}

	public function getShopTemplate($dir=false) {
		if(!$this->inited) $this->init();
		if($dir==false) $dir=$GLOBALS['__subsubdir__'];
		$orderby=kGetVar('shop-order',1);
		$expired=kGetVar('shop-order',2);
		$query="SELECT layout FROM ".TABLE_SHOP_ITEM." WHERE dir='".addslashes($dir)."' AND ll='".LANG."' AND online='y' ";
		if(!isset($_GET['preview'])||$_GET['preview']!=md5(ADMIN_MAIL)) $query.="AND public<=NOW() ";
		if($dir!="") $query.="AND dir='".$dir."' ";
		if($expired=="nascondi") $query.="AND expired<=NOW() ";
		if(count($this->cat)>0) {
			$query.="AND (categorie='' ";
			foreach($this->cat as $cat=>$true) {
				$query.="OR categorie LIKE '%,".$cat.",%' ";
				}
			$query.=") ";
			}
		$query.=" LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			return $row;
			}
		}
	public function getMetadata($dir=null,$ll=false) {
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		if($dir!=null) $dir=explode("/",$dir);
		else $dir=array($GLOBALS['__dir__'],$GLOBALS['__subdir__'],$GLOBALS['__subsubdir__']);
		$metadata=array();
		$metadata['titolo']=$dir[0];
		$metadata['traduzioni']="";
		foreach(kGetLanguages() as $code=>$lang) { $metadata['traduzioni'].=$code."|".kGetVar('dir_shop',1,$code)."\n"; }
		$metadata['template']=kGetVar('shop-template',1);
		$metadata['layout']="";
		if(isset($dir[1])&&$dir[1]!="") {
			$cat=$this->getCatByDir($dir[1]);
			$metadata['titolo'].=" &gt; ".$cat['categoria'];
			}
		if(isset($dir[2])&&$dir[2]!="") {
			$query="SELECT idsitem,titolo,layout FROM ".TABLE_SHOP_ITEMS." WHERE dir='".b3_htmlize($dir[2],true,"")."' AND ll='".$ll."' LIMIT 1";
				$results=mysql_query($query);
					$row=mysql_fetch_array($results);
			$metadata['titolo'].=" &gt; ".$row['titolo'];
			$metadata['traduzioni']="";
			if($metadata['template']!="") $metadata['template']="";
			if($metadata['layout']!="") $metadata['layout']=$row['layout'];
			$idsitem=$row['idsitem'];
			}
		if(isset($idsitem)) {
			$query="SELECT * FROM ".TABLE_METADATA." WHERE tabella='".TABLE_SHOP_ITEMS."' AND id='".$idsitem."'";
				$results=mysql_query($query);
					while($row=mysql_fetch_array($results)) {
				$metadata[$row['param']]=$row['value'];
				}
			}
		return $metadata;
		}
	
	public function getItemVar($param) {
		if(!$this->inited) $this->init();
		return $this->loadedItem[$param];
		}

	public function getCatByDir($dir) {
		if(!$this->inited) $this->init();
		foreach($this->allthecats as $cat) {
			if($cat['dir']==$dir) return $cat;
			}
		return false;
		}
	public function getCategories($vars) {
		if(!$this->inited) $this->init();
		if(isset($vars['all'])&&$vars['all']==true) {
			return $this->allthecats[$idcat];
			}
		elseif(isset($vars['id'])) {
			return $this->allthecats[$vars['id']];
			}
		else {
			$output=array();
			foreach($this->allowedcats as $idcat) {
				$output[]=$this->allthecats[$idcat];
				}
			return $output;
			}
		}
		
	public function countItems($conditions="") {
		///guardarci!
		if(!$this->inited) $this->init();
		$query="SELECT count(*) AS tot FROM ".TABLE_SHOP_ITEMS." WHERE ll='".LANG."' AND `created`<=NOW() AND `categorie`<>',,' ";
		if($this->geo>0) $query.="AND categorie LIKE '%,".$this->geo.",%' ";
		if($this->cat>0) $query.="AND categorie LIKE '%,".$this->cat.",%' ";
		if($conditions!="") $query.="AND (".$conditions.") ";
		$query.="ORDER BY data DESC,idnews DESC";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $row['tot'];
		}
		
	public function briciole() {
		///GUARDARCI!!!
		if(!$this->inited) $this->init();
		$cat=$this->cat;
		$briciole='<a href="'.BASEDIR.'/index.php">Home page</a>';
		if($geo>0) $briciole.=' &gt; <a href="'.BASEDIR.'/notizie/'.title2dir($this->geoList[$geo]).'">'.$this->geoList[$geo].'</a>';
		if($cat>0) $briciole.=' &gt; <a href="'.BASEDIR.'/notizie/'.title2dir($this->geoList[$geo]).'/'.title2dir($this->catList[$cat]).'">'.$this->catList[$cat].'</a>';
		return $briciole;
		}

	private function row2output($row,$orderby="titolo") {
		if(!$this->inited) $this->init();
		$output=$row;
		if($orderby=="") $orderby=$GLOBALS['__template']->getVar('shop-order',1);
		if($orderby=="") $orderby="titolo";
		$output['categorie']=array();
		$subdir="";
		foreach($this->cats as $cat=>$true) {
			if(strpos($row['categorie'],','.$cat.',')!==false) {
				$output['categories'][]=$cat;
				if($GLOBALS['__dir__']==$GLOBALS['__template']->getVar('dir_shop',1)&&$GLOBALS['__subdir__']==$this->allthecats[$cat]['dir']) $subdir=$GLOBALS['__subdir__'];
				elseif($subdir=="") $subdir=$this->allthecats[$cat]['dir'];
				}
			}
		if($subdir==""&&isset($output[$i]['categories'][0])) $subdir=$output[$i]['categories'][0]['dir'];
		$output['permalink']=BASEDIR.strtolower(LANG).'/'.$GLOBALS['__template']->getVar('dir_shop',1).'/'.$subdir.'/'.$row['dir'];
		$output['catpermalink']=BASEDIR.strtolower(LANG).'/'.$GLOBALS['__template']->getVar('dir_shop',1).'/'.$subdir;
		$kText=new kText();
		$output['testo']=$row['testo'];
		$output['embeddedimgs']=array();
		$output['embeddeddocs']=array();
		$output['embeddedmedias']=array();
		$output['anteprima']=$this->kText->formatText($output['anteprima']);
		$tmp=$this->kText->embedImg($output['anteprima']);
			$output['anteprima']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddedimgs']=array_merge($output['embeddedimgs'],$tmp[1]);
		$tmp=$this->kText->embedDocs($output['anteprima']);
			$output['anteprima']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddeddocs']=array_merge($output['embeddeddocs'],$tmp[1]);
		$tmp=$this->kText->embedMedia($output['anteprima']);
			$output['anteprima']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddedmedias']=array_merge($output['embeddedmedias'],$tmp[1]);
		$output['testo']=$this->kText->formatText($output['testo']);
		$tmp=$this->kText->embedImg($output['testo']);
			$output['testo']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddedimgs']=array_merge($output['embeddedimgs'],$tmp[1]);
		$tmp=$this->kText->embedDocs($output['testo']);
			$output['testo']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddeddocs']=array_merge($output['embeddeddocs'],$tmp[1]);
		$tmp=$this->kText->embedMedia($output['testo']);
			$output['testo']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddedmedias']=array_merge($output['embeddedmedias'],$tmp[1]);

		$output['imgs']=$this->imgallery->getList(TABLE_SHOP_ITEMS,$row['idsitem']);
		$output['docs']=$this->docgallery->getList(TABLE_SHOP_ITEMS,$row['idsitem']);
		$output['commenti']=$this->getComments($row['idsitem']);

		$output['privatearea']=explode("\n",trim($output['privatearea']));

		$output['customfields']=array();
		foreach($this->getCustomFields(explode(",",trim($row['categorie'],","))) as $field) {
			$output['customfields'][]=$field;
			}
		foreach(explode("</field>",trim($row['customfields'])) as $f) {
			$f=trim($f);
			if(!empty($f)) {
				preg_match('/^<field id="(\d+)">(.*)/s',$f,$match);
				for($i=0;isset($output['customfields'][$i]);$i++) {
					if($output['customfields'][$i]['idsfield']==$match[1]) $output['customfields'][$i]['value']=$match[2];
					}
				}
			}
		$output['variations']=$this->getVariations($row['idsitem']);

		/* price calc */
		$output['realprice']=$output['prezzo'];
		if(kGetVar('shop-discount',1)=='always') $output['realprice']=$output['scontato']>0?$output['scontato']:$output['prezzo'];
		elseif(kGetVar('shop-discount',1)=='qty') {
			if(kGetVar('shop-discount',2)<=$this->getCartItemsCount()) $output['realprice']=$output['scontato']>0?$output['scontato']:$output['prezzo'];
			}
		return $output;
		}
		
	public function addComment($name,$email,$text,$idnews,$public="n") {
		if(!$this->inited) $this->init();
		if($public!="s") $public="n";
		$query="INSERT INTO ".TABLE_COMMENTI." (ip,data,tabella,id,autore,email,testo,public) VALUES('".$_SERVER['REMOTE_ADDR']."',NOW(),'".TABLE_NEWS."','".$idnews."','".b3_htmlize($name,true,"")."','".b3_htmlize($email,true,"")."','".b3_htmlize($text,true,"")."','".$public."')";
		mysql_query($query);
		$idcomm=mysql_insert_id();
		
		//notifica
		$mail=array("headers"=>"","to"=>"","subject"=>"","message"=>"");
		$query="SELECT idnews,titolo,iduser FROM ".TABLE_NEWS." WHERE idnews=".$idnews." LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$iduser=$row['iduser'];
		$titolo=$row['titolo'];
		$idnews=$row['idnews'];
		$query="SELECT * FROM ".TABLE_USERS." WHERE iduser='".$iduser."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			$siteTitle=kGetVar('sitename',1);
			$mail['subject']="[".$siteTitle."] Nuovo commento";
			$mail['message']="Ciao ".$row['name'].",\n"
							."Hai ricevuto un commento all'oggetto ".$titolo." del tuo sito ".SITE_URL."\n"
							."Ecco cosa hanno scritto:\n"
							."---\n"
							.$text."\n"
							."---\n"
							."autore: ".$name." (".$email." - ip:".$_SERVER['REMOTE_ADDR'].")\n"
							."---\n";
			if($public=="n") {
				$mail['subject'].=" da moderare";
				$mail['message'].="\n"
								."Questo commento attende la tua moderazione, quindi non sarà visibile fino a quando non l'avrai approvato.\n"
								."Per approvarlo vai qui: ".SITE_URL."/admin/news/commenti.php?idnews=".$idnews."\n";
				}
			$mail['headers']='From: '.$name.' <'.$email.'>';
			$mail['to']=ADMIN_MAIL;
			mail($mail['to'],$mail['subject'],$mail['message'],$mail['headers']);
			}
		}

	public function getCustomFields($vars=array()) {
		if(!$this->inited) $this->init();
		/* returns an array with custom fields */
		$output=array();
		$categories=array();

		foreach($this->customFields as $field) {
			if(isset($vars['categories'])) {
				//filter categories
				$isValid=false;
				foreach($vars['categories'] as $c) {
					if(strpos($field['categories'],','.$c.',')!==false) {
						$isValid=true;
						break;
						}
					}
				if($isValid==true) {
					$output[]=$field;
					}
				}
			else {
				$output[]=$field;
				}
			}
		return $output;
		}

	public function getItemCustomFields($vars) {
		if(!$this->inited) $this->init();
		$output=array();
		if(isset($vars['name'])) {
			//filter by field name
			foreach($this->loadedItem['customfields'] as $f) {
				if($f['name']==$vars['name']) $output[]=$f;
				}
			}
		elseif(isset($vars['id'])) {
			//filter by idsfield
			foreach($this->loadedItem['customfields'] as $f) {
				if($f['idsfield']==$vars['id']) $output[]=$f;
				}
			}
		else $output=$this->loadedItem['customfields'];
		return $output;
		}
	
	public function getVariations($idsitem) {
		/* loads from database the variations for a gived item */
		$output=array();
		
		$query="SELECT * FROM `".TABLE_SHOP_VARIATIONS."` WHERE `idsitem`='".intval($idsitem)."' ORDER BY `collection` ASC,`order` ASC";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$output[$row['collection']][]=$row;
			}
		return $output;
		}

	public function getItemVariations($vars) {
		if(!$this->inited) $this->init();
		if(isset($vars['collection'])) {
			//filter by collection
			if(isset($this->loadedItem['variations'][$vars['collection']])) return $this->loadedItem['variations'][$vars['collection']];
			}
		elseif(isset($vars['id'])) {
			//filter by id
			foreach($this->loadedItem['variations'] as $c) {
				foreach($c as $v) {
					if($v['idsvar']==$vars['id']) return $v;
					}
				}
			}
		else return $this->loadedItem['variations'];
		}
	
	public function getComments($idsitem) {
		$output=array();
		$query="SELECT * FROM ".TABLE_COMMENTI." WHERE tabella='".TABLE_SHOP_ITEMS."' AND id='".$idsitem."' AND public='s' ORDER BY data";
		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$output[$i]=$row;
			}
		return $output;
		}


	/* COUPONS */
	public function getCouponByCode($code) {
		if(trim($code)=="") return false;

		$output=array();

		// get the coupon
		$query="SELECT * FROM `".TABLE_SHOP_COUPONS_CODES."` WHERE `code`='".mysql_real_escape_string($code)."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if($row==false) return false;
		$output=$row;

		// get the coupon's collection
		$query="SELECT * FROM `".TABLE_SHOP_COUPONS."` WHERE `idscoup`='".mysql_real_escape_string($row['idscoup'])."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if($row==false) return false;
		
		// join coupon and container
		$output=array_merge($output,$row);

		if(substr($output['action'],0,9)=='discount=') {
			$output['value']=number_format(floatval(substr($output['action'],9)),2).' '.$GLOBALS['__template']->getVar('shop-currency',2);
			}
		elseif(substr($output['action'],0,16)=='discountpercent=') {
			$output['value']=substr($output['action'],16);
			$output['value']=round($price/100*floatval($output['value']),2).' %';
			}
		elseif($output['action']=='freeshipping') {
			$output['value']="Free shipping";
			}
		elseif($output['action']=='freecheaper') {
			$cheaper=-1;
			foreach($this->getCart() as $item) {
				if($cheaper==-1||$cheaper>$item['realprice']) $cheaper=$item['realprice'];
				}
			$output['value']=number_format($cheaper,2).' '.$GLOBALS['__template']->getVar('shop-currency',2);
			}
		elseif($output['action']=='freemoreexpensive') {
			$expensive=0;
			foreach($this->getCart() as $item) {
				if($expensive<$item['realprice']) $expensive=$item['realprice'];
				}
			$output['value']=number_format($expensive,2).' '.$GLOBALS['__template']->getVar('shop-currency',2);
			}
		elseif($output['action']=='usediscountprices') {
			$discount=0;
			foreach($this->getCart() as $item) {
				$discount+=($item['realprice']-$item['scontato'])*$item['qty'];
				}
			$output['value']=number_format($discount,2).' '.$GLOBALS['__template']->getVar('shop-currency',2);
			}

		
		$output['isValid']=true;
		
		if(!$this->checkCouponValidity($output)) {
			$output['isValid']=false;
			$output['value']="Coupon is not valid";
			}
		return $output;
		}

	//check if a given copon is actually valid
	public function checkCouponValidity($c) {
		$price=$this->getCartItemsPrice();

		//check if coupon was never used
		if($c['valid']==0) return false;

		//check if coupon is applicable
		if(substr($c['context'],0,1)==">"&&$price<floatval(substr($c['context'],1))) return false;
		elseif(substr($c['context'],0,2)=="#>"&&$this->getCartItemsCount()<intval(substr($c['context'],2))) return false;

		//check if it is expired or not active yet
		$starting=mktime(substr($c['starting_date'],11,2),substr($c['starting_date'],14,2),substr($c['starting_date'],17,2),substr($c['starting_date'],5,2),substr($c['starting_date'],8,2),substr($c['starting_date'],0,4));
		$expiration=mktime(substr($c['expiration_date'],11,2),substr($c['expiration_date'],14,2),substr($c['expiration_date'],17,2),substr($c['expiration_date'],5,2),substr($c['expiration_date'],8,2),substr($c['expiration_date'],0,4));
		if($starting>time()||$expiration<time()) return false; //coupon expired
		
		return true;
		}

	public function couponsMarkAsUsed($coupons) {
		if(!is_array($coupons)) $coupons=array($coupons);
		foreach($coupons as $code) {
			$query="UPDATE `".TABLE_SHOP_COUPONS_CODES."` SET `valid`=0 WHERE `code`='".mysql_real_escape_string($code)."' LIMIT 1";
			mysql_query($query);
			}
		return true;
		}

		
	/* CART */
	public function getCart() {
		if(!$this->inited) $this->init();
		if(isset($_SESSION['shop']['cart'])) {
			$output=array();
			foreach($_SESSION['shop']['cart'] as $idcart=>$qty) {
				$id=count($output);
				
				//the id is composed in this way: idsitem-idsvar-idsvar-idsvar...
				$idsvars=explode("-",$idcart);
				$idsitem=$idsvars[0];
				unset($idsvars[0]);
				
				$output[$id]=$this->getItemById($idsitem);
				$output[$id]['id']=$idcart; //the id as saved on the cart
				$variations=$output[$id]['variations'];
				$output[$id]['variations']=array();
				foreach($idsvars as $idsvar) {
					foreach($variations as $collection=>$v) {
						foreach($v as $var) {
							if($var['idsvar']==$idsvar) $output[$id]['variations'][]=$var;
							}
						}
					}
				$output[$id]['qty']=$qty;
				
				/* price calc */
				$output[$id]['realprice']=$output[$id]['prezzo'];
				if(kGetVar('shop-discount',1)=='always') $output[$id]['realprice']=$output[$id]['scontato']>0?$output[$id]['scontato']:$output[$id]['prezzo'];
				elseif(kGetVar('shop-discount',1)=='qty') {
					if(kGetVar('shop-discount',2)<=$this->getCartItemsCount()) $output[$id]['realprice']=$output[$id]['scontato']>0?$output[$id]['scontato']:$output[$id]['prezzo'];
					}
				}
			return $output;
			}
		else return array();
		}
	public function getCartItemsPrice() {
		$totalprice=0;
		foreach($this->getCart() as $item) {
			$totalprice+=$item['qty']*$item['realprice'];
			}
		return $totalprice;
		}
	public function getCartShippingPrice($iddel=false,$country=false) {
		if(!$this->inited) $this->init();
		if($iddel==false) $iddel=$this->getCartVar('deliverer');
		if($country==false) $country=$this->getCartVar('del_Country');
		$zone=4;
		foreach($this->getCountries() as $c) {
			if($c['ll']==$country) $zone=$c['zone'];
			}
		$this->setDelivererById($iddel);
		$totalweight=0;
		foreach($this->getCart() as $item) {
			$totalweight+=floatval($item['weight'])*$item['qty'];
			}
		return $this->getDelivererPriceByKg($totalweight,$zone);
		}
	public function getCartPaymentPrice($totalamount=false,$idspay=false,$iddel=false,$country=false) {
		if(!$this->inited) $this->init();
		if($idspay==false) $idspay=$this->getCartVar('payment');
		if($iddel==false) $iddel=$this->getCartVar('deliverer');
		if($country==false) $country=$this->getCartVar('del_Country');
		if($totalamount==false) $totalamount=$this->getCartItemsPrice()+$this->getCartShippingPrice($iddel,$country);
		$payment_method=$this->getPaymentById($idspay);
		return $payment_method['price']+($totalamount*$payment_method['pricepercent']/100);
		}
	public function getCartTotalPrice($vars=false,$iddel=false,$country=false) {
		if(!$this->inited) $this->init();
		if(!is_array($vars)) {
			$vars=array("idspay"=>$vars);
			$vars['iddel']=$iddel;
			$vars['country']=$country;
			}
		if(!isset($vars['country']))  return false;
		if(!isset($vars['iddel'])) $vars['iddel']=false;
		if(!isset($vars['idspay'])) $vars['idspay']=false;
		if(!isset($vars['coupons'])) $vars['coupons']=array();
		$price=$this->getCartItemsPrice();
		$shippingprice=$this->getCartShippingPrice($vars['iddel'],$vars['country']);
		$paymentprice=$this->getCartPaymentPrice($price,$vars['idspay']);
		
		/* coupons discounts */
		$discount=0;
		foreach($vars['coupons'] as $code) {
			$c=$this->getCouponByCode($code);
			if($c==false) continue;

			if($c['isValid']==false) continue;
			
			//apply the action
			if(substr($c['action'],0,9)=='discount=') {
				$dval=substr($c['action'],9);
				$discount+=floatval($dval);
				}
			elseif(substr($c['action'],0,16)=='discountpercent=') {
				$dval=substr($c['action'],16);
				$discount+=round($price/100*floatval($dval),2);
				}
			elseif($c['action']=='freeshipping') {
				$discount+=$shippingprice;
				}
			elseif($c['action']=='freecheaper') {
				$cheaper=-1;
				foreach($this->getCart() as $item) {
					if($cheaper==-1||$cheaper>$item['realprice']) $cheaper=$item['realprice'];
					}
				$discount+=$cheaper;
				}
			elseif($c['action']=='freemoreexpensive') {
				$expensive=0;
				foreach($this->getCart() as $item) {
					if($expensive<$item['realprice']) $expensive=$item['realprice'];
					}
				$discount+=$expensive;
				}
			elseif($c['action']=='usediscountprices') {
				foreach($this->getCart() as $item) {
					$discount+=($item['realprice']-$item['scontato'])*$item['qty'];
					}
				}
			}

		$price=$price+$paymentprice+$shippingprice-$discount;
		return $price;
		}

	public function addItemToCart($idsitem,$qty=1,$variations=array()) {
		$id=$idsitem;
		asort($variations);
		foreach($variations as $idsvar) {
			$id.="-".$idsvar;
			}
		if(!isset($_SESSION['shop'])) $_SESSION['shop']=array();
		if(!isset($_SESSION['shop']['cart'])) $_SESSION['shop']['cart']=array();
		if(!isset($_SESSION['shop']['cart'][$id])) $_SESSION['shop']['cart'][$id]=0;
		$_SESSION['shop']['cart'][$id]+=$qty;
		}
	public function removeItemFromCart($idsitem,$qty=1,$variations=array()) {
		$id=$idsitem;
		asort($variations);
		foreach($variations as $idsvar) {
			$id.="-".$idsvar;
			}
		if(!isset($_SESSION['shop']['cart'][$id])) $_SESSION['shop']['cart'][$id]=0;
		if($_SESSION['shop']['cart'][$id]>1) $_SESSION['shop']['cart'][$id]-=$qty;
		else unset($_SESSION['shop']['cart'][$id]);
		}
	public function emptyCart() {
		if(isset($_SESSION['shop']['cart'])) $_SESSION['shop']['cart']=array();
		}
	public function setCartVar($param,$value) {
		if(!isset($_SESSION['shop'])) $_SESSION['shop']=array();
		if(!isset($_SESSION['shop']['data'])) $_SESSION['shop']['data']=array();
		$_SESSION['shop']['data'][$param]=$value;
		}
	public function getCartVar($param) {
		if(isset($_SESSION['shop']['data'][$param])) return $_SESSION['shop']['data'][$param];
		else return "";
		}
	public function getCartItemsCount() {
		$c=0;
		if(isset($_SESSION['shop']['cart'])) {
			foreach($_SESSION['shop']['cart'] as $items) {
				$c+=$items;
				}
			}
		return $c;
		}

		
	/**************************************/
	/* CHECK IF ORDER IS VALID            */
	/**************************************/
	/*
	FIELDS FOR ORDERS
	---- personal data
	[customer]	[name]
				[email]
				[phone]
				[address]
				[city]
				[zipcode]
				[country] <-- code
				[...]
	---- shipping data (if missing, personal data will be used)
	[delivery]	[name]
				[email]
				[phone]
				[address]
				[city]
				[zipcode]
				[country] <-- code
				[carrier] <-- id (if missing, the first one will be used)
				[...]
	---- invoice data (if missing, personal data will be used)
	[payment]	[name]
				[idnumber]
				[vat]
				[email]
				[phone]
				[address]
				[city]
				[zipcode]
				[country] <-- code
				[method] <-- id (if missing, the first one will be used)
				[...]
	---- others
	[coupons] <-- array of codes
	*/
	public function checkOrderValidity($vars) {
		if(!$this->inited) $this->init();
		//check if cart is empty
		if($this->getCartItemsCount()==0) return array("code"=>"000","description"=>"Your cart is empty");
		
		//check personal data
		if(!isset($vars['customer'])) return array("code"=>"100","description"=>"Customer's data is missing");
		if(!isset($vars['customer']['name'])) return array("code"=>"101","description"=>"Customer's name is missing");
		if(!isset($vars['customer']['email'])) return array("code"=>"102","description"=>"Customer's e-mail is missing");
		if(!isset($vars['customer']['phone'])) return array("code"=>"103","description"=>"Customer's phone is missing");
		if(!isset($vars['customer']['address'])) return array("code"=>"104","description"=>"Customer's address is missing");
		if(!isset($vars['customer']['city'])) return array("code"=>"105","description"=>"Customer's city is missing");
		if(!isset($vars['customer']['zipcode'])) return array("code"=>"106","description"=>"Customer's zip code is missing");
		if(!isset($vars['customer']['country'])) return array("code"=>"107","description"=>"Customer's country is missing");
		if($vars['customer']['country']=="") return array("code"=>"108","description"=>"Customer's country can't be empty");
		//check e-mail syntax
		if(!preg_match("/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i",$vars['customer']['email'])) return array("code"=>"150","description"=>"Invalid e-mail address");
		
		//check if countries are availables
		$countries=",";
		foreach(kGetShopCountries() as $c) { $countries.=$c['ll'].","; }
		if(strpos($countries,','.$vars['customer']['country'].',')===false) return array("code"=>"151","description"=>"Invalid customer's country");
		if(isset($vars['delivery']['country'])&&strpos($countries,','.$vars['delivery']['country'].',')===false) return array("code"=>"152","description"=>"Invalid delivery country");
		if(isset($vars['payment']['payment'])&&strpos($countries,','.$vars['payment']['country'].',')===false) return array("code"=>"153","description"=>"Invalid invoice country");
		
		//check if there is at least one valid carrier
		if(!isset($vars['delivery']['country'])||$vars['delivery']['country']=="") $vars['delivery']['country']=$vars['customer']['country'];
		$zone=$this->getZoneByCountry($vars['delivery']['country']);
		$carriers=$this->getDeliverersByZone($zone);
		if(!isset($carriers[0])) return array("code"=>"200","description"=>"No carriers available for this zone");
		//check if selected carrier is valid
		if(isset($vars['delivery']['carrier'])&&$vars['delivery']['carrier']!="") {
			$valid=false;
			foreach($carriers as $c) {
				if($c['iddel']==$vars['delivery']['carrier']) $valid=true;
				}
			if($valid==false) return array("code"=>"201","description"=>"Invalid country");
			}

		//check if there is at least one valid payment method
		if(!isset($vars['payment']['country'])||$vars['payment']['country']=="") $vars['payment']['country']=$vars['customer']['country'];
		$zone=$this->getZoneByCountry($vars['payment']['country']);
		$payments=$this->getPaymentsByZone($zone);
		if(!isset($payments[0])) return array("code"=>"300","description"=>"No payment method available for this zone");
		//check if selected method is valid
		if(isset($vars['payment']['method'])&&$vars['payment']['method']!="") {
			$valid=false;
			foreach($payments as $c) {
				if($c['idspay']==$vars['payment']['method']) $valid=true;
				}
			if($valid==false) return array("code"=>"301","description"=>"Invalid payment method");
			}
		
		//check if the coupon is valid
		if(isset($vars['coupon'])&&!is_array($vars['coupon'])) $vars['coupon']=array($vars['coupon']);
		if(isset($vars['coupon'])&&count($vars['coupon'])>0) {
			foreach($vars['coupon'] as $code) {
				$coupon=$this->getCouponByCode($code);
				if(!$this->checkCouponValidity($coupon)) return array("code"=>"401","description"=>"The coupon code is not valid");
				}
			}

		return false;
		}


	/*************************************/
	/* SAVE ORDER AND SEND NOTIFICATIONS */
	/*************************************/
	public function saveOrder($vars,$emptycart=true) {
		if(!$this->inited) $this->init();
		global $__config;
		global $__template;
		global $__emails;
		
		if(!isset($__config)) $__config=new kImpostazioni();
		if(!isset($__template)) $__template=new kTemplate();
		if(!isset($__emails)) $__emails=new kEmails();
		
		$log="";
		
		/* check validity */
		$log=$this->checkOrderValidity($vars);
		if($log!=false) {
			trigger_error($log);
			return false;
			}
		
		/* fill required fields */
		foreach(array("name","email","phone","address","city","zipcode","country") as $var) {
			if(!isset($vars['delivery'][$var])) $vars['delivery'][$var]=$vars['customer'][$var];
			if(!isset($vars['payment'][$var])) $vars['payment'][$var]=$vars['customer'][$var];
			}
		
		/* search for member id */
		$idmember=0;
		if(isset($_SESSION['member']['idmember'])) {
			$idmember=$_SESSION['member']['idmember'];
			}
		else {
			//if member doesn't exists, try to create a new one
			$name=$vars['customer']['name'];
			$username=preg_replace('/[^[a-zA-Z]]/','-',strtolower(str_replace(" ","",$name)));
			$email=$vars['customer']['email'];
			$idm=kMemberRegister($username,false,$name,$email);
			//if just exists a user with the same username, try with a different username for 10 times
			$i=0;
			while($idm==false&&$i<10) {
				$username.=rand(0,10);
				$idm=kMemberRegister($username,false,$name,$email);
				$i++;
				}
			$u=kGetMemberById($idm);
			if(isset($u['username'])) {
				$idmember=$u['idmember'];
				kMemberLogIn($u['username'],$u['password']);
				}
			else {
				trigger_error('Error creating your user');
				return false;
				}
			}

		/* unique id generation */
		$uid=$this->uidGenerator($idmember);

		/* item list */
		$items=",";
		$itemsprice=0;
		foreach($this->getCart() as $item) {
			$items.=$item['id'].":".$item['qty'].":".$item['realprice'].",";
			$itemsprice+=$item['realprice'];
			}

		/* idzone */
		$country=$vars['delivery']['country'];
		$idzone=4;
		foreach($this->getCountries() as $c) {
			if($c['ll']==$country) $idzone=$c['zone'];
			}

		/* deliverer */
		$iddel=$vars['delivery']['carrier'];
		if($iddel=="") {
			$carriers=$this->getDeliverersByZone($idzone);
			$iddel=$carriers[0]['iddel'];
			}
		if($iddel=="") {
			trigger_error('Missing carrier');
			return false;
			}
		$deliverer=$this->getDelivererById($iddel);

		/* payments */
		$idspay=$vars['payment']['method'];
		if($idspay=="") {
			$payments=$this->getPaymentsByZone($idzone);
			$idspay=$payments[0]['idspay'];
			}
		if($idspay=="") {
			trigger_error('Missing payment method');
			return false;
			}
		$payment_method=$this->getPaymentById($idspay);

		/* recalculate price */
		$price=$this->getCartTotalPrice($idspay,$iddel,$country);

		/* personal data */
		if(!isset($vars['customer']['email'])) {
			//se manca l'e-mail, ferma tutto
			trigger_error('Missing e-mail address');
			return false;
			}
		$personal_data="";
		foreach($vars['customer'] as $k=>$v) {
			$personal_data.="<".$k.">".$v."</".$k.">\n";
			}

		/* invoice data */
		$invoice_data="";
		foreach($vars['payment'] as $k=>$v) {
			$invoice_data.="<".$k.">".$v."</".$k.">\n";
			}

		/* shipping data */
		$shipping_data="";
		foreach($vars['delivery'] as $k=>$v) {
			$shipping_data.="<".$k.">".$v."</".$k.">\n";
			}

		/* generate mail, and insert order summary into mail body */
		$tmp=$__config->getParam("shop-mail_checkout");
		$tmp['address']=$vars['customer']['address']."<br />\n".$vars['customer']['zipcode']." ".$vars['customer']['city']." (".$vars['customer']['country'].")";

		$tmp['items']="<table><tr><th>".$__template->translate('Item')."</th><th>".$__template->translate('Price')."</th><th>".$__template->translate('Qty')."</th></tr>";
		foreach($this->getCart() as $item) {
			$tmp['items'].="<tr>";
			$tmp['items'].="<td>".$item['titolo']."</td>";
			$tmp['items'].="<td>".$item['realprice'].' '.$GLOBALS['__template']->getVar('shop-currency',2)."</td>";
			$tmp['items'].="<td>".$item['qty']."</td>";
			$tmp['items'].="</tr>";
			}
		$tmp['items'].="</table>";

		$tmp['shipping_address']=$vars['delivery']['address']."<br />\n".$vars['delivery']['zipcode']." ".$vars['delivery']['city']." (".$vars['delivery']['country'].")";
		$tmp['billing_data']=$vars['payment']['address']."<br />\n".$vars['payment']['zipcode']." ".$vars['payment']['city']." (".$vars['payment']['country'].")";
		$tmp['payment_method']='<strong>'.$payment_method['name'].'</strong>'.$payment_method['mail_instructions'];

		$mail=array();
		$mail['from']=$__config->getParam("shop-mail_from");
		if($mail['from']['value2']!="") $mail['from']=$mail['from']['value1'].' <'.$mail['from']['value2'].'>';
		else $mail['from']=ADMIN_NAME.' <'.ADMIN_MAIL.'>';
		$mail['to']=$vars['customer']['name'].' <'.$vars['customer']['email'].'>';
		$mail['subject']=$tmp['value2'];
		$mail['subject']=str_replace("{NAME}",$vars['customer']['name'],$mail['subject']);
		$mail['subject']=str_replace("{ORDER_NUMBER}",$uid,$mail['subject']);
		$mail['message']=$tmp['value1'];
		$mail['message']=str_replace("{NAME}",$vars['customer']['name'],$mail['message']);
		$mail['message']=str_replace("{USERNAME}",kGetMemberUsername(),$mail['message']);
		$mail['message']=str_replace("{PASSWORD}",kGetMemberPassword(),$mail['message']);
		$mail['message']=str_replace("{ADDRESS}",$tmp['address'],$mail['message']);
		$mail['message']=str_replace("{ORDER_NUMBER}",$uid,$mail['message']);
		$mail['message']=str_replace("{ORDER_ITEMS}",$tmp['items'],$mail['message']);
		$mail['message']=str_replace("{BILLING_DATA}",$tmp['billing_data'],$mail['message']);
		$mail['message']=str_replace("{DELIVERER}",$deliverer['name'],$mail['message']);
		$mail['message']=str_replace("{SHIPPING_ADDRESS}",$tmp['shipping_address'],$mail['message']);
		$mail['message']=str_replace("{PAYMENT_METHOD}",$tmp['payment_method'],$mail['message']);
		$mail['message']=str_replace("{TRACKING_NUMBER}","",$mail['message']);
		$mail['message']=str_replace("{PAYMENT_PRICE}",number_format($this->getCartPaymentPrice($price,$idspay,$iddel,$country),2).' '.$GLOBALS['__template']->getVar('shop-currency',1),$mail['message']);
		$mail['message']=str_replace("{SHIPPING_PRICE}",number_format($this->getCartShippingPrice($iddel,$country),2).' '.$GLOBALS['__template']->getVar('shop-currency',1),$mail['message']);
		$mail['message']=str_replace("{ORDER_PRICE}",number_format($price,2).' '.$GLOBALS['__template']->getVar('shop-currency',1),$mail['message']);

		/* save order */
		$query="INSERT INTO `".TABLE_SHOP_ORDERS."` (`uid`, `ip`, `date`, `items`, `idmember`, `personal_data`, `invoice_data`, `shipping_address`, `idzone`, `deliverer`, `iddel`, `payment_method`, `idspay`, `notes`, `status`, `totalprice`, `payed`, `payedon`, `idstrans`, `shipped`, `shippedon`, `tracking_number`, `tracking_url`, `order_summary`, `ll`) VALUES ('".$uid."', '".$_SERVER['REMOTE_ADDR']."', NOW(), '".$items."', '".$idmember."', '".mysql_real_escape_string($personal_data)."', '".mysql_real_escape_string($invoice_data)."', '".mysql_real_escape_string($shipping_data)."', '".intval($idzone)."', '".mysql_real_escape_string($deliverer['name'])."', '".intval($iddel)."', '".mysql_real_escape_string($payment_method['name'])."', '".intval($idspay)."', '', 'OPN', '".$price."', 'n', '0000-00-00', '', 'n', '0000-00-00', '', '', '".mysql_real_escape_string($mail['message'])."', '".LANG."')";
		if(!mysql_query($query)) {
			trigger_error('Error while saving order into database');
			return false;
			}

		/* send notifications */
		if(trim(strip_tags($mail['message'],"<img>"))!="") {
			$__emails->send($mail['from'],$mail['to'],$mail['subject'],$mail['message']);
			$__emails->send($mail['to'],$mail['from'],$mail['subject'],$mail['message']); //to admin
			}

		/* subtract quantities from items */
		//TODO

		/* empty cart */
		if($emptycart==true) $this->emptyCart();

		/* return */
		return $uid;
		}

	public function getOrders($vars) {
		if(!$this->inited) $this->init();
		$output=array();
		if(!isset($vars['orderby'])) $vars['orderby']="`date` DESC";
		$query="SELECT * FROM `".TABLE_SHOP_ORDERS."` WHERE uid<>'' ";
		if(isset($vars['idmember'])) $query.=" AND `idmember`='".mysql_real_escape_string($vars['idmember'])."'";
		$query.=" ORDER BY ".$vars['orderby'];
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$id=count($output);
			$output[$id]=$this->ordersRow2output($row);
			}
		return $output;
		}	
	public function getOrderByNumber($uid) {
		if(!$this->inited) $this->init();
		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_ORDERS."` WHERE uid='".mysql_real_escape_string($uid)."' ORDER BY `date` DESC LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			$output=$this->ordersRow2output($row);
			}
		return $output;
		}

	/* converts database rows into an array with all informations about an order */
	public function ordersRow2output($row) {
		if(!$this->inited) $this->init();
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR.'inc/utenti.lib.php');
		$kMembers=new kMembers();
		$output=$row;
		$output['friendlydate']=preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 $4:$5",$row['date']);
		if($row['idmember']>0) $output['member']=$kMembers->getById($row['idmember']);
		$tmp=$this->getPaymentById($row['idspay']);
		if(isset($tmp['idspay'])) $output['payment_method']=$tmp;
		else $output['payment_method']=array('name'=>$output['payment_method']);
		$tmp=$this->getDelivererById($row['iddel']);
		if(isset($tmp['iddel'])) $output['deliverer']=$tmp;
		else $output['deliverer']=array('name'=>$output['deliverer']);
		$output['transactions']=$this->getTransactionsByOrderId($row['idord']);
		$output['items']=array();
		foreach(explode(",",trim($row['items'],",")) as $item) {
			$id=count($output['items']);
			list($idsitem,$qta,$price)=explode(":",$item);
			$output['items'][$id]=$this->getItemById($idsitem);
			$output['items'][$id]['qta']=$qta;
			$output['items'][$id]['realprice']=$price;
			}
		return $output;
		}
	
	public function setOrderByNumber($uid) {
		if(!$this->inited) $this->init();
		$this->orderDB=$this->getOrderByNumber($uid);
		}
	public function getOrderVar($param) {
		if(!$this->inited) $this->init();
		return $this->orderDB[$param];
		}

	public function getTransactionsByOrderId($idord) {
		if(!$this->inited) $this->init();
		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_TRANSACTIONS."` WHERE idord='".intval($idord)."' ORDER BY `date`";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$id=count($output);
			$output[$id]=$row;
			$output[$id]['friendlydate']=preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 $4:$5",$output[$id]['date']);
			}
		return $output;
		}
	public function tnxIdExists($txn_id) {
		$query="SELECT * FROM `".TABLE_SHOP_TRANSACTIONS."` WHERE details LIKE 'txn_id=\'".mysql_real_escape_string($txn_id)."\'' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if($row['idtrans']!="") return true;
		else return false;
		}

	private function uidGenerator($prefix,$length=8) {
		if(strlen($prefix)>$length-2) $prefix=substr($prefix,0,$length-2);
		$uid=strtoupper(uniqid());
		$uid=$prefix.substr($uid,-strlen($prefix)-$length);
		if(round(rand(0,1))==1) $uid=substr($uid,0,8);
		else $uid=substr($uid,-8);
		return $uid;
		}

	public function addPayment($uid,$value,$idspay,$details,$currency=false) {
		if(!$this->inited) $this->init();
		if($value>0) {
			kSetShopOrderByNumber($uid);
			$o=kGetShopOrderByNumber($uid);
			$idord=$this->getOrderVar('idord');
			$p=$this->getPaymentById($idspay);
			if($idord=="") return false;
			if($currency==false) $currency=kGetShopCurrency("symbol");
			$query="INSERT INTO `".TABLE_SHOP_TRANSACTIONS."` (`idord`,`date`,`value`,`currency`,`idspay`,`method`,`details`) VALUES('".$idord."',NOW(),'".number_format($value,2)."','".b3_htmlize($currency,true,"")."','".$p['idspay']."','".b3_htmlize($p['name'],true,"")."','".b3_htmlize($details,true,"")."')";
			if(!mysql_query($query)) return false;
			
			$totalamount=kGetShopOrderTotalAmount();
			if($totalamount<=$value) {
				$query="UPDATE `".TABLE_SHOP_ORDERS."` SET payed='s' WHERE `idord`='".intval($idord)."' LIMIT 1";
				if(!mysql_query($query)) return false;
				
				$dirlist=array();
				foreach($o['items'] as $items) {
					foreach($items['privatearea'] as $dir) {
						if($items['privatearea']!="") $dirlist[$dir]=true;
						}
					}
				/* grant access to private area */
				foreach($dirlist as $dir=>$true) {
					$p=$GLOBALS['__private']->getPermissions($dir);
					if($p['permissions']=='restricted') {
						$armembers=array($o['idmember']=>true);
						foreach($p['members'] as $m) {
							$armembers[$m['idmember']]=true;
							}
						$GLOBALS['__private']->setPermissions($dir,'restricted',$armembers);
						}
					}
				
				}
			if($o['member']['email']!="") $this->sendEmail('payed',$uid);
			return true;
			}
		}
	
	function sendEmail($type,$uid) {
		if(!$this->inited) $this->init();
		if(strip_tags(trim(kGetVar('shop-mail_'.$type,1)))!="") {
			$o=kGetShopOrderByNumber($uid);
			$mail=array();
			$mail['from']=kGetVar('shop-mail_from',1).' <'.kGetVar('shop-mail_from',2).'>';
			if(trim($mail['from']," <>")=="") $mail['from']="";
			$mail['to']=$o['member']['name'].' <'.$o['member']['email'].'>';
			
			//we need a country: get the first country of the zone
			$countries=getCountries($o['idzone']);

			$mail['subject']=kGetVar('shop-mail_'.$type,2);
			$mail['subject']=str_replace("{NAME}",$o['member']['name'],$mail['subject']);
			$mail['subject']=str_replace("{ORDER_NUMBER}",$o['uid'],$mail['subject']);

			$mail['message']=kGetVar('shop-mail_'.$type,1);
			$mail['message']=str_replace("{NAME}",$o['member']['name'],$mail['message']);
			$mail['message']=str_replace("{EMAIL}",$o['member']['email'],$mail['message']);
			$mail['message']=str_replace("{USERNAME}",$o['member']['username'],$mail['message']);
			$mail['message']=str_replace("{PASSWORD}",$o['member']['password'],$mail['message']);
			$mail['message']=str_replace("{ADDRESS}",$o['member']['Address'].'<br />'.$o['member']['ZipCode'].' '.$o['member']['City'],$mail['message']);
			$mail['message']=str_replace("{DELIVERER}",$o['deliverer'],$mail['message']);
			$mail['message']=str_replace("{TRACKING_URL}",$o['tracking_url'],$mail['message']);
			$mail['message']=str_replace("{TRACKING_NUMBER}",$o['tracking_number'],$mail['message']);
			$mail['message']=str_replace("{SHIPPING_ADDRESS}",nl2br(strip_tags($o['shipping_address'])),$mail['message']);
			$mail['message']=str_replace("{PAYMENT_METHOD}",$o['payment_method'],$mail['message']);
			$mail['message']=str_replace("{ORDER_ITEMS}","",$mail['message']);
			$mail['message']=str_replace("{ORDER_NUMBER}",$o['uid'],$mail['message']);
			$mail['message']=str_replace("{BILLING_DATA}",nl2br(strip_tags($o['invoice_data'])),$mail['message']);
			$mail['message']=str_replace("{ORDER_PRICE}",$o['totalprice'],$mail['message']);
			$mail['message']=str_replace("{PAYMENT_PRICE}",number_format($this->getCartPaymentPrice($o['totalprice'],$o['idspay'],$o['iddel'],$countries[0]['ll']),2).' '.$GLOBALS['__template']->getVar('shop-currency',1),$mail['message']);
			$mail['message']=str_replace("{SHIPPING_PRICE}",number_format($this->getCartShippingPrice($o['iddel'],$countries[0]['ll']),2).' '.$GLOBALS['__template']->getVar('shop-currency',1),$mail['message']);
			
			$GLOBALS['__emails']->send($mail['from'],$mail['to'],$mail['subject'],$mail['message']);
			}
		}

	}

?>
