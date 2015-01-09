<?php 
/* visualizzazione dello shop */
class kShop {
	protected 	$inited,
				$cats,
				$allowedcats,
				$allthecats,
				$kText,
				$imgs,
				$imgallery,
				$docgallery,
				$loadedItem,
				$delivererDB,
				$paymentDB,
				$payPalBusinessId,
				$virtualPayBusinessId,
				$virtualPayABI,
				$virtualPayKEY,
				$orderDB,
				$customFields,
				$zone,
				$coupons,
				$loadedManufacturer;

	public function __construct()
	{
		$this->inited=false;
	}
	
	public function init()
	{
		$this->inited=true;
		global $__template;
		global $__users;
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."admin/inc/main.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/images.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/documents.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/kalamun.lib.php");
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/utenti.lib.php");
		$this->kText=new kText();
		$this->imgs=new kImages();
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
		if($tmp!="*")
		{
			foreach(explode(",",trim($tmp,",")) as $cat)
			{
				$this->allowedcats[]=$cat;
			}
		}
		$query="SELECT * FROM ".TABLE_CATEGORIE." WHERE `tabella`='".TABLE_SHOP_ITEMS."' AND `ll`='".ksql_real_escape_string(LANG)."' ORDER BY `ref`,`ordine`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			$row['categoria']=mb_convert_encoding($row['categoria'],"UTF-8");
			$this->allthecats[$row['idcat']]=$row;
			$this->allthecats[$row['idcat']]['permalink']=BASEDIR.$GLOBALS['__template']->getLanguageURI(LANG).$__template->getVar('dir_shop',1).'/'.$row['dir'];

			// get photogallery in the correct order
			$this->allthecats[$row['idcat']]['imgs']=array();
			if(trim($row['photogallery'],",")!="" )
			{
				$conditions="";
				foreach(explode(",",trim($row['photogallery'],",")) as $idimg)
				{
					$conditions.="`idimg`='".intval($idimg)."' OR ";
				}
				$conditions.="`idimg`='0'";
				
				$imgs=$GLOBALS['__images']->getList(false,false,false,$conditions);
				
				foreach(explode(",",trim($row['photogallery'],",")) as $idimg)
				{
					foreach($imgs as $img)
					{
						if($img['idimg']==$idimg) $this->allthecats[$row['idcat']]['imgs'][]=$img;
					}
				}
			}

