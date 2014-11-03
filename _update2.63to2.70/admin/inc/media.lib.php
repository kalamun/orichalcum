<?php /* (c) Kalamun.org - GNU/GPL 3 */

// gestione del caricamento e del ridimensionamento delle immagini

class kaMedia {
	protected $media,$thumb;

	function kaMedia() {
		require_once('kalamun.lib.php');

		//carico i valori predefiniti
		$thumb=array('resize','mode','width','height','quality');
		
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
	
	
	function setThumbResize($v) { if($v!="all"&&$v!="bigger"&&$v!="smaller") $v="none"; $this->thumb['resize']=$v; }
	function setThumbMode($v) { if($v!="inside"&&$v!="outside") $v="fit"; $this->thumb['mode']=$v; }
	function setThumbWidth($v) { $this->thumb['width']=intval($v); }
	function setThumbHeight($v) { $this->thumb['height']=intval($v); }
	function setThumbQuality($v) { $this->thumb['quality']=intval($v); }

	function getThumbResize() { return $this->thumb['resize']; }
	function getThumbMode() { return $this->thumb['mode']; }
	function getThumbWidth() { return $this->thumb['width']; }
	function getThumbHeight() { return $this->thumb['height']; }
	function getThumbQuality() { return $this->thumb['quality']; }

	
	function countList($tabella=false,$id=false,$conditions='') {
		if(!defined("TABLE_MEDIA")|!defined("DIR_MEDIA")) return false;
		$output=array();

		$query="SELECT count(*) AS tot FROM ".TABLE_MEDIA." WHERE idmedia>0 ";
		if($tabella!="") $query.=" AND tabella='".$tabella."' ";
		if($id!="") $query.=" AND id='".$id."' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";

		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $row['tot'];
		}

	function getList($tabella=false,$id=false,$orderby='ordine',$conditions='',$offset=false,$rowcount=false) {
		if(!defined("TABLE_MEDIA")|!defined("DIR_MEDIA")) return false;
		$output=array();

		$query="SELECT * FROM ".TABLE_MEDIA." WHERE idmedia>0 ";
		if($tabella!="") $query.=" AND tabella='".$tabella."' ";
		if($id!="") $query.=" AND id='".$id."' ";
		if($conditions!="") $query.=" AND (".$conditions.") ";
		if($orderby!="") $query.=" ORDER BY ".$orderby." ";
		if($offset!=""||$rowcount!="") {
			$query.=" LIMIT ".$offset;
			if($rowcount!="") $query.=",".$rowcount;
			}

		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$output[$i]=$row;
			if($output[$i]['hotlink']!=""&&$row['filename']!="") {
				$output[$i]['filename']=basename($output[$i]['hotlink']);
				$output[$i]['url']=$output[$i]['hotlink'];
				$output[$i]['hotlink']=true;
				}
			else {
				$output[$i]['url']=ltrim(DIR_MEDIA,"./").$row['idmedia'].'/'.$row['filename'];
				$output[$i]['hotlink']=false;
				}
			$output[$i]['thumb']['filename']=$row['thumbnail'];
			$output[$i]['thumb']['url']=DIR_MEDIA.$row['idmedia'].'/'.$row['thumbnail'];
			if($output[$i]['thumbnail']!="") $size=@getimagesize(BASERELDIR.DIR_MEDIA.$row['idmedia'].'/'.$row['thumbnail']);
			else $size=array(0,0,0,"");
			$output[$i]['thumb']['width']=$size[0];
			$output[$i]['thumb']['height']=$size[1];
			}
		
		return $output;
		}

	function getMedia($idmedia) {
		if(!defined("TABLE_MEDIA")|!defined("DIR_MEDIA")) return false;
		$output=array();

		$query="SELECT * FROM ".TABLE_MEDIA." WHERE idmedia=".$idmedia." LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$output=$row;
		if($output['hotlink']!=""&&$row['filename']!="") {
			$output['filename']=basename($output['hotlink']);
			$output['url']=$output['hotlink'];
			$output['hotlink']=true;
			}
		else {
			$row['hotlink']=false;
			$output['url']=ltrim(DIR_MEDIA,"./").$row['idmedia'].'/'.$row['filename'];
			}
		$output['thumb']['filename']=$output['thumbnail'];
		$output['thumb']['url']=ltrim(DIR_MEDIA,"./").$row['idmedia'].'/'.$row['thumbnail'];
		if($output['thumbnail']!="") $size=getimagesize(BASERELDIR.DIR_MEDIA.$row['idmedia'].'/'.$row['thumbnail']);
		else $size=array(0,0,0,"");
		$output['thumb']['width']=$size[0];
		$output['thumb']['height']=$size[1];
		
		return $output;
		}		
	
