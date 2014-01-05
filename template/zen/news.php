<? kPrintHeader(); ?>

	<?
	if(kHaveNews()) {
		kPrintNews();	
		}

	else { ?>
		<div id="titleBox">
			<div class="title"><div class="bottom">
				<h1><?= kGetTitle(); ?></h1>
				</div></div>
			<div class="curve"></div>
			</div>

		<div id="contentsBox">
			<?
			if(!isset($_GET['y'])) $_GET['y']=strftime("%Y");
			$nws=kGetNewsQuickList(false,false,1,false,false,false,kGetVar('news-order',1));
			kSetNewsByDir($nws[0]['dir']);
			$oldestyear=kGetNewsDate("%Y");
			$nws=kGetNewsQuickList(false,false,500,false,kGetVar('news-order',1)." LIKE '".intval($_GET['y'])."%'");
			$tmpyyyy="";
			$tmpmm="";
			$tmpdd="";
			?>
			<div class="tabs">
				<ul><?
				for($i=strftime("%Y");$i>=$oldestyear;$i--) { ?>
					<li><a href="?y=<?= $i; ?>"<?= ($i==$_GET['y']?' class="sel"':''); ?>><?= $i; ?></a></li>
					<? }
				?></ul>
				</div>

			<table><?
			foreach($nws as $n) {
				$date=$n['data'];
				$yyyy=substr($date,0,4);
				$mm=substr($date,5,2);
				$dd=substr($date,8,2);
				if($tmpmm!=$mm) {
					$tmpmm=$mm;
					$bb=strftime("%B",mktime(substr($date,11,2),substr($date,14,2),substr($date,17,2),$mm,$dd,$yyyy));
					?><tr><td style="text-align:center;"><h3><?= $bb; ?></h3></td><td></td></tr><?
					}
				?>
				<tr><td style="text-align:center;"><strong><?= $dd; ?></strong></td><td><a href="<?= $n['permalink']; ?>"><?= $n['titolo']; ?></a></td></tr>
				<? }
			?></table>
		<? } ?>

<? kPrintFooter(); ?>
