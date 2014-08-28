<?php
/* (c) Kalamun.org - GNU/GPL 3 */

// gestione del caricamento e del ridimensionamento delle immagini

class kaImages {
	protected $img,$thumb;

	public function __construct() {
		require_once('kalamun.lib.php');

		//carico i valori predefiniti
		$img=array('resize','mode','width','height','quality');
		$thumb=array('resize','mode','width','height','quality');
		
		$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='img_resize' AND ll='*' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$this->img['resize']=$row['value1'];
		$this->img['mode']=$row['value2'];
		$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='img_size' AND ll='*' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$this->img['width']=$row['value1'];
		$this->img['height']=$row['value2'];
		$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='img_quality' AND ll='*' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$this->img['quality']=$row['value1'];
		
		$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='thumb_resize' AND ll='*' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$this->thumb['resize']=$row['value1'];
		$this->thumb['mode']=$row['value2'];
		$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='thumb_size' AND ll='*' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$this->thumb['width']=$row['value1'];
		$this->thumb['height']=$row['value2'];
		$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='thumb_quality' AND ll='*' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$this->thumb['quality']=$row['value1'];
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

	function getImgResize() { return $this->img['resize']; }
	function getImgMode() { return $this->img['mode']; }
	function getImgWidth() { return $this->img['width']; }
	function getImgHeight() { return $this->img['height']; }
	function getImgQuality() { return $this->img['quality']; }
	function getThumbResize() { return $this->thumb['resize']; }
	function getThumbMode() { return $this->thumb['mode']; }
	function getThumbWidth() { return $this->thumb['width']; }
	function getThumbHeight() { return $this->thumb['height']; }
	function getThumbQuality() { return $this->thumb['quality']; }

	
	function countList($tabella=false,$id=false,$conditions='') {
		if(!defined("TABLE_IMG")|!defined("DIR_IMG")) return false;
		$output=array();

		$query="SELECT count(*) AS tot FROM `".TABLE_IMG."` WHERE `idimg`>0 ";
		if($conditions!="") $query.=" AND (".$conditions.") ";

		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $row['tot'];
		}

	function getList($orderby='`creation_date` DESC',$conditions='',$offset=false,$rowcount=false) {
		if(!defined("TABLE_IMG")|!defined("DIR_IMG")) return false;
		$output=array();

		$query="SELECT * FROM `".TABLE_IMG."` WHERE `idimg`>0 ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		if($orderby!="") $query.=" ORDER BY ".$orderby." ";
		if($offset==false) $offset=0;
		if($offset!=""||$rowcount!="") {
			$query.=" LIMIT ".intval($offset);
			if($rowcount!="") $query.=",".intval($rowcount);
			}

		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++)
		{
			$output[$i]=$row;
			if($output[$i]['hotlink']!=""&&$row['filename']!="") {
				$output[$i]['filename']=basename($output[$i]['hotlink']);
				$output[$i]['url']=$output[$i]['hotlink'];
				$output[$i]['hotlink']=true;
				}
			else {
				$output[$i]['url']=ltrim(DIR_IMG,"./").$row['idimg'].'/'.$row['filename'];
				$output[$i]['hotlink']=false;
				}
			if($output[$i]['filename']!=""&&file_exists(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['filename'])) $size=getimagesize(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['filename']);
			else $size=array(0,0,0,"");
			$output[$i]['width']=$size[0];
			$output[$i]['height']=$size[1];
			$output[$i]['thumb']['filename']=$row['thumbnail'];
			$output[$i]['thumb']['url']=DIR_IMG.$row['idimg'].'/'.$row['thumbnail'];
			if($output[$i]['thumbnail']!=""&&file_exists(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['thumbnail'])) $size=getimagesize(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['thumbnail']);
			else $size=array(0,0,0,"");
			$output[$i]['thumb']['width']=$size[0];
			$output[$i]['thumb']['height']=$size[1];
			
			$output[$i]['alts']=json_decode($output[$i]['alt'],true);
			if($output[$i]['alts']!=false)
			{
				if(!isset($output[$i]['alts'][$_SESSION['ll']])) $output[$i]['alts'][$_SESSION['ll']]="";
				$output[$i]['alt']=$output[$i]['alts'][$_SESSION['ll']];
			}
			
		}
		
		return $output;
	}