	function upload($file="",$filename="",$tabella,$id,$title,$alt,$resize=true,$width=0,$height=0,$duration=60) {
		if(!defined("TABLE_MEDIA")|!defined("DIR_MEDIA")) return false;
		$filename=preg_replace("/([^A-Za-z0-9\._-])+/i",'_',$filename);
		if($filename!=""&&substr(strtolower($filename),-4)!='.php'&&substr(strtolower($filename),-4)!='.php3') {
			$id=intval($id);
			$duration=intval($duration);
			
			/* indice dell'ordine */
			$query="SELECT ordine FROM ".TABLE_MEDIA." WHERE tabella='".$tabella."' AND id='".$id."' ORDER BY ordine DESC LIMIT 0,1";
			$results=mysql_query($query);
			$row=mysql_fetch_array($results);
			if($row['ordine']==false) $row['ordine']=0;
			$ordine=$row['ordine']+1;
			
			$query="INSERT INTO ".TABLE_MEDIA." (filename,thumbnail,hotlink,htmlcode,tabella,id,title,width,height,duration,alt,ordine) VALUES('".addslashes($filename)."','','','','".$tabella."','".$id."','".b3_htmlize($title,true,"")."','".$width."','".$height."','".$duration."','".b3_htmlize($alt,true,"strong,em,u,a,acronym")."',".$ordine.")";
			if(mysql_query($query)) { $idmedia=mysql_insert_id(); }
			else { return false; }
			
			mkdir(BASERELDIR.DIR_MEDIA.$idmedia);
			if($file!="") {
				if(!copy($file,BASERELDIR.DIR_MEDIA.$idmedia.'/'.$filename)) return false;
				}
			
			return $idmedia;
			}
		return false;
		}
	function embed($tabella,$id,$htmlcode,$title,$duration=60,$alt) {
		$id=intval($id);
		$duration=intval($duration);

		/* indice dell'ordine */
		$query="SELECT ordine FROM ".TABLE_MEDIA." WHERE tabella='".$tabella."' AND id='".$id."' ORDER BY ordine DESC LIMIT 0,1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if($row['ordine']==false) $row['ordine']=0;
		$ordine=$row['ordine']+1;
		
		$query="INSERT INTO ".TABLE_MEDIA." (filename,thumbnail,hotlink,htmlcode,tabella,id,title,width,height,duration,alt,ordine) VALUES('','','','".addslashes(stripslashes($htmlcode))."','".$tabella."','".$id."','".b3_htmlize($title,true,"")."','0','0','".$duration."','".b3_htmlize($alt,true,"strong,em,u,a,acronym")."',".$ordine.")";
		if(mysql_query($query)) { $idmedia=mysql_insert_id(); }
		else { return false; }

		mkdir(BASERELDIR.DIR_MEDIA.$idmedia);
		return $idmedia;
		}
	function setHotlink($idmedia,$url) {
		$media=$this->getMedia($idmedia);
		if($media['idmedia']>0) {
			$query="UPDATE ".TABLE_MEDIA." SET filename='".basename($url)."',hotlink='".$url."' WHERE idmedia='".$media['idmedia']."' LIMIT 1";
			if(!mysql_query($query)) return false;
			}
		return true;
		}
	function setThumb($idmedia,$file,$filename,$resize=true) {
		$filename=preg_replace("/([^A-Za-z0-9\._-])+/i",'_',$filename);
		if($filename!=""&&substr(strtolower($filename),-4)!='.php'&&substr(strtolower($filename),-4)!='.php3') {
			$media=$this->getMedia($idmedia);
			if($media['idmedia']>0) {
				if($media['thumb']['filename']!="") unlink(BASERELDIR.DIR_MEDIA.$idmedia.'/'.$media['thumb']['filename']);
				copy($file,BASERELDIR.DIR_MEDIA.$media['idmedia'].'/'.$filename);
				$query="UPDATE ".TABLE_MEDIA." SET thumbnail='".$filename."' WHERE idmedia='".$media['idmedia']."' LIMIT 1";
				if(!mysql_query($query)) return false;
				if($resize==true) $this->resize(BASERELDIR.DIR_MEDIA.$idmedia.'/'.$filename,$this->thumb['width'],$this->thumb['height'],$this->thumb['quality'],$this->thumb['mode']);
				}
			return true;
			}
		return false;
		}
	function updateProperties($idmedia,$title=null,$alt=null,$width=null,$height=null,$duration=null,$htmlcode=null) {
		$query="UPDATE ".TABLE_MEDIA." SET idmedia=idmedia ";
		if($title!=null) $query.=",title='".b3_htmlize($title,true,"")."' ";
		if($alt!=null) $query.=",alt='".b3_htmlize($alt,true,"strong,em,u,a,acronym")."' ";
		if($width!=null) $query.=",width='".intval($width)."' ";
		if($height!=null) $query.=",height='".intval($height)."' ";
		if($duration!=null) $query.=",duration='".intval($duration)."' ";
		if($htmlcode!=null) $query.=",htmlcode='".addslashes(stripslashes($htmlcode))."' ";
		$query.=" WHERE idmedia=".$idmedia;
		if(!mysql_query($query)) return false;
		return true;
		}
	function updateMedia($idmedia,$file,$filename) {
		if(!defined("TABLE_MEDIA")|!defined("DIR_MEDIA")) return false;
		
		$filename=preg_replace("/([^A-Za-z0-9\._-])+/i",'_',$filename);
		if($filename!=""&&substr(strtolower($filename),-4)!='.php'&&substr(strtolower($filename),-4)!='.php3') {
			$query="SELECT filename FROM ".TABLE_MEDIA." WHERE idmedia=".$idmedia;
			$results=mysql_query($query);
			$row=mysql_fetch_array($results);
			$query="UPDATE ".TABLE_MEDIA." SET filename='".addslashes($filename)."',hotlink='' WHERE idmedia=".$idmedia;
			if(!mysql_query($query)) return false;
			
			//copio nella dir assegnata
			if(!file_exists(BASERELDIR.DIR_MEDIA.$idmedia)) mkdir(BASERELDIR.DIR_MEDIA.$idmedia);
			if(!copy($file,BASERELDIR.DIR_MEDIA.$idmedia.'/'.$filename)) return false;
			if($filename!=$row['filename']) @unlink(BASERELDIR.DIR_MEDIA.$idmedia.'/'.$row['filename']); //elimino il vecchio file
			}
		
		return $idmedia;
		}

