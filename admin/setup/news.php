<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Setup:News settings");
include_once("../inc/head.inc.php");

$kaTranslate->import('news');

/* AZIONI */
if(isset($_POST['update'])) {
	if(!isset($_POST['news-commenti'][1])) $_POST['news-commenti'][1]='n';
	if(!isset($_POST['news-commenti'][2])) $_POST['news-commenti'][2]='n';
	$kaImpostazioni->setParam('news-commenti',$_POST['news-commenti'][1],$_POST['news-commenti'][2]);

	$layout=",";
	if(isset($_POST['layout'])) {
		foreach($_POST['layout'] as $ka=>$v) {
			$layout.=$ka.",";
			}
		}
	$kaImpostazioni->setParam('admin-news-layout',$layout,$_POST['news-mode'],"*");

	$_POST['news'][2]=",";
	if(isset($_POST['cat'])) {
		if(isset($_POST['cat']['all'])) $_POST['news'][2].="*,";
		else {
			foreach($_POST['cat'] as $cat) {
				$_POST['news'][2].=$cat.',';
				}
			}
		}

	$kaImpostazioni->setParam('news',$_POST['news'][1],$_POST['news'][2]);
	$kaImpostazioni->setParam('news-template',$_POST['news-template'][1],$_POST['news-template'][2]);
	$kaImpostazioni->setParam('news-order',$_POST['news-order'][1],$_POST['news-order'][2]);
	$kaImpostazioni->setParam('facebook',(isset($_POST['facebook'][1])?'s':'n'),"");
	$kaImpostazioni->setParam('facebook-config',$_POST['facebook-config'][1],$_POST['facebook-config'][2]);
	$kaImpostazioni->setParam('facebook-location',$_POST['facebook-location'][1],"");
	$kaImpostazioni->setParam('facebook-address',$_POST['facebook-address'][1],$_POST['facebook-address'][2]);
	$kaImpostazioni->setParam('facebook-country',$_POST['facebook-country'][1],$_POST['facebook-country'][2]);
	$kaImpostazioni->setParam('facebook-contacts',$_POST['facebook-contacts'][1],$_POST['facebook-contacts'][2]);
	$kaImpostazioni->setParam('facebook-page',$_POST['facebook-page'][1],"");
	}
/* FINE AZIONI */

$news=$kaImpostazioni->getParam('news');
$template=$kaImpostazioni->getParam('news-template');
$order=$kaImpostazioni->getParam('news-order');
$comments=$kaImpostazioni->getParam('news-commenti');
$layout=$kaImpostazioni->getParam('admin-news-layout',"*");
$facebook=$kaImpostazioni->getParam('facebook');
if(!isset($facebook['value1'])) $facebook['value1']="";
$facebook['config']=$kaImpostazioni->getParam('facebook-config');
$facebook['location']=$kaImpostazioni->getParam('facebook-location');
$facebook['address']=$kaImpostazioni->getParam('facebook-address');
$facebook['country']=$kaImpostazioni->getParam('facebook-country');
$facebook['contacts']=$kaImpostazioni->getParam('facebook-contacts');
$facebook['page']=$kaImpostazioni->getParam('facebook-page');

?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

<script type="text/javascript">
	function ableAllFields(f) {
		inputs=f.getElementsByTagName('INPUT');
		for(var i=0;inputs[i];i++) {
			inputs[i].disabled=false;
			}
		return true;
		}
	</script>

<form action="?" method="post" onsubmit="return ableAllFields(this)">

	<table>
	<tr>
		<td><label for="news-template1">Template di default per le news</label></td>
		<td><?php 
			$option=array("");
			$value=array("-default-");
			foreach($kaImpostazioni->getTemplateList() as $file) {
				$option[]=$file;
				$value[]=str_replace("_"," ",$file);
				}
			echo b3_create_select("news-template[1]","",$value,$option,$template['value1']);
			?></td>
		</tr>
	<tr>
		<td><label for="news-template2">Layout di default per le news</label></td>
		<td><?php 
			if(!isset($template['value1'])||$template['value1']=="") $template['value1']=$kaImpostazioni->getVar('template_default',1);
			if(!isset($template['value2'])||$template['value2']=="") $template['value2']=$kaImpostazioni->getVar('template_default',1);
			$option=array("");
			$value=array("-default-");
			//scandaglio la directory per i template
			$dir=BASERELDIR.DIR_TEMPLATE.$template['value1'].'/layouts';
			if(file_exists($dir)&&is_dir($dir)&&$handle=opendir($dir)) {
				while(false!==($file=readdir($handle))) {
					if(trim($file,".")!="") {
						$option[]=$file;
						$value[]=str_replace("_"," ",$file);
						}
					}
				closedir($handle);
				}
			echo b3_create_select("news-template[2]","",$value,$option,$template['value2']);
			?></td>
		</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	
	<tr>
		<td><label for="news-mode">Nel pannello di controllo, visualizza come</label></td>
		<td><?php 
			$option=array("calendario","elenco");
			$value=array("Calendario","Elenco");
			echo b3_create_select("news-mode","",$value,$option,$layout['value2']);
			?></td>
		</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	
	<tr>
		<td><label for="news-order1">Ordina le news per</label></td>
		<td><?php 
			$option=array("data DESC","pubblica DESC","starting_date DESC","scadenza DESC");
			$value=array("Data di inserimento","Data di pubblicazione","Data di inizio","Data di scadenza");
			echo b3_create_select("news-order[1]","",$value,$option,$order['value1']);
			?></td>
		</tr>
	<tr>
		<td><label for="news-order2">Quelle scadute</label></td>
		<td><?php 
			$option=array("","archivia","nascondi");
			$value=array("trattale come tutte le altre","mettile in archivio","nascondile completamente");
			echo b3_create_select("news-order[2]","",$value,$option,$order['value2']);
			?></td>
		</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	
	<tr>
		<td><label for="news1">Numero di notizie per pagina</label></td>
		<td><?= b3_create_input("news[1]","text","",b3_lmthize($news['value1'],"input"),"50px",3); ?></td>
		</tr>
	</table>
