<?
/****************************/
/*         SITEMAP          */
/****************************/
header('Content-type: application/xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1"> 
<?
foreach(kGetLanguages() as $l) {
	foreach(kGetPageList($l['ll']) as $p) {
		kSetPageByDir($p['dir']);
		$md=kGetSeoMetadata($p['dir']);
		if(strpos($md['robots'],"noindex")===false) {
			?>
			<url>
			<loc><?= kGetSiteUrl().kGetPagePermalink(); ?></loc>
			<lastmod><?= kGetPageDateModified()!=""?date("c",mktime(substr(kGetPageDateModified(),11,2),substr(kGetPageDateModified(),14,2),substr(kGetPageDateModified(),17,2),substr(kGetPageDateModified(),5,2),substr(kGetPageDateModified(),8,2),substr(kGetPageDateModified(),0,4))):date("c"); ?></lastmod>
			<changefreq><?= $md['changefreq']; ?></changefreq>
			<priority><?= $md['priority']; ?></priority>
			<? if(count(kGetPageEmbeddedMedias())>0) { ?>
				<video:video>
				<? foreach(kGetPageEmbeddedMedias() as $video) {
					kSetMedia($video);
					?>
					<video:thumbnail_loc><?= kGetMediaThumbUrl(); ?></video:thumbnail_loc> 
					<video:title><![CDATA[<?= kGetMediaTitle()!=""?kGetMediaTitle():kGetMediaFilename(); ?>]]></video:title>
					<video:description><![CDATA[<?= kGetMediaCaption(); ?>]]></video:description>
					<? if(!kMediaIsHtmlCode()) { ?><video:content_loc><?= kGetMediaUrl(); ?></video:content_loc><? }
					else { ?><video:player_loc allow_embed="yes" autoplay="ap=1"><?= preg_replace('#.*?"(http://[^"]*?)".*#',"$1",kGetMediaHtmlCode()); ?></video:player_loc><? } ?>
					<video:duration><?= kGetMediaDuration(); ?></video:duration>
					<video:requires_subscription>no</video:requires_subscription>
					<? } ?>
				</video:video>
				<? } ?>
			</url>
			<?
			}
		}
	foreach(kGetNewsList(false,false,9999,0,"","","",$l['ll']) as $p) {
		kSetNewsByDir($p['dir']);
		$md=kGetSeoMetadata(kGetNewsDir($l['ll']).'/'.$p['categorie'][0]['dir'].'/'.$p['dir']);
		if(strpos($md['robots'],"noindex")===false) {
			?>
			<url>
			<loc><?= kGetSiteUrl().kGetNewsPermalink(); ?></loc>
			<lastmod><?= kGetNewsDateModified()!=""?date("c",mktime(substr(kGetNewsDateModified(),11,2),substr(kGetNewsDateModified(),14,2),substr(kGetNewsDateModified(),17,2),substr(kGetNewsDateModified(),5,2),substr(kGetNewsDateModified(),8,2),substr(kGetNewsDateModified(),0,4))):date("c"); ?></lastmod>
			<changefreq><?= $md['changefreq']; ?></changefreq>
			<priority><?= $md['priority']; ?></priority>
			<? if(count(kGetNewsEmbeddedMedias())>0) { ?>
				<video:video>
				<? foreach(kGetNewsEmbeddedMedias() as $video) {
					kSetMedia($video);
					?>
					<video:thumbnail_loc><?= kGetMediaThumbUrl(); ?></video:thumbnail_loc> 
					<video:title><![CDATA[<?= kGetMediaTitle()!=""?kGetMediaTitle():kGetMediaFilename(); ?>]]></video:title>
					<video:description><![CDATA[<?= kGetMediaCaption(); ?>]]></video:description>
					<? if(!kMediaIsHtmlCode()) { ?><video:content_loc><?= kGetMediaUrl(); ?></video:content_loc><? }
					else { ?><video:player_loc allow_embed="yes" autoplay="ap=1"><?= preg_replace('#.*?"(http://[^"]*?)".*#',"$1",kGetMediaHtmlCode()); ?></video:player_loc><? } ?>
					<video:duration><?= kGetMediaDuration(); ?></video:duration>
					<video:requires_subscription>no</video:requires_subscription>
					<? } ?>
				</video:video>
				<? } ?>
			</url>
			<?
			}
		}
	}?>
</urlset>