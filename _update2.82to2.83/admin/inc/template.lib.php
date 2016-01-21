<?php 
/* (c) Kalamun.org - GPL v3 */

class kaTemplate {
	protected $tpl,$log;

	public function __construct($tpl=false) {
		$this->log="";
		if($tpl!=false) $tpl=trim($tpl," ./");
		if($tpl==false||!file_exists(BASERELDIR.DIR_TEMPLATE.$tpl)) {
			$this->tpl="";
			return false;
			}
		else {
			$this->tpl=$tpl;
			return true;
			}
		}

	public function delete($tpl=false) {
		require_once(ADMINRELDIR.'inc/kalamun.lib.php');
		if($tpl==false) $tpl=$this->tpl;
		else $tpl=trim($tpl," ./");
		if($tpl!=""&&file_exists(BASERELDIR.DIR_TEMPLATE.$tpl)&&is_dir(BASERELDIR.DIR_TEMPLATE.$tpl)) {
			if(kRemoveDir(BASERELDIR.DIR_TEMPLATE.$tpl)) return true;
			else {
				$this->log="Impossibile cancellare il template... non hai i permessi?";
				return false;
				}
			}
		else {
			$this->log="Il template non esiste";
			return false;
			}
		}
	
	public function copy($source,$dest) {
		require_once(ADMINRELDIR.'inc/kalamun.lib.php');
		$source=trim($source," ./");
		$dest=trim($dest," ./");
		if($dest==""||file_exists(BASERELDIR.DIR_TEMPLATE.$dest)) {
			$this->log="Il template di destinazione esiste gi&agrave;: copia annullata";
			return false;
			}
		if($source!=""&&file_exists(BASERELDIR.DIR_TEMPLATE.$source)&&is_dir(BASERELDIR.DIR_TEMPLATE.$source)) {
			if(kDirCopy(BASERELDIR.DIR_TEMPLATE.$source,BASERELDIR.DIR_TEMPLATE.$dest)) return true;
			else {
				$this->log="Impossibile copiare il template... non hai i permessi?";
				return false;
				}
			}
		else {
			$this->log="Il template sorgente non esiste";
			return false;
			}
		}
	
	public function getError() {
		return $this->log;
		}
	}

class kaTemplateFile {
	protected $filename,$contents,$log;

	public function __construct($filename)
	{
		$this->log="";
		$filename=trim($filename," ./");
		$this->filename=$filename;
		if($this->read()==false) return false;
		else return true;
	}

	public function getFilename()
	{
		return $this->filename;
	}

	public function read()
	{
		if(file_exists(BASERELDIR.DIR_TEMPLATE.$this->filename))
		{
			$this->contents=file_get_contents(BASERELDIR.DIR_TEMPLATE.$this->filename);
			if($this->contents=="") $this->contents=" ";
			return $this->contents;
		}
		return false;
	}
	
	public function write($contents)
	{
		$this->log="";
		if(is_writable(BASERELDIR.DIR_TEMPLATE.$this->filename))
		{
			if (!$handle=fopen(BASERELDIR.DIR_TEMPLATE.$this->filename.'.tmp.php','w'))
			{
				$this->log="Impossibile aprire ".$this->filename.'.tmp.php';
				exit;
			}

			if(fwrite($handle,$contents)===FALSE)
			{
				$this->log="Impossibile scrivere sul file ".$this->filename.'.tmp.php';
				exit;
			}
			fclose($handle);
			unlink(BASERELDIR.DIR_TEMPLATE.$this->filename);
			rename(BASERELDIR.DIR_TEMPLATE.$this->filename.'.tmp.php',BASERELDIR.DIR_TEMPLATE.$this->filename);

		} else {
			$this->log="Non hai i permessi per scrivere su ".$this->filename;
		}
		
		if($this->__construct($this->filename)==false)
		{
			$this->log="Errore nel salvataggio del file";
		}

		if($this->log=="") return true;
		return false;
	}

	public function delete($filename=false)
	{
		if($filename==false) $filename=$this->filename;
		else $filename=trim($filename," ./");
		if(file_exists(BASERELDIR.DIR_TEMPLATE.$filename))
		{
			if(unlink(BASERELDIR.DIR_TEMPLATE.$filename)) return true;
			else {
				$this->log="Impossibile cancellare il file... non hai i permessi?";
				return false;
			}
		} else {
			$this->log="Il file non esiste";
			return false;
		}
	}
	
	public function getError()
	{
		return $this->log;
	}
}
