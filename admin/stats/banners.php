<?php
define("PAGE_NAME","Statistics:Banners Statistics");
include_once("../inc/head.inc.php");

include_once("stats.lib.php");
$kaStats=new kaStats();

include_once('../banner/banner.lib.php');
include_once('../inc/categorie.lib.php');
$kaBanner=new kaBanner();
$kaCategorie=new kaCategorie();

// current month
$currentYear = isset($_GET['y']) ? intval($_GET['y']) : date("Y");
$currentMonth = isset($_GET['m']) ? intval($_GET['m']) : date("n");
$currentTimestamp = mktime(1,0,0, $currentMonth, 15, $currentYear);
$numberOfDays = date("t", $currentTimestamp);

$prevMonth = date("m", $currentTimestamp-2592000);
$prevYear = date("Y", $currentTimestamp-2592000);
$nextMonth = date("m", $currentTimestamp+2592000);
$nextYear = date("Y", $currentTimestamp+2592000);
?>
<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />

	<div class="tab">
		<dl>
		<?php
		$currentCategory = array();
		
 		foreach($kaCategorie->getList(TABLE_BANNER) as $c)
		{
			if(!isset($_GET['idcat'])) $_GET['idcat'] = $c['idcat'];
			if($_GET['idcat'] == $c['idcat']) $currentCategory = $c;
			?>
			<dt>
				<a href="?idcat=<?= $c['idcat']; ?>" class="<?= ($c['idcat']==$_GET['idcat']?'sel':''); ?>"><?= $c['categoria']; ?></a>
			</dt>
			<?php
		}
		
		$orderby = $kaMetadata->get(TABLE_CATEGORIE, $currentCategory['idcat'], 'orderby');
		$currentCategory['orderby'] = $orderby['value'];
		?>
		</dl>
	</div>
	<br />

	<div style="width:50%;float:left;">
		<h1><?= strftime("%B", $currentTimestamp).' '.$currentYear; ?></h1>
		<a href="?idcat=<?= $_GET['idcat']; ?>&m=<?= $prevMonth; ?>&y=<?= $prevYear; ?>" class="smallbutton">&lt; <?= $kaTranslate->translate('Statistics:Previous month'); ?></a>
		<a href="?idcat=<?= $_GET['idcat']; ?>&m=<?= $nextMonth; ?>&y=<?= $nextYear; ?>" class="smallbutton"><?= $kaTranslate->translate('Statistics:Next month'); ?> &gt;</a>
		<br><br>
		<table class="tabella">
		<thead>
			<tr>
				<th><?= $kaTranslate->translate('Statistics:Title'); ?></th>
				<th><?= $kaTranslate->translate('Statistics:Target URL'); ?></th>
				<th><?= $kaTranslate->translate('Statistics:Views'); ?></th>
				<th><?= $kaTranslate->translate('Statistics:Clicks'); ?></th>
				<th>&nbsp;</th>
			</tr>
		</thead>
		<tbody>
		<?php 
		foreach($kaBanner->getList($_GET['idcat']) as $banner)
		{
			// count how many views
			$views = $kaStats->getSummaryCount("banner", "view", $banner['idbanner'].': '.$currentYear.'-'.sprintf("%02d",$currentMonth).'%');

			// count how many clicks
			$clicks = $kaStats->getSummaryCount("banner", "click", $banner['idbanner'].': '.$currentYear.'-'.sprintf("%02d",$currentMonth).'%');
			
			?><tr>
				<td>
					<?php  echo $banner['title']; ?>
					<?php  if($banner['online']=='n') echo '<small class="alert">'.$kaTranslate->translate('Statistics:DRAFT').'</small>'; ?><br />
				</td>
				<td class="percorso"><?= $banner['url']; ?></td>
				<td class="views"><?= intval($views); ?></td>
				<td class="clicks"><?= intval($clicks); ?></td>
				<td class="actions"><a href="javascript:kGetDetails('<?= $banner['idbanner']; ?>','<?= $currentMonth; ?>','<?= $currentYear; ?>')"><?= $kaTranslate->translate('Statistics:Details...'); ?></a></td>
			</tr>
			<?php 
		}
		?>
		</tbody>
		</table>
	</div>

	<div style="width:50%;float:left;">
		<div id="statsViewer"></div>
	</div>

	<div class="clearBoth"></div>
	
	<script type="text/javascript">
	function kGetDetails(idbanner, month, year)
	{
		var aj = new kAjax();
		aj.onSuccess(kPrintDetails);
		aj.onFail(kPrintDetails);
		aj.send('get', 'ajax/bannersHandler.php', '&action=monthDetails&idbanner='+idbanner+'&m='+month+'&y='+year);
	}
	
	function kPrintDetails(html)
	{
		var json = JSON.parse(html);
		var statsViewer = document.getElementById('statsViewer');
		statsViewer.innerHTML = '';

		var max = 1;
		for(var i in json.views)
		{
			max = Math.max(json.views[i], max);
		}

		for(var i in json.views)
		{
			var column = document.createElement('DIV');

			var views = document.createElement('DIV');
			views.className = 'views';
			views.appendChild(document.createTextNode(json.views[i]));
			views.style.height = (100/max*json.views[i])+'%';
			column.appendChild(views);
			
			statsViewer.appendChild(column);
		}
	}
	
	</script>
<?php
include_once("../inc/foot.inc.php");
