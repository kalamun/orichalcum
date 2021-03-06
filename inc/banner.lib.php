<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

/* PAGINE */
class kBanners {
	protected $inited;
	protected $bannerDB;

	public function __construct()
	{
		$this->inited=false;
	}

	public function init()
	{
		$this->inited=true;
		$this->bannerDB=array();
	}

	function getCategories()
	{
		if(!$this->inited) $this->init();

		$cat=array();
		$query="SELECT * FROM `".TABLE_CATEGORIE."` WHERE `tabella`='".TABLE_BANNER."' AND `ll`='".ksql_real_escape_string(LANG)."' ORDER BY `ordine`";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			$cat[]=$row;
		}
		return $cat;
	}

	// get banners for a single category
	function getBanners($vars=array())
	{
		if(!$this->inited) $this->init();

		$banners=array();
		
		if(empty($vars['lang'])) $vars['lang'] = LANG;
	
		// get the current category
		$cat = $this->getCategories();
		if(empty($vars['category'])) $vars['category'] = $cat[0]['category'];
		else $vars['category'] = $vars['category'];
		foreach($cat as $c)
		{
			if($c['categoria'] == $vars['category']
				|| $c['categoria'] == b3_htmlize($vars['category'],false,"")
				|| $c['dir'] == $vars['category']
				|| $c['idcat'] == $vars['category']
				)
			{
				$idcat = $c['idcat'];
				break;
			}
		}

		if(empty($vars['orderby']))
		{
			// get order from category metadata
			$query="SELECT * FROM `".TABLE_METADATA."` WHERE `tabella`='".TABLE_CATEGORIE."' AND `id`='".intval($idcat)."' AND `param`='orderby' LIMIT 1";
			$results = ksql_query($query);
			$row = ksql_fetch_array($results);
			$vars['orderby'] = "`".ksql_real_escape_string($row['value'])."`";
			if($vars['orderby']=='clicks') $vars['orderby']='RAND()';
		}

		if(trim($vars['orderby'],"` ")=="") $vars['orderby'] = '`ordine`';
		$query = "SELECT * FROM `".TABLE_BANNER."` WHERE `categoria`='".ksql_real_escape_string($idcat)."' AND `ll`='".ksql_real_escape_string(strtoupper($vars['lang']))."' AND `online`='s' ORDER BY ".ksql_real_escape_string($vars['orderby'])."";

		if(isset($vars['from'])) $vars['offset'] = $vars['from']; //backwards compatibility
		if((isset($vars['limit'])&&$vars['limit']>=0)) $query .= " LIMIT ".intval($vars['limit'])." ";
		if((isset($vars['offset'])&&$vars['offset']>=0)) $query .= " OFFSET ".intval($vars['offset'])." ";

		$results = ksql_query($query);

		for($i=0; $row=ksql_fetch_array($results); $i++)
		{
			$banners[$i] = $row;
			if($row['featuredimage'] > 0 && $row['type']=='image') $banners[$i]['featuredimage'] = $GLOBALS['__images']->getImage($row['featuredimage']);
			else $row['featuredimage'] = array();
			
			// url to register click
			$banners[$i]['register_click_url'] = SITE_URL . BASEDIR . 'inc/event_logger.php?family=banner&event=click&ref=' . urlencode($row['idbanner'].': '.date("Y-m-d"));
			
			// increase the views counter
			if(strpos($system,"MSIE")!==false
			||strpos($system,"Edge")!==false
			||strpos($system,"Firefox")!==false
			||strpos($system,"Chrome")!==false
			||strpos($system,"Safari")!==false
			||strpos($system,"Opera")!==false
			) {
				$query="UPDATE `".TABLE_BANNER."` SET `views`=(`views`+1) WHERE `idbanner`='".$row['idbanner']."' LIMIT 1";
				ksql_query($query);

				registerEvent("banner", "view", $row['idbanner'].': '.date("Y-m-d"));
			}

		}

		return $banners;
	}

	function setBanner($bannerDB) {
		if(!$this->inited) $this->init();
		$this->bannerDB=$bannerDB;
		}
	
	function getVar($param) {
		if(!$this->inited) $this->init();
		if(isset($this->bannerDB[$param])) return $this->bannerDB[$param];
		else return false;
		}
	}
