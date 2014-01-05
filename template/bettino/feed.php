<?
header('Content-type: application/rss+xml; charset=UTF-8');
echo '<?xml version="1.0" encoding="UTF-8"?>';
setlocale(LC_ALL,'en_US');
?>
<rss xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:wfw="http://wellformedweb.org/CommentAPI/" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:slash="http://purl.org/rss/1.0/modules/slash/"  version="2.0">

<channel>
	<title><?= kGetSiteName(); ?></title>
	<link><?= kGetSiteURL() ?></link>
	<description><?= kGetSiteName(); ?> <?= kGetSitePayoff(); ?></description>
	<lastBuildDate><?= date("r"); ?></lastBuildDate>
	<generator>http://www.bettino.it</generator>
	<sy:updatePeriod>daily</sy:updatePeriod>
	<sy:updateFrequency>1</sy:updateFrequency>
	<language><?= strtolower(kGetLanguage().'-'.kGetLanguage()); ?></language>
	<copyright><?= kGetSiteName(); ?></copyright>
	<atom:link href="<?= kGetSiteURL().kGetFeedDir(); ?>" rel="self" type="application/rss+xml" />

<?
foreach(kGetNewsQuickList(false,false,false,false) as $n) {
	kSetNewsByDir($n['dir']);
	?>
	<item>
		<title><![CDATA[<?= kGetNewsTitle(); ?>]]></title>
		<link><?= kGetSiteURL().kGetNewsPermalink(); ?></link>
		<guid><?= kGetSiteURL().kGetNewsPermalink(); ?></guid>
		<pubDate><?= kGetNewsDate("%a, %d %b %Y %H:%M:%S %z"); ?></pubDate>
		<?
		foreach(kGetNewsCategories() as $cat) { ?>
			<category><![CDATA[<?= $cat['categoria']; ?>]]></category>
			<? }
		?>
		<dc:creator><?= kGetNewsAuthor(); ?></dc:creator>
		<description><![CDATA[<?= trim(kGetNewsPreview()); ?>]]></description>
		<content:encoded><![CDATA[<?= trim(kGetNewsPreview()); ?><?= trim(kGetNewsText()); ?>]]></content:encoded>
		<slash:comments><?= kGetNewsCommentsCount(); ?></slash:comments>
		</item>
	<? } ?>
	</channel>
</rss>