<br /><br />



<h3>Commenti</h3>
	<?= b3_create_input("news-commenti[1]","checkbox","Abilita i commenti","s","","",($comments['value1']=="s"?'checked':''),true); ?><br />
	<?= b3_create_input("news-commenti[2]","checkbox","Modera i commenti","s","","",($comments['value2']=="s"?'checked':''),true); ?><br />
	<br /><br />

	
<h3>Categorie da visualizzare per default</h3>
	<?php 
	echo b3_create_input("cat[all]","checkbox","Tutte (anche quelle future)","*","","",(',*,'==$news['value2']?'checked':''),true).'<br />';
	$query_c="SELECT * FROM ".TABLE_CATEGORIE." WHERE tabella='".TABLE_NEWS."' AND ll='".$_SESSION['ll']."' ORDER BY ordine";
	$results_c=ksql_query($query_c);
	while($row_c=ksql_fetch_array($results_c)) {
		echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.b3_create_input("cat[]","checkbox",$row_c['categoria'],$row_c['idcat'],"","",(strpos($news['value2'],','.$row_c['idcat'].',')!==false?'checked':''),true).'<br />';
		}
	?>
	<br /><br />

	
<h3>Facebook</h3>
	<?php 
	echo b3_create_input("facebook[1]","checkbox","Abilita integrazione con facebook","s","","",($facebook['value1']=="s"?'checked':''),true);
	?>
	<table>
	<tr><td><label for="facebook-config1">API key</label></td><td><?= b3_create_input("facebook-config[1]","text","",b3_lmthize($facebook['config']['value1'],"input"),"120px",255); ?></td></tr>
	<tr><td><label for="facebook-config2">Security key</label></td><td><?= b3_create_input("facebook-config[2]","text","",b3_lmthize($facebook['config']['value2'],"input"),"220px",255); ?></td></tr>
	<tr><td><label for="facebook-location1">Location</label></td><td><?= b3_create_input("facebook-location[1]","text","",b3_lmthize($facebook['location']['value1'],"input"),"120px",255); ?></td></tr>
	<tr><td><label for="facebook-address1">Street</label></td><td><?= b3_create_input("facebook-address[1]","text","",b3_lmthize($facebook['address']['value1'],"input"),"200px",255); ?></td></tr>
	<tr><td><label for="facebook-address2">City</label></td><td><?= b3_create_input("facebook-address[2]","text","",b3_lmthize($facebook['address']['value2'],"input"),"150px",255); ?></td></tr>
	<tr><td><label for="facebook-country1">State</label></td><td><?= b3_create_input("facebook-country[1]","text","",b3_lmthize($facebook['country']['value1'],"input"),"50px",255); ?></td></tr>
	<tr><td><label for="facebook-country2">Country</label></td><td><?= b3_create_input("facebook-country[2]","text","",b3_lmthize($facebook['country']['value2'],"input"),"150px",255); ?></td></tr>
	<tr><td><label for="facebook-contacts1">Phone</label></td><td><?= b3_create_input("facebook-contacts[1]","text","",b3_lmthize($facebook['contacts']['value1'],"input"),"120px",255); ?></td></tr>
	<tr><td><label for="facebook-contacts2">E-mail</label></td><td><?= b3_create_input("facebook-contacts[2]","text","",b3_lmthize($facebook['contacts']['value2'],"input"),"200px",255); ?></td></tr>
	<tr><td><label for="facebook-page1">Pubblica nella pagina</label></td><td><?= b3_create_input("facebook-page[1]","text","",b3_lmthize($facebook['page']['value1'],"input"),"200px",255); ?> <small>Page ID</small></td></tr>
	</table>
	<br /><br />
	

<h3><?= $kaTranslate->translate('Setup:Fields to display in administration panel'); ?></h3>
	<?php 
	$elm=array("title"=>"News:Title","subtitle"=>"News:Subtitle","featuredimage"=>"News:Featured Image","preview"=>"News:Preview","text"=>"News:Text","categories"=>"News:Categories","home"=>"News:Show in home page","calendario"=>"News:Show in calendar","date"=>"News:Created","public"=>"News:Visible from","startingdate"=>"News:Starting date","expiration"=>"News:Expiration date","photogallery"=>"News:Photo gallery","documentgallery"=>"News:Document gallery","template"=>"News:Template","layout"=>"News:Layout","translate"=>"News:Translations","metadata"=>"News:Metadata","seo"=>"News:SEO");
	$elmobl=array("title"=>true,"text"=>true);
	foreach($elm as $ka=>$v) {
		echo b3_create_input("layout[".$ka."]","checkbox",$kaTranslate->translate($v),"s","","",(strpos($layout['value1'],",".$ka.",")!==false||isset($elmobl[$ka])?'checked':'').' '.(isset($elmobl[$ka])?'disabled':''),true).'<br />';
		}
	?>

	<br /><br />
	<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button"></div>
</form></div><br /><br />

<?php 
include_once("../inc/foot.inc.php");
