<?
/* (c)2010 Kalamun.org GPLv3 */

class kaMetadata {
	
	public function kaMetadata() {
		}

	public function set($tabella,$id,$param,$value) {
		if(get_magic_quotes_gpc()) {
			$param=stripslashes($param);
			$value=stripslashes($value);
			}
		$tabella=mysql_real_escape_string($tabella);
		$id=mysql_real_escape_string($id);
		$param=mysql_real_escape_string($param);
		$value=mysql_real_escape_string($value);

		// if value is empty, delete record
		if($value=="") {
			$query="DELETE FROM `".TABLE_METADATA."` WHERE `tabella`='".$tabella."' AND `id`='".$id."' AND `param`='".$param."' LIMIT 1";
			if(mysql_query($query)) return true;
			else return false;
			}

		// else if record exists, update else create
		$query="SELECT * FROM `".TABLE_METADATA."` WHERE `tabella`='".$tabella."' AND `id`='".$id."' AND `param`='".$param."' LIMIT 1";
		$results=mysql_query($query);
		if($row=mysql_fetch_array($results)) {
			$query="UPDATE `".TABLE_METADATA."` SET `value`='".$value."' WHERE `tabella`='".$tabella."' AND `id`='".$id."' AND `param`='".$param."' LIMIT 1";
			return mysql_query($query);
			}
		else {
			$query="INSERT INTO `".TABLE_METADATA."` (`tabella`,`id`,`param`,`value`) VALUES('".$tabella."','".$id."','".$param."','".$value."')";
			return mysql_query($query);
			}
		}

	public function get($tabella,$id,$param) {
		if(get_magic_quotes_gpc()) {
			$param=stripslashes($param);
			}
		$tabella=mysql_real_escape_string($tabella);
		$id=mysql_real_escape_string($id);
		$param=mysql_real_escape_string($param);
		$query="SELECT * FROM `".TABLE_METADATA."` WHERE `tabella`='".$tabella."' AND `id`='".$id."' AND `param`='".$param."' LIMIT 1";
		$results=mysql_query($query);
		$row=mysql_fetch_array($results);
		return $row;
		}

	public function getList($vars,$id=false) {
		if(!is_array($vars)) {
			$vars=array("table"=>$vars,"id"=>$id);
			}
		if(!isset($vars['order'])) $vars['order']="`param`,`id`";
		foreach($vars as $k=>$v) {
			if($k!='conditions') $vars[$k]=mysql_real_escape_string($v);
			}
		$v=array();
		$query="SELECT * FROM `".TABLE_METADATA."` WHERE ";
		if(isset($vars['table'])) $query.=" `tabella`='".$vars['table']."' AND ";
		if(isset($vars['id'])) $query.=" `id`='".$vars['id']."' AND ";
		if(isset($vars['param'])) $query.=" `param`='".$vars['param']."' AND ";
		if(isset($vars['value'])) {
			if(!isset($vars['value_operator'])) $vars['value_operator']="=";
			$query.=" `value`".$vars['value_operator']."'".$vars['value']."' AND ";
			}
		if(isset($vars['conditions'])) $query.=" (".$vars['conditions'].") AND ";
		$query.=" `tabella`<>'' ORDER BY ".$vars['order'];
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			if(isset($vars['return_records'])&&$vars['return_records']==true) $v[]=$row;
			else $v[$row['param']]=$row['value'];
			}
		return $v;
		}

	public function getParams($tabella,$id=false) {
		$tabella=mysql_real_escape_string($tabella);
		$id=mysql_real_escape_string($id);
		$v=array();
		if($id!=false) {
			$query="SELECT * FROM `".TABLE_METADATA."` WHERE `tabella`='".$tabella."' AND `id`='".$id."' ORDER BY `param`";
			$results=mysql_query($query);
			while($row=mysql_fetch_array($results)) {
				$v[$row['param']]=$row['value'];
				}
			}
		$output=array();
		$query="SELECT param FROM `".TABLE_METADATA."` WHERE `tabella`='".$tabella."' GROUP BY `param` ORDER BY `param`";
		$results=mysql_query($query);
		while($row=mysql_fetch_array($results)) {
			isset($v[$row['param']])?$row['value']=$v[$row['param']]:$row['value']="";
			$output[]=$row;
			}
		return $output;
		}

	}

?>