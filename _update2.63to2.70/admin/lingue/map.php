<?php 
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Map of translations");
include_once("../inc/head.inc.php");

if(isset($log)) {
	if($log=="") echo '<div id="MsgSuccess">Modifiche effettuate con successo</div>';
	else echo '<div id="MsgAlert">'.$log.'</div>';
	}

$languages=$kaAdminMenu->getLanguages();


function printList($pages) {
	global $languages;
	?>
	<table class="tabella">
		<tr><?php 
		foreach($languages as $lang) { ?>
			<th><?= $lang['lingua']; ?></th>
			<?php  } ?></tr>
		<?php 
		foreach($pages as $idpag=>$page) {
			if($page['ll']==$languages[0]['ll']) { ?>
				<tr>
				<td class="dropElement"><div id="<?= $page['ll'].$idpag; ?>" class="dragElement">
					<strong><?= $page['title']; ?></strong><br />
					<small><?= $page['dir']; ?></small>
					</div></td>
				<?php 
				$pagesused[$idpag]=true;
				
				foreach($languages as $i=>$lang) {
					if($i>0) { ?>
						<td class="dropElement"><?php 
						if(isset($page['translations'][$lang['ll']])&&isset($pages[$page['translations'][$lang['ll']]])) { ?>
							<div id="<?= $lang['ll'].$page['translations'][$lang['ll']]; ?>" class="dragElement">
							<strong><?= $pages[$page['translations'][$lang['ll']]]['title']; ?></strong><br />
							<small><?= $pages[$page['translations'][$lang['ll']]]['dir']; ?></small>
							<?php 
							if(!isset($pages[$page['translations'][$lang['ll']]]['translations'][$page['ll']])
							   ||$pages[$page['translations'][$lang['ll']]]['translations'][$page['ll']]!=$idpag) { ?>
							   <div class="alert">Link non corrisposto</div>
							   <?php  }
							$pagesused[$page['translations'][$lang['ll']]]=true;
							?></div>
							<?php  } ?>
							</td><?php 
						}
					}
				?>
				</tr>
				<?php  }
			}
		foreach($languages as $i=>$lang) {
			if($i>0) {
				foreach($pages as $idpag=>$page) {
					if($page['ll']==$lang['ll']&&!isset($pagesused[$idpag])) { ?>
						<tr>
						<?php 
						for($c=0;$c<$i;$c++) { ?>
							<td class="dropElement"></td>
							<?php  }
						?>
						<td class="dropElement"><div id="<?= $lang['ll'].$idpag; ?>" class="dragElement">
							<strong><?= $page['title']; ?></strong><br />
							<small><?= $page['dir']; ?></small>
							</div></td>
						<?php 
						$pagesused[$idpag]=true;
						
						foreach($languages as $j=>$sublang) {
							if($j>$i) { ?>
								<td class="dropElement"><?php 
								if(isset($page['translations'][$sublang['ll']])&&isset($pages[$page['translations'][$sublang['ll']]])&&!isset($pagesused[$page['translations'][$sublang['ll']]])) { ?>
									<div id="<?= $sublang['ll'].$page['translations'][$sublang['ll']]; ?>" class="dragElement">
									<strong><?= $pages[$page['translations'][$sublang['ll']]]['title']; ?></strong><br />
									<small><?= $pages[$page['translations'][$sublang['ll']]]['dir']; ?></small>
									<?php 
									if(!isset($pages[$page['translations'][$sublang['ll']]]['translations'][$page['ll']])
									   ||$pages[$page['translations'][$sublang['ll']]]['translations'][$page['ll']]!=$idpag) { ?>
									   <div class="alert">Link non corrisposto</div>
									   <?php  }
									$pagesused[$page['translations'][$sublang['ll']]]=true;
									?></div>
									<?php  } ?>
									</td><?php 
								}
							}
						?></tr>
						<?php  }
					}
				}
			}
		?>
		<tr><?php 
		foreach($languages as $lang) { ?>
			<td class="dropElement"></td>
			<?php  } ?></tr>
		</table>
	<?php  } ?>


<h1><?= $kaTranslate->translate('Languages:'.PAGE_NAME); ?></h1>
<br />


