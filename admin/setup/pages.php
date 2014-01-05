<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Setup:Page settings");
include_once("../inc/head.inc.php");

$kaTranslate->import('pagine');

/* AZIONI */
if(isset($_POST['update'])) {
	if(!isset($_POST['pages-commenti'][1])) $_POST['pages-commenti'][1]='n';
	if(!isset($_POST['pages-commenti'][2])) $_POST['pages-commenti'][2]='n';
	$kaImpostazioni->setParam('pages-commenti',$_POST['pages-commenti'][1],$_POST['pages-commenti'][2]);

	$layout=",";
	if(isset($_POST['layout'])) {
		foreach($_POST['layout'] as $ka=>$v) {
			$layout.=$ka.",";
			}
		}
	$kaImpostazioni->setParam('admin-page-layout',$layout,"","*");

	}
/* FINE AZIONI */

$comments=$kaImpostazioni->getParam('pages-commenti');
$layout=$kaImpostazioni->getParam('admin-page-layout',"*");

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
<h3>Commenti</h3>
	<?= b3_create_input("pages-commenti[1]","checkbox","Abilita i commenti","s","","",($comments['value1']=="s"?'checked':''),true); ?><br />
	<?= b3_create_input("pages-commenti[2]","checkbox","Modera i commenti","s","","",($comments['value2']=="s"?'checked':''),true); ?><br />

	<br /><br />

<h3><?= $kaTranslate->translate('Setup:Fields to display in administration panel'); ?></h3>
	<?
	$elm=array("title"=>"Pages:Title","subtitle"=>"Pages:Subtitle","preview"=>"Pages:Preview","text"=>"Pages:Text","categories"=>"Pages:Categories","photogallery"=>"Pages:Photo gallery","documentgallery"=>"Pages:Document gallery","template"=>"Pages:Template","layout"=>"Pages:Layout","traduzioni"=>"Pages:Translations","metadata"=>"Pages:Metadata","commenti"=>"Pages:Comments","seo"=>"Pages:SEO","conversion"=>"Pages:Conversions");
	$elmobl=array("title"=>true,"text"=>true);
	foreach($elm as $ka=>$v) {
		echo b3_create_input("layout[".$ka."]","checkbox",$kaTranslate->translate($v),"s","","",(strpos($layout['value1'],",".$ka.",")!==false||isset($elmobl[$ka])?'checked':'').' '.(isset($elmobl[$ka])?'disabled':''),true).'<br />';
		}
	?>

	<br /><br />
	<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button"></div>
</form></div><br /><br />

<?
include_once("../inc/foot.inc.php");
?>
