<?php /* (c) Kalamun.org - GNU/GPL 3 */

/* TEMPLATE */
class kTemplate {
	protected $id,$subtitle,$pagecontents,$pageindex,$pagepreview,$images,$documentgallery,$thumbs,$docs,$freetext,$tpl,$layout,$menu,$menuCollection,$menuSelected,$menuManualURL,$menuCrumbs,$dizionario,$metadata,$menuCurrentSettings,$categories,$categoriesList,$llurl,$kText;
	public $imgDB,$docDB,$mediaDB,$pageDB,$commentDB,$config,$menuStructure,$menuByRef,$contents,$currentConversion; //contents is a temporary recipient
	
	public function __construct() {
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR.'inc/setlang.inc.php');
		require_once($_SERVER['DOCUMENT_ROOT'].BASEDIR."inc/images.lib.php");

		$this->config=array();
		$query="SELECT * FROM `".TABLE_CONFIG."` WHERE `ll`='*' OR `ll`='**' OR `ll`='".LANG."'";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			$this->config[$row['param']]=$row;
		}

		$this->kText = new kText();

		// prevent language indication in URLs for default language if this option is turned on in Settings > General Settings
		$this->llurl=strtolower(LANG)."/";
		if(LANG==DEFAULT_LANG && $this->getVar('short_permalink_default_lang',1,'*')=="true") $this->llurl="";
		
		//load default template
		$this->tpl=$this->isMobile()?$this->getVar('template_default',2):$this->getVar('template_default',1);
		if($this->tpl=="") $this->tpl=$this->getVar('template_default',1);
		$this->layout=false;
		$this->metadata=array("titolo"=>"","traduzioni"=>array(),"template"=>"");
		$this->currentConversion=array();
		$this->menuCollection='';
		$this->menuCurrentSettings=array("sub"=>false,"recursive"=>false,"img"=>false,"ll"=>false);
		if(!isset($GLOBALS['__images'])) $GLOBALS['__images']=new kImages();
		
		//load all categories for the current language
		$this->categories=array(); //categories list, key is the id
		$this->categoriesStructure=array(); //structure of nested categories, key is order
		$query="SELECT * FROM `".TABLE_CATEGORIE."` WHERE `ll`='".LANG."' ORDER BY `ordine`";
		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++)
		{
			$this->categories[$row['idcat']]=$row;
			$this->categories[$row['idcat']]['permalink']=BASEDIR.$this->llurl.$this->getVar('dir_shop',1).'/'.$row['dir'];
		}

		foreach($this->categories as $row)
		{
			if($row['ref']==0)
			{
				$this->categoriesStructure[$row['tabella']][$row['ordine']]['idcat']=$row['idcat'];
				$this->categoriesStructure[$row['tabella']][$row['ordine']]["childNodes"]=$this->loadSubCategories($row['idcat']);
			}
		}

		//load dictionary terms for the current language
		$this->dizionario=array();
		$query="SELECT * FROM `".TABLE_DIZIONARIO."` WHERE `ll`='".LANG."'";
		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++)
		{
			$this->dizionario[$i]=array();
			$this->dizionario[$i]['param']=$row['param'];
			$this->dizionario[$i]['testo']=$row['testo'];
		}
	}
	
	/* return specific value from config db table */
	public function getVar($param,$value=1,$ll=false)
	{
		if($ll==false) $ll=LANG;
		if($value!=2) $value=1;
		if((trim($ll,'*')=="" || $ll==LANG) && isset($this->config[$param]['value'.$value]))
		{
			if(!isset($this->config[$param]['value'.$value])) $this->config[$param]['value'.$value]="";
			return $this->config[$param]['value'.$value];
		} else {
			$query="SELECT `value".$value."` FROM `".TABLE_CONFIG."` WHERE param='".ksql_real_escape_string($param)."' AND ll='".ksql_real_escape_string($ll)."' LIMIT 1";
			$results=ksql_query($query);
			$row=ksql_fetch_array($results);
			return $row['value'.$value];
		}
	}
	
	public function getLanguageURI($ll="")
	{
		$ll=trim($ll,"/ ");
		$ll=strtolower($ll);
		if($ll=="" || $ll==strtolower(LANG)) return $this->llurl;
		else return $ll."/";
	}

	public function setTemplate($tpl) {
		if($tpl!="") $this->tpl=$tpl;
		}
	public function getTemplate() {
		return $this->tpl;
		}
	public function setLayout($layout) {
		$this->layout=$layout;
		}
	public function getLayout() {
		return $this->layout;
		}
	public function setMetaData($metadata) {
		if(is_array($metadata)) {
			$this->metadata=array_merge($this->metadata,$metadata);
			if(isset($metadata['traduzioni'])) $this->setTrad($metadata['traduzioni']);
			}
		}
	public function getTitle() {
		if(isset($this->metadata['seo_title'])&&trim($this->metadata['seo_title'])!="") return $this->metadata['seo_title'];
		else return $this->metadata['titolo'];
		}
	public function getTranslations() {
		if(!is_array($this->metadata['traduzioni'])) $this->metadata['traduzioni']=array();
		return $this->metadata['traduzioni'];
		}

	public function setTrad($traduzioni) {
		if(!is_array($traduzioni)) {
			$tmp=explode("\n",$traduzioni);
			$traduzioni=array();
			foreach($tmp as $line) {
				if($line!="") {
					$line=explode("|",$line);
					$traduzioni[$line[0]]=$line[1];
					}
				}
			}
		$this->metadata['traduzioni']=$traduzioni;
		}

	//dictionary
	public function translate($param,$ll=false,$args=array()) {
		if($ll==false) $ll=LANG;
		if($ll==LANG) {
			foreach($this->dizionario as $d) {
				if($d['param']==$param) return $d['testo'];
				}
			}
		else {
			$query="SELECT * FROM ".TABLE_DIZIONARIO." WHERE param='".ksql_real_escape_string($param)."' AND ll='".ksql_real_escape_string($ll)."' LIMIT 1";
			$results=ksql_query($query);
			if($row=ksql_fetch_array($results)) return $row['testo'];
			}

		if(count($args)>0) {
			return vsprintf($param,$args);
			}
		return $param;
		}

	// navigation menu
	public function setMenuCollection($collection) {
		$this->menuCollection=$collection;
		}
	public function getMenuCollection($collection) {
		return $this->menuCollection;
		}
	public function getMenuContents($ll=false) {
		$this->menuContents=array();
		$this->menuByRef=array();
		if($ll==false) $ll=LANG;

		// prevent language indication in URLs for default language if this option is turned on in Settings > General Settings
		$llurl=strtolower($ll)."/";
		if($ll==DEFAULT_LANG && $this->getVar('short_permalink_default_lang',1,'*')=="true") $llurl="";
		
		/* get all menu's metadata */
		$meta=array();
		$query="SELECT * FROM ".TABLE_METADATA." WHERE `tabella`='".TABLE_MENU."'";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$meta[$row['id']][$row['param']]=$row['value'];
			}
		
		/* get and set menu's data */
		$query="SELECT * FROM ".TABLE_MENU." WHERE `ll`='".ksql_real_escape_string($ll)."' AND `collection`='".ksql_real_escape_string($this->menuCollection)."' ORDER BY ref";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			/* populate an array with all params for each menu element */
			$this->menuContents[$row['idmenu']]=$row;
			$this->menuContents[$row['idmenu']]['href']="";
			if($row['url']!="") {
				if(preg_match("/^https?:\/\/.*/",$this->menuContents[$row['idmenu']]['url'])) $this->menuContents[$row['idmenu']]['href']=$this->menuContents[$row['idmenu']]['url'];
				else $this->menuContents[$row['idmenu']]['href']=BASEDIR.$llurl.$this->menuContents[$row['idmenu']]['url'];
				}
			$this->menuContents[$row['idmenu']]['photogallery']=array();
			foreach(explode(",",trim($row['photogallery'],",")) as $idimg)
			{
				if(!isset($this->menuImgs[$idimg])) $this->menuImgs[$idimg]=$GLOBALS['__images']->getImage($idimg);
				$this->menuContents[$row['idmenu']]['photogallery'][]=$this->menuImgs[$idimg];
			}
			$this->menuContents[$row['idmenu']]['ancestor']=false;
			$this->menuContents[$row['idmenu']]['selected']=$this->menuIsSelected($row['idmenu']);
			if($this->menuContents[$row['idmenu']]['selected']==true) $this->menuSelected=$row['idmenu'];
			if(isset($meta[$row['idmenu']])) $this->menuContents[$row['idmenu']]['metadata']=$meta[$row['idmenu']];
			
			/* populate an array with the order and id for each ref */
			$this->menuByRef[$row['ref']][$row['ordine']]=$row['idmenu'];
			}
		}
	public function getMenuStructure($ref=0,$recursive=true,$ll=false) {
		$this->menuStructure=$this->getSubMenuStructure($ref,$recursive,$ll);
		return $this->menuStructure;
		}
	private function getSubMenuStructure($ref=0,$recursive=true,$ll=false) {
		$menu=array('idmenu'=>$ref);
		if($ll==false) $ll=LANG;
		if(isset($this->menuByRef[$ref])) {
			foreach($this->menuByRef[$ref] as $ordine=>$idmenu) {
				if($recursive==true) $menu[$ordine]=$this->getSubMenuStructure($idmenu);
				$menu[$ordine]['idmenu']=$idmenu;
				}
			ksort($menu);
			}
		return $menu;
		}
	private function printSubMenu($submenu,$img=false,$labels=true) {
		if(count($submenu)>1) {
			$menu='<ul>';
			$i=0;
			foreach($submenu as $k=>$v) {
				if($k!='idmenu'&$k!='selected'&$k!='ancestor') {
					$m=$this->menuContents[$v['idmenu']];
					$class="";
					if($i==0) $class.="first ";
					if($k==count($submenu)-1) $class.="last ";
					if($m['selected']==true) $class.="sel";
					elseif($m['ancestor']==true) $class.="ancestor";
					$class=trim($class);
					$menu.='<li'.($class!=""?' class="'.$class.'"':'').'>';
					$menu.='<a';
					if($m['href']!="") $menu.=' href="'.$m['href'].'"'; //if url is not empty
					$menu.=($class!=""?' class="'.$class.'"':'').'>';
					if($img==true && !empty($m['photogallery']))
					{
						foreach($m['photogallery'] as $img)
						{
							$menu.='<img src="'.$img['url'].'" width="'.$img['width'].'" height="'.$img['height'].'" alt="'.addslashes($labels==true ? $img['alt'] : $m['label']).'" />';
						}
					}
					if($labels==true) $menu.=$m['label'];
					$menu.='</a>';
					$menu.=$this->printSubMenu($v,$img,$labels);
					$menu.='</li>';
					$i++;
					}
				}
			$menu.='</ul>';
			}
		else $menu="";
		return $menu;
		}
	public function menuIsSelected($idmenu) {
		$row=$this->menuContents[$idmenu];
		$url=explode("/",$row['url']);
		if($this->menuManualURL!=false) {
			$page=$this->menuManualURL;
			if(rtrim($row['url'],"/")==$page || rtrim($row['url'],"/")==b3_htmlize($page,true,"")) return true;
			}
		else {
			$page=substr(rtrim(urldecode($_SERVER['REQUEST_URI']),"/"),(strlen(BASEDIR)+strlen($this->getLanguageURI(LANG))));
			if(
				rtrim($row['url'],"/")==$page ||
				rtrim($row['url'],"/")==b3_htmlize($page,true,"") ||
				($GLOBALS['__dir__']==kGetVar('dir_news',1) && $url[0]==$GLOBALS['__dir__']) ||
				($GLOBALS['__dir__']==kGetVar('dir_shop',1) && $url[0]==$GLOBALS['__dir__']) ||
				($GLOBALS['__dir__']==kGetVar('dir_photogallery',1) && $url[0]==$GLOBALS['__dir__']) ||
				($GLOBALS['__dir__']==kGetVar('dir_private',1) && $url[0]==$GLOBALS['__dir__']) ||
				($GLOBALS['__dir__']==kGetVar('dir_users',1) && $url[0]==$GLOBALS['__dir__'])
				) return true;
			}
		return false;
		}
	private function menuAddAncestors() {
		$this->menuCrumbs=array();
		if($this->menuSelected!=false) {
			$ref=$this->menuContents[$this->menuSelected]['ref'];
			while($ref>0) {
				$this->menuContents[$ref]['ancestor']=true;
				array_unshift($this->menuCrumbs,$ref);
				$ref=$this->menuContents[$ref]['ref'];
				}
			}
		}
	public function loadMenu($sub=false,$recursive=true,$img=false,$labels=true,$ll=false,$template='menu') {
		//prevent to reload menu when the requested menu is the same of the loaded one
		if(!isset($this->menuContents)||$this->menuContents==null||$this->menuCurrentSettings['sub']!=$sub||$this->menuCurrentSettings['recursive']!=$recursive||$this->menuCurrentSettings['img']!=$img||$this->menuCurrentSettings['ll']!=$ll||$this->menuCurrentSettings['collection']!=$this->menuCollection) {
			//first load of menu contents
			$this->menuCurrentSettings=array("sub"=>$sub,"recursive"=>$recursive,"img"=>$img,"ll"=>$ll,"collection"=>$this->menuCollection);
			if($sub==false) $sub=0;
			$this->menuImgs=array();
			$this->menuSelected=false;
			$this->menuCrumbs=array();
			$this->getMenuContents();
			}
		$this->menuStructure=$this->getMenuStructure($sub,$recursive,$ll);
		$this->menuAddAncestors();
		if($template=='') $template='menu';
		$this->menu=$this->getSubTemplate($template); //try with template
		if($this->menu==false) $this->menu=$this->printSubMenu($this->menuStructure,$img,$labels); //if template not found, generate default markup
		}
	public function printMenu($vars) {
		$this->loadMenu($vars['sub'],$vars['recursive'],$vars['img'],$vars['labels'],$vars['ll'],$vars['template']);
		return $this->menu;
		}
	public function getMenuSelected() {
		return $this->menuContents[$this->menuSelected];
		}
	public function setMenuSelectedByURL($url) {
		$url=trim($url," /");
		$this->menuManualURL=$url;
		$this->menuContents=null;
		}
	public function getMenuCrumbs($mode="print") {
		if($mode=="array") {
			$output=array();
			foreach($this->menuCrumbs as $c) {
				$output[]=$this->menuContents[$c];
				}
			if($this->menuSelected!=false) {
				$output[]=$this->menuContents[$this->menuSelected];
				}
			return $output;
			}
		else {
			$output="<ul>";
			foreach($this->menuCrumbs as $c) {
				$output.="<li>";
				$output.='<a href="'.BASEDIR.$this->getLanguageURI(LANG).$this->menuContents[$c]['url'].'">'.$this->menuContents[$c]['label'].'</a>';
				$output.="</li>";
				}
			if($this->menuSelected!=false) {
				$output.="<li>";
				$output.='<a href="'.BASEDIR.$this->getLanguageURI(LANG).$this->menuContents[$this->menuSelected]['url'].'">'.$this->menuContents[$this->menuSelected]['label'].'</a>';
				$output.="</li>";
				}
			$output.="</ul>";
			return $output;
			}
		}

	/* languages */
	function getLanguages($translations=array()) {
		$output=array();
		$translations[LANG]=$_SERVER['REQUEST_URI'];
		$query="SELECT * FROM ".TABLE_LINGUE." WHERE online='s' ORDER BY `ordine`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$output[$row['ll']]=$row;
			if(!isset($translations[$row['ll']]) || trim($translations[$row['ll']]," /.")=="") $output[$row['ll']]['url']=BASEDIR.strtolower($row['ll']).'/';
			else $output[$row['ll']]['url']=$translations[$row['ll']];

			// prevent language indication in URLs for default language if this option is turned on in Settings > General Settings
			if($row['ll']==DEFAULT_LANG && $this->getVar('short_permalink_default_lang',1,'*')=="true") $output[$row['ll']]['url']=str_replace("/".strtolower(DEFAULT_LANG)."/", "/", $output[$row['ll']]['url']);
			
			$icon=BASEDIR.DIR_TEMPLATE.$this->tpl.'/lang/'.$row['code'].'.png';
			if(!file_exists($_SERVER['DOCUMENT_ROOT'].$icon)) {
				$icon=BASEDIR.'img/lang/'.strtolower($row['ll']).'.gif';
				}
			$output[$row['ll']]['defaultIcon']=$icon;
			}
		return $output;
		}
	function printLanguages($vars) {
		if(!isset($vars['flags'])) $vars['flags']=true;
		if(!isset($vars['labels'])) $vars['labels']=false;
		if(!isset($vars['shortcodes'])) $vars['shortcodes']=false;
		if(!isset($vars['translations'])) $vars['translations']=false;
		$menu="";
		$langs=$this->getLanguages($vars['translations']);
		if(count($langs)>1) {
			$menu.='<ul>';
			foreach($langs as $k=>$v) {
				$icon=BASEDIR.DIR_TEMPLATE.$this->tpl.'/lang/'.$v['code'].'.png';
				if(!file_exists($_SERVER['DOCUMENT_ROOT'].$icon)) {
					$icon=BASEDIR.'img/lang/'.strtolower($v['ll']).'.gif';
					}
				$menu.='<li><a href="'.$v['url'].'"'.($k==LANG?' class="sel"':'').'>';
				if($vars['flags']==true) {
					$size=getimagesize($_SERVER['DOCUMENT_ROOT'].$icon);
					$menu.='<img src="'.$icon.'" '.$size[3].' alt="'.$v['ll'].'" />';
					}
				if($vars['labels']==true) {
					$menu.=$v['lingua'];
					}
				if($vars['shortcodes']==true) {
					$menu.=$v['ll'];
					}
				$menu.='</a></li>';
				}
			$menu.='</ul>';
			}
		return $menu;
		}

	/* categories */
	
	// return the list of subcategories recursively. it is only used on init
	private function loadSubCategories($refId)
	{
		$output=array();
		foreach($this->categories as $k=>$cat)
		{
			if($cat['ref']==$refId)
			{
				$output[$cat['ordine']]['idcat']=$cat['idcat'];
				$output[$cat['ordine']]["childNodes"]=$this->loadSubCategories($cat['idcat']);
			}
		}
		return $output;
	}

	public function getCategories($vars)
	{
		if(!isset($vars['table'])) return false;
		if(!isset($vars['ref'])) $vars['ref']=0;
	
		$output=array();
		if(!empty($this->categoriesStructure[$vars['table']]))
		{
			$substructure=$this->getCategoryChildsByIdcat($vars['ref'],$this->categoriesStructure[$vars['table']]);
			$output=$this->getCategoriesBranch($substructure);
		}
		return $output;
	}
	
	// starting from a full category structure, it returns only the interested node with subnodes
	public function getCategoryChildsByIdcat($idcat,$substructure)
	{
		if($idcat==0) return $substructure;
		
		foreach($substructure as $cat) {
			if($cat['idcat']==$idcat) return $cat['childNodes'];
			else {
				$output=$this->getCategoryChildsByIdcat($idcat,$cat['childNodes']);
				if($output!=false) return $output;
			}
		}
		return false;
	}
	
	// return a branch of category tree, filled with all the category data
	public function getCategoriesBranch($substructure)
	{
		$output=array();
		if(empty($substructure)) return false;
		foreach($substructure as $k=>$cat) {
			if(!empty($this->categories[$cat['idcat']]))
			{

				// load images (if not already loaded)
				if(!isset($this->categories[$cat['idcat']]['imgs']))
				{
					$this->categories[$cat['idcat']]['imgs']=array();

					if(trim($this->categories[$cat['idcat']]['photogallery'],",")!="")
					{
						$conditions="";
						foreach(explode(",",trim($this->categories[$cat['idcat']]['photogallery'],",")) as $idimg)
						{
							$conditions.="`idimg`='".intval($idimg)."' OR ";
						}
						$conditions.="`idimg`='0'";
						
						$imgs=$GLOBALS['__images']->getList(false,false,false,$conditions);
						
						foreach(explode(",",trim($this->categories[$cat['idcat']]['photogallery'],",")) as $idimg)
						{
							foreach($imgs as $img)
							{
								if($img['idimg']==$idimg) $this->categories[$cat['idcat']]['imgs'][]=$img;
							}
						}
					}
				}

				$this->categories[$cat['idcat']]['featuredimage'] = ($this->categories[$cat['idcat']]['featuredimage']>0 ? $GLOBALS['__images']->getImage($this->categories[$cat['idcat']]['featuredimage']) : array());
				
				$this->categories[$cat['idcat']]['description'] = $this->kText->formatText($this->categories[$cat['idcat']]['description']);
				$tmp=$this->kText->embedImg($this->categories[$cat['idcat']]['description']);
				$tmp=$this->kText->embedDocs($tmp[0]);
				$tmp=$this->kText->embedMedia($tmp[0]);
				$this->categories[$cat['idcat']]['description'] = $tmp[0];
				$this->categories[$this->categories[$cat['idcat']]['idcat']]['description'] = $this->categories[$cat['idcat']]['description'];

				$output[$k]=$this->categories[$cat['idcat']];

				if(!empty($cat['childNodes'])) $output[$k]['childNodes']=$this->getCategoriesBranch($cat['childNodes']);
			}
		}
		return $output;
	}

	/* return only one category: for multiple categories, use getCategoresList() */
	public function getCategory($vars)
	{
		// parse also the html entities alternative
		$vars['htmldir']= (isset($vars['dir']) ? b3_htmlize($vars['dir'],"") : "");
		foreach($this->categories as $k=>$cat) {
			$results=true;
			if(isset($vars['dir']) && $cat['dir']!=$vars['dir'] && $cat['dir']!=$vars['htmldir']) $results=false;
			if(isset($vars['table']) && $cat['tabella']!=$vars['table']) $results=false;
			if(isset($vars['id']) && $cat['idcat']!=$vars['id']) $results=false;
		
			if($results==true)
			{
				if(!isset($cat['imgs']))
				{
					$this->categories[$k]['imgs']=array();
					
					if(trim($cat['photogallery'],",")!="")
					{
						$conditions="";
						foreach(explode(",",trim($cat['photogallery'],",")) as $idimg)
						{
							$conditions.="`idimg`='".intval($idimg)."' OR ";
						}
						$conditions.="`idimg`='0'";
						
						$imgs=$GLOBALS['__images']->getList(false,false,false,$conditions);
						
						foreach(explode(",",trim($cat['photogallery'],",")) as $idimg)
						{
							foreach($imgs as $img)
							{
								if($img['idimg']==$idimg) $this->categories[$k]['imgs'][]=$img;
							}
						}
					}

					$cat['imgs']=$this->categories[$k]['imgs'];
				}
				return $cat;
			}
		}
		return false;
	}
	
	/* return an array of categories */
	public function getCategoresList($vars)
	{
		$output=array();

		$vars['htmldir']= (isset($vars['dir']) ? b3_htmlize($vars['dir'],"") : "");
		foreach($this->categories as $k=>$cat) {
			if((!empty($vars['table']) && $vars['table']==$cat['tabella']) ||
				(!empty($vars['id']) && $vars['id']==$cat['idcat']) ||
				(!empty($vars['dir']) && ($vars['dir']==$cat['dir'] || $vars['htmldir']==$cat['dir']))
				) $output[]=$cat;
		}

		return $output;
	}

	public function getParentCategories($vars)
	{
		$output=array();
		foreach($this->categories as $cat)
		{
			$results=true;
			if(isset($vars['dir'])&&$cat['dir']!=$vars['dir']) $results=false;
			if(isset($vars['table'])&&$cat['tabella']!=$vars['table']) $results=false;
			if(isset($vars['id'])&&$cat['idcat']!=$vars['id']) $results=false;
			if($results==true) $currentCat=$cat;
		}
		if(isset($currentCat))
		{
			for($ref=$currentCat['ref'];$ref>0;)
			{
				if(!isset($this->categories[$ref])) break;
				$output[]=$this->categories[$ref];
				$ref=$this->categories[$ref]['ref'];
			}
		}
		$output=array_reverse($output);
		return $output;
	}
	
	/* template engine */
	function getSubTemplate($f,$tpl='') {
		$__template=$this;
		if($tpl=="") $tpl=$this->tpl;
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_TEMPLATE.$tpl.'/inc/'.$f.'.php')) $tpl='bettino';
		if(is_file($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_TEMPLATE.$tpl.'/inc/'.$f.'.php')) {
			ob_start();
			include($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_TEMPLATE.$tpl.'/inc/'.$f.'.php');
			$contents=ob_get_contents();
			ob_end_clean();
			return $contents;
			}
		return false;
		}
		
	function get($tpl=false) {
		$__template=$this;
		$file="";
		if($tpl==false||trim($tpl)=="") $tpl=$this->tpl;
		if($this->layout!=false) $file='layouts/'.$this->layout;
		else {
			if($this->isHome()) $file="home.php";
			elseif($this->isNews()) $file="news.php";
			elseif($this->isShopCart()) $file="cart.php";
			elseif($this->isShopManufacturer()) $file="manufacturers.php";
			elseif($this->isShop()) $file="shop.php";
			elseif($this->isPrivate()) $file="private.php";
			elseif($this->isPhotogallery()) $file="photogallery.php";
			elseif($this->isUsers()) $file="users.php";
			elseif($this->isFeed()) $file="feed.php";
			elseif($this->isSearch()) $file="search.php";
			elseif($this->isSitemap()) $file="sitemap.php";
			elseif(!$GLOBALS['__pages']->pageExists(trim($GLOBALS['__dir__'].'/'.$GLOBALS['__subdir__'].'/'.$GLOBALS['__subsubdir__'],"/"))) { header("HTTP/1.0 404 Not Found"); $file="404.php"; }
			else $file="index.php";
			}
		set_include_path(get_include_path().PATH_SEPARATOR.$_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_TEMPLATE.$tpl);
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_TEMPLATE.$tpl.'/'.$file)) {
			if(file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_TEMPLATE.$tpl.'/index.php')) $file="index.php";
			else $tpl=$this->tpl;
			}
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_TEMPLATE.$tpl.'/'.$file)) $file="index.php";
		if(is_file($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_TEMPLATE.$tpl.'/'.$file)) {
			$this->setTemplate($tpl);
			if(ob_get_length()>0) { ob_clean(); }
			ob_start();
			include($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_TEMPLATE.$tpl.'/'.$file);
			$contents=ob_get_contents();
			ob_end_clean();
			echo $contents;
			return true;
			}
		return false;
		}
	
	public function getTemplateDir() {
		$tpl=$this->tpl;
		if($tpl==""||!file_exists($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.DIR_TEMPLATE.$tpl)) $tpl="bettino";
		return DIR_TEMPLATE.$tpl.'/';
		}
		
	/* detect mobile devices */
	public function isMobile($returnModel=false)
	{
		/* thanks to php-mobile-detect http://code.google.com/p/php-mobile-detect/ */

        if(isset($_SERVER['HTTP_ACCEPT']) &&
				(strpos($_SERVER['HTTP_ACCEPT'], 'application/x-obml2d') !== false || // Opera Mini
				 strpos($_SERVER['HTTP_ACCEPT'], 'application/vnd.rim.html') !== false || // BlackBerry devices
				 strpos($_SERVER['HTTP_ACCEPT'], 'text/vnd.wap.wml') !== false ||
				 strpos($_SERVER['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml') !== false) ||
			isset($_SERVER['HTTP_X_WAP_PROFILE'])             ||
			isset($_SERVER['HTTP_X_WAP_CLIENTID'])            ||
			isset($_SERVER['HTTP_WAP_CONNECTION'])            ||
			isset($_SERVER['HTTP_PROFILE'])                   ||
			isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])      || // Reported by Nokia devices (eg. C3)
			isset($_SERVER['HTTP_X_NOKIA_IPADDRESS'])         ||
			isset($_SERVER['HTTP_X_NOKIA_GATEWAY_ID'])        ||
			isset($_SERVER['HTTP_X_ORANGE_ID'])               ||
			isset($_SERVER['HTTP_X_VODAFONE_3GPDPCONTEXT'])   ||
			isset($_SERVER['HTTP_X_HUAWEI_USERID'])           ||
			isset($_SERVER['HTTP_UA_OS'])                     || // Reported by Windows Smartphones.
			isset($_SERVER['HTTP_X_MOBILE_GATEWAY'])          || // Reported by Verizon, Vodafone proxy system.
			isset($_SERVER['HTTP_X_ATT_DEVICEID'])            || // Seend this on HTC Sensation. @ref: SensationXE_Beats_Z715e
			(isset($_SERVER['HTTP_UA_CPU']) &&
					$_SERVER['HTTP_UA_CPU'] == 'ARM'          // Seen this on a HTC.
				)
			) return $returnModel==false?true:'GenericPhone';

		$phoneDevices = array(
			'iPhone'        => '\biPhone\b|\biPod\b', // |\biTunes
			'BlackBerry'    => 'BlackBerry|\bBB10\b|rim[0-9]+',
			'HTC'           => 'HTC|HTC.*(Sensation|Evo|Vision|Explorer|6800|8100|8900|A7272|S510e|C110e|Legend|Desire|T8282)|APX515CKT|Qtek9090|APA9292KT|HD_mini|Sensation.*Z710e|PG86100|Z715e|Desire.*(A8181|HD)|ADR6200|ADR6400L|ADR6425|001HT|Inspire 4G|Android.*\bEVO\b|T-Mobile G1|Z520m',
			'Nexus'         => 'Nexus One|Nexus S|Galaxy.*Nexus|Android.*Nexus.*Mobile|Nexus 4|Nexus 5|Nexus 6',
			'Dell'          => 'Dell.*Streak|Dell.*Aero|Dell.*Venue|DELL.*Venue Pro|Dell Flash|Dell Smoke|Dell Mini 3iX|XCD28|XCD35|\b001DL\b|\b101DL\b|\bGS01\b',
			'Motorola'      => 'Motorola|DROIDX|DROID BIONIC|\bDroid\b.*Build|Android.*Xoom|HRI39|MOT-|A1260|A1680|A555|A853|A855|A953|A955|A956|Motorola.*ELECTRIFY|Motorola.*i1|i867|i940|MB200|MB300|MB501|MB502|MB508|MB511|MB520|MB525|MB526|MB611|MB612|MB632|MB810|MB855|MB860|MB861|MB865|MB870|ME501|ME502|ME511|ME525|ME600|ME632|ME722|ME811|ME860|ME863|ME865|MT620|MT710|MT716|MT720|MT810|MT870|MT917|Motorola.*TITANIUM|WX435|WX445|XT300|XT301|XT311|XT316|XT317|XT319|XT320|XT390|XT502|XT530|XT531|XT532|XT535|XT603|XT610|XT611|XT615|XT681|XT701|XT702|XT711|XT720|XT800|XT806|XT860|XT862|XT875|XT882|XT883|XT894|XT901|XT907|XT909|XT910|XT912|XT928|XT926|XT915|XT919|XT925|XT1021|\bMoto E\b',
			'Samsung'       => 'Samsung|SM-G9250|GT-19300|SGH-I337|BGT-S5230|GT-B2100|GT-B2700|GT-B2710|GT-B3210|GT-B3310|GT-B3410|GT-B3730|GT-B3740|GT-B5510|GT-B5512|GT-B5722|GT-B6520|GT-B7300|GT-B7320|GT-B7330|GT-B7350|GT-B7510|GT-B7722|GT-B7800|GT-C3010|GT-C3011|GT-C3060|GT-C3200|GT-C3212|GT-C3212I|GT-C3262|GT-C3222|GT-C3300|GT-C3300K|GT-C3303|GT-C3303K|GT-C3310|GT-C3322|GT-C3330|GT-C3350|GT-C3500|GT-C3510|GT-C3530|GT-C3630|GT-C3780|GT-C5010|GT-C5212|GT-C6620|GT-C6625|GT-C6712|GT-E1050|GT-E1070|GT-E1075|GT-E1080|GT-E1081|GT-E1085|GT-E1087|GT-E1100|GT-E1107|GT-E1110|GT-E1120|GT-E1125|GT-E1130|GT-E1160|GT-E1170|GT-E1175|GT-E1180|GT-E1182|GT-E1200|GT-E1210|GT-E1225|GT-E1230|GT-E1390|GT-E2100|GT-E2120|GT-E2121|GT-E2152|GT-E2220|GT-E2222|GT-E2230|GT-E2232|GT-E2250|GT-E2370|GT-E2550|GT-E2652|GT-E3210|GT-E3213|GT-I5500|GT-I5503|GT-I5700|GT-I5800|GT-I5801|GT-I6410|GT-I6420|GT-I7110|GT-I7410|GT-I7500|GT-I8000|GT-I8150|GT-I8160|GT-I8190|GT-I8320|GT-I8330|GT-I8350|GT-I8530|GT-I8700|GT-I8703|GT-I8910|GT-I9000|GT-I9001|GT-I9003|GT-I9010|GT-I9020|GT-I9023|GT-I9070|GT-I9082|GT-I9100|GT-I9103|GT-I9220|GT-I9250|GT-I9300|GT-I9305|GT-I9500|GT-I9505|GT-M3510|GT-M5650|GT-M7500|GT-M7600|GT-M7603|GT-M8800|GT-M8910|GT-N7000|GT-S3110|GT-S3310|GT-S3350|GT-S3353|GT-S3370|GT-S3650|GT-S3653|GT-S3770|GT-S3850|GT-S5210|GT-S5220|GT-S5229|GT-S5230|GT-S5233|GT-S5250|GT-S5253|GT-S5260|GT-S5263|GT-S5270|GT-S5300|GT-S5330|GT-S5350|GT-S5360|GT-S5363|GT-S5369|GT-S5380|GT-S5380D|GT-S5560|GT-S5570|GT-S5600|GT-S5603|GT-S5610|GT-S5620|GT-S5660|GT-S5670|GT-S5690|GT-S5750|GT-S5780|GT-S5830|GT-S5839|GT-S6102|GT-S6500|GT-S7070|GT-S7200|GT-S7220|GT-S7230|GT-S7233|GT-S7250|GT-S7500|GT-S7530|GT-S7550|GT-S7562|GT-S7710|GT-S8000|GT-S8003|GT-S8500|GT-S8530|GT-S8600|SCH-A310|SCH-A530|SCH-A570|SCH-A610|SCH-A630|SCH-A650|SCH-A790|SCH-A795|SCH-A850|SCH-A870|SCH-A890|SCH-A930|SCH-A950|SCH-A970|SCH-A990|SCH-I100|SCH-I110|SCH-I400|SCH-I405|SCH-I500|SCH-I510|SCH-I515|SCH-I600|SCH-I730|SCH-I760|SCH-I770|SCH-I830|SCH-I910|SCH-I920|SCH-I959|SCH-LC11|SCH-N150|SCH-N300|SCH-R100|SCH-R300|SCH-R351|SCH-R400|SCH-R410|SCH-T300|SCH-U310|SCH-U320|SCH-U350|SCH-U360|SCH-U365|SCH-U370|SCH-U380|SCH-U410|SCH-U430|SCH-U450|SCH-U460|SCH-U470|SCH-U490|SCH-U540|SCH-U550|SCH-U620|SCH-U640|SCH-U650|SCH-U660|SCH-U700|SCH-U740|SCH-U750|SCH-U810|SCH-U820|SCH-U900|SCH-U940|SCH-U960|SCS-26UC|SGH-A107|SGH-A117|SGH-A127|SGH-A137|SGH-A157|SGH-A167|SGH-A177|SGH-A187|SGH-A197|SGH-A227|SGH-A237|SGH-A257|SGH-A437|SGH-A517|SGH-A597|SGH-A637|SGH-A657|SGH-A667|SGH-A687|SGH-A697|SGH-A707|SGH-A717|SGH-A727|SGH-A737|SGH-A747|SGH-A767|SGH-A777|SGH-A797|SGH-A817|SGH-A827|SGH-A837|SGH-A847|SGH-A867|SGH-A877|SGH-A887|SGH-A897|SGH-A927|SGH-B100|SGH-B130|SGH-B200|SGH-B220|SGH-C100|SGH-C110|SGH-C120|SGH-C130|SGH-C140|SGH-C160|SGH-C170|SGH-C180|SGH-C200|SGH-C207|SGH-C210|SGH-C225|SGH-C230|SGH-C417|SGH-C450|SGH-D307|SGH-D347|SGH-D357|SGH-D407|SGH-D415|SGH-D780|SGH-D807|SGH-D980|SGH-E105|SGH-E200|SGH-E315|SGH-E316|SGH-E317|SGH-E335|SGH-E590|SGH-E635|SGH-E715|SGH-E890|SGH-F300|SGH-F480|SGH-I200|SGH-I300|SGH-I320|SGH-I550|SGH-I577|SGH-I600|SGH-I607|SGH-I617|SGH-I627|SGH-I637|SGH-I677|SGH-I700|SGH-I717|SGH-I727|SGH-i747M|SGH-I777|SGH-I780|SGH-I827|SGH-I847|SGH-I857|SGH-I896|SGH-I897|SGH-I900|SGH-I907|SGH-I917|SGH-I927|SGH-I937|SGH-I997|SGH-J150|SGH-J200|SGH-L170|SGH-L700|SGH-M110|SGH-M150|SGH-M200|SGH-N105|SGH-N500|SGH-N600|SGH-N620|SGH-N625|SGH-N700|SGH-N710|SGH-P107|SGH-P207|SGH-P300|SGH-P310|SGH-P520|SGH-P735|SGH-P777|SGH-Q105|SGH-R210|SGH-R220|SGH-R225|SGH-S105|SGH-S307|SGH-T109|SGH-T119|SGH-T139|SGH-T209|SGH-T219|SGH-T229|SGH-T239|SGH-T249|SGH-T259|SGH-T309|SGH-T319|SGH-T329|SGH-T339|SGH-T349|SGH-T359|SGH-T369|SGH-T379|SGH-T409|SGH-T429|SGH-T439|SGH-T459|SGH-T469|SGH-T479|SGH-T499|SGH-T509|SGH-T519|SGH-T539|SGH-T559|SGH-T589|SGH-T609|SGH-T619|SGH-T629|SGH-T639|SGH-T659|SGH-T669|SGH-T679|SGH-T709|SGH-T719|SGH-T729|SGH-T739|SGH-T746|SGH-T749|SGH-T759|SGH-T769|SGH-T809|SGH-T819|SGH-T839|SGH-T919|SGH-T929|SGH-T939|SGH-T959|SGH-T989|SGH-U100|SGH-U200|SGH-U800|SGH-V205|SGH-V206|SGH-X100|SGH-X105|SGH-X120|SGH-X140|SGH-X426|SGH-X427|SGH-X475|SGH-X495|SGH-X497|SGH-X507|SGH-X600|SGH-X610|SGH-X620|SGH-X630|SGH-X700|SGH-X820|SGH-X890|SGH-Z130|SGH-Z150|SGH-Z170|SGH-ZX10|SGH-ZX20|SHW-M110|SPH-A120|SPH-A400|SPH-A420|SPH-A460|SPH-A500|SPH-A560|SPH-A600|SPH-A620|SPH-A660|SPH-A700|SPH-A740|SPH-A760|SPH-A790|SPH-A800|SPH-A820|SPH-A840|SPH-A880|SPH-A900|SPH-A940|SPH-A960|SPH-D600|SPH-D700|SPH-D710|SPH-D720|SPH-I300|SPH-I325|SPH-I330|SPH-I350|SPH-I500|SPH-I600|SPH-I700|SPH-L700|SPH-M100|SPH-M220|SPH-M240|SPH-M300|SPH-M305|SPH-M320|SPH-M330|SPH-M350|SPH-M360|SPH-M370|SPH-M380|SPH-M510|SPH-M540|SPH-M550|SPH-M560|SPH-M570|SPH-M580|SPH-M610|SPH-M620|SPH-M630|SPH-M800|SPH-M810|SPH-M850|SPH-M900|SPH-M910|SPH-M920|SPH-M930|SPH-N100|SPH-N200|SPH-N240|SPH-N300|SPH-N400|SPH-Z400|SWC-E100|SCH-i909|GT-N7100|GT-N7105|SCH-I535|SM-N900A|SGH-I317|SGH-T999L|GT-S5360B|GT-I8262|GT-S6802|GT-S6312|GT-S6310|GT-S5312|GT-S5310|GT-I9105|GT-I8510|GT-S6790N|SM-G7105|SM-N9005|GT-S5301|GT-I9295|GT-I9195|SM-C101|GT-S7392|GT-S7560|GT-B7610|GT-I5510|GT-S7582|GT-S7530E|GT-I8750|SM-G9006V|SM-G9008V|SM-G9009D|SM-G900A|SM-G900D|SM-G900F|SM-G900H|SM-G900I|SM-G900J|SM-G900K|SM-G900L|SM-G900M|SM-G900P|SM-G900R4|SM-G900S|SM-G900T|SM-G900V|SM-G900W8|SHV-E160K|SCH-P709|SCH-P729|SM-T2558|GT-I9205',
			'LG'            => '\bLG\b;|LG[- ]?(C800|C900|E400|E610|E900|E-900|F160|F180K|F180L|F180S|730|855|L160|LS740|LS840|LS970|LU6200|MS690|MS695|MS770|MS840|MS870|MS910|P500|P700|P705|VM696|AS680|AS695|AX840|C729|E970|GS505|272|C395|E739BK|E960|L55C|L75C|LS696|LS860|P769BK|P350|P500|P509|P870|UN272|US730|VS840|VS950|LN272|LN510|LS670|LS855|LW690|MN270|MN510|P509|P769|P930|UN200|UN270|UN510|UN610|US670|US740|US760|UX265|UX840|VN271|VN530|VS660|VS700|VS740|VS750|VS910|VS920|VS930|VX9200|VX11000|AX840A|LW770|P506|P925|P999|E612|D955|D802)',
			'Sony'          => 'SonyST|SonyLT|SonyEricsson|SonyEricssonLT15iv|LT18i|E10i|LT28h|LT26w|SonyEricssonMT27i|C5303|C6902|C6903|C6906|C6943|D2533',
			'Asus'          => 'Asus.*Galaxy|PadFone.*Mobile',
			'Micromax'      => 'Micromax.*\b(A210|A92|A88|A72|A111|A110Q|A115|A116|A110|A90S|A26|A51|A35|A54|A25|A27|A89|A68|A65|A57|A90)\b',
			'Palm'          => 'PalmSource|Palm',
			'Vertu'         => 'Vertu|Vertu.*Ltd|Vertu.*Ascent|Vertu.*Ayxta|Vertu.*Constellation(F|Quest)?|Vertu.*Monika|Vertu.*Signature', // Just for fun ;)
			'Pantech'       => 'PANTECH|IM-A850S|IM-A840S|IM-A830L|IM-A830K|IM-A830S|IM-A820L|IM-A810K|IM-A810S|IM-A800S|IM-T100K|IM-A725L|IM-A780L|IM-A775C|IM-A770K|IM-A760S|IM-A750K|IM-A740S|IM-A730S|IM-A720L|IM-A710K|IM-A690L|IM-A690S|IM-A650S|IM-A630K|IM-A600S|VEGA PTL21|PT003|P8010|ADR910L|P6030|P6020|P9070|P4100|P9060|P5000|CDM8992|TXT8045|ADR8995|IS11PT|P2030|P6010|P8000|PT002|IS06|CDM8999|P9050|PT001|TXT8040|P2020|P9020|P2000|P7040|P7000|C790',
			'Fly'           => 'IQ230|IQ444|IQ450|IQ440|IQ442|IQ441|IQ245|IQ256|IQ236|IQ255|IQ235|IQ245|IQ275|IQ240|IQ285|IQ280|IQ270|IQ260|IQ250',
			'Wiko'          => 'KITE 4G|HIGHWAY|GETAWAY|STAIRWAY|DARKSIDE|DARKFULL|DARKNIGHT|DARKMOON|SLIDE|WAX 4G|RAINBOW|BLOOM|SUNSET|GOA|LENNY|BARRY|IGGY|OZZY|CINK FIVE|CINK PEAX|CINK PEAX 2|CINK SLIM|CINK SLIM 2|CINK +|CINK KING|CINK PEAX|CINK SLIM|SUBLIM',
			'iMobile'       => 'i-mobile (IQ|i-STYLE|idea|ZAA|Hitz)',
			'SimValley'     => '\b(SP-80|XT-930|SX-340|XT-930|SX-310|SP-360|SP60|SPT-800|SP-120|SPT-800|SP-140|SPX-5|SPX-8|SP-100|SPX-8|SPX-12)\b',
			'Wolfgang'      => 'AT-B24D|AT-AS50HD|AT-AS40W|AT-AS55HD|AT-AS45q2|AT-B26D|AT-AS50Q',
			'Alcatel'       => 'Alcatel',
			'Nintendo' 		=> 'Nintendo 3DS',
			'Amoi'          => 'Amoi',
			'INQ'           => 'INQ',
			'GenericPhone'  => 'Tapatalk|PDA;|SAGEM|\bmmp\b|pocket|\bpsp\b|symbian|Smartphone|smartfon|treo|up.browser|up.link|vodafone|\bwap\b|nokia|Series40|Series60|S60|SonyEricsson|N900|MAUI.*WAP.*Browser',
			'GenericAndroid'=> 'Android',
		);

        foreach($phoneDevices as $name=>$uA)
		{
            if(preg_match('/'.$uA.'/is',$_SERVER['HTTP_USER_AGENT'])) return $returnModel==false?true:$name;
		}
        return false;
	}

	public function isHome() {
		$url=rtrim(implode("/",$GLOBALS['url']),"/");
		if($url=="" || $url==$this->getVar('home_page',1)) return true;
		else return false;
		}
	public function isNews() {
		if(is_array($GLOBALS['__dir__'])) $dir=$GLOBALS['__dir__'];
		else $dir=explode("/",trim($GLOBALS['__dir__'],"/"));
		if($dir[0]==$this->getVar('dir_news',1)) return true;
		else return false;
		}
	public function isShop() {
		if(is_array($GLOBALS['__dir__'])) $dir=$GLOBALS['__dir__'];
		else $dir=explode("/",trim($GLOBALS['__dir__'],"/"));
		if( $GLOBALS['__dir__']==$this->getVar('dir_shop',1) ) return true;
		else return false;
		}
	public function isShopCart() {
		if(is_array($GLOBALS['__dir__'])) $dir=$GLOBALS['__dir__'];
		else $dir=explode("/",trim($GLOBALS['__dir__'],"/"));
		if( $GLOBALS['__dir__'] == $this->getVar('dir_shop',1)
			&& $GLOBALS['__subdir__'] != ""
			&& $GLOBALS['__subdir__'] == $this->getVar('dir_shop_cart',1)
		) return true;
		else return false;
		}
	public function isShopManufacturer() {
		if(is_array($GLOBALS['__dir__'])) $dir=$GLOBALS['__dir__'];
		else $dir=explode("/",trim($GLOBALS['__dir__'],"/"));
		if( $GLOBALS['__dir__'] == $this->getVar('dir_shop',1)
			&& $GLOBALS['__subdir__'] != ""
			&& $GLOBALS['__subdir__'] == $this->getVar('dir_shop_manufacturers',1)
		) return true;
		else return false;
		}
	public function isPrivate() {
		if(is_array($GLOBALS['__dir__'])) $dir=$GLOBALS['__dir__'];
		else $dir=explode("/",trim($GLOBALS['__dir__'],"/"));
		if($GLOBALS['__dir__']==$this->getVar('dir_private',1)) return true;
		else return false;
		}
	public function isFeed() {
		if(is_array($GLOBALS['__dir__'])) $dir=$GLOBALS['__dir__'];
		else $dir=explode("/",trim($GLOBALS['__dir__'],"/"));
		if($GLOBALS['__dir__']==$this->getVar('dir_feed',1)) return true;
		else return false;
		}
	public function isSearch() {
		if(is_array($GLOBALS['__dir__'])) $dir=$GLOBALS['__dir__'];
		else $dir=explode("/",trim($GLOBALS['__dir__'],"/"));
		if($GLOBALS['__dir__']==$this->getVar('dir_search',1)) return true;
		else return false;
		}
	public function isPhotogallery() {
		if(is_array($GLOBALS['__dir__'])) $dir=$GLOBALS['__dir__'];
		else $dir=explode("/",trim($GLOBALS['__dir__'],"/"));
		if($GLOBALS['__dir__']==$this->getVar('dir_photogallery',1)) return true;
		else return false;
		}
	public function isUsers() {
		if(is_array($GLOBALS['__dir__'])) $dir=$GLOBALS['__dir__'];
		else $dir=explode("/",trim($GLOBALS['__dir__'],"/"));
		if($GLOBALS['__dir__']==$this->getVar('dir_users',1)) return true;
		else return false;
		}
	public function isSitemap() {
		if(is_array($GLOBALS['__dir__'])) $dir=$GLOBALS['__dir__'];
		else $dir=explode("/",trim($GLOBALS['__dir__'],"/"));
		if(isset($_GET['url'])&&$_GET['url']=="sitemap.xml") return true;
		else return false;
		}
	
	/* COMMENTS */
	public function setComment($c) {
		$this->commentDB=$c;
		}

	public function getComments($vars=array()) {
		/*
		return an array with the approved comments of a post, page, shop item or photogallery.
		accept an array with the following inputs:
		 - table : the table of the objects (es. TABLE_NEWS) (mandatory)
		 - id : the id of the object (mandatory)
		 - orderby : how to sort results
		If no table specified, assume the current page type.
		If no id specified, assume the current page id.
		If no orderby specified, assume 'data DESC'.
		*/

		if(!isset($vars['table'])) return false;
		if(!isset($vars['id'])) return false;
		if(!isset($vars['orderby'])) $vars['orderby']='`data` DESC';
		$output="";
		
		$query="SELECT * FROM `".TABLE_COMMENTI."` WHERE `tabella`='".ksql_real_escape_string($vars['table'])."' AND `id`='".intval($vars['id'])."' AND `public`='s' ORDER BY ".$vars['orderby'];
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results)) {
			$output[]=$row;
			}
		
		return $output;
		}

	public function addComment($vars) {
		if(!isset($vars['table'])) return false;
		if(!isset($vars['id'])) return false;
		if(!isset($vars['name'])) $vars['name']="";
		if(!isset($vars['email'])) $vars['email']="";
		if(!isset($vars['text'])) $vars['text']="";
		if(!isset($vars['public'])||$vars['public']!="s") $vars['public']="n";

		$query="INSERT INTO `".TABLE_COMMENTI."` (`ip`,`data`,`tabella`,`id`,`autore`,`email`,`testo`,`public`) VALUES('".$_SERVER['REMOTE_ADDR']."',NOW(),'".ksql_real_escape_string($vars['table'])."','".intval($vars['id'])."','".b3_htmlize($vars['name'],true,"")."','".b3_htmlize($vars['email'],true,"")."','".b3_htmlize($vars['text'],true,"")."','".$vars['public']."')";
		$results=ksql_query($query);
		$idcomm=ksql_insert_id();
		
		//notification
		if(DEFAULT_LANG=='IT') {
			$subject="Nuovo commento sul sito ".kGetVar('sitename',1);
			$message="Hai ricevuto un nuovo commento sul sito:<br />\n"
					."<br />\n"
					.$vars['text']."<br />\n"
					."<br />\n"
					.$vars['name']." (".$vars['email']." - ip:".$_SERVER['REMOTE_ADDR'].")<br />\n"
					."<br />\n";
			if($vars['public']=='n') {
				$message.="Questo commento attende la tua moderazione, quindi non sarà visibile fino a quando non l'avrai approvato.<br />\n"
					."Per approvarlo vai nel pannello di controllo: <a href=\"".SITE_URL."/admin/\">".SITE_URL."/admin/</a><br />\n"
					."<br />\n";
				}
			}
		else {
			$subject="Someone wrote a comment on your website ".kGetVar('sitename',1);
			$message="You've received a comment on your website:<br />\n"
					."<br />\n"
					.$vars['text']."<br />\n"
					."<br />\n"
					.$vars['name']." (".$vars['email']." - ip:".$_SERVER['REMOTE_ADDR'].")<br />\n"
					."<br />\n";
			if($vars['public']=='n') {
				$message.="This comment is waiting for moderation, so it will be hidden until your approvation.<br />\n"
					."To approve it, go to your control panel: <a href=\"".SITE_URL."/admin/\">".SITE_URL."/admin/</a><br />\n"
					."<br />\n";
				}
			}

		kSendEmail(ADMIN_MAIL,ADMIN_MAIL,$subject,$message);
		}

	public function saveComments($table,$id,$moderate='s') {
		if(isset($_POST['commentSubmit'])&&isset($_POST['commentName'])&&isset($_POST['commentText'])) {
			$vars=array();
			$vars['table']=$table;
			$vars['id']=$id;
			$vars['name']=$_POST['commentName'];
			$vars['text']=$_POST['commentText'];
			$vars['email']=$_POST['commentEmail']?$_POST['commentEmail']:'';
			$vars['public']=$moderate=='s'?'s':'n';
			$this->addComment($vars);
			}
		}

	}
