<?php
/* generates a valid javascript object containing all the Javascript terms defined in /admin/inc/locale */

header('Content-type: application/javascript');
require_once('../inc/main.lib.php');
$orichalcum = new kaOrichalcum();
$orichalcum->init( array("check-permissions"=>false) );

if(!isset($_SESSION['ui']['lang'])||$_SESSION['ui']['lang']=="")
{
	$query="SELECT * FROM ".TABLE_LINGUE." WHERE ll='".DEFAULT_LANG."' LIMIT 1";
	$results=mysql_query($query);
	$row=mysql_fetch_array($results);
	$_SESSION['ui']['lang']=$row['code'];
}
$file=ADMINRELDIR.'inc/locale/'.$_SESSION['ui']['lang'].'.txt';

if(file_exists($file))
{
	?>
	var kaDictionary={};
	<?php
	$diz=file($file);
	if($diz)
	{
		foreach($diz as $line)
		{
			if(trim($line)!=""&&substr($line,0,2)!="//")
			{
				$line=trim($line);
				$line=preg_replace("/(\t+)/","\t",$line);
				$elm=explode("\t",$line);
				if(substr(trim($elm[0]),0,11)=='Javascript:')
				{ ?>
					kaDictionary.<?= substr(trim($elm[0]),11); ?>='<?= addslashes(trim($elm[1])); ?>';
				<?php }
			}
		}
	}
}
?>