			if($tmp=="*") $this->allowedcats[$row['idcat']]=true;
			if(array_search($row['idcat'],$this->allowedcats)!==false) $this->cats[$row['idcat']]=true;
		}
		unset($tmp);

		$this->customFields=array();
		$query="SELECT * FROM ".TABLE_SHOP_CUSTOMFIELDS." WHERE `categories`='' ";
		foreach($this->allthecats as $cat)
		{
			$query.=" OR `categories` LIKE '%".$cat['idcat']."%' ";
		}
		$query.=" ORDER BY `order`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
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

		$query="SELECT * FROM `".TABLE_SHOP_ITEMS."` WHERE (`dir`='".b3_htmlize($dir[2],true,"")."' OR `dir`='".ksql_real_escape_string($dir[2])."') AND ll='".ksql_real_escape_string(strtoupper($ll))."' AND online='y' ";
		if(!isset($_GET['preview'])||$_GET['preview']!=md5(ADMIN_MAIL)) $query.="AND `public`<=NOW() ";
		//if($expired=="nascondi") $query.="AND expired<=NOW() ";
		$query.=" LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) return true;
		else return false;
		}

	public function shopManufacturerExists($dir=null,$ll=null) {
		if($ll==null) $ll=LANG;
		if($dir==null) $dir=array($GLOBALS['__dir__'],$GLOBALS['__subdir__'],$GLOBALS['__subsubdir__']);
		else $dir=explode("/",$dir);
		if($dir[0]!=kGetVar('dir_shop')) return false;
		if(!isset($dir[1])) $dir[1]="";
		if($dir[1]!=kGetVar('dir_shop_manufacturers')) return false;
		if(!isset($dir[2])) $dir[2]="";

		$query="SELECT * FROM `".TABLE_SHOP_MANUFACTURERS."` WHERE (`dir`='".b3_htmlize($dir[2],true,"")."' OR `dir`='".ksql_real_escape_string($dir[2])."') AND ll='".ksql_real_escape_string(strtoupper($ll))."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) return true;
		else return false;
		}

	public function shopCartExists($dir=null,$ll=null) {
		if($ll==null) $ll=LANG;
		if($dir==null) $dir=array($GLOBALS['__dir__'],$GLOBALS['__subdir__'],$GLOBALS['__subsubdir__']);
		else $dir=explode("/",$dir);
		if($dir[0]!=kGetVar('dir_shop')) return false;
		if(!isset($dir[1])) $dir[1]="";
		if($dir[1]!=kGetVar('dir_shop_cart')) return false;
		return true;
		}

	public function getCountries($zone=false) {
		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_COUNTRIES."` ";
		if($zone!=false) $query.="WHERE `zone`='".intval($zone)."' ";
		$query.="ORDER BY `country`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$output[]=$row;
			}
		return $output;
		}
	public function getZoneByCountry($country) {
		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_COUNTRIES."` WHERE `ll`='".ksql_real_escape_string($country)."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
			return $row['zone'];
			}
		return false;
		}
	public function getPaymentsByZone($zone,$ll=null) {
		if($ll==null) $ll=LANG;
		$output=array();
		$query="SELECT * FROM ".TABLE_SHOP_PAYMENTS." WHERE zones LIKE '%,".intval($zone).",%' AND ll='".$ll."' ORDER BY ordine";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$output[]=$row;
			}
		return $output;
		}
	public function getPaymentsByCountryCode($ll) {
		return $this->getPaymentsByZone($this->getZoneByCountry($ll));
		}
	public function getPaymentById($idspay) {
		$query="SELECT * FROM ".TABLE_SHOP_PAYMENTS." WHERE idspay='".ksql_real_escape_string($idspay)."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
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
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$output[]=$row;
			}
		return $output;
		}
	public function getDeliverersByCountryCode($ll) {
		return $this->getDeliverersByZone($this->getZoneByCountry($ll));
		}
	public function getDelivererById($iddel) {
		$query="SELECT * FROM ".TABLE_SHOP_DELIVERERS." WHERE iddel='".ksql_real_escape_string($iddel)."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
			$output=$row;
			$output['prices']=array();
			$query="SELECT * FROM ".TABLE_SHOP_DEL_PRICES." WHERE iddel='".ksql_real_escape_string($iddel)."' ORDER BY maxweight";
				$results=ksql_query($query);
					while($row=ksql_fetch_array($results)) {
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

	public function countItems($vars) {
		if(!$this->inited) $this->init();
		if(!isset($vars['category']))
		{
			if($this->shopExists()==true&&!$this->shopManufacturerExists()&&!$this->shopCartExists()&&$GLOBALS['__subdir__']!="") $vars['category']=$GLOBALS['__subdir__'];
			else $vars['category']='*';
		}
		if($vars['category']!='*' && !is_array($vars['category'])) $vars['category']=array($vars['category']);
		if(!isset($vars['category_operator'])) $vars['category_operator']='OR';
		$vars['category_operator']=strtoupper($vars['category_operator']);

		if(!isset($vars['expired'])) $vars['expired']=$GLOBALS['__template']->getVar('shop-order',2);
		if(!isset($vars['ll'])||$vars['ll']=="") $vars['ll']=LANG;

		$query="SELECT count(`idsitem`) AS `tot` FROM `".TABLE_SHOP_ITEMS."` WHERE ll='".$vars['ll']."' AND `online`='y' AND `public`<=NOW() ";
		if(isset($vars['expired'])&&$vars['expired']=="nascondi") $query.="AND `expired`<NOW() ";
		if(isset($vars['conditions'])&&$vars['conditions']!="") $query.="AND (".$vars['conditions'].") ";
		if(isset($vars['manufacturer'])&&$vars['manufacturer']!="") $query.=" AND `manufacturer`='".intval($vars['manufacturer'])."' ";
		if($vars['ll']==LANG)
		{
			if(count($this->cats)>0&&$vars['category']!="*")
			{
				if($vars['category_operator']=='OR') $query.="AND (categorie=',' ";
				else $query.="AND (categorie!='' ";
				foreach($vars['category'] as $category)
				{
					foreach($this->cats as $cat=>$true)
					{
						if($category==$cat||$category==$this->allthecats[$cat]['dir']||$category==b3_htmlize($this->allthecats[$cat]['dir'],true,"")) $query.=$vars['category_operator']." categorie LIKE '%,".$cat.",%' ";
					}
				}
				$query.=") ";
			}
		}

		if(isset($vars['options'])&&$vars['options']!="") $query.=" ".$vars['options']." ";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row['tot'];
		}

	public function getItemList($vars)
	{
		if(!$this->inited) $this->init();
		
		if(!isset($vars['photogallery'])) $vars['photogallery']=true;
		if(!isset($vars['documentgallery'])) $vars['documentgallery']=true;
		if(!isset($vars['comments'])) $vars['comments']=true;
		if(!isset($vars['translations'])) $vars['translations']=true;
		if(!isset($vars['customfields'])) $vars['customfields']=true;
		if(!isset($vars['variations'])) $vars['variations']=true;
		
		if(!isset($vars['from'])&&isset($vars['page'])) $vars['from']=(intval($vars['page'])-1)*$GLOBALS['__template']->getVar('shop',1);
		if(!isset($vars['from'])||$vars['from']=="") $vars['from']=0;
		if(!isset($vars['limit'])||$vars['limit']=="") $vars['limit']=$GLOBALS['__template']->getVar('shop',1);
		if(!isset($vars['orderby'])||$vars['orderby']=="") $vars['orderby']=$GLOBALS['__template']->getVar('shop-order',1);
		
		if(!isset($vars['category']))
		{
			if($this->shopExists()==true&&!$this->shopManufacturerExists()&&!$this->shopCartExists()&&$GLOBALS['__subdir__']!="") $vars['category']=$GLOBALS['__subdir__'];
			else $vars['category']='*';
		}
		if($vars['category']!='*' && !is_array($vars['category'])) $vars['category']=array($vars['category']);
		if(!isset($vars['category_operator'])) $vars['category_operator']='OR';
		$vars['category_operator']=strtoupper($vars['category_operator']);

		if($vars['orderby']=="") $orderby="public";
		if($vars['orderby']=="created"||$vars['orderby']=="public"||$vars['orderby']=="expired") $dataRef=$vars['orderby']; else $dataRef='titolo';
		if(!isset($vars['expired'])) $vars['expired']=$GLOBALS['__template']->getVar('shop-order',2);
		if(!isset($vars['ll'])||$vars['ll']=="") $vars['ll']=LANG;

		$output=array();
		$query="SELECT * FROM ".TABLE_SHOP_ITEMS." WHERE ll='".$vars['ll']."' AND `online`='y' AND `public`<=NOW() ";
		if(isset($vars['expired'])&&$vars['expired']=="nascondi") $query.="AND `expired`<NOW() ";
		if(isset($vars['conditions'])&&$vars['conditions']!="") $query.="AND (".$vars['conditions'].") ";
		if(isset($vars['manufacturer'])&&$vars['manufacturer']!="") $query.=" AND `manufacturer`='".intval($vars['manufacturer'])."' ";
		if($vars['ll']==LANG)
		{
			if(count($this->cats)>0&&$vars['category']!="*")
			{
				if($vars['category_operator']=='OR') $query.="AND (categorie=',' ";
				else $query.="AND (`categorie`!='' ";
				foreach($vars['category'] as $category)
				{
					foreach($this->cats as $cat=>$true)
					{
						if($category==$cat||$category==$this->allthecats[$cat]['dir']||$category==b3_htmlize($this->allthecats[$cat]['dir'],false,"")) $query.=$vars['category_operator']." categorie LIKE '%,".$cat.",%' ";
					}
				}
				$query.=") ";
			}
		}
		if(isset($vars['options'])&&$vars['options']!="") $query.=" ".$vars['options']." ";
		$query.="ORDER BY ".$vars['orderby'].",`titolo`,`idsitem` DESC LIMIT ".$vars['from'].",".$vars['limit']."";
		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++) {
			$output[$i]=$this->row2output($row,$vars);
			}
		return $output;
	}

	public function getItemQuickList($vars=array())
	{
		if(!isset($vars['photogallery'])) $vars['photogallery']=false;
		if(!isset($vars['documentgallery']))$vars['documentgallery']=false;
		if(!isset($vars['comments']))$vars['comments']=false;
		if(!isset($vars['variations']))$vars['variations']=false;
		if(!isset($vars['customfields']))$vars['customfields']=false;
		if(!isset($vars['translations']))$vars['translations']=false;

		return $this->getItemList($vars);
	}

	public function getCategoryByDir($dir) {
		if(!$this->inited) $this->init();
		
		// version with html entitites
		$htmldir=b3_htmlize($dir,false,"");
		
		if(count($this->allthecats)>0) {
			foreach($this->allthecats as $cat) {
				if($cat['dir']==$dir || $cat['dir']==$htmldir) return $cat;
				}
			}
		return false;
		}

	public function setItemByDir($dir,$ll=false) {
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		$this->loadedItem=$this->getItemByDir($dir,$ll=false);
		$this->setManufacturerById($this->loadedItem['manufacturer']);
		}
	public function setItemById($idsitem) {
		if(!$this->inited) $this->init();
		$this->loadedItem=$this->getItemById($idsitem);
		$this->setManufacturerById($this->loadedItem['manufacturer']);
		}

	public function getItemByDir($dir,$ll=false) {
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		$expired=$GLOBALS['__template']->getVar('shop-order',2);

		$query="SELECT * FROM ".TABLE_SHOP_ITEMS." WHERE `online`='y' AND (`dir`='".b3_htmlize($dir,true,'')."' OR `dir`='".ksql_real_escape_string($dir)."') AND `ll`='".ksql_real_escape_string($ll)."' ";
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
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $this->row2output($row);
		}
	public function getItemById($idsitem,$ll=false) {
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		$idsitem=intval($idsitem);
		$expired=$GLOBALS['__template']->getVar('shop-order',2);

		$query="SELECT * FROM `".TABLE_SHOP_ITEMS."` WHERE `online`='y' AND `idsitem`='".$idsitem."' AND `ll`='".ksql_real_escape_string($ll)."' ";
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
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
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
		$query="SELECT `layout` FROM `".TABLE_SHOP_ITEM."` WHERE `ll`='".LANG."' AND `online`='y' ";
		if(!isset($_GET['preview'])||$_GET['preview']!=md5(ADMIN_MAIL)) $query.="AND `public`<=NOW() ";
		if($dir!="") $query.="AND `dir`='".ksql_real_escape_string($dir)."' ";
		if($expired=="nascondi") $query.="AND `expired`<=NOW() ";
		if(count($this->cat)>0) {
			$query.="AND (`categorie`='' ";
			foreach($this->cat as $cat=>$true) {
				$query.="OR `categorie` LIKE '%,".$cat.",%' ";
				}
			$query.=") ";
			}
		$query.=" LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
			return $row;
			}
		}
	public function getMetadata($dir=null,$ll=false) {
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		if($dir!=null) $dir=explode("/",$dir);
		else $dir=array($GLOBALS['__dir__'],$GLOBALS['__subdir__'],$GLOBALS['__subsubdir__']);
		$metadata=array();
		$metadata['titolo']="";
		$metadata['traduzioni']="";
		foreach(kGetLanguages() as $code=>$lang) { $metadata['traduzioni'].=$code."|".kGetVar('dir_shop',1,$code)."\n"; }
		$metadata['template']=kGetVar('shop-template',1);
		$metadata['layout']="";
		if(isset($dir[2])&&$dir[2]!="") {
			$query="SELECT `idsitem`,`titolo`,`layout`,`featuredimage` FROM `".TABLE_SHOP_ITEMS."` WHERE `dir`='".b3_htmlize($dir[2],true,"")."' AND `ll`='".ksql_real_escape_string($ll)."' LIMIT 1";
			$results=ksql_query($query);
			$row=ksql_fetch_array($results);
			$metadata['titolo'].=$row['titolo'];
			$metadata['traduzioni']="";
			$metadata['featuredimage']=($row['featuredimage']>0 ? $this->imgs->getImage($row['featuredimage']) : array());
			if($metadata['template']!="") $metadata['template']="";
			if($metadata['layout']!="") $metadata['layout']=$row['layout'];
			$idsitem=$row['idsitem'];
			}
		if(isset($dir[1])&&$dir[1]!="") {
			$cat=$this->getCatByDir($dir[1]);
			$metadata['titolo'].=" [".$cat['categoria']."]";
			}
		if(isset($idsitem)) {
			$query="SELECT * FROM `".TABLE_METADATA."` WHERE `tabella`='".TABLE_SHOP_ITEMS."' AND `id`='".$idsitem."'";
			$results=ksql_query($query);
			while($row=ksql_fetch_array($results)) {
				$metadata[$row['param']]=$row['value'];
				}
			}
		return $metadata;
		}
	
	public function getItemVar($param) {
		if(!$this->inited) $this->init();
		return $this->loadedItem[$param];
		}

	public function getItemPrice($vars)
	{
		/*
		input vars:
		- idsitem [int]
		- variations [array]
		- coupons [array]
		*/
		if(!$this->inited) $this->init();
		
		// ERROR: no pre-loaded or requested item, return false
		if(!isset($this->loadedItem['idsitem'])&&!isset($vars['idsitem'])) return false;
		
		// get the item price and variations list
		if(!isset($vars['idsitem'])) $vars['idsitem']=$this->loadedItem['idsitem'];
		if(isset($this->loadedItem['idsitem'])&&$this->loadedItem['idsitem']==$vars['idsitem'])
		{
			$variations=$this->loadedItem['variations'];
			$price=$this->loadedItem['realprice'];
			$pricediscounted=$this->loadedItem['scontato'];
		} else {
			$item=$this->getItemById($vars['idsitem']);
			$variations=$item['variations'];
			$price=$item['realprice'];
			$pricediscounted=$item['scontato'];
		}
		
		/* check for coupons with "apply discounts" clausule */
		if(empty($vars['coupons'])) $vars['coupons']=array();
		if(!is_array($vars['coupons'])) $vars['coupons']=array($vars['coupons']);
		
		$applydiscount=false;
		foreach($vars['coupons'] as $code)
		{
			$c=$this->getCouponByCode($code);
			if($c==false) continue;

			if($c['isValid']==false) continue;
			
			if($c['action']=='usediscountprices') $applydiscount=true;
		}


		// apply discount price in case of
		if(
			$applydiscount==true || //valid coupon
			(kGetVar('shop-discount',1)=='always'&&$pricediscounted>0) || //set to always use discount prices
			($pricediscounted>0 && kGetVar('shop-discount',1)=='qty' && kGetVar('shop-discount',2)<=$this->getCartItemsCount()) //set to discount when there are more than # items into the cart
			) $price=$pricediscounted;


		/* apply variations*/
		if(!isset($vars['variations'])||!is_array($vars['variations'])) $vars['variations']=array();
		$variationsprice=0;
		foreach($variations as $variation)
		{
			foreach($variation as $v)
			{
				if(array_search($v['idsvar'],$vars['variations'])!==false)
				{
					// apply discount price if active
					if($applydiscount==true || (kGetVar('shop-discount',1)=='always' && $pricediscounted>0)) $v['realprice']=trim($v['discounted']);
					elseif(kGetVar('shop-discount',1)=='qty')
					{
						if(kGetVar('shop-discount',2)<=$this->getCartItemsCount() && $v['discounted']!="") $v['realprice']=trim($v['discounted']);
					} else {
						// normal price
						$v['realprice']=trim($v['price']);
					}
					
					if($v['realprice']!="")
					{
						if(substr($v['realprice'],0,1)=='+')
						{
							if(substr($v['realprice'],-1)=='%')
							{
								$variationsprice+=floatval($price/100*substr($v['realprice'],1,-1));
							} else {
								$variationsprice+=floatval(substr($v['realprice'],1));
							}
						} elseif(substr($v['realprice'],0,1)=='-') {
							if(substr($v['realprice'],-1)=='%')
							{
								$variationsprice-=floatval($price/100*substr($v['realprice'],1,-1));
							} else {
								$variationsprice-=floatval(substr($v['realprice'],1));
							}
						} else {
							if(substr($v['realprice'],-1)=='%')
							{
								$price=floatval($price/100*substr($v['realprice'],1,-1));
							} else {
								$price=floatval($v['realprice']);
							}
						}
					}
				}
			}
		}
		$price+=$variationsprice;
		if($price<0) $price=0;
		return $price;
		}
	
	public function getPermalinkById($idsitem) {
		if(!$this->inited) $this->init();
		$query="SELECT `ll`,`dir`,`categorie` FROM `".TABLE_SHOP_ITEMS."` WHERE `idsitem`='".intval($idsitem)."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);

		$subdir="";

		$allowedCategories=$GLOBALS['__template']->getVar('shop',2,$row['ll']);
		$catquery="SELECT * FROM `".TABLE_CATEGORIE."` WHERE `tabella`='".TABLE_SHOP_ITEMS."' AND `ll`='".$row['ll']."' ORDER BY `ordine`";
		$catresults=ksql_query($catquery);
		while($catrow=ksql_fetch_array($catresults)) {
			if($allowedCategories==",*,"&&strpos($row['categorie'],','.$catrow['idcat'].',')!==false) {
				$subdir=$catrow['dir'];
				break;
				}
			elseif(strpos($allowedCategories,','.$catrow['idcat'].',')!==false&&strpos($row['categorie'],','.$catrow['idcat'].',')!==false) {
				$subdir=$catrow['dir'];
				break;
				}
			}
		unset($allowedCategories);

		return BASEDIR.$GLOBALS['__template']->getLanguageURI($row['ll']).$GLOBALS['__template']->getVar('dir_shop',1).'/'.$subdir.'/'.$row['dir'];
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
		
	public function briciole() {
		// TODO.
		return "";
		}

	private function row2output($row,$vars=array()) {
		if(!$this->inited) $this->init();

		if(!isset($vars['photogallery'])) $vars['photogallery']=true;
		if(!isset($vars['documentgallery'])) $vars['documentgallery']=true;
		if(!isset($vars['comments'])) $vars['comments']=true;
		if(!isset($vars['translations'])) $vars['translations']=true;
		if(!isset($vars['customfields'])) $vars['customfields']=true;
		if(!isset($vars['variations'])) $vars['variations']=true;
		
		$orderby="";
		if(isset($vars['orderby'])) $orderby=$vars['orderby'];
		if(!isset($vars['ll'])) $vars['ll']=LANG;

		$output=$row;
		if($orderby=="") $orderby=$GLOBALS['__template']->getVar('shop-order',1);
		if($orderby=="") $orderby="titolo";
		$output['categories']=array();
		$subdir="";
		foreach($this->cats as $cat=>$true) {
			if(strpos($row['categorie'],','.$cat.',')!==false) {
				$output['categories'][$cat]=$this->allthecats[$cat];
				if($GLOBALS['__dir__']==$GLOBALS['__template']->getVar('dir_shop',1)&&$GLOBALS['__subdir__']==$this->allthecats[$cat]['dir']) $subdir=$GLOBALS['__subdir__'];
				elseif($subdir=="") $subdir=$this->allthecats[$cat]['dir'];
				}
			}
		if($subdir==""&&isset($output['categories'][0])) $subdir=$output['categories'][0]['dir'];
		$output['permalink']=BASEDIR.$GLOBALS['__template']->getLanguageURI($vars['ll']).$GLOBALS['__template']->getVar('dir_shop',1).'/'.$subdir.'/'.$row['dir'];
		$output['catpermalink']=BASEDIR.$GLOBALS['__template']->getLanguageURI($vars['ll']).$GLOBALS['__template']->getVar('dir_shop',1).'/'.$subdir;
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

		$output['featuredimage']=$this->imgs->getImage($row['featuredimage']);
		if($row['featuredimage']==0) $output['featuredimage']=false;
		
		// get photogallery in the correct order
		$output['imgs']=array();
		if($vars['photogallery']==true && trim($row['photogallery'],",")!="" )
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
		if($vars['documentgallery']==true) $output['docs']=$this->docgallery->getList(TABLE_SHOP_ITEMS,$row['idsitem']);

		$output['commenti']=array();
		if($vars['comments']==true) $output['commenti']=$this->getComments($row['idsitem']);
		
		$output['traduzioni']=array();
		if($vars['translations']==true)
		{
			foreach(explode("|",trim($row['traduzioni'],"|")) as $trad)
			{
				if(substr($trad,0,2)!="") $output['traduzioni'][substr($trad,0,2)]=$this->getPermalinkById(substr($trad,3));
			}
		}

		$output['privatearea']=explode("\n",trim($output['privatearea']));

		if($vars['customfields']==true)
		{
			$output['customfields']=array();
			foreach($this->getCustomFields(explode(",",trim($row['categorie'],","))) as $field)
			{
				$output['customfields'][]=$field;
			}
			foreach(explode("</field>",trim($row['customfields'])) as $f)
			{
				$f=trim($f);
				if(!empty($f))
				{
					preg_match('/^<field id="(\d+)">(.*)/s',$f,$match);
					for($i=0;isset($output['customfields'][$i]);$i++)
					{
						if($output['customfields'][$i]['idsfield']==$match[1]) $output['customfields'][$i]['value']=$match[2];
					}
				}
			}
		}
		
		$output['variations']=array();
		if($vars['variations']==true) $output['variations']=$this->getVariations($row['idsitem']);

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
		ksql_query($query);
		$idcomm=ksql_insert_id();
		
		//notifica
		$mail=array("headers"=>"","to"=>"","subject"=>"","message"=>"");
		$query="SELECT idnews,titolo,iduser FROM ".TABLE_NEWS." WHERE idnews=".$idnews." LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$iduser=$row['iduser'];
		$titolo=$row['titolo'];
		$idnews=$row['idnews'];
		$query="SELECT * FROM ".TABLE_USERS." WHERE iduser='".$iduser."' LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
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
	
	public function getVariations($idsitem)
	{
		/* loads from database the variations for a gived item */
		$output=array();
		
		$query="SELECT * FROM `".TABLE_SHOP_VARIATIONS."` WHERE `idsitem`='".intval($idsitem)."' ORDER BY `collection` ASC,`order` ASC";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			$row['descr']=$this->kText->formatText($row['descr']);
			list($row['descr'],$tmp) = $this->kText->embedImg($row['descr']);
			list($row['descr'],$tmp) = $this->kText->embedDocs($row['descr']);
			list($row['descr'],$tmp) = $this->kText->embedMedia($row['descr']);

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
		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++) {
			$output[$i]=$row;
			}
		return $output;
		}


	/* COUPONS */
	public function getCouponByCode($code) {
		if(trim($code)=="") return false;
		if(isset($this->coupons[$code])) return $this->coupons[$code]; //cache

		$output=array();

		// get the coupon
		$query="SELECT * FROM `".TABLE_SHOP_COUPONS_CODES."` WHERE `code`='".ksql_real_escape_string($code)."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		if($row==false) return false;
		$output=$row;

		// get the coupon's collection
		$query="SELECT * FROM `".TABLE_SHOP_COUPONS."` WHERE `idscoup`='".ksql_real_escape_string($row['idscoup'])."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
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
			foreach($this->getCart() as $item)
			{
				$discount+=($item['realprice']-$item['scontato'])*$item['qty'];
			}
			$output['value']=number_format($discount,2).' '.$GLOBALS['__template']->getVar('shop-currency',2);
			}

		
		$output['isValid']=true;
		
		if(!$this->checkCouponValidity($output))
		{
			$output['isValid']=false;
			$output['value']="Coupon is not valid";
		}
		
		$this->coupons[$code]=$output; //cache
		return $output;
	}

	//check if a given copon is actually valid
	public function checkCouponValidity($c)
	{
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

	public function couponsMarkAsUsed($coupons)
	{
		if(!is_array($coupons)) $coupons=array($coupons);
		foreach($coupons as $code)
		{
			$query="UPDATE `".TABLE_SHOP_COUPONS_CODES."` SET `valid`=0 WHERE `code`='".ksql_real_escape_string($code)."' LIMIT 1";
			ksql_query($query);
		}
		return true;
	}

		
	/*********************************************
	* CART
	*********************************************/
	public function getCart($vars=array())
	{
		if(!$this->inited) $this->init();
		
		if(empty($vars['coupons'])) $vars['coupons']=array();
		if(!is_array($vars['coupons'])) $vars['coupons']=array($vars['coupons']);
		
		if(isset($_SESSION['shop']['cart']))
		{
			$output=array();
			foreach($_SESSION['shop']['cart'] as $item)
			{
				//check if object exists yet (multiple items of the same type)
				$exists=-1;
				foreach($output as $k=>$itm)
				{
					if($itm['uid']==$item['uid'])
					{
						$exists=$k;
						break;
					}
				}
				
				//if this is the first occurrence of the item, add to output array
				if($exists==-1)
				{
					$id=count($output);
					
					$output[$id]=$this->getItemById($item['id']);
					$output[$id]['id']=$item['id']; //the id as saved on the cart
					$variations=$output[$id]['variations'];
					$output[$id]['variations']=array();
					$variationsIds=array();
					foreach($item['variations'] as $idsvar=>$true)
					{
						foreach($variations as $collection=>$v)
						{
							foreach($v as $var)
							{
								if($var['idsvar']==$idsvar)
								{
									$output[$id]['variations'][]=$var;
									$variationsIds[]=$idsvar;
								}
							}
						}
					}
					$output[$id]['customvariations']=$item['customvariations'];
					$output[$id]['qty']=1;
					$output[$id]['uid']=$item['uid'];
					
					/* price calc */
					$output[$id]['realprice']=$this->getItemPrice(array('idsitem'=>$item['id'],'variations'=>$variationsIds,'coupons'=>$vars['coupons']));
					$output[$id]['totalprice']=$output[$id]['realprice']*$output[$id]['qty'];
				
				//else add 1 to quantity
				} else {
					$output[$exists]['qty']++;
					$output[$id]['totalprice']=$output[$id]['realprice']*$output[$id]['qty'];
				}
			}
			return $output;
		}
		else return array();
	}

	public function getCartItemsPrice($vars=array())
	{
		$totalprice=0;
		foreach($this->getCart($vars) as $item)
		{
			$totalprice+=$item['qty']*$item['realprice'];
		}
		return $totalprice;
	}

	public function getCartShippingPrice($iddel=false,$country=false)
	{
		if(!$this->inited) $this->init();

		//if country is not passed, get the first country from the highest zone
		$idzone=0;
		if($country==false)
		{
			foreach($this->getCountries() as $c)
			{
				if($c['zone']>$idzone)
				{
					$idzone=$c['zone'];
					$country=$c['ll'];
				}
			}
		}
		if($idzone==0)
		{
			trigger_error('Missing shipping country');
			return false;
		}
		
		//if courier is not passed, get the first one
		if($iddel==false)
		{
			$carriers=$this->getDeliverersByZone($idzone);
			$iddel=$carriers[0]['iddel'];
		}
		if($iddel=="")
		{
			trigger_error('Missing carrier');
			return false;
		}
		$this->setDelivererById($iddel);
		
		//calculate weight from cart
		$totalweight=0;
		foreach($this->getCart() as $item)
		{
			$totalweight+=floatval($item['weight'])*$item['qty'];
		}

		return $this->getDelivererPriceByKg($totalweight,$idzone);
	}

	public function getCartPaymentPrice($totalamount=false,$idspay=false,$iddel=false,$country=false)
	{
		if(!$this->inited) $this->init();
		if($idspay==false) $idspay=$this->getCartVar('payment');
		if($iddel==false) $iddel=$this->getCartVar('deliverer');
		if($country==false) $country=$this->getCartVar('del_Country');
		if($totalamount==false) $totalamount=$this->getCartItemsPrice()+$this->getCartShippingPrice($iddel,$country);
		$payment_method=$this->getPaymentById($idspay);
		return $payment_method['price']+($totalamount*$payment_method['pricepercent']/100);
	}

	public function getCartTotalPrice($vars=false,$iddel=false,$country=false)
	{
		if(!$this->inited) $this->init();
		if(!is_array($vars))
		{
			$vars=array("idspay"=>$vars);
			$vars['iddel']=$iddel;
			$vars['country']=$country;
		}
		if(!isset($vars['country'])) return false;
		if(!isset($vars['iddel'])) $vars['iddel']=false;
		if(!isset($vars['idspay'])) $vars['idspay']=false;
		if(!isset($vars['coupons'])) $vars['coupons']=array();
		if(!is_array($vars['coupons'])) $vars['coupons']=array($vars['coupons']);
		
		$price=$this->getCartItemsPrice();
		$shippingprice=$this->getCartShippingPrice($vars['iddel'],$vars['country']);
		$paymentprice=$this->getCartPaymentPrice($price,$vars['idspay']);
		
		/* coupons discounts */
		$discount=0;
		foreach($vars['coupons'] as $code)
		{
			$c=$this->getCouponByCode($code);
			if($c==false) continue;

			if($c['isValid']==false) continue;
			
			//apply the action
			if(substr($c['action'],0,9)=='discount=')
			{
				$dval=substr($c['action'],9);
				$discount+=floatval($dval);
			} elseif(substr($c['action'],0,16)=='discountpercent=') {
				$dval=substr($c['action'],16);
				$discount+=round($price/100*floatval($dval),2);
			} elseif($c['action']=='freeshipping') {
				$discount+=$shippingprice;
			} elseif($c['action']=='freecheaper') {
				$cheaper=-1;
				foreach($this->getCart() as $item) {
					if($cheaper==-1||$cheaper>$item['realprice']) $cheaper=$item['realprice'];
				}
				$discount+=$cheaper;
			} elseif($c['action']=='freemoreexpensive') {
				$expensive=0;
				foreach($this->getCart() as $item)
				{
					if($expensive<$item['realprice']) $expensive=$item['realprice'];
				}
				$discount+=$expensive;
			} elseif($c['action']=='usediscountprices') {
				foreach($this->getCart() as $item)
				{
					$discount+=($item['realprice']-$item['scontato'])*$item['qty'];
				}
			}
		}

		$price=$price+$paymentprice+$shippingprice-$discount;
		return $price;
	}

	/* create a string that identifies the item with his variations */
	private function getItemUID($idsitem,$variations=array(),$customvariations=array())
	{
		if(!is_array($variations)) $variations=array();
		asort($variations);
		$string=$idsitem;
		foreach($variations as $k=>$v)
		{
			$string.='-'.$k;
		}
		foreach($customvariations as $k=>$v)
		{
			$string.='-'.$k.':'.$v;
		}
		$string=base64_encode($string);
		return $string;
	}

	public function addItemToCart($idsitem, $qty=1, $variations=array(), $customvariations=array())
	{
		asort($variations);
		if(!isset($_SESSION['shop'])) $_SESSION['shop']=array();
		if(!isset($_SESSION['shop']['cart'])) $_SESSION['shop']['cart']=array();
		for($i=0;$i<$qty;$i++)
		{
			$_SESSION['shop']['cart'][] = array("id"=>$idsitem,
												"variations"=>$variations,
												"customvariations"=>$customvariations,
												"uid"=>$this->getItemUID($idsitem,$variations,$customvariations)
												);
		}
	}

	public function removeItemFromCart ($idsitem, $qty=1, $variations=array(), $customvariations=array())
	{
		$uid=$this->getItemUID($idsitem,$variations,$customvariations);
		$this->removeItemFromCartByUniqueID($uid,$qty);
	}
	
	public function addItemToCartByUniqueID ($uid, $qty=1)
	{
		for($i=0;$i<$qty;$i++)
		{
			foreach($_SESSION['shop']['cart'] as $id=>$item)
			{
				if($item['uid']==$uid)
				{
					$_SESSION['shop']['cart'][]=$item;
					break;
				}
			}
		}
	}

	public function removeItemFromCartByUniqueID ($uid, $qty=1)
	{
		for($i=0;$i<$qty;$i++)
		{
			foreach($_SESSION['shop']['cart'] as $id=>$item)
			{
				if($item['uid']==$uid)
				{
					unset($_SESSION['shop']['cart'][$id]);
					break;
				}
			}
		}
	}

	public function emptyCart()
	{
		if(isset($_SESSION['shop']['cart'])) $_SESSION['shop']['cart']=array();
	}

	public function setCartVar($param,$value) //deprecated
	{
		if(!isset($_SESSION['shop'])) $_SESSION['shop']=array();
		if(!isset($_SESSION['shop']['data'])) $_SESSION['shop']['data']=array();
		$_SESSION['shop']['data'][$param]=$value;
	}
	
	public function getCartVar($param) //deprecated
	{
		if(isset($_SESSION['shop']['data'][$param])) return $_SESSION['shop']['data'][$param];
		else return "";
	}

	public function getCartItemsCount($vars=array()) {
		/*
		inputs for filtering:
		[idsitem] -> the id of the items (optional)
		[variations] -> an array of variation ids (optional)
		*/
		if(!isset($_SESSION['shop']['cart'])) return 0;

		$c=0;
		foreach($_SESSION['shop']['cart'] as $item)
		{
			if(isset($vars['idsitem'])&&$vars['idsitem']>0) {
				// match item when no variations is specified
				if(!isset($vars['variations'])&&$item['id']==$vars['idsitem']) $c++;
					
				// match only items with all the specified variations
				elseif($this->getItemUID($vars['idsitem'],$vars['variations'])==$item['uid']) $c++;

			} else $c++;
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
		if(isset($vars['delivery']['carrier'])&&$vars['delivery']['carrier']!="")
		{
			$valid=false;
			foreach($carriers as $c)
			{
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
		if(isset($vars['payment']['method'])&&$vars['payment']['method']!="")
		{
			$valid=false;
			foreach($payments as $c)
			{
				if($c['idspay']==$vars['payment']['method']) $valid=true;
			}
			if($valid==false) return array("code"=>"301","description"=>"Invalid payment method");
		}
		
		//check if the coupon is valid
		if(isset($vars['coupon'])&&!is_array($vars['coupon'])) $vars['coupon']=array($vars['coupon']);
		if(isset($vars['coupon'])&&count($vars['coupon'])>0)
		{
			foreach($vars['coupon'] as $code)
			{
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
		if($log!=false)
		{
			trigger_error($log);
			return false;
		}
		
		/* fill required fields */
		foreach(array("name","email","phone","address","city","zipcode","country") as $var)
		{
			if(!isset($vars['delivery'][$var])) $vars['delivery'][$var]=$vars['customer'][$var];
			if(!isset($vars['payment'][$var])) $vars['payment'][$var]=$vars['customer'][$var];
		}
		if(!isset($vars['coupons'])) $vars['coupons']=array();
		if(!is_array($vars['coupons'])) $vars['coupons']=array($vars['coupons']);
		
		/* check if member is logged. if not, try to create a user, then log-in */
		$idmember=0;
		if(isset($_SESSION['member']['idmember']))
		{
			$idmember=$_SESSION['member']['idmember'];
		} else {
			//if member doesn't exists, try to create a new one
			$name=$vars['customer']['name'];
			$username=preg_replace('/[^[a-zA-Z]]/','-',strtolower(str_replace(" ","",$name)));
			$email=$vars['customer']['email'];
			$idm=kMemberRegister($username,false,$name,$email);
			//if just exists a user with the same username, try with a different username for 10 times
			$i=0;
			while($idm==false&&$i<10)
			{
				$username.=rand(0,10);
				$idm=kMemberRegister($username,false,$name,$email);
				$i++;
			}
			$u=kGetMemberById($idm);
			if(isset($u['username']))
			{
				$idmember=$u['idmember'];
				kMemberLogIn($u['username'],$u['password']);
			} else {
				trigger_error('Error creating your user');
				return false;
			}
		}
		
		/* save/update metadata for the current user with the order data */
		if(isset($_SESSION['member']['idmember'])&&kGetMemberUsername()!="")
		{
			foreach($vars['customer'] as $param=>$value)
			{
				kMemberReplaceMetadata(kGetMemberUsername(),"shop_customer_".$param,$value);
			}

			foreach($vars['delivery'] as $param=>$value)
			{
				kMemberReplaceMetadata(kGetMemberUsername(),"shop_delivery_".$param,$value);
			}

			foreach($vars['payment'] as $param=>$value)
			{
				kMemberReplaceMetadata(kGetMemberUsername(),"shop_payment_".$param,$value);
			}
		}

		/* unique id generation */
		$uid=$this->uidGenerator($idmember);

		/* item list */
		$items=array();
		foreach($this->getCart() as $item)
		{
			$items[]=array(
				"idsitem"=>$item['idsitem'],
				"dir"=>$item['dir'],
				"categories"=>$item['categorie'],
				"productcode"=>$item['productcode'],
				"title"=>$item['titolo'],
				"subtitle"=>$item['sottotitolo'],
				"price"=>$item['prezzo'],
				"discounted"=>$item['scontato'],
				"created"=>$item['created'],
				"modified"=>$item['modified'],
				"weight"=>$item['weight'],
				"ll"=>$item['ll'],
				"variations"=>$item['variations'],
				"customvariations"=>$item['customvariations'],
				"realprice"=>$item['realprice'],
				"totalprice"=>$item['totalprice'],
				"privatearea"=>$item['privatearea'],
				"qty"=>$item['qty']
			);
		}
		$items=json_encode($items);
		$itemsprice=0;
		foreach($this->getCart() as $item)
		{
			$itemsprice+=$item['realprice'];
		}

		/* idzone */
		$country=isset($vars['delivery']['country']) ? $vars['delivery']['country'] : '';
		$idzone=4;
		foreach($this->getCountries() as $c)
		{
			if($c['ll']==$country) $idzone=$c['zone'];
		}

		/* deliverer */
		$iddel=isset($vars['delivery']['carrier']) ? $vars['delivery']['carrier'] : '';
		if($iddel=="")
		{
			$carriers=$this->getDeliverersByZone($idzone);
			$iddel=$carriers[0]['iddel'];
		}
		if($iddel=="")
		{
			trigger_error('Missing carrier');
			return false;
		}
		$deliverer=$this->getDelivererById($iddel);

		/* payments */
		$idspay=isset($vars['payment']['method']) ? $vars['payment']['method'] : '';
		if($idspay=="")
		{
			$payments=$this->getPaymentsByZone($idzone);
			$idspay=$payments[0]['idspay'];
		}
		if($idspay=="")
		{
			trigger_error('Missing payment method');
			return false;
		}
		$payment_method=$this->getPaymentById($idspay);

		/* recalculate price */
		$price=$this->getCartTotalPrice(array("idspay"=>$idspay,"iddel"=>$iddel,"country"=>$country,"coupons"=>$vars['coupons']));

		/* personal data */
		if(!isset($vars['customer']['email']))
		{
			//se manca l'e-mail, ferma tutto
			trigger_error('Missing e-mail address');
			return false;
		}
		$personal_data="";
		foreach($vars['customer'] as $k=>$v)
		{
			$personal_data.="<".$k.">".$v."</".$k.">\n";
		}

		/* invoice data */
		$invoice_data="";
		foreach($vars['payment'] as $k=>$v)
		{
			$invoice_data.="<".$k.">".$v."</".$k.">\n";
		}

		/* shipping data */
		$shipping_data="";
		foreach($vars['delivery'] as $k=>$v)
		{
			$shipping_data.="<".$k.">".$v."</".$k.">\n";
		}

		/* generate mail, and insert order summary into mail body */
		$tmp=$__config->getParam("shop-mail_checkout");
		$tmp['address']=$GLOBALS['__emails']->getMailSubTemplate('shop_order_address');
		if($tmp['address']==false)
		{
			$tmp['address']=$vars['customer']['name']."<br />\n".$vars['customer']['address']."<br />\n".$vars['customer']['zipcode']." ".$vars['customer']['city']." (".$vars['customer']['country'].")";
		}

		$tmp['items']=$GLOBALS['__emails']->getMailSubTemplate('shop_order_items_list');
		if($tmp['items']==false)
		{
			$tmp['items']='<table class="items"><tr><th>'.$__template->translate('Item')."</th><th>".$__template->translate('Price')."</th><th>".$__template->translate('Qty')."</th></tr>";
			foreach($this->getCart() as $item)
			{
				$tmp['items'].="<tr>";
				$tmp['items'].='<td><a href="'.SITE_URL.$item['permalink'].'"><strong>'.$item['titolo'].'</strong>';
					if($item['productcode']!="") $tmp['items'].=' ('.$item['productcode'].')';
					$tmp['items'].='</a><br>';
					if($item['sottotitolo']!="") $tmp['items'].="".$item['sottotitolo']."<br>";
					
					$variations="";
					foreach($item['variations'] as $v)
					{
						$variations.=' '.$v['collection'].': '.$v['name'].',';
					}
					$variations=rtrim($variations,",-");
					if($variations!="") $tmp['items'].="<em>".$variations."</em><br>";
						
					$customvariations="";
					foreach($item['customvariations'] as $k=>$v) {
						$customvariations.=' '.$k.': '.$v.',';
						}
					$customvariations=rtrim($customvariations,",");
					if($customvariations!="") { $tmp['items'].="<small>".$customvariations."</small><br>"; }
					
					$tmp['items'].="</td>";
				$tmp['items'].="<td>".number_format($item['realprice']*$item['qty'],2).' '.$GLOBALS['__template']->getVar('shop-currency',2)."</td>";
				$tmp['items'].="<td>".$item['qty']."</td>";
				$tmp['items'].="</tr>";
			}
			$tmp['items'].="</table>";
		}

		$tmp['shipping_address']=$GLOBALS['__emails']->getMailSubTemplate('shop_order_shipping_address');
		if($tmp['shipping_address']==false)
		{
			$tmp['shipping_address']=$vars['delivery']['name']."<br />\n".$vars['delivery']['address']."<br />\n".$vars['delivery']['zipcode']." ".$vars['delivery']['city']." (".$vars['delivery']['country'].")";
		}

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
		$query="INSERT INTO `".TABLE_SHOP_ORDERS."` (`uid`, `ip`, `date`, `items`, `idmember`, `personal_data`, `invoice_data`, `shipping_address`, `idzone`, `deliverer`, `iddel`, `payment_method`, `idspay`, `notes`, `status`, `totalprice`, `payed`, `payedon`, `idstrans`, `shipped`, `shippedon`, `tracking_number`, `tracking_url`, `order_summary`, `ll`) VALUES ('".$uid."', '".ksql_real_escape_string($_SERVER['REMOTE_ADDR'])."', NOW(), '".ksql_real_escape_string($items)."', '".$idmember."', '".ksql_real_escape_string($personal_data)."', '".ksql_real_escape_string($invoice_data)."', '".ksql_real_escape_string($shipping_data)."', '".intval($idzone)."', '".ksql_real_escape_string($deliverer['name'])."', '".intval($iddel)."', '".ksql_real_escape_string($payment_method['name'])."', '".intval($idspay)."', '', 'OPN', '".ksql_real_escape_string($price)."', 'n', '0000-00-00', '', 'n', '0000-00-00', '', '', '".ksql_real_escape_string($mail['message'])."', '".ksql_real_escape_string(LANG)."')";
		if(!ksql_query($query)) {
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
		if(isset($vars['idmember'])) $query.=" AND `idmember`='".ksql_real_escape_string($vars['idmember'])."'";
		$query.=" ORDER BY ".$vars['orderby'];
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$id=count($output);
			$output[$id]=$this->ordersRow2output($row);
			}
		return $output;
		}	
	public function getOrderByNumber($uid) {
		if(!$this->inited) $this->init();
		$output=array();
		$query="SELECT * FROM `".TABLE_SHOP_ORDERS."` WHERE uid='".ksql_real_escape_string($uid)."' ORDER BY `date` DESC LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results)) {
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
		$output['items']=json_decode($row['items']);
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
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$id=count($output);
			$output[$id]=$row;
			$output[$id]['friendlydate']=preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).(\d{2})/","$3-$2-$1 $4:$5",$output[$id]['date']);
			}
		return $output;
		}
	public function tnxIdExists($txn_id) {
		$query="SELECT * FROM `".TABLE_SHOP_TRANSACTIONS."` WHERE details LIKE 'txn_id=\'".ksql_real_escape_string($txn_id)."\'' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
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
			if(!ksql_query($query)) return false;
			
			$totalamount=kGetShopOrderTotalAmount();
			if($totalamount<=$value) {
				$query="UPDATE `".TABLE_SHOP_ORDERS."` SET payed='s' WHERE `idord`='".intval($idord)."' LIMIT 1";
				if(!ksql_query($query)) return false;
				
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
						$GLOBALS['__private']->setPermissions($dir,'restricted',$armembers,'private',array());
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

		
	/***********************************************
	* MANUFACTURERS
	***********************************************/
	
	public function getManufacturerMetadata($dir=null,$ll=false)
	{
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		if($dir!=null) $dir=explode("/",$dir);
		else $dir=array($GLOBALS['__dir__'],$GLOBALS['__subdir__'],$GLOBALS['__subsubdir__']);
		$metadata=array();
		$metadata['titolo']="";
		$metadata['traduzioni']="";
		foreach(kGetLanguages() as $code=>$lang) { $metadata['traduzioni'].=$code."|".kGetVar('dir_shop',1,$code)."\n"; }
		$metadata['template']="";
		$metadata['layout']="";
		
		if(isset($dir[2])&&$dir[2]!="")
		{
			$query="SELECT `idsman`,`name`,`translations`,`featuredimage` FROM `".TABLE_SHOP_MANUFACTURERS."` WHERE (`dir`='".b3_htmlize($dir[2],true,"")."' OR `dir`='".ksql_real_escape_string($dir[2])."') AND `ll`='".ksql_real_escape_string($ll)."' LIMIT 1";
			$results=ksql_query($query);
			$row=ksql_fetch_array($results);
			$metadata['titolo'].=$row['name'];
			$metadata['traduzioni']=$row['translations'];
			$metadata['featuredimage']=($row['featuredimage']>0 ? $this->imgs->getImage($row['featuredimage']) : array());
			$idsman=$row['idsman'];
		}
		
		if(isset($idsman))
		{
			$query="SELECT * FROM `".TABLE_METADATA."` WHERE `tabella`='".TABLE_SHOP_MANUFACTURERS."' AND `id`='".$idsman."'";
			$results=ksql_query($query);
			while($row=ksql_fetch_array($results))
			{
				$metadata[$row['param']]=$row['value'];
			}
		}

		return $metadata;
	}
	
	public function getManufacturersList($vars)
	{
		if(!$this->inited) $this->init();
		if(!isset($vars['from'])||$vars['from']=="") $vars['from']=0;
		if(!isset($vars['limit'])||$vars['limit']=="") $vars['limit']=10000;
		if(!isset($vars['orderby'])||$vars['orderby']=="") $vars['orderby']="`name`";
		if(!isset($vars['ll'])||$vars['ll']=="") $vars['ll']=LANG;
		if(!isset($vars['haveitems'])) $vars['haveitems']=false;

		$output=array();
		$query="SELECT a.* ";
		if($vars['haveitems']) $query.=", count(b.`idsitem`) AS `numberofitems` ";
		$query.=" FROM `".TABLE_SHOP_MANUFACTURERS."` AS `a` ";
		if($vars['haveitems']) $query.=" INNER JOIN `".TABLE_SHOP_ITEMS."` AS b ON a.idsman=b.manufacturer ";
		$query.=" WHERE a.`ll`='".ksql_real_escape_string($vars['ll'])."' ";
		if(isset($vars['idsman'])) $query.="AND a.`idsman`='".intval($vars['idsman'])."' ";

		if($vars['haveitems']) $query.=" AND b.`ll`='".ksql_real_escape_string($vars['ll'])."' AND b.`public`< NOW() AND b.`online`='y' AND b.`idsitem` IS NOT NULL ";
		if(isset($vars['conditions'])&&$vars['conditions']!="") $query.="AND (".$vars['conditions'].") ";
		if(isset($vars['options'])&&$vars['options']!="") $query.=" ".$vars['options']." ";
		$query.="GROUP BY a.`idsman` ORDER BY ".$vars['orderby']." LIMIT ".ksql_real_escape_string($vars['from']).",".ksql_real_escape_string($vars['limit'])."";

		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++)
		{
			$output[$i]=$this->manufacturerRowToOutput($row);
		}
		return $output;
	}

	public function setManufacturer($dir="",$ll=false)
	{
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		$this->loadedManufacturer=$this->getManufacturerByDir($dir,$ll=false);
	}
	public function setManufacturerById($id)
	{
		if(!$this->inited) $this->init();
		$this->loadedManufacturer=$this->getManufacturerById($id);
	}

	public function getManufacturerByDir($dir,$ll=false)
	{
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		$query="SELECT * FROM `".TABLE_SHOP_MANUFACTURERS."` WHERE (`dir`='".b3_htmlize($dir,true,'')."' OR `dir`='".ksql_real_escape_string($dir)."') AND `ll`='".$ll."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $this->manufacturerRowToOutput($row);
	}
	public function getManufacturerById($id)
	{
		if(!$this->inited) $this->init();
		$query="SELECT * FROM `".TABLE_SHOP_MANUFACTURERS."` WHERE `idsman`='".intval($id)."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $this->manufacturerRowToOutput($row);
	}
	
	public function getManufacturerPermalinkById($idsman)
	{
		if(!$this->inited) $this->init();
		$query="SELECT `ll`,`dir` FROM `".TABLE_SHOP_MANUFACTURERS."` WHERE `idsman`='".intval($idsman)."' LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);

		return BASEDIR.$GLOBALS['__template']->getLanguageURI($row['ll']).$GLOBALS['__template']->getVar('dir_shop',1).'/'.$GLOBALS['__template']->getVar('dir_shop_manufacturers',1).'/'.$row['dir'];
	}
	
	public function getManufacturerVar($param)
	{
		return isset($this->loadedManufacturer[$param]) ? $this->loadedManufacturer[$param] : false;
	}
	
	/* for a given row from database, it returns an enriched array with more useful values */
	public function manufacturerRowToOutput($row)
	{
		if(!$this->inited) $this->init();
		
		$vars['photogallery']=true; // always on for now...
		
		/* if no manufacturer, return an array with empty values */
		if(!isset($row['idsman'])||$row['idsman']=="")
		{
			return array(
				"idsman"=>0,
				"permalink"=>"",
				"dir"=>"",
				"name"=>"",
				"preview"=>"",
				"description"=>"",
				"featuredimage"=>"",
				"embeddedimgs"=>array(),
				"embeddeddocs"=>array(),
				"embeddedmedias"=>array(),
				"translations"=>array(),
				"traduzioni"=>"",
				"imgs"=>array(),
				"docs"=>array(),
				"comments"=>array(),
				"ll"=>LANG
			);
		}
		
		$output=$row;
		
		$output['permalink']=BASEDIR.$GLOBALS['__template']->getLanguageURI($row['ll']).$GLOBALS['__template']->getVar('dir_shop',1).'/'.$GLOBALS['__template']->getVar('dir_shop_manufacturers',1).'/'.$row['dir'];

		$kText=new kText();
		$output['embeddedimgs']=array();
		$output['embeddeddocs']=array();
		$output['embeddedmedias']=array();
		$output['preview']=$this->kText->formatText($output['preview']);
		$output['description']=$this->kText->formatText($output['description']);
		
		$tmp=$this->kText->embedImg($output['preview']);
			$output['preview']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddedimgs']=array_merge($output['embeddedimgs'],$tmp[1]);
		$tmp=$this->kText->embedDocs($output['preview']);
			$output['preview']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddeddocs']=array_merge($output['embeddeddocs'],$tmp[1]);
		$tmp=$this->kText->embedMedia($output['preview']);
			$output['preview']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddedmedias']=array_merge($output['embeddedmedias'],$tmp[1]);

		$tmp=$this->kText->embedImg($output['description']);
			$output['description']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddedimgs']=array_merge($output['embeddedimgs'],$tmp[1]);
		$tmp=$this->kText->embedDocs($output['description']);
			$output['description']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddeddocs']=array_merge($output['embeddeddocs'],$tmp[1]);
		$tmp=$this->kText->embedMedia($output['description']);
			$output['description']=$tmp[0];
			if(is_array($tmp[1])) $output['embeddedmedias']=array_merge($output['embeddedmedias'],$tmp[1]);

		if($row['featuredimage']==0) $output['featuredimage']=false;
		else $output['featuredimage']=$this->imgs->getImage($row['featuredimage']);
		
		// get photogallery in the correct order
		$output['imgs']=array();
		if($vars['photogallery']==true && trim($row['photogallery'],",")!="" )
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

		$output['docs']=$this->docgallery->getList(TABLE_SHOP_MANUFACTURERS,$row['idsman']);
		$output['comments']=$this->getComments($row['idsman']);
		$output['translations']=array();
		foreach(explode("|",trim($row['translations'],"|")) as $trad) {
			if(substr($trad,0,2)!="") $output['translations'][substr($trad,0,2)]=$this->getManufacturerPermalinkById(substr($trad,3));
			}
		$output['traduzioni']=$row['translations'];

		return $output;
	}
	
}