	function delete($idmedia) {
		if(!defined("TABLE_MEDIA")|!defined("DIR_MEDIA")) return false;

		$query="SELECT * FROM ".TABLE_MEDIA." WHERE idmedia=".$idmedia;
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			$query="UPDATE FROM ".TABLE_MEDIA." SET ordine=ordine-1 WHERE tabella='".$row['tabella']."' AND id='".$row['id']."' AND ordine>".$row['ordine'];
			mysql_query($query);
			$query="DELETE FROM ".TABLE_MEDIA." WHERE idmedia=".$idmedia;
			if(!mysql_query($query)) return false;
			
			if($row['filename']!=""&&file_exists(BASERELDIR.DIR_MEDIA.$idmedia.'/'.$row['filename'])) unlink(BASERELDIR.DIR_MEDIA.$idmedia.'/'.$row['filename']); //elimino la vecchia immagine
			if($row['thumbnail']!=""&&file_exists(BASERELDIR.DIR_MEDIA.$idmedia.'/'.$row['thumbnail'])) unlink(BASERELDIR.DIR_MEDIA.$idmedia.'/'.$row['thumbnail']); //elimino la vecchia thumb
			rmdir(BASERELDIR.DIR_MEDIA.$idmedia); //elimino la dir
			}
		else return false;
		
