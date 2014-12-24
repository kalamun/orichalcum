<?php /* (c) Kalamun.org - GNU/GPL 3 */

// gestione del caricamento e del ridimensionamento delle immagini

class kaImages {
	protected $img, $thumb, $kaImpostazioni;

	public function __construct()
	{
		require_once('kalamun.lib.php');
		require_once('config.lib.php');

		$this->kaImpostazioni = new kaImpostazioni();
		
		// load default values
		$img=array('resize','mode','width','height','quality');
		$thumb=array('resize','mode','width','height','quality');
		$mobile=array('active','ratio');
		
		$row=$this->kaImpostazioni->getParam('img_resize','*');
		$this->img['resize']=$row['value1'];
		$this->img['mode']=$row['value2'];
		
		$row=$this->kaImpostazioni->getParam('img_size','*');
		$this->img['width']=intval($row['value1']);
		$this->img['height']=intval($row['value2']);

		$row=$this->kaImpostazioni->getParam('img_quality','*');
		$this->img['quality']=intval($row['value1']);
		
		$row=$this->kaImpostazioni->getParam('thumb_resize','*');
		$this->thumb['resize']=$row['value1'];
		$this->thumb['mode']=$row['value2'];
		
		$row=$this->kaImpostazioni->getParam('thumb_size','*');
		$this->thumb['width']=intval($row['value1']);
		$this->thumb['height']=intval($row['value2']);
		
		$row=$this->kaImpostazioni->getParam('thumb_quality','*');
		$this->thumb['quality']=intval($row['value1']);

		$row=$this->kaImpostazioni->getParam('img_mobile','*');
		$this->mobile['active']=$row['value1'];
		$this->mobile['ratio']=intval($row['value2']);
		if($this->mobile['ratio']>100) $this->mobile['ratio']=100;
	}
	
	
	function setImgResize($v) { if($v!="all"&&$v!="bigger"&&$v!="smaller") $v="none"; $this->img['resize']=$v; }
	function setImgMode($v) { if($v!="inside"&&$v!="outside") $v="fit"; $this->img['mode']=$v; }
	function setImgWidth($v) { $this->img['width']=intval($v); }
	function setImgHeight($v) { $this->img['height']=intval($v); }
	function setImgQuality($v) { $this->img['quality']=intval($v); }
	function setThumbResize($v) { if($v!="all"&&$v!="bigger"&&$v!="smaller") $v="none"; $this->thumb['resize']=$v; }
	function setThumbMode($v) { if($v!="inside"&&$v!="outside") $v="fit"; $this->thumb['mode']=$v; }
	function setThumbWidth($v) { $this->thumb['width']=intval($v); }
	function setThumbHeight($v) { $this->thumb['height']=intval($v); }
	function setThumbQuality($v) { $this->thumb['quality']=intval($v); }

	
	// count images
	function countList($tabella=false, $id=false, $conditions='')
	{
		$output=array();

		$query="SELECT count(*) AS tot FROM `".TABLE_IMG."` WHERE `idimg`>0 ";
		if($conditions!="") $query.=" AND (".$conditions.") ";

		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row['tot'];
	}