<div class="tab"><dl>
	<?php 
	$tabs=array();
	if($kaUsers->canIUse("pages")) $tabs[]=array("label"=>"Pages","url"=>"pages");
	if($kaUsers->canIUse("news")) $tabs[]=array("label"=>"News","url"=>"news");
	if($kaUsers->canIUse("photogallery")) $tabs[]=array("label"=>"Photogallery","url"=>"photogallery");
	if($kaUsers->canIUse("shop")) $tabs[]=array("label"=>"Shop","url"=>"shop");
	foreach($tabs as $t) {
		if(!isset($_GET['tab'])) $_GET['tab']=$t['url'];
		?>
		<dt><a href="?tab=<?= $t['url']; ?>" class="<?= $_GET['tab']==$t['url']?'sel':''; ?>"><?= $kaTranslate->translate("Menu:".$t['label']); ?></a></dt>
		<?php  }
	?>
	</dl></div>

<?php 
if($_GET['tab']=="pages") {
	require('../pages/pages.lib.php');
	$kaPages=new kaPages();
	
	// new combination of pages
	if(isset($_GET['t'])&&$_GET['t']!="") {
		$translations="";
		foreach(explode("|",trim($_GET['t'],"|")) as $t) {
			$k=substr($t,0,2);
			$v=substr($t,2);
			if($v!="") {
				$translations.=$k.'='.$v.'|';
				$kaPages->removePageFromTranslations($v);
				}
			}
		// first of all, clear translations from previous+current pages
/*		foreach($page['traduzioni'] as $k=>$v) {
			if($v!="") $kaPages->removePageFromTranslations($v);
			}*/
		// then set the new translations in the current pages
		foreach(explode("|",trim($_GET['t'],"|")) as $t) {
			$k=substr($t,0,2);
			$v=substr($t,2);
			if($v!="") {
				$kaPages->setTranslations($v,$translations);
				}
			}
		}
	
	$pages=array();
	foreach($kaPages->getQuickList(array()) as $row) {
		$translations=array();
		foreach(explode("|",$row['traduzioni']) as $t) {
			$ll=substr($t,0,2);
			$id=intval(substr($t,3));
			if($ll!=""&&$id!=0) $translations[$ll]=$id;
			}
		$pages[$row['idpag']]=array(
			"title"=>$row['titolo'],
			"dir"=>$row['dir'],
			"ll"=>$row['ll'],
			"translations"=>$translations
			);
		}

	printList($pages);
	}


elseif($_GET['tab']=="news") {
	require('../news/news.lib.php');
	$kaNews=new kaNews();

	// new combination of pages
	if(isset($_GET['t'])&&$_GET['t']!="") {
		$translations="";
		foreach(explode("|",trim($_GET['t'],"|")) as $t) {
			$k=substr($t,0,2);
			$v=substr($t,2);
			if($v!="") {
				$translations.=$k.'='.$v.'|';
				$kaNews->removePageFromTranslations($v);
				}
			}
		// first of all, clear translations from previous+current pages
/*		foreach($page['traduzioni'] as $k=>$v) {
			if($v!="") $kaPages->removePageFromTranslations($v);
			}*/
		// then set the new translations in the current pages
		foreach(explode("|",trim($_GET['t'],"|")) as $t) {
			$k=substr($t,0,2);
			$v=substr($t,2);
			if($v!="") {
				$kaNews->setTranslations($v,$translations);
				}
			}
		}

	$pages=array();
	foreach($kaNews->getQuickList(array()) as $row) {
		$translations=array();
		foreach(explode("|",$row['traduzioni']) as $t) {
			$ll=substr($t,0,2);
			$id=intval(substr($t,3));
			if($ll!=""&&$id!=0) $translations[$ll]=$id;
			}
		$pages[$row['idnews']]=array(
			"title"=>$row['titolo'],
			"dir"=>$row['dir'],
			"ll"=>$row['ll'],
			"translations"=>$translations
			);
		}
	printList($pages);
	}


