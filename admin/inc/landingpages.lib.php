<?
/* (c) Kalamun.org - GPL v3 */

class kaLandingPages {
	protected $kaLog;
	
	public function kaLandingPages() {
		require_once('log.lib.php');
		$this->kaLog=new kaLog();
		}

	public function add($dir,$titolo,$template="",$chiave="",$testo="",$form="",$form2="",$conversion="",$online="n",$traduzioni="",$ll="") {
		if($ll=="") $ll=$_SESSION['ll'];
		$log="";

		$query="INSERT INTO ".TABLE_LANDINGPAGE." (dir,titolo,template,chiave,testo,form,form2,conversion,online,traduzioni,ll) VALUES('".b3_htmlize($dir,true,"")."','".b3_htmlize($titolo,true,"")."','".$template."','".$chiave."','".b3_htmlize($testo,true)."','".$form."','".$form2."','".$conversion."','".$online."','".$traduzioni."','".$ll."')";
		if(mysql_query($query)) {
			$id=mysql_insert_id();
			$this->kaLog->add("INS",'Creata la landing page <a href="'.BASEDIR.strtolower($_SESSION['ll']).'/'.$_POST['dir'].'">'.$_POST['dir'].'</a>');
			return $id;
			}
		else {
			$this->kaLog->add("ERR",'Errore nella creazione della landing page <em>'.b3_htmlize($_POST['dir'],true,"").'</em>');
			return false;
			}
		}

	public function copy($fromId,$toId) {
		$log=toId;

		$query="SELECT * FROM ".TABLE_LANDINGPAGE." WHERE idlp=".$fromId;
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$query2="UPDATE ".TABLE_LANDINGPAGE." SET testo='".addslashes($row['testo'])."',form='".addslashes($row['form'])."',form2='".addslashes($row['form2'])."',conversion='".addslashes($row['conversion'])."',template='".addslashes($row['template'])."' WHERE idlp=".$toId;
		if(!mysql_query($query2)) $log=false;
		else {
			$query="SELECT * FROM ".TABLE_LANDINGPAGE_T." WHERE idlp=".$fromId;
			$results=mysql_query($query);
			while($row=mysql_fetch_array($results)) {
				$query2="INSERT INTO ".TABLE_LANDINGPAGE_T." (idlp,titolo,sottotitolo,testo,ordine) VALUES('".$toId."','".addslashes($row['titolo'])."','".addslashes($row['sottotitolo'])."','".addslashes($row['testo'])."','".$row['ordine']."')";
				if(mysql_query($query2)) $id2=mysql_insert_id();
				else $log=false;
				}
			}

		return $log;
		}
		
	public function getList($from=0,$to=0,$ordine="data DESC",$lang=false) {
		$output=array();
		if($to==0) $to=9999;
		if($lang==false) $lang=$this->ll;
		$query="SELECT * FROM ".TABLE_NEWS." WHERE ll='".$lang."' ORDER BY ".$ordine." LIMIT ".$from.",".$to;
		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$output[$i]=$row;
			$output[$i]['commentiOnline']=$this->kaCommenti->count(TABLE_NEWS,$row['idnews'],"public='s'");
			$output[$i]['commentiTot']=$this->kaCommenti->count(TABLE_NEWS,$row['idnews']);
			}
		return $output;
		}

	public function get($idnews) {
		$output=array();
		$query="SELECT * FROM ".TABLE_NEWS." WHERE idnews='".$idnews."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		$output=$row;
		$output['commentiOnline']=$this->kaCommenti->count(TABLE_NEWS,$row['idnews'],"public='s'");
		$output['commentiTot']=$this->kaCommenti->count(TABLE_NEWS,$row['idnews']);
		return $output;
		}
		
	public function count($from=0,$to=0,$lang=false) {
		if($to==0) $to=9999;
		if($lang==false) $lang=$this->ll;
		$query="SELECT count(*) AS tot FROM ".TABLE_NEWS." WHERE ll='".$lang."' LIMIT ".$from.",".$to;
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $row['tot'];
		}

	}
?>