	// returns an array of images
	function getList($orderby='`creation_date` DESC', $conditions='', $offset=false, $limit=false)
	{
		$output=array();

		$query="SELECT * FROM `".TABLE_IMG."` WHERE `idimg`>0 ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		if($orderby!="") $query.=" ORDER BY ".$orderby." ";
		if($offset==false) $offset=0;
		if($offset!=""||$limit!="")
		{
			$query.=" LIMIT ".intval($offset);
			if($limit!="") $query.=",".intval($limit);
		}

		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++)
		{
			$output[$i]=$row;
			
			if($output[$i]['hotlink']!="" && $row['filename']!="")
			{
				$output[$i]['filename']=basename($output[$i]['hotlink']);
				$output[$i]['url']=$output[$i]['hotlink'];
				$output[$i]['hotlink']=true;
			} else {
				$output[$i]['url']=ltrim(DIR_IMG,"./").$row['idimg'].'/'.$row['filename'];
				$output[$i]['hotlink']=false;
			}
			
			if($output[$i]['filename']!="" && file_exists(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['filename'])) $size=getimagesize(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['filename']);
			else $size=array(0,0,0,"");

			$output[$i]['width']=$size[0];
			$output[$i]['height']=$size[1];
			$output[$i]['thumb']['filename']=$row['thumbnail'];
			$output[$i]['thumb']['url']=DIR_IMG.$row['idimg'].'/'.$row['thumbnail'];
			
			if($output[$i]['thumbnail']!="" && file_exists(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['thumbnail'])) $size=getimagesize(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['thumbnail']);
			else $size=array(0,0,0,"");
			$output[$i]['thumb']['width']=$size[0];
			$output[$i]['thumb']['height']=$size[1];
			
			$output[$i]['alts']=json_decode($output[$i]['alt'],true);
			if(!is_array($output[$i]['alts'])) $output[$i]['alts']=array($_SESSION['ll']=>$output[$i]['alt']);
			if(empty($output[$i]['alts'][$_SESSION['ll']])) $output[$i]['alts'][$_SESSION['ll']]="";
			$output[$i]['alt']=$output[$i]['alts'][$_SESSION['ll']];
		}
		
