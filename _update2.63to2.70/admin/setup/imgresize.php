<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Impostazioni per le immagini");
include_once("../inc/head.inc.php");

/* AZIONI */
if(isset($_POST['update'])||isset($_POST['test'])) {
	$log="";

	//img_size
	$params=array("img_size","img_quality","img_resize","thumb_size","thumb_quality","thumb_resize");
	foreach($params as $param) {
		$value1=b3_htmlize($_POST[$param.'1'],true,"");
		$value2=b3_htmlize($_POST[$param.'2'],true,"");
		$kaImpostazioni->setParam($param,$value1,$value2,"*");
		}

	if($log=="") echo '<div id="MsgSuccess">Configurazione salvata con successo</div>';
	else echo '<div id="MsgAlert">'.$log.'</div>';
	}
/* FINE AZIONI */

?>
<h1><?php  echo PAGE_NAME; ?></h1>
<br />
<?php 
echo '<form action="?" method="post" enctype="multipart/form-data">';
?>

<div class="topset"><?php 
	echo "<h3>IMMAGINI</h3>";
	$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='img_resize' AND ll='*' LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
		$option=array("all","bigger","smaller","none");
		$value=array("Ridimensiona tutte le immagini","Ridimensiona solo quelle pi&ugrave; grandi","Ridimensiona solo quelle più piccole","Non ridimensionare");
		echo b3_create_select("img_resize1","",$value,$option,b3_lmthize($row['value1'],"input")).'<br />';
		$option=array("inside","outside","fit");
		$value=array("Internamente","Esternamente","Adatta");
		echo b3_create_select("img_resize2","",$value,$option,b3_lmthize($row['value2'],"input")).'<br />';

	$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='img_size' AND ll='*' LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
		echo b3_create_input("img_size1","text","Larghezza ",b3_lmthize($row['value1'],"input"),"50px",5).' px<br />';
		echo b3_create_input("img_size2","text","Altezza ",b3_lmthize($row['value2'],"input"),"50px",5).' px<br />';

	$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='img_quality' AND ll='*' LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
		echo b3_create_input("img_quality1","text","Qualit&agrave; ",b3_lmthize($row['value1'],"input"),"50px",3).'/100<br />';
		echo b3_create_input("img_quality2","hidden","",b3_lmthize($row['value2'],"input"),"50px",5);

	echo '<br /><br />';

	echo "<h3>ANTEPRIME</h3>";
	$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='thumb_resize' AND ll='*' LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
		echo b3_create_input("thumb_resize1","hidden","",b3_lmthize($row['value1'],"input"),"50px",5);
		$option=array("inside","outside","fit");
		$value=array("Internamente","Esternamente","Adatta");
		echo b3_create_select("thumb_resize2","",$value,$option,b3_lmthize($row['value2'],"input")).'<br />';

	$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='thumb_size' AND ll='*' LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
		echo b3_create_input("thumb_size1","text","Larghezza ",b3_lmthize($row['value1'],"input"),"50px",5).' px<br />';
		echo b3_create_input("thumb_size2","text","Altezza ",b3_lmthize($row['value2'],"input"),"50px",5).' px<br />';

	$query="SELECT * FROM ".TABLE_CONFIG." WHERE param='thumb_quality' AND ll='*' LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
		echo b3_create_input("thumb_quality1","text","Qualit&agrave; ",b3_lmthize($row['value1'],"input"),"50px",3).'/100<br />';
		echo b3_create_input("thumb_quality2","hidden","",b3_lmthize($row['value2'],"input"),"50px",5);

	echo '<br /><br />';
?></div><?php 

echo '<div class="submit"><input type="submit" name="update" value="Salva" class="button"></div>';
echo '</form></div><br /><br />';


include_once("../inc/foot.inc.php");
