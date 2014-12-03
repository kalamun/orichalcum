<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Negozio Paesi e zone");
include_once("../inc/head.inc.php");

$countriesUsed=",";
$query="SELECT * FROM ".TABLE_SHOP_COUNTRIES;
$results=ksql_query($query);
while($row=ksql_fetch_array($results)) {
	$countriesUsed.=$row['ll'].",";
	}

$countries=array();
$countriesUnused=array();
foreach(file('../shop/countries.txt') as $line) {
	$line=trim($line);
	if(substr($line,0,1)!="#") {
		$line=explode("\t",$line);
		$countries[$line[1]]=$line[0];
		if(strpos($countriesUsed,','.$line[1].',')===false) $countriesUnused[$line[1]]=$line[0];
		}
	}


/* AZIONI */
if(isset($_POST['update'])) {
	ksql_query("TRUNCATE TABLE `k_shop_countries`");
	for($i=1;isset($_POST['zone'.$i]);$i++) {
		foreach(explode(",",trim($_POST['zone'.$i],",")) as $code) {
			if($code!="") ksql_query("INSERT INTO ".TABLE_SHOP_COUNTRIES." (country,ll,zone) VALUES('".$countries[$code]."','".$code."','".$i."')");
			}
		}
	?><div id="MsgSuccess">Zone salvate con successo</div><?php 
	$kaLog->add('UPD','Shop: Salvate le zone');
	}
/* FINE AZIONI */

?>
<h1><?= $kaTranslate->translate('Negozio'); ?></h1>
<?php  include('shopmenu.php'); ?>
<br />

<div class="subset">
	<h3>Paesi non attivati</h3>
	<div class="inactive">
		<ul class="DragZone"><?php 
		foreach($countriesUnused as $code=>$country) {
			?><li countryCode="<?= $code; ?>"><?= $country; ?></li><?php 
			}
			?></ul>
		</div>
	</div>

<div class="topset">
	<?php 	for($i=1;$i<=7;$i++) { ?>
		<div>
			<h3>ZONA <?= $i; ?></h3>
			<div class="zoneContainer" id="zoneContainer<?= $i; ?>">
				<ul class="DropZone"><?php 
				$query="SELECT * FROM ".TABLE_SHOP_COUNTRIES." WHERE zone='".$i."' ORDER BY ll,country";
				$results=ksql_query($query);
				while($row=ksql_fetch_array($results)) {
					?><li countryCode="<?= $row['ll']; ?>"><img src="<?= BASEDIR; ?>img/lang/<?= strtolower($row['ll']); ?>.gif" /> <?= $row['country']; ?></li><?php 
					}
				?><li style="clear:left;"></li></ul>
				<div style="clear:left;"></div>
				</div>
			</div><br />
		<?php  } ?>

	<form action="" method="post" onsubmit="populateInputs();">
	<?php 	for($i=1;$i<=7;$i++) { ?>
		<input type="hidden" value="" name="zone<?= $i; ?>" id="zoneInput<?= $i; ?>">
		<?php  } ?>
	<div class="submit"><input type="submit" name="update" value="<?= $kaTranslate->translate('UI:Save'); ?>" class="button" /></div>
	</form>
	</div>

<script type="text/javascript" src="<?php  echo ADMINDIR; ?>/js/drag_and_drop.js"></script>
<script type="text/javascript">
		kDragAndDrop=new kDrago();
		kDragAndDrop.dragClass("DragZone");
		kDragAndDrop.dropClass("DropZone");
		kDragAndDrop.containerTag('LI');
		kDragAndDrop.onDrag(function (drag,target) {
			});
		kDragAndDrop.onDrop(function (drag,target) {
			while(target) {
				if(target.className=="DragZone"||target.className=="DropZone") {
					target.insertBefore(drag,target.childNodes[target.childNodes.length-1]);
					kDragAndDrop.savePosition();
					}
				target=target.parentNode;
				}
			//document.getElementById('saveOrder').style.display='block';
			});
		
		function populateInputs() {
			for(var i=1;i<=7;i++) {
				var codes=",";
				var c=document.getElementById("zoneContainer"+i);
				for(var j=0;c.getElementsByTagName('LI')[j];j++) {
					var li=c.getElementsByTagName('LI')[j];
					var code=li.getAttribute("countryCode");
					if(code) codes+=code+',';
					}
				document.getElementById("zoneInput"+i).value=codes;
				}
			return true;
			}
	</script>


<?php 
include_once("../inc/foot.inc.php");