		return $output;
	}

	
	// returns an array for a single image
	function getImage($idimg)
	{
		$output=array();

		$query="SELECT * FROM `".TABLE_IMG."` WHERE `idimg`=".intval($idimg)." LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		if(isset($row['idimg']))
		{
			$output=$row;
			$output['url']=ltrim(DIR_IMG,"./").$row['idimg'].'/'.$row['filename'];
			if(isset($output['hotlink'])&&$output['hotlink']!=""&&$row['filename']!="")
			{
				$output['filename']=basename($output['hotlink']);
				$output['url']=$output['hotlink'];
				$output['hotlink']=true;
			}
			else $row['hotlink']=false;
			
			if(isset($output['filename']) && $output['filename']!=""&&file_exists(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['filename'])) $size=getimagesize(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['filename']);
			else $size=array(0,0,0,"");
			$output['width']=$size[0];
			$output['height']=$size[1];
			
			$output['thumb']['filename']=isset($output['thumbnail'])?$output['thumbnail']:array();
			$output['thumb']['url']=(isset($row['idimg'])&&isset($row['thumbnail']))?ltrim(DIR_IMG,"./").$row['idimg'].'/'.$row['thumbnail']:'';
			
			if(isset($output['thumbnail']) && $output['thumbnail']!=""&&file_exists(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['thumbnail'])) $size=getimagesize(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['thumbnail']);
			else $size=array(0,0,0,"");
			$output['thumb']['width']=$size[0];
			$output['thumb']['height']=$size[1];
			
			$output['alts']=json_decode($output['alt'],true);
			if($output['alts']!=false)
			{
				if(!isset($output['alts'][$_SESSION['ll']])) $output['alts'][$_SESSION['ll']]="";
				$output['alt']=$output['alts'][$_SESSION['ll']];
			}
		
			return $output;

		} else {
			return false;
		}
	}

	
	// insert an image into db, then create a directory called as the id and upload the file inside
	function upload($file,$filename)
	{
		$filename=preg_replace("/([^A-Za-z0-9\._-])+/i",'_',$filename);
		if(substr(strtolower($filename),-4)=='.php' || substr(strtolower($filename),-5)=='.php3') return false;
		
		if(!defined("TABLE_IMG")|!defined("DIR_IMG")) return false;
		
		$query="INSERT INTO `".TABLE_IMG."` (`filename`,`thumbnail`,`hotlink`,`alt`,`creation_date`) VALUES('".ksql_real_escape_string($filename)."','','','',NOW())";
		if(ksql_query($query)) $idimg=ksql_insert_id();
		else return false;
		
		//copy on the right dir
		if(!file_exists(BASERELDIR.DIR_IMG)) mkdir(BASERELDIR.DIR_IMG);
		mkdir(BASERELDIR.DIR_IMG.$idimg);

		$ffile = BASERELDIR.DIR_IMG.$idimg.'/'.$filename;
		if(copy($file, $ffile)) unlink($file);
		else return false;
		
		//another copy before resize, to preserve the original version
		$ofile = BASERELDIR.DIR_IMG.$idimg.'/-originalsize';
		copy($ffile, $ofile);

		//resize
		$size=getimagesize($ffile);
		if($this->needToResize($size[0],$size[1])==true) $this->resize($ffile, $this->img['width'], $this->img['height'], $this->img['quality'], $this->img['mode']);
		else $this->recompress($ffile, $this->img['quality']);
		
		$this->setThumb($idimg);

		// create mobile version, if active
		if($this->mobile['active']=="y")
		{
			$mfile = BASERELDIR.DIR_IMG.$idimg.'/m_'.$filename;
			copy($ofile, $mfile);
			$this->mobile['width'] = intval($this->img['width'] / 100 * $this->mobile['ratio']);
			$this->mobile['height'] = intval($this->img['height'] / 100 * $this->mobile['ratio']);
			$this->mobile['quality'] = intval($this->img['quality'] / 100 * ($this->mobile['ratio'] + ((100 - $this->mobile['ratio']) / 2)));
			$this->resize($mfile, $this->mobile['width'], $this->mobile['height'], $this->mobile['quality'], $this->img['mode']);
		}

		return $idimg;
	}
	
	function setHotlink($idimg,$url)
	{
		$img=$this->getImage($idimg);
		if($img['idimg']>0)
		{
			$query="UPDATE ".TABLE_IMG." SET filename='',hotlink='".$url."' WHERE idimg='".$img['idimg']."' LIMIT 1";
			if(!ksql_query($query)) return false;
		}
	}

	function setThumb($idimg,$file=null,$filename=null,$resize=true)
	{
		$filename=preg_replace("/([^A-Za-z0-9\._-])+/i",'_',$filename);
		$img=$this->getImage($idimg);
		if($img['idimg']>0)
		{
			if($file==null) $file=BASERELDIR.$img['url'];
			if($filename==null||$filename==$img['filename']) $filename='t_'.$img['filename'];
			if($img['thumb']['filename']!="") unlink(BASERELDIR.DIR_IMG.$idimg.'/'.$img['thumb']['filename']);
			copy($file,BASERELDIR.DIR_IMG.$img['idimg'].'/'.$filename);
			$query="UPDATE `".TABLE_IMG."` SET `thumbnail`='".ksql_real_escape_string($filename)."' WHERE `idimg`='".$img['idimg']."' LIMIT 1";
			if(!ksql_query($query)) return false;
			if($resize==true) $this->resize(BASERELDIR.DIR_IMG.$idimg.'/'.$filename,$this->thumb['width'],$this->thumb['height'],$this->thumb['quality'],$this->thumb['mode']);
		}
		return true;
	}

	function updateAlt($idimg,$alt) {
		/*
		Updates captions.
		It stores the caption of an image, encoded in JSON as an array where each element is the caption in a different language
		*/
		$query="SELECT `alt` FROM `".TABLE_IMG."` WHERE `idimg`=".$idimg." LIMIT 1";
		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		$caption=json_decode($row['alt'],true);
		$defaultCaption="";
		if(empty($caption) || !is_array($caption))
		{
			$defaultCaption=b3_lmthize($row['alt'],"textarea");
			$caption=array();
		}
		
		$kaAdminMenu=new kaAdminMenu();
		foreach($kaAdminMenu->getLanguages() as $l)
		{
			if(!isset($caption[$l['ll']])) $caption[$l['ll']]=$defaultCaption;
		}
		
		$caption[$_SESSION['ll']]=$alt;
		$caption=json_encode($caption);

		$query="UPDATE ".TABLE_IMG." SET alt='".ksql_real_escape_string($caption)."' WHERE `idimg`=".$idimg." LIMIT 1";
		if(!ksql_query($query)) return false;
		return true;
		}

	function updateImage($idimg,$file,$filename,$resize=true,$width=0,$height=0)
	{
		$filename=preg_replace("/([^A-Za-z0-9\._-])+/i",'_',$filename);
		if($filename!="" && substr(strtolower($filename),-4)!='.php' && substr(strtolower($filename),-4)!='.php3')
		{
			$query="SELECT `filename` FROM ".TABLE_IMG." WHERE `idimg`=".$idimg;
			$results=ksql_query($query);
			$row=ksql_fetch_array($results);
			$query="UPDATE `".TABLE_IMG."` SET `filename`='".addslashes($filename)."',hotlink='' WHERE idimg=".$idimg;
			if(!ksql_query($query)) return false;

			$ffile=BASERELDIR.DIR_IMG.$idimg.'/'.$filename;
			$mfile=BASERELDIR.DIR_IMG.$idimg.'/m_'.$filename;
			$ofile=BASERELDIR.DIR_IMG.$idimg.'/-originalsize';
			
			// copy image into the right dir
			if(!file_exists(BASERELDIR.DIR_IMG.$idimg)) mkdir(BASERELDIR.DIR_IMG.$idimg);
			if(!copy($file, $ffile)) return false;
			copy($file, BASERELDIR.DIR_IMG.$idimg.'/-originalsize');


			// delete old images
			if($filename!=$row['filename'] && file_exists(BASERELDIR.DIR_IMG.$idimg.'/'.$row['filename'])) unlink(BASERELDIR.DIR_IMG.$idimg.'/'.$row['filename']);
			if(file_exists(BASERELDIR.DIR_IMG.$idimg.'/m_'.$row['filename'])) unlink(BASERELDIR.DIR_IMG.$idimg.'/m_'.$row['filename']);

			if($resize==true)
			{
				$size=getimagesize(BASERELDIR.DIR_IMG.$idimg.'/'.$filename);
				if($this->needToResize($size[0],$size[1])==true) $this->resize($ffile, $this->img['width'], $this->img['height'], $this->img['quality'], $this->img['mode']);
				else $this->recompress($ffile, $this->img['quality']);
				
				$this->mobile['width'] = intval($this->img['width'] / 100 * $this->mobile['ratio']);
				$this->mobile['height'] = intval($this->img['height'] / 100 * $this->mobile['ratio']);

			} else {
				if($width>0 || $height>0)
				{
					$size=getimagesize(BASERELDIR.DIR_IMG.$idimg.'/'.$filename);
					if($width==0) $width=$size[0]/$size[1]*$height;
					elseif($height==0) $height=$size[1]/$size[0]*$width;
					$this->resize($ffile,$width,$height,$this->img['quality'],'fit');

					$this->mobile['width'] = intval($width / 100 * $this->mobile['ratio']);
					$this->mobile['height'] = intval($height / 100 * $this->mobile['ratio']);
				}
			}

			// create mobile version, if active
			if($this->mobile['active']=="y")
			{
				copy($ofile, $mfile);
				$this->mobile['quality'] = intval($this->img['quality'] / 100 * ($this->mobile['ratio'] + ((100 - $this->mobile['ratio']) / 2)));
				$this->resize($mfile, $this->mobile['width'], $this->mobile['height'], $this->mobile['quality'], $this->img['mode']);
			}

		}
		
		return $idimg;
	}

	function delete($idimg)
	{
		$query="SELECT * FROM `".TABLE_IMG."` WHERE `idimg`=".intval($idimg)." LIMIT 1";
		$results=ksql_query($query);
		if($row=ksql_fetch_array($results))
		{
			$query="DELETE FROM `".TABLE_IMG."` WHERE `idimg`=".intval($idimg);
			if(!ksql_query($query)) return false;
			
			kRemoveDir(BASERELDIR.DIR_IMG.$idimg); // recursive remove

		} else return false;
		
		return true;
	}

	function usage($idimg) {
		$output=array();
		$id=array(TABLE_CONFIG=>"idconf",TABLE_USERS=>"iduser",TABLE_BANNER=>"idbanner",TABLE_PAGINE=>"idpag",TABLE_NEWS=>"idnews",TABLE_PHOTOGALLERY=>"idphg",TABLE_SHOP_ITEMS=>"idsitem",TABLE_SHOP_MANUFACTURERS=>"idsman",TABLE_MENU=>"idmenu");
		$type=array(TABLE_CONFIG=>"Configurazione",TABLE_USERS=>"Utenti",TABLE_BANNER=>"Banner",TABLE_PAGINE=>"Pagina",TABLE_NEWS=>"News",TABLE_PHOTOGALLERY=>"Gallerie Fotografiche",TABLE_SHOP_ITEMS=>"Oggetti del negozio",TABLE_SHOP_MANUFACTURERS=>"Produttori",TABLE_MENU=>"Menù di navigazione");

		$output=array();
		
		// search for embedded images
		$search=array();
		$search[]=array(TABLE_CONFIG,'value1');
		$search[]=array(TABLE_CONFIG,'value2');
		$search[]=array(TABLE_PAGINE,'anteprima');
		$search[]=array(TABLE_PAGINE,'testo');
		$search[]=array(TABLE_NEWS,'anteprima');
		$search[]=array(TABLE_NEWS,'testo');
		$search[]=array(TABLE_PHOTOGALLERY,'testo');
		$search[]=array(TABLE_SHOP_ITEMS,'anteprima');
		$search[]=array(TABLE_SHOP_ITEMS,'testo');
		$search[]=array(TABLE_SHOP_MANUFACTURERS,'preview');
		$search[]=array(TABLE_SHOP_MANUFACTURERS,'description');
		
		foreach($search as $s)
		{
			$query="SELECT * FROM ".$s[0]." WHERE ".$s[1]." LIKE '%id=\"img".$idimg."\"%' OR ".$s[1]." LIKE '%id=\"thumb".$idimg."\"%'";
			$results=ksql_query($query);
			while($row=ksql_fetch_array($results))
			{
				if(!isset($row['dir'])) $row['dir']='';
				$output[]=array(
					"table"=>$s[0],
					"id"=>$row[$id[$s[0]]],
					"descr"=>$type[$s[0]],
					"lang"=>$row['ll'],
					"dir"=>$row['dir']
					);
			}
		}
			
		// search for id
		$searchID=array();
		$searchID[]=array(TABLE_PAGINE,'featuredimage');
		$searchID[]=array(TABLE_NEWS,'featuredimage');
		$searchID[]=array(TABLE_SHOP_ITEMS,'featuredimage');
		$searchID[]=array(TABLE_SHOP_MANUFACTURERS,'featuredimage');

		foreach($searchID as $s)
		{
			$query="SELECT * FROM ".$s[0]." WHERE ".$s[1]."='".intval($idimg)."'";
			$results=ksql_query($query);
			while($row=ksql_fetch_array($results))
			{
				$output[]=array(
					"table"=>$s[0],
					"id"=>$row[$id[$s[0]]],
					"descr"=>$type[$s[0]],
					"lang"=>$row['ll'],
					"dir"=>$row['dir']
					);
			}
		}
		
		// search into gallery
		$query="SELECT * FROM ".TABLE_IMGALLERY." WHERE `idimg`='".intval($idimg)."'";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			$output[]=array(
				"table"=>$row['tabella'],
				"id"=>$row['id'],
				"descr"=>$type[$row['tabella']],
				"lang"=>"",
				"dir"=>""
				);
		}
		
		// search into menus
		$query="SELECT * FROM ".TABLE_MENU." WHERE `photogallery` LIKE '%,".intval($idimg).",%'";
		$results=ksql_query($query);
		while($row=ksql_fetch_array($results))
		{
			$output[]=array(
				"table"=>TABLE_MENU,
				"id"=>$row[$row['idmenu']],
				"descr"=>$type[$row['tabella']].': '.$row['label'],
				"lang"=>$row['ll'],
				"dir"=>$row['url']
				);
		}
		
		
		return $output;
		}

	// check if for the given dimensions a resize is needed, according to the global image resize configuration
	private function needToResize($w,$h)
	{
		$resize=false;
		if($this->img['resize']=="all")
		{
			$resize=true;
			
		} elseif($this->img['resize']=="bigger") {
			if($this->img['mode']=="inside"&&($w>$this->img['width']||$h>$this->img['height'])) $resize=true;
			elseif($this->img['mode']=="outside"&&($w>$this->img['width']&&$h>$this->img['height'])) $resize=true;
			elseif($this->img['mode']=="fit") $resize=true;
			
		} elseif($this->img['resize']=="smaller") {
			if($this->img['mode']=="inside"&&($w<$this->img['width']&&$h<$this->img['height'])) $resize=true;
			elseif($this->img['mode']=="outside"&&($w<$this->img['width']||$h<$this->img['height'])) $resize=true;
			elseif($this->img['mode']=="fit") $resize=true;
		}
		return $resize;
	}

	// resize an image to a gived dimension with given aspect ratio, then compress with the quality param (0=worst, 100=best)
	function resize($img, $x, $y, $quality=100, $mode="inside")
	{
		$size=getimagesize($img);
		
		// if one size is 0, set it larger than the equivalent default size proportionally with the other side
		if($x==0&&$y==0) return false;
		elseif($x==0) { $x=round($size[0]*($y/$size[1]))+10; }
		elseif($y==0) { $y=round($size[1]*($x/$size[0]))+10; }
		
		// add 1 pixel for side to prevent the black border issue (this pixel will be cropped later)
		$x++; $y++;

		// maintain aspect ratio
		switch($mode)
		{
			case "inside":
				if(($size[0]/$x)>=($size[1]/$y))
				{
					$cropW=$x-1;
					$ratio=$size[0]/$x;
					$y=$size[1]/$ratio;
					$cropH=$y-1;
				} else {
					$cropH=$y-1;
					$ratio=$size[1]/$y;
					$x=$size[0]/$ratio;
					$cropW=$x-1;
				}
				break;

			case "outside":
				if(($size[0]/$x)<=($size[1]/$y))
				{
					$cropW=$x-1;
					$ratio=$size[0]/$x;
					$y=$size[1]/$ratio;
					$cropH=$y-1;
				} else {
					$cropH=$y-1;
					$ratio=$size[1]/$y;
					$x=$size[0]/$ratio;
					$cropW=$x-1;
				}
				break;

			case "fit":
				$cropW=$x-1;
				$cropH=$y-1;
				break;
		}
		
		$cropX=0; //crop left starting point
		$cropY=0; //crop top starting point

		if($size['mime']=='image/jpeg')
		{
			$source=imagecreatefromjpeg($img);
		
			$destination=imagecreatetruecolor($x,$y);
			if(function_exists('imagecopyresampled')) imagecopyresampled($destination,$source,0,0,0,0,$x,$y,imagesx($source),imagesy($source));
			else imagecopyresized($destination,$source,0,0,0,0,$x,$y,imagesx($source),imagesy($source));
		
			$finalthumb=imagecreatetruecolor($cropW,$cropH);

			// crop black borders
			for ($i=$cropY;$i<($cropY+$cropH);$i++)
			{
				for ($j=$cropX;$j<($cropX+$cropW);$j++)
				{
					$pixel=imagecolorat($destination,$j,$i);
					imagesetpixel($finalthumb,$j-$cropX,$i-$cropY,$pixel);
				}
			}

			imagedestroy($destination);
			imagejpeg($finalthumb,$img,$quality);
			imagedestroy($finalthumb);

			return $img;

		} elseif($size['mime']=='image/png') {
			$source=imagecreatefrompng($img);
			
			$destination=imagecreatetruecolor($x,$y);
			
			// maintain transparency
			imagealphablending($destination,false);
			imagesavealpha($destination,true);
			
			if(function_exists('imagecopyresampled')) imagecopyresampled($destination,$source,0,0,0,0,$x,$y,imagesx($source),imagesy($source));
			else imagecopyresized($destination,$source,0,0,0,0,$x,$y,imagesx($source),imagesy($source));
		
			$finalthumb=imagecreatetruecolor($cropW,$cropH);
			
			// maintain transparency in resized image
			imagealphablending($finalthumb,false);
			imagesavealpha($finalthumb,true);
			
			// crop black borders
			for ($i=$cropY;$i<($cropY+$cropH);$i++)
			{
				for ($j=$cropX;$j<($cropX+$cropW);$j++)
				{
					$pixel=imagecolorat($destination,$j,$i);
					imagesetpixel($finalthumb,$j-$cropX,$i-$cropY,$pixel);
				}
			}
			
			imagedestroy($destination);
			imagepng($finalthumb,$img,round($quality/100*9));
			imagedestroy($finalthumb);

			return $img;

		} elseif($size['mime']=='image/gif') {
		
			//if animated gif, let it intact
			$animated=$this->isAnimatedGif($img);
			if($animated==true) return $img;

			$source=imagecreatefromgif($img);
			
			$destination=imagecreatetruecolor($x,$y);
			
			// maintain transparency
			$transp_color=imagecolortransparent($destination);
			$transp_index=imagecolorallocate($destination, $transp_color['red'], $transp_color['green'], $transp_color['blue']);
			imagecolortransparent($destination, $transp_index);
			
			if(function_exists('imagecopyresampled')) imagecopyresampled($destination,$source,0,0,0,0,$x,$y,imagesx($source),imagesy($source));
			else imagecopyresized($destination,$source,0,0,0,0,$x,$y,imagesx($source),imagesy($source));
		
			$finalthumb=imagecreatetruecolor($cropW,$cropH);
			
			// maintain transparency in resized image
			$transp_color=imagecolortransparent($finalthumb);
			$transp_index=imagecolorallocate($finalthumb, $transp_color['red'], $transp_color['green'], $transp_color['blue']);
			imagecolortransparent($finalthumb, $transp_index);
			
			// crop black borders
			for ($i=$cropY;$i<($cropY+$cropH);$i++)
			{
				for ($j=$cropX;$j<($cropX+$cropW);$j++)
				{
					$pixel=imagecolorat($destination,$j,$i);
					imagesetpixel($finalthumb,$j-$cropX,$i-$cropY,$pixel);
				}
			}
			
			imagedestroy($destination);
			imagegif($finalthumb,$img);
			imagedestroy($finalthumb);
			
			return $img;
		}
		return false;
	}
	
	public function recompress($img, $quality)
	{
		$size=getimagesize($img);
		
		if($size['mime']=='image/jpeg')
		{
			$source=imagecreatefromjpeg($img);
			imagejpeg($source, $img, $quality);
			return $img;

		} elseif($size['mime']=='image/png') {
			$source=imagecreatefrompng($img);
			imagepng($source,$img,round($quality/100*9));
			return $img;

		} elseif($size['mime']=='image/gif') {
			$animated=$this->isAnimatedGif($img);
			if($animated==true) return $img;

			$source=imagecreatefromgif($img);
			imagegif($source,$img);
			return $img;
		}
		return false;
	}
	
	public function isAnimatedGif($img)
	{
		if(!($fh = @fopen($img, 'rb'))) return false;
		$count = 0;
		while(!feof($fh) && $count < 2)
		{
			$chunk = fread($fh, 1024 * 100); //read 100kb at a time
			$count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00\x2C#s', $chunk, $matches);
		}

		fclose($fh);
		return $count > 1;
	}
}