	function getImage($idimg)
	{
		if(!defined("TABLE_IMG")|!defined("DIR_IMG")) return false;
		$output=array();

		$query="SELECT * FROM `".TABLE_IMG."` WHERE `idimg`=".intval($idimg)." LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if(isset($row['idimg']))
		{
			$output=$row;
			$output['url']=ltrim(DIR_IMG,"./").$row['idimg'].'/'.$row['filename'];
			if(isset($output['hotlink'])&&$output['hotlink']!=""&&$row['filename']!="") {
				$output['filename']=basename($output['hotlink']);
				$output['url']=$output['hotlink'];
				$output['hotlink']=true;
				}
			else $row['hotlink']=false;
			if(isset($output['filename'])&&$output['filename']!=""&&file_exists(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['filename'])) $size=getimagesize(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['filename']);
			else $size=array(0,0,0,"");
			$output['width']=$size[0];
			$output['height']=$size[1];
			$output['thumb']['filename']=isset($output['thumbnail'])?$output['thumbnail']:array();
			$output['thumb']['url']=(isset($row['idimg'])&&isset($row['thumbnail']))?ltrim(DIR_IMG,"./").$row['idimg'].'/'.$row['thumbnail']:'';
			if(isset($output['thumbnail'])&&$output['thumbnail']!=""&&file_exists(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['thumbnail'])) $size=getimagesize(BASERELDIR.DIR_IMG.$row['idimg'].'/'.$row['thumbnail']);
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

	/* INSERT AN IMAGE INTO DB, THEN CREATE A DIRECTORY WITH THE ID AND UPLOAD THE FILE INSIDE */
	function upload($file,$filename) {
		$filename=preg_replace("/([^A-Za-z0-9\._-])+/i",'_',$filename);
		if(substr(strtolower($filename),-4)!='.php'&&substr(strtolower($filename),-5)!='.php3') {
			if(!defined("TABLE_IMG")|!defined("DIR_IMG")) return false;
			
			$query="INSERT INTO `".TABLE_IMG."` (`filename`,`thumbnail`,`hotlink`,`alt`,`creation_date`) VALUES('".mysql_real_escape_string($filename)."','','','',NOW())";
			if(mysql_query($query)) $idimg=mysql_insert_id();
			else return false;
			
			//copy on the right dir
			mkdir(BASERELDIR.DIR_IMG.$idimg);
			if(copy($file,BASERELDIR.DIR_IMG.$idimg.'/'.$filename)) unlink($file);
			else return false;
			
			//another copy before risize, to preserve the original version
			copy(BASERELDIR.DIR_IMG.$idimg.'/'.$filename,BASERELDIR.DIR_IMG.$idimg.'/-originalsize');

			//resize
			$size=getimagesize(BASERELDIR.DIR_IMG.$idimg.'/'.$filename);
			if($this->needToResize($size[0],$size[1])==true) $this->resize(BASERELDIR.DIR_IMG.$idimg.'/'.$filename,$this->img['width'],$this->img['height'],$this->img['quality'],$this->img['mode']);
			
			$this->setThumb($idimg);

			return $idimg;
			}
		return false;
		}
	function setHotlink($idimg,$url) {
		$img=$this->getImage($idimg);
		if($img['idimg']>0) {
			$query="UPDATE ".TABLE_IMG." SET filename='',hotlink='".$url."' WHERE idimg='".$img['idimg']."' LIMIT 1";
			if(!mysql_query($query)) return false;
			}
		}
	function setThumb($idimg,$file=null,$filename=null,$resize=true) {
		$filename=preg_replace("/([^A-Za-z0-9\._-])+/i",'_',$filename);
		$img=$this->getImage($idimg);
		if($img['idimg']>0) {
			if($file==null) $file=BASERELDIR.$img['url'];
			if($filename==null||$filename==$img['filename']) $filename='t_'.$img['filename'];
			if($img['thumb']['filename']!="") unlink(BASERELDIR.DIR_IMG.$idimg.'/'.$img['thumb']['filename']);
			copy($file,BASERELDIR.DIR_IMG.$img['idimg'].'/'.$filename);
			$query="UPDATE `".TABLE_IMG."` SET `thumbnail`='".mysql_real_escape_string($filename)."' WHERE `idimg`='".$img['idimg']."' LIMIT 1";
			if(!mysql_query($query)) return false;
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
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$caption=json_decode($row['alt'],true);
		$defaultCaption="";
		if($caption==false)
		{
			$defaultCaption=b3_lmthize($row['alt']);
			$caption=array();
		}
		
		$kaAdminMenu=new kaAdminMenu();
		foreach($kaAdminMenu->getLanguages() as $l)
		{
			if(!isset($caption[$l['ll']])) $caption[$l['ll']]=$defaultCaption;
		}
		
		$caption[$_SESSION['ll']]=$alt;
		$caption=json_encode($caption);

		$query="UPDATE ".TABLE_IMG." SET alt='".mysql_real_escape_string($caption)."' WHERE `idimg`=".$idimg." LIMIT 1";
		if(!mysql_query($query)) return false;
		return true;
		}

	function updateImage($idimg,$file,$filename,$resize=true,$width=0,$height=0) {
		if(!defined("TABLE_IMG")|!defined("DIR_IMG")) return false;

		$filename=preg_replace("/([^A-Za-z0-9\._-])+/i",'_',$filename);
		if($filename!=""&&substr(strtolower($filename),-4)!='.php'&&substr(strtolower($filename),-4)!='.php3') { //aggiornamento dell'alt e dell'immagine
			$query="SELECT filename FROM ".TABLE_IMG." WHERE idimg=".$idimg;
			$results=mysql_query($query);
			$row=mysql_fetch_array($results);
			$query="UPDATE ".TABLE_IMG." SET filename='".addslashes($filename)."',hotlink='' WHERE idimg=".$idimg;
			if(!mysql_query($query)) return false;
			
			//copio nella dir assegnata
			if(!file_exists(BASERELDIR.DIR_IMG.$idimg)) mkdir(BASERELDIR.DIR_IMG.$idimg);
			if(!copy($file,BASERELDIR.DIR_IMG.$idimg.'/'.$filename)) return false;
			if($filename!=$row['filename']) @unlink(BASERELDIR.DIR_IMG.$idimg.'/'.$row['filename']); //elimino la vecchia immagine

			if($resize==true) {
				$size=getimagesize(BASERELDIR.DIR_IMG.$idimg.'/'.$filename);
				if($this->needToResize($size[0],$size[1])==true) $this->resize(BASERELDIR.DIR_IMG.$idimg.'/'.$filename,$this->img['width'],$this->img['height'],$this->img['quality'],$this->img['mode']);
				}
			else {
				if($width>0||$height>0) {
					$size=getimagesize(BASERELDIR.DIR_IMG.$idimg.'/'.$filename);
					if($width==0) $width=$size[0]/$size[1]*$height;
					elseif($height==0) $height=$size[1]/$size[0]*$width;
					$this->resize(BASERELDIR.DIR_IMG.$idimg.'/'.$filename,$width,$height,$this->img['quality'],'fit');
					}
				}
			}
		
		return $idimg;
		}

	function delete($idimg)
	{
		if(!defined("TABLE_IMG")|!defined("DIR_IMG")) return false;

		$query="SELECT * FROM `".TABLE_IMG."` WHERE `idimg`=".intval($idimg);
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results))
		{
			$query="DELETE FROM `".TABLE_IMG."` WHERE `idimg`=".intval($idimg);
			if(!mysql_query($query)) return false;
			
			if(file_exists(BASERELDIR.DIR_IMG.$idimg.'/'.$row['filename'])&&!is_dir(BASERELDIR.DIR_IMG.$idimg.'/'.$row['filename'])) unlink(BASERELDIR.DIR_IMG.$idimg.'/'.$row['filename']); //delete full size image
			if(file_exists(BASERELDIR.DIR_IMG.$idimg.'/'.$row['thumbnail'])&&!is_dir(BASERELDIR.DIR_IMG.$idimg.'/'.$row['thumbnail'])) unlink(BASERELDIR.DIR_IMG.$idimg.'/'.$row['thumbnail']); //delete thumbnail
			if(file_exists(BASERELDIR.DIR_IMG.$idimg.'/-originalsize')) unlink(BASERELDIR.DIR_IMG.$idimg.'/-originalsize'); //delete original size
			rmdir(BASERELDIR.DIR_IMG.$idimg); //elimino la dir
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
			$results=mysql_query($query);
			while($row=mysql_fetch_array($results))
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
			$results=mysql_query($query);
			while($row=mysql_fetch_array($results))
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
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results))
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
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results))
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

	private function needToResize($w,$h) {
		$resize=false;
		if($this->img['resize']=="all") {
			$resize=true;
			}
		elseif($this->img['resize']=="bigger") {
			if($this->img['mode']=="inside"&&($w>$this->img['width']||$h>$this->img['height'])) $resize=true;
			elseif($this->img['mode']=="outside"&&($w>$this->img['width']&&$h>$this->img['height'])) $resize=true;
			elseif($this->img['mode']=="fit") $resize=true;
			}
		elseif($this->img['resize']=="smaller") {
			if($this->img['mode']=="inside"&&($w<$this->img['width']&&$h<$this->img['height'])) $resize=true;
			elseif($this->img['mode']=="outside"&&($w<$this->img['width']||$h<$this->img['height'])) $resize=true;
			elseif($this->img['mode']=="fit") $resize=true;
			}
		return $resize;
		}

	function resize($img,$x,$y,$quality=100,$mode="inside") {
		/*
		$x e $y sono le dimensioni di un ipotetico rettangolo
		$mode dice se l'immagine deve essere interna,esterna o combaciante con il rettangolo
		
		ESEMPIO DI UTILIZZO:
		b3_thumbalize($img,400,400,75,"fit"),"img/filename.jpg");
		*/
		$size=getimagesize($img);
		// nel caso una delle due dimensioni sia 0, vuol dire che il resize deve essere
		// fatto solo per il lato specificato, mantenendo l'altro proporzionato.
		// per fare cio', la dimensione pari a 0 la porto a dimensione sufficiente ad
		// essere maggiore nel rapporto con $size
		if($x==0&&$y==0) return false;
		elseif($x==0) { $x=round($size[0]*($y/$size[1]))+10; }
		elseif($y==0) { $y=round($size[1]*($x/$size[0]))+10; }
		// maggioro $x e $y di 1, per togliere poi il bordino nero di 1 pixel
		$x++; $y++;

		// maintain aspect ratio
		// in base alle dimensioni dell'immagine e del box, e al mode scelto, ridimensiono
		switch($mode) {
			case "inside":
				if(($size[0]/$x)>=($size[1]/$y)) {
					$cropW=$x-1;
					$ratio=$size[0]/$x;
					$y=$size[1]/$ratio;
					$cropH=$y-1;
					}
				else {
					$cropH=$y-1;
					$ratio=$size[1]/$y;
					$x=$size[0]/$ratio;
					$cropW=$x-1;
					}
				break;
			case "outside":
				if(($size[0]/$x)<=($size[1]/$y)) {
					$cropW=$x-1;
					$ratio=$size[0]/$x;
					$y=$size[1]/$ratio;
					$cropH=$y-1;
					}
				else {
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
		$cropX=0; //punto di taglio X partendo dall'alto
		$cropY=0; //punto di taglio Y partendo dall'alto


		/* 1=GIF, 2=JPG, 3=PNG, 4=SWF, 5=PSD, 6=BMP, 7=TIFF(intel), 8=TIFF(motorola),
		9=JPC, 10=JP2, 11=JPX, 12=JB2, 13=SWC, 14=IFF, 15=WBMP, 16=XBM. */
		if($size['mime']=='image/jpeg') { //se l'immagine e' un JPG
			$source=imagecreatefromjpeg($img);
		
			//creo un'immagine in true-color delle dimensioni di $x e $y
			$destination=imagecreatetruecolor($x,$y);
			if(function_exists('imagecopyresampled')) imagecopyresampled($destination,$source,0,0,0,0,$x,$y,imagesx($source),imagesy($source));
			else imagecopyresized($destination,$source,0,0,0,0,$x,$y,imagesx($source),imagesy($source));
		
			// crop dei bordi neri dell'immagine.
			$finalthumb=imagecreatetruecolor($cropW,$cropH);
			for ($i=$cropY;$i<($cropY+$cropH);$i++) {
				for ($j=$cropX;$j<($cropX+$cropW);$j++) {
					$pixel=imagecolorat($destination,$j,$i);
					imagesetpixel($finalthumb,$j-$cropX,$i-$cropY,$pixel);
					}
				}
			imagedestroy($destination);
			imagejpeg($finalthumb,$img,$quality);
			imagedestroy($finalthumb);

			return $img;
			}
		elseif($size['mime']=='image/png') {
			$source=imagecreatefrompng($img);
			
			//creo un'immagine in true-color delle dimensioni di $x e $y
			$destination=imagecreatetruecolor($x,$y);
			
			// Mantiene la trasparenza
			imagealphablending($destination,false);
			imagesavealpha($destination,true);
			
			if(function_exists('imagecopyresampled')) imagecopyresampled($destination,$source,0,0,0,0,$x,$y,imagesx($source),imagesy($source));
			else imagecopyresized($destination,$source,0,0,0,0,$x,$y,imagesx($source),imagesy($source));
		
			// crop dei bordi neri dell'immagine.
			$finalthumb=imagecreatetruecolor($cropW,$cropH);
			
			// Mantiene la trasparenza
			imagealphablending($finalthumb,false);
			imagesavealpha($finalthumb,true);
			
			for ($i=$cropY;$i<($cropY+$cropH);$i++) {
				for ($j=$cropX;$j<($cropX+$cropW);$j++) {
					$pixel=imagecolorat($destination,$j,$i);
					imagesetpixel($finalthumb,$j-$cropX,$i-$cropY,$pixel);
					}
				}
			
			imagedestroy($destination);
			imagepng($finalthumb,$img,round($quality/100*9));
			imagedestroy($finalthumb);

			return $img;
			}
		elseif($size['mime']=='image/gif') {
			$source=imagecreatefromgif($img);
			
			//creo un'immagine in true-color delle dimensioni di $x e $y
			$destination=imagecreatetruecolor($x,$y);
			
			// Mantiene la trasparenza
			$transp_color=imagecolortransparent($destination);
			$transp_index=imagecolorallocate($destination, $transp_color['red'], $transp_color['green'], $transp_color['blue']);
			imagecolortransparent($destination, $transp_index);
			
			if(function_exists('imagecopyresampled')) imagecopyresampled($destination,$source,0,0,0,0,$x,$y,imagesx($source),imagesy($source));
			else imagecopyresized($destination,$source,0,0,0,0,$x,$y,imagesx($source),imagesy($source));
		
			// crop dei bordi neri dell'immagine.
			$finalthumb=imagecreatetruecolor($cropW,$cropH);
			
			// Mantiene la trasparenza
			$transp_color=imagecolortransparent($finalthumb);
			$transp_index=imagecolorallocate($finalthumb, $transp_color['red'], $transp_color['green'], $transp_color['blue']);
			imagecolortransparent($finalthumb, $transp_index);
			
			for ($i=$cropY;$i<($cropY+$cropH);$i++) {
				for ($j=$cropX;$j<($cropX+$cropW);$j++) {
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
		
	}
?>
