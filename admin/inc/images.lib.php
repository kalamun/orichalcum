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

	
	/* count images
	input vars:
	- filetype
	- conditions
	*/
	function countList($vars = array())
	{
		if(empty($vars['filetype'])) $vars['filetype'] = 1;
		
		$output=array();

		$query="SELECT count(*) AS `tot` FROM `".TABLE_IMG."` WHERE `filetype`='".intval($vars['filetype'])."' ";
		if(!empty($vars['conditions'])) $query.=" AND (".$vars['conditions'].") ";

		$results=ksql_query($query);
		$row=ksql_fetch_array($results);
		return $row['tot'];
	}

	/*
	returns an array of images
	input vars:
	- filetype
	- orderby
	- conditions
	- offset
	- limit
	*/
	function getList($vars=false, $conditions='', $offset=0, $limit=false)
	{
		// parse input vars
		if(!is_array($vars)) {
			$vars=array("orderby"=>$vars);
			$vars['conditions'] = $conditions;
			if(!empty($offset)) $vars['offset'] = $offset;
			if(!empty($limit)) $vars['limit'] = $limit;
		}
		
		if(empty($vars['orderby'])) $vars['orderby']='`creation_date` DESC';
		if(empty($vars['filetype'])) $vars['filetype']=1;
		if(empty($vars['offset'])) $vars['offset']=0;
		
		$output=array();

		$query="SELECT * FROM `".TABLE_IMG."` WHERE `filetype`='".intval($vars['filetype'])."' ";
		if(!empty($vars['conditions'])) $query.=" AND (".$vars['conditions'].") ";
		if($vars['orderby']!="") $query.=" ORDER BY ".$vars['orderby']." ";

		if($vars['offset']>0 || !empty($vars['limit']))
		{
			$query.=" LIMIT ".intval($vars['offset']);
			if(!empty($vars['limit'])) $query.=",".intval($vars['limit']);
		}

		$results=ksql_query($query);
		for($i=0;$row=ksql_fetch_array($results);$i++)
		{
			$output[$i] = $this->row2image($row);
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
		if(isset($row['idimg'])) return $this->row2image($row);
		else return false;
	}

	
	// process db row and return a full array
	function row2image($row)
	{
		$output=$row;
		
		// metadata
		$output['metadata'] = unserialize($output['metadata']);
		if(empty($output['metadata']['duration'])) $output['metadata']['duration'] = 0;
		if(empty($output['metadata']['rotation'])) $output['metadata']['rotation'] = 0;
		if(empty($output['metadata']['embeddingcode'])) $output['metadata']['embeddingcode'] = '';
		if(empty($output['metadata']['subtitles'])) $output['metadata']['subtitles'] = array();
		
		$output['mime-type'] = $this->getMimeType($row['filename']);
		
		if($row['filetype'] == 1) $dir = DIR_IMG;
		elseif($row['filetype'] == 2) $dir = DIR_MEDIA;
		elseif($row['filetype'] == 3 || $row['filetype'] == 9) $dir = DIR_DOCS;
		$dir = ltrim($dir,"./");
		
		$output['url']=$dir.$row['idimg'].'/'.$row['filename'];
		if(isset($output['hotlink'])&&$output['hotlink']!=""&&$row['filename']!="")
		{
			$output['filename']=basename($output['hotlink']);
			$output['url']=$output['hotlink'];
			$output['hotlink']=true;
		}
		else $row['hotlink']=false;

		$output['width'] = 0;
		$output['height'] = 0;

		if(isset($output['filename']) && $output['filename']!="" && file_exists(BASERELDIR.$dir.$row['idimg'].'/'.$row['filename']) && filesize(BASERELDIR.$dir.$row['idimg'].'/'.$row['filename']) > 11)
		{
			if($row['filetype'] == 1)
			{
				if(exif_imagetype(BASERELDIR.$dir.$row['idimg'].'/'.$row['filename'])!=false) $size = getimagesize(BASERELDIR.$dir.$row['idimg'].'/'.$row['filename']);
				else $size=array(0,0,0,"");
				$output['width'] = $size[0];
				$output['height'] = $size[1];
				
			} elseif($row['filetype'] == 2) {
				if(empty($output['metadata']['width'])) $output['metadata']['width']=1280;
				if(empty($output['metadata']['height'])) $output['metadata']['height']=720;
				$output['width'] = $output['metadata']['width'];
				$output['height'] = $output['metadata']['height'];
				
			}
		
			$output['thumb']['filename']=isset($output['thumbnail'])?$output['thumbnail']:array();
			$output['thumb']['url']=(isset($row['idimg'])&&isset($row['thumbnail']))?$dir.$row['idimg'].'/'.$row['thumbnail']:'';
			
			if(!empty($output['thumbnail']) && file_exists(BASERELDIR.$dir.$row['idimg'].'/'.$row['thumbnail']) && filesize(BASERELDIR.$dir.$row['idimg'].'/'.$row['thumbnail']) && exif_imagetype(BASERELDIR.$dir.$row['idimg'].'/'.$row['thumbnail'])!=false) $size=getimagesize(BASERELDIR.$dir.$row['idimg'].'/'.$row['thumbnail']);
			else $size=array(0,0,0,"");
			$output['thumb']['width']=$size[0];
			$output['thumb']['height']=$size[1];
			
			$output['alts']=json_decode($output['alt'],true);
			if(!empty($output['alts']) && is_array($output['alts']))
			{
				if(!isset($output['alts'][$_SESSION['ll']])) $output['alts'][$_SESSION['ll']]="";
				$output['alt']=$output['alts'][$_SESSION['ll']];
			} else {
				$output['alts']=array($_SESSION['ll'] => $output['alt']);
			}
		}

		return $output;
	}
	
	// insert an image into db, then create a directory called as the id and upload the file inside
	function upload($file,$filename)
	{
		$filename=preg_replace("/([^A-Za-z0-9\._-])+/i",'-',$filename);

		/* check filename validity */
		$filename=trim($filename," ./");
		$filename=str_replace("/","",$filename);

		// set default metadata
		$metadata = array();
		
		// get file type: 1 = images, 2 = media, 9 = documents
		$filetype = $this->getFileType($filename);
		if($filetype == 9)
		{
			$filename .= '-renamed';
			$filetype = 3;
		}

		// set db table and dir according to file type
		$default_table = "";
		$default_dir = "";
		if($filetype==1) $default_dir = DIR_IMG;
		elseif($filetype==2) $default_dir = DIR_MEDIA;
		elseif($filetype==3) $default_dir = DIR_DOCS;
		
		// insert into db
		$query="INSERT INTO `".TABLE_IMG."` (`filetype`,`filename`,`thumbnail`,`hotlink`,`alt`,`creation_date`,`metadata`) VALUES('".$filetype."', '".ksql_real_escape_string($filename)."','','','',NOW(),'".ksql_real_escape_string(serialize($metadata))."')";
		if(ksql_query($query)) $idimg=ksql_insert_id();
		else return false;
		
		//copy on the right dir
		if(!file_exists(BASERELDIR.$default_dir)) mkdir(BASERELDIR.$default_dir);
		if(!file_exists(BASERELDIR.$default_dir.$idimg)) mkdir(BASERELDIR.$default_dir.$idimg);

		$ffile = $_SERVER['DOCUMENT_ROOT'].BASEDIR.$default_dir.$idimg.'/'.$filename;
		if(copy($file, $ffile)) unlink($file);
		else return false;
		
		if($filetype==1)
		{
			//another copy before resize, to preserve the original version
			$ofile = BASERELDIR.$default_dir.$idimg.'/-originalsize';
			copy($ffile, $ofile);

			//resize
			$size=getimagesize($ffile);
			if($this->needToResize($size[0],$size[1])==true) $this->resize($ffile, $this->img['width'], $this->img['height'], $this->img['quality'], $this->img['mode']);
			else $this->recompress($ffile, $this->img['quality']);
			
			$this->setThumb($idimg);

			// create mobile version, if active
			if($this->mobile['active']=="y")
			{
				$mfile = BASERELDIR.$default_dir.$idimg.'/m_'.$filename;
				$mwidth = min($size[0], $this->img['width']);
				$mheight = min($size[1], $this->img['height']);
				copy($ofile, $mfile);
				$this->mobile['width'] = intval($mwidth / 100 * $this->mobile['ratio']);
				$this->mobile['height'] = intval($mheight / 100 * $this->mobile['ratio']);
				$this->mobile['quality'] = intval($this->img['quality'] / 100 * ($this->mobile['ratio'] + ((100 - $this->mobile['ratio']) / 2)));
				$this->resize($mfile, $this->mobile['width'], $this->mobile['height'], $this->mobile['quality'], $this->img['mode']);
			}

		} elseif($filetype==2) {
			// check if ffmpeg is available
			exec("ffmpeg -h", $output);

			if(!empty($output) && is_array($output) && $output[0] != 1)
			{
				unset($output);
				exec("ffmpeg -i ".escapeshellarg($ffile)." 2>&1", $output);

				// try to find video dimensions, rotation and duration
				$width = 1280;
				$height = 720;
				foreach($output as $line)
				{
					$line = trim($line);
					
					if(strtolower(substr($line, 0, 7)) == "stream ")
					{
						preg_match("/ (\d+)x(\d+),/", $line, $match);
						if(isset($match[1]))
						{
							$width = intval($match[1]);
							$metadata['width'] = $width;
						}
						if(isset($match[2]))
						{
							$height = intval($match[2]);
							$metadata['height'] = $height;
						}

					} elseif(strtolower(substr($line, 0, 7)) == "rotate ") {
						preg_match("/ (\d+)/", $line, $match);
						if(isset($match[1])) $metadata['rotation'] = intval($match[1]);

					} elseif(strtolower(substr($line, 0, 9)) == "duration ") {
						preg_match("/ (\d{2}:\d{2}:\d{2}\.\d{2})/", $line, $match);
						if(isset($match[1]))
						{
							$match[1] = str_replace(":", "", $match[1]);
							$metadata['duration'] = floatval($match[1]);
						}
					}
					
				}
				
				$this->updateMetadata($idimg, $metadata);
				
				// try to automatically create a thumbnail
				$tfilename = basename($ffile);
				$tfilename = substr($tfilename, 0, strrpos($tfilename, ".")).'.png';
				$tfile = $_SERVER['DOCUMENT_ROOT'].BASEDIR.$default_dir.$idimg.'/'.$tfilename;
				
				exec("ffmpeg -i ".escapeshellarg($ffile)." -ss 00:00:01.000 -vframes 1 ".escapeshellarg($tfile)." 2>&1");
				
				if(file_exists($tfile))
				{
					if($this->setThumb($idimg, $tfile, "t_".$tfilename) == true) unlink($tfile);
				}
			}
			
		}
		

		return $idimg;
	}
	
	/*
	Add a file into the same subdirectory of the main file
	*/
	public function addFile($id, $file)
	{
		if($file['error']>0) return false;

		$filename = $file['name'];
		$tmpfilename = $file['tmp_name'];

		// check if ID is valid
		$img = $this->getImage($id);
		if(empty($img['idimg'])) return false;

		// clean file name
		$filename = preg_replace("/([^A-Za-z0-9\._-])+/i",'-',$filename);
		
		// check that file name is different from image and thumbnail name
		if($img['filename'] == $filename || $img['thumbnail'] == $filename) return false;
		
		// check if filename is valid
		if($this->getFileType($filename) == 9) return false;
		
		// get the path
		$default_dir = "";
		if($img['filetype']==1) $default_dir = DIR_IMG;
		elseif($img['filetype']==2) $default_dir = DIR_MEDIA;
		elseif($img['filetype']==3) $default_dir = DIR_DOCS;
		$path = $_SERVER['DOCUMENT_ROOT'].BASEDIR.$default_dir.'/'.$id.'/';
		
		// copy file
		copy($file['tmp_name'], $path.$filename);
		return $filename;
	}
	
	/*
	Clean temporary directory from failed uploads
	*/
	public function cleanTmpDir()
	{
		$dir = $_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_TEMP;
		if(!file_exists($dir) || !is_dir($dir)) mkdir($dir);
		
		foreach(scandir($dir) as $filename)
		{
			// remove all the files that...
			if(strlen($filename) > 8 // are long (the short ones are not upload's temp files)
				&& substr($filename, 0, 3) == 'tmp' // start with "tmp"
				&& intval(substr($filename, 3, strlen(time()))) < time()-3600 // are older than 1 hour
				) unlink($dir.$filename);
				
			// remove all the progress files...
			if(strlen($filename) > 10 // are long (the short ones are not upload's temp files)
				&& substr($filename, 0, 8) == 'progress' // start with "progress"
				) unlink($dir.$filename);
		}
	}
	
	function setHotlink($idimg,$url)
	{
		$img=$this->getImage($idimg);
		if($img['idimg']>0)
		{
			$query="UPDATE ".TABLE_IMG." SET `filename`='',`hotlink`='".ksql_real_escape_string($url)."' WHERE `idimg`='".$img['idimg']."' LIMIT 1";
			if(!ksql_query($query)) return false;
		}
	}
	
	function updateMetadata($idimg, $metadata)
	{
		$img=$this->getImage($idimg);
		if($img['idimg']>0)
		{
			$query="UPDATE ".TABLE_IMG." SET `metadata`='".ksql_real_escape_string(serialize($metadata))."' WHERE `idimg`='".$img['idimg']."' LIMIT 1";
			if(!ksql_query($query)) return false;
		}
		return true;
	}

	function setThumb($idimg,$file=null,$filename=null,$resize=true)
	{
		$filename=preg_replace("/([^A-Za-z0-9\._-])+/i",'_',$filename);
		$img=$this->getImage($idimg);
		if($img['idimg']>0)
		{
			if($file==null) $file=$_SERVER['DOCUMENT_ROOT'].BASEDIR.$img['url'];
			if($filename==null||$filename==$img['filename']) $filename='t_'.$img['filename'];
			
			if(!file_exists($file) || !is_file($file)) return false;
			
			$filetype = $this->getFileType($file);
			if($filetype != 1) return false; //if is not an image, end

			$dir = trim(dirname($img['url']), "/");
			
			if($img['thumb']['filename']!="") unlink($_SERVER['DOCUMENT_ROOT'].BASEDIR.$dir.'/'.$img['thumb']['filename']);
			$targetfile = $_SERVER['DOCUMENT_ROOT'].BASEDIR.$dir.'/'.$filename;
			if(!copy($file, $targetfile)) return false;
			
			$query="UPDATE `".TABLE_IMG."` SET `thumbnail`='".ksql_real_escape_string($filename)."' WHERE `idimg`='".$img['idimg']."' LIMIT 1";
			if(!ksql_query($query)) return false;
			
			if($resize==true) $this->resize($targetfile, $this->thumb['width'], $this->thumb['height'], $this->thumb['quality'], $this->thumb['mode']);
		}
		return true;
	}
	
	/* get file type
	returns:
	 1 = image
	 2 = media
	 3 = document
	 9 = document that must be renamed
	*/
	function getFileType($file)
	{
		$filename=trim(basename(strtolower($file))," ./");
		$filename=str_replace("/","",$filename);
		$fileextension=substr($filename,strrpos($filename,".")+1);

		// images
		$ext=array_flip(array("png","jpg","jpeg","gif"));
		if(isset($ext[$fileextension])) return 1;
		
		// medias
		$ext=array_flip(array("mov","mpg","mp3","mp4","webm","ogv","ogg","oga","avi","wmv","flv","f4v","swf"));
		if(isset($ext[$fileextension])) return 2;
		
		// rename file in case of file types not allowed
		$ext=array_flip(array("php","php3","exe","msi"));
		if(isset($ext[$fileextension])) return 9;
		return 3;

		/* mime types, for further use
		$mime=array_flip(array("image/jpeg","image/pjpeg","image/png","image/gif"));
		$mime=array_flip(array("video/quicktime","video/mpeg","video/x-mpeg","video/avi","video/msvideo","video/x-flv","video/x-f4v","video/mp4","video/x-ms-wmv","video/ogg","audio/mpeg","audio/x-mpeg","audio/mpeg3","audio/x-mpeg3","audio/ogg","application/x-shockwave-flash"));
		$mime=array_flip(array("text/php","text/x-php","application/php","application/x-php","application/x-httpd-php","application/x-httpd-php-source","application/x-msdownload"));
		*/
	}
	
	/* get mime type based on file name */
	function getMimeType($filename)
	{
		$filename=trim(basename($filename)," ./");
		$filename=str_replace("/","",$filename);
		$fileextension=substr($filename,strrpos($filename,".")+1);
		
		$mime=array(
			"jpg"=>"image/jpeg",
			"jpeg"=>"image/jpeg",
			"png"=>"image/png",
			"gif"=>"image/gif",
			"mov"=>"video/quicktime",
			"mpg"=>"video/mpeg",
			"avi"=>"video/avi",
			"wmv"=>"video/msvideo",
			"flv"=>"video/x-flv",
			"f4v"=>"video/x-f4v",
			"mp4"=>"video/mp4",
			"ogv"=>"video/ogg",
			"mp3"=>"audio/mpeg",
			"ogg"=>"audio/ogg",
			"midi"=>"audio/x-midi",
			"swf"=>"application/x-shockwave-flash",
			"php"=>"text/php",
			"doc"=>"application/msword",
			"docx"=>"application/vnd.openxmlformats",
			"ppt"=>"application/vnd.ms-powerpoint",
			"pptx"=>"application/vnd.openxmlformats",
			"xls"=>"application/vnd.ms-excel",
			"xlsx"=>"application/vnd.openxmlformats",
			"html"=>"text/html",
			"svg"=>"image/svg+xml",
			"txt"=>"text/plain",
			"zip"=>"application/zip",
			"xml"=>"application/xml",
			"pdf"=>"application/pdf"
			);
		if(!empty($mime[$fileextension])) return $mime[$fileextension];
		return false;
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
		if($filename!="" && substr($this->getMimeType($filename),0,6) == "image/")
		{
			$query="SELECT `filename` FROM ".TABLE_IMG." WHERE `idimg`=".$idimg;
			$results=ksql_query($query);
			$row=ksql_fetch_array($results);
			$query="UPDATE `".TABLE_IMG."` SET `filename`='".addslashes($filename)."',hotlink='' WHERE idimg=".$idimg;
			if(!ksql_query($query)) return false;

			$ffile=$_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$idimg.'/'.$filename;
			$mfile=$_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$idimg.'/m_'.$filename;
			$ofile=$_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$idimg.'/-originalsize';
			
			// copy image into the right dir
			if(!file_exists($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$idimg)) mkdir($_SERVER['DOCUMENT_ROOT'].BASEDIR.DIR_IMG.$idimg);
			if($file!=$ffile)
			{
				if(copy($file, $ffile)) unlink($file);
				else return false;
			}
			
			copy($ffile, $ofile);


			// delete old images
			if($filename!=$row['filename'] && file_exists(BASERELDIR.DIR_IMG.$idimg.'/'.$row['filename'])) unlink(BASERELDIR.DIR_IMG.$idimg.'/'.$row['filename']);
			if(file_exists(BASERELDIR.DIR_IMG.$idimg.'/m_'.$row['filename'])) unlink(BASERELDIR.DIR_IMG.$idimg.'/m_'.$row['filename']);

			if($resize==true)
			{
				$size=getimagesize(BASERELDIR.DIR_IMG.$idimg.'/'.$filename);
				if($this->needToResize($size[0],$size[1])==true) $this->resize($ffile, $this->img['width'], $this->img['height'], $this->img['quality'], $this->img['mode']);
				else $this->recompress($ffile, $this->img['quality']);
				
				$size=getimagesize(BASERELDIR.DIR_IMG.$idimg.'/'.$filename);
				$this->mobile['width'] = intval($size[0] / 100 * $this->mobile['ratio']);
				$this->mobile['height'] = intval($size[1] / 100 * $this->mobile['ratio']);

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
				$mwidth = min($size[0], $this->img['width']);
				$mheight = min($size[1], $this->img['height']);
				copy($ofile, $mfile);
				$this->mobile['width'] = intval($mwidth / 100 * $this->mobile['ratio']);
				$this->mobile['height'] = intval($mheight / 100 * $this->mobile['ratio']);
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

			case "cropcenter":
				if(($size[0]/$x)<=($size[1]/$y))
				{
					$ratio=$size[0]/$x;
					$y=$size[1]/$ratio;
					$cropW=$x-1;
					$cropH=$cropW;
				} else {
					$ratio=$size[1]/$y;
					$x=$size[0]/$ratio;
					$cropH=$y-1;
					$cropW=$cropH;
				}
				$cropX = floor( ($x-$cropW) / 2 );
				$cropY = floor( ($y-$cropH) / 2 );
				break;

		}
		
		if( !isset($cropX) ) $cropX=0; //crop left starting point
		if( !isset($cropY) ) $cropY=0; //crop top starting point

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
			imagealphablending($source,false);
			imagesavealpha($source,true);
			imagepng($source,$img,round($quality/100*9));
			return $img;

		} elseif($size['mime']=='image/gif') {
			//no needs to recompress
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
