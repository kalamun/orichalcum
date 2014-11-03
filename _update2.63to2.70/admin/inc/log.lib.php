<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

class kaLog {
	private $kaImpostazioni,$expiration,$azioni;

	function kaLog() {
		require_once('connect.inc.php');
		require_once('config.lib.php');
		require_once('main.lib.php');
		
		$this->azioni=array("INS"=>"Nuovo Inserimento","UPD"=>"Aggiornamento","DEL"=>"Eliminazione","CFG"=>"Configurazione","ERR"=>"Errore","GEN"=>"Generico");
		
		$this->kaImpostazioni=new kaImpostazioni;
		$tmp=$this->kaImpostazioni->getParam('log_expiration');
		$this->expiration=$tmp['value1'];
		if($this->expiration!="0"&&$this->expiration!="") $this->clear($this->expiration);
		}
	
	function add($type,$descr) {
		if($type!="INS"&&$type!="UPD"&&$type!="DEL"&&$type!="CFG"&&$type!="ERR"&&$type!="GEN") $type="";
		$query="INSERT INTO ".TABLE_LOG." (iduser,username,data,ll,type,descr) VALUES ("
				."'".$_SESSION['iduser']."',"
				."'".$_SESSION['username']."',"
				."'".date("Y-m-d H:i:s")."',"
				."'".$_SESSION['ll']."',"
				."'".$type."',"
				."'".b3_htmlize($descr,true,"strong,em,u,a")."'"
				.")";
		if(mysql_query($query)) return mysql_insert_id();
		else return false;
		}

	function get($from=0,$to=50,$conditions="") {
		$output=array();
		$query="SELECT * FROM ".TABLE_LOG." WHERE data<NOW() ";
		if($conditions!="") $query.="AND (".$conditions.") ";
		$query.="ORDER BY data DESC LIMIT ".$from.",".$to;
		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$output[$i]=$row;
			$output[$i]['dataleggibile']=preg_replace('/(\d{4})-(\d{2})-(\d{2}) (\d{2}:\d{2}):(\d{2})/','$3-$2-$1',$row['data']);
			$output[$i]['oraleggibile']=preg_replace('/(\d{4})-(\d{2})-(\d{2}) (\d{2}:\d{2}):(\d{2})/','$4.$5',$row['data']);
			$output[$i]['azione']=$this->azioni[$row['type']];
			}
		return $output;
		}

	function clear($exp) {
		if($exp=="all") $exp=0;
		$expDate=time()-($exp*24*60*60);
		$query="DELETE FROM ".TABLE_LOG." WHERE data<'".date("Y-m-d H:i:s",$expDate)."'";
		if(mysql_query($query)) return true;
		else return false;
		}

	}


class kaEmailLog {
	
	function kaEmailLog() {
		}
	
	function get($from=0,$to=50,$conditions="") {
		$output=array();
		$query="SELECT * FROM ".TABLE_EMAIL_LOG." WHERE `date`<NOW() ";
		if($conditions!="") $query.="AND (".$conditions.") ";
		$query.="ORDER BY `date` DESC LIMIT ".$from.",".$to;
		$results=mysql_query($query);
		for($i=0;$row=mysql_fetch_array($results);$i++) {
			$output[$i]=$row;
			$output[$i]['dataleggibile']=preg_replace('/(\d{4})-(\d{2})-(\d{2}) (\d{2}:\d{2}):(\d{2})/','$3-$2-$1',$row['date']);
			$output[$i]['oraleggibile']=preg_replace('/(\d{4})-(\d{2})-(\d{2}) (\d{2}:\d{2}):(\d{2})/','$4.$5',$row['date']);
			}
		return $output;
		}
	
	function getEmailContent($ideml) {
		$output="";
		$query="SELECT html,plain FROM ".TABLE_EMAIL_LOG." WHERE `ideml`='".mysql_real_escape_string($ideml)."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		if($row['html']!="") {
			$output=$row['html'];
			$output=str_replace("=\n","",$output);
			$output=str_replace("=\r\n","",$output);
			$output=str_replace("=3D","=",$output);
			}
		else $output=$row['plain'];
		return $output;
		}

	function count($conditions="") {
		$query="SELECT count(*) AS tot FROM ".TABLE_EMAIL_LOG." WHERE `date`<NOW() ";
		if($conditions!="") $query.="AND (".$conditions.") ";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $row['tot'];
		}
	}

