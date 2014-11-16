<?php 
/* (c) Kalamun - GPL 3 */

class kPhotogallery {
	protected $inited;
	protected $__template,$images,$imgs,$loadedGallery,$commentsDB=false;

	public function __construct() {
		$this->inited=false;
		}
		
	public function init() {
		$this->inited=true;
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.'inc/template.lib.php');
		require_once($_SERVER['DOCUMENT_ROOT'].'/'.BASEDIR.'inc/images.lib.php');
		$this->imgs=new kImages();
		$this->loadedNews=false;
		}

	public function photogalleryExists($dir=false) {
		if($dir==false) $dir=$GLOBALS['__subdir__'];
		$query="SELECT * FROM ".TABLE_PHOTOGALLERY." WHERE ll='".LANG."' AND data<=NOW() AND (`dir`='".b3_htmlize($dir,true,"")."' OR `dir`='".mysql_real_escape_string($dir)."') LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) return true;
		else return false;
		}

	public function getMetadata($dir="false",$ll=false) {
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		if($dir!="false") $dir=explode("/",$dir);
		else $dir=array($GLOBALS['__dir__'],$GLOBALS['__subdir__'],$GLOBALS['__subsubdir__']);
		$metadata=array();
		$metadata['titolo']=$dir[0];
		$metadata['traduzioni']="";
		foreach(kGetLanguages() as $ll=>$lang) { $metadata['traduzioni'].=$ll."|".kGetVar('dir_photogallery',1,$ll)."\n"; }
		$metadata['template']=kGetVar('photogallery-template',1);
		$metadata['layout']="";
		$metadata['featuredimage']=($row['featuredimage']>0 ? $this->imgs->getImage($row['featuredimage']) : array());
		if(isset($dir[1])&&$dir[1]!="") {
			$query="SELECT idphg,titolo,traduzioni,template FROM ".TABLE_PHOTOGALLERY." WHERE (`dir`='".b3_htmlize($GLOBALS['__subdir__'],true,"")."' OR `dir`='".mysql_real_escape_string($GLOBALS['__subdir__'])."') AND ll='".$ll."' LIMIT 1";
				$results=mysql_query($query);
					$row=mysql_fetch_array($results);
			$metadata['titolo'].=" &gt; ".$row['titolo'];
			$metadata['traduzioni']=$row['traduzioni'];
			if($metadata['template']!="") $metadata['template']=$row['template'];
			$idphg=$row['idphg'];
			}
		if(isset($idphg)) {
			$query="SELECT * FROM ".TABLE_METADATA." WHERE tabella='".TABLE_PHOTOGALLERY."' AND id='".$idphg."'";
			$results=mysql_query($query);
			while($row=mysql_fetch_array($results)) {
				$metadata[$row['param']]=$row['value'];
				}
			}
		return $metadata;
		}

	public function getGalleryByDir($dir=false) {
		if(!$this->inited) $this->init();
		if($dir==false) $dir=$GLOBALS['__subdir__'];
		
		$vars['photogallery']=true; // it needs to be implemented

		$output=array();
		$query="SELECT * FROM ".TABLE_PHOTOGALLERY." WHERE ll='".LANG."' AND data<=NOW() AND (`dir`='".b3_htmlize($dir,true,"")."' OR `dir`='".mysql_real_escape_string($dir)."') LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$output=$row;
		
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

		$output['categories']=array();
		if(strpos(kGetVar('admin-page-layout',1),",categories,")!==false)
		{
			$row['categories']=trim($row['categories'],",");
			foreach(explode(",",$row['categories']) as $cat)
			{
				$output['categories'][]=$this->getCatById($cat);
			}
		}

		$output['permalink']=BASEDIR.$GLOBALS['__template']->getLanguageURI(LANG).$GLOBALS['__template']->getVar('dir_photogallery',1).'/'.$row['dir'];
		// get photogallery in the correct order
		$output['imgs']=array();
		if(trim($row['photogallery'],",")!="" )
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
		$output['commenti']=$this->getComments($row['idphg']);
		return $output;
		}

	public function getList($from=0,$num=999,$conditions="",$options="") {
		if(!$this->inited) $this->init();
		$output=array();
		$query="SELECT * FROM ".TABLE_PHOTOGALLERY." WHERE ll='".LANG."' ";
		if($conditions!="") $query.=" AND(".$conditions.") ";
		if($options!="") $query.=" ".$options." ";
		$query.=" AND data<=NOW() ORDER BY ".kGetVar('photogallery-order',1);
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			$id=count($output);
			$output[$id]=$row;
			$output[$id]['permalink']=BASEDIR.$GLOBALS['__template']->getLanguageURI(LANG).$GLOBALS['__template']->getVar('dir_photogallery',1).'/'.$row['dir'];
			// get photogallery in the correct order
			$output[$id]['imgs']=array();
			if(trim($row['photogallery'],",")!="" )
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
						if($img['idimg']==$idimg) $output[$id]['imgs'][]=$img;
					}
				}
			}
			$output[$id]['commenti']=$this->getComments($row['idphg']);

			if($row['featuredimage']==0) $output[$id]['featuredimage']=false;
			else $output[$id]['featuredimage']=$this->imgs->getImage($row['featuredimage']);
			}
		return $output;
		}

	public function setGalleryByDir($dir) {
		if(!$this->inited) $this->init();
		$this->loadedGallery=$this->getGalleryByDir($dir);
		}
	public function getGalleryVar($var) {
		if(!$this->inited) $this->init();
		if($this->loadedGallery==false) $this->setGalleryByDir($GLOBALS['__subdir__']);
		return $this->loadedGallery[$var];
		}

	public function getCatByName($name,$ll=false) {
		if(!$this->inited) $this->init();
		if($ll==false) $ll=LANG;
		$query="SELECT * FROM ".TABLE_CATEGORIE." WHERE `tabella`='".TABLE_PHOTOGALLERY."' AND (`categoria`='".b3_htmlize($name,true,"")."' OR `categoria`='".mysql_real_escape_string($name)."') AND `ll`='".strtoupper($ll)."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) return $row;
		else return false;
		}
	public function getCatById($idcat) {
		if(!$this->inited) $this->init();
		$query="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_PHOTOGALLERY."' AND idcat='".intval($idcat)."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) return $row;
		else return false;
		}

	public function addComment($name,$email,$text,$idphg,$public="n") {
		if($public!="s") $public="n";
		$query="INSERT INTO ".TABLE_COMMENTI." (ip,data,tabella,id,autore,email,testo,public) VALUES('".$_SERVER['REMOTE_ADDR']."',NOW(),'".TABLE_PHOTOGALLERY."','".$idphg."','".b3_htmlize($name,true,"")."','".b3_htmlize($email,true,"")."','".b3_htmlize($text,true,"")."','".$public."')";
		mysql_query($query);
		$idcomm=mysql_insert_id();
		return $idcomm;
		}
	public function getComments($idnews) {
		$output=array();
		$query="SELECT * FROM ".TABLE_COMMENTI." WHERE tabella='".TABLE_PHOTOGALLERY."' AND id='".intval($idnews)."' AND public='s' ORDER BY data";
		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$output[$i]=$row;
			$output[$i]['dataleggibile']=preg_replace("/(\d{4}).(\d{2}).(\d{2}) (\d{2}).(\d{2}).*/","$3-$2-$1 $4:$5",$row['data']);
			}
		return $output;
		}

	}