		return true;
		}

	function usage($idmedia) {
		$output=array();
		$id=array(TABLE_CONFIG=>"idconf",TABLE_USERS=>"iduser",TABLE_BANNER=>"idbanner",TABLE_PAGINE=>"idpag",TABLE_LANDINGPAGE=>"idlp",TABLE_LANDINGPAGE_T=>"idlpt",TABLE_THANKYOUPAGE=>"idlpt",TABLE_NEWS=>"idnews",TABLE_PHOTOGALLERY=>"idphg");
		$type=array(TABLE_CONFIG=>"Configurazione",TABLE_USERS=>"Utenti",TABLE_BANNER=>"Banner",TABLE_PAGINE=>"Pagina",TABLE_LANDINGPAGE=>"Landing-page",TABLE_LANDINGPAGE_T=>"Landing-page",TABLE_THANKYOUPAGE=>"Thankyou-page",TABLE_NEWS=>"News",TABLE_PHOTOGALLERY=>"Gallerie Fotografiche");

		$query="SELECT * FROM ".TABLE_MEDIA." WHERE idmedia=".$idmedia;
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$descr=$type[$row['tabella']].' ';
		$query2="SELECT * FROM ".$row['tabella']." WHERE ".$id[$row['tabella']]."=".$row['id']." LIMIT 1";
		$results2=mysql_query($query2);
		$row2=mysql_fetch_array($results2);
		if(!isset($row2['ll'])) $row2['ll']="";
		if(!isset($row2['dir'])) $row2['dir']="";
		$descr.='<strong>'.strtolower($row2['ll']).'/'.($row['tabella']==TABLE_NEWS?'news/':'').$row2['dir'].'</strong>';
		$url=BASEDIR.strtolower($row2['ll']).'/'.($row['tabella']==TABLE_NEWS?'news/':'').$row2['dir'];

		$output[]=array("table"=>$row['tabella'],"id"=>$row['id'],"descr"=>$descr,"url"=>$url);
		
		$search=array();
		$search[]=array(TABLE_PAGINE,'anteprima');
		$search[]=array(TABLE_PAGINE,'testo');
		$search[]=array(TABLE_LANDINGPAGE,'testo');
		$search[]=array(TABLE_LANDINGPAGE_T,'testo');
		$search[]=array(TABLE_THANKYOUPAGE,'testo');
		$search[]=array(TABLE_NEWS,'anteprima');
		$search[]=array(TABLE_NEWS,'testo');
		
		foreach($search as $s) {
			$query2="SELECT * FROM ".$s[0]." WHERE ".$s[1]." LIKE '%id=\"img".$idmedia."\"%' OR ".$s[1]." LIKE '%id=\"thumb".$idmedia."\"%' LIMIT 1";
			$results2=mysql_query($query2);
			while($row2=mysql_fetch_array($results2)) {
				$descr=$type[$s[0]].' <strong>'.strtolower($row2['ll']).'/'.($s[0]==TABLE_NEWS?'news/':'').$row2['dir'].'</strong>';
				$url=BASEDIR.strtolower($row2['ll']).'/'.($s[0]==TABLE_NEWS?'news/':'').$row2['dir'];
				if($url!=$output[0]['url']) $output[]=array("table"=>$s[0],"id"=>$row2[$id[$s[0]]],"descr"=>$descr,"url"=>$url);
				}
			}
		
		return $output;
		}

	private function needToResize($w,$h) {
		$resize=false;
		if($this->thumb['resize']=="all") {
			$resize=true;
			}
		elseif($this->thumb['resize']=="bigger") {
			if($this->thumb['mode']=="inside"&&($w>$this->thumb['width']||$h>$this->thumb['height'])) $resize=true;
			elseif($this->thumb['mode']=="outside"&&($w>$this->thumb['width']&&$h>$this->thumb['height'])) $resize=true;
			elseif($this->thumb['mode']=="fit") $resize=true;
			}
		elseif($this->thumb['resize']=="smaller") {
			if($this->thumb['mode']=="inside"&&($w<$this->thumb['width']&&$h<$this->thumb['height'])) $resize=true;
			elseif($this->thumb['mode']=="outside"&&($w<$this->thumb['width']||$h<$this->thumb['height'])) $resize=true;
			elseif($this->thumb['mode']=="fit") $resize=true;
			}
		return $resize;
		}

	function resize($media,$x,$y,$quality=100,$mode="inside") {
		/*
		$x e $y sono le dimensioni di un ipotetico rettangolo
		$mode dice se l'immagine deve essere interna,esterna o combaciante con il rettangolo
		
		ESEMPIO DI UTILIZZO:
		b3_thumbalize($media,400,400,75,"fit"),"img/filename.jpg");
		*/
		$size=getimagesize($media);
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
		if($size[2]==2) { //se l'immagine e' un JPG
			$source=imagecreatefromjpeg($media);
		
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
			imagejpeg($finalthumb,$media,$quality);
			imagedestroy($finalthumb);

			return $media;
			}
		return false;
		}
		
	}