elseif($_GET['tab']=="photogallery") {
	require('../photogallery/photogallery.lib.php');
	$kaPhotogallery=new kaPhotogallery();

	// new combination of pages
	if(isset($_GET['t'])&&$_GET['t']!="") {
		$translations="";
		foreach(explode("|",trim($_GET['t'],"|")) as $t) {
			$k=substr($t,0,2);
			$v=substr($t,2);
			if($v!="") {
				$translations.=$k.'='.$v.'|';
				$kaPhotogallery->removePageFromTranslations($v);
				}
			}
		// first of all, clear translations from previous+current pages
/*		foreach($page['traduzioni'] as $k=>$v) {
			if($v!="") $kaPages->removePageFromTranslations($v);
			}*/
		// then set the new translations in the current pages
		foreach(explode("|",trim($_GET['t'],"|")) as $t) {
			$k=substr($t,0,2);
			$v=substr($t,2);
			if($v!="") {
				$kaPhotogallery->setTranslations($v,$translations);
				}
			}
		}

	$pages=array();
	foreach($kaPhotogallery->getQuickList(array()) as $row) {
		$translations=array();
		foreach(explode("|",$row['traduzioni']) as $t) {
			$ll=substr($t,0,2);
			$id=intval(substr($t,3));
			if($ll!=""&&$id!=0) $translations[$ll]=$id;
			}
		$pages[$row['idphg']]=array(
			"title"=>$row['titolo'],
			"dir"=>$row['dir'],
			"ll"=>$row['ll'],
			"translations"=>$translations
			);
		}
	printList($pages);
	}


elseif($_GET['tab']=="shop") {
	require('../shop/shop.lib.php');
	$kaShop=new kaShop();

	// new combination of pages
	if(isset($_GET['t'])&&$_GET['t']!="") {
		$translations="";
		foreach(explode("|",trim($_GET['t'],"|")) as $t) {
			$k=substr($t,0,2);
			$v=substr($t,2);
			if($v!="") {
				$translations.=$k.'='.$v.'|';
				$kaShop->removePageFromTranslations($v);
				}
			}
		// first of all, clear translations from previous+current pages
/*		foreach($page['traduzioni'] as $k=>$v) {
			if($v!="") $kaPages->removePageFromTranslations($v);
			}*/
		// then set the new translations in the current pages
		foreach(explode("|",trim($_GET['t'],"|")) as $t) {
			$k=substr($t,0,2);
			$v=substr($t,2);
			if($v!="") {
				$kaShop->setTranslations($v,$translations);
				}
			}
		}
	
	$pages=array();
	foreach($kaShop->getQuickList(array()) as $row) {
		$translations=array();
		foreach(explode("|",$row['traduzioni']) as $t) {
			$ll=substr($t,0,2);
			$id=intval(substr($t,3));
			if($ll!=""&&$id!=0) $translations[$ll]=$id;
			}
		$pages[$row['idsitem']]=array(
			"title"=>$row['titolo'],
			"dir"=>$row['dir'],
			"ll"=>$row['ll'],
			"translations"=>$translations
			);
		}
	printList($pages);
	}

?>
<script type="text/javascript">
	dd=new kDragAndDrop();
	
	var onDragStart=function(e) {
		}
	var onDrag=function(e) {
		}
	var onDropOver=function(e) {
		this.parentNode.className+=' dragOver';
		}
	var onDropLeave=function(e) {
		this.parentNode.className=this.className.replace('dragOver','');
		}
	var onDrop=function(e) {
		var tdnum=-1;
		for(var i=0,c=dd.getDraggedObject().parentNode.parentNode.getElementsByTagName('TD');c[i];i++) {
			if(c[i]==dd.getDraggedObject().parentNode) tdnum=i;
			}
		if(tdnum<0) return false;
		var target=dd.getDroppedObject().parentNode.getElementsByTagName('TD')[tdnum];
		if(target!=dd.getDraggedObject().parentNode) {
			target.innerHTML="";
			target.appendChild(dd.getDraggedObject());
			
			var url='?tab=<?= $_GET['tab']; ?>&t=';
			for(var i=0,c=target.parentNode.querySelectorAll('.dragElement');c[i];i++) {
				url+=c[i]['id']+"|";
				}
			location.href=url;
			}
		}
	
	for(var i=0,c=document.body.querySelectorAll('.dragElement');c[i];i++) {
		dd.makeDraggable(c[i],onDragStart,onDrag);
		}
	for(var i=0,c=document.body.querySelectorAll('.dropElement');c[i];i++) {
		dd.makeDroppable(c[i],onDropOver,onDropLeave,onDrop);
		}
	</script>
<?php 

include_once("../inc/foot.inc.php");
