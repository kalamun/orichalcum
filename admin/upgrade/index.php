<?
/* (c) Kalamun.org - GNU/GPL 3 */

define("PAGE_NAME","Upgrade:Automatic upgrade");
include_once("../inc/head.inc.php");

error_reporting(0);

function kMultidomainFileGetContents($url) {
	$filecontent="";

	/* if fopen wrapper is enabled */
	if(ini_get('allow_url_fopen')) {
		$headers=get_headers($url);
		if(substr($headers[0],9,3)!="404") $filecontent=file_get_contents($url);
		}

	/* else try using cURL */
	elseif(function_exists('curl_init')) {
		$ch=curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_HEADER,0);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5) Gecko/20041107 Firefox/1.0');
		$filecontent=curl_exec($ch);
		curl_close($ch);
		}

	/* else try using fsock */
	else {
		$parsedUrl=parse_url($url);
		$host=$parsedUrl['host'];
		if(isset($parsedUrl['path'])) $path=$parsedUrl['path'];
		else $path='/';
		if(isset($parsedUrl['query'])) $path.='?'.$parsedUrl['query'];
		if(isset($parsedUrl['port'])) $port=$parsedUrl['port'];
		else $port='80';
		$timeout=10;

		$fp=@fsockopen($host,'80',$errno,$errstr,$timeout );

		if($fp) {
			fputs($fp,"GET $path HTTP/1.0\r\n" .
				"Host: $host\r\n" .
				"User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.0.3) Gecko/20060426 Firefox/1.5.0.3\r\n" .
				"Accept: */*\r\n" .
				"Accept-Language: en-us,en;q=0.5\r\n" .
				"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7\r\n" .
				"Keep-Alive: 300\r\n" .
				"Connection: keep-alive\r\n" .
				"Referer: http://$host\r\n\r\n");

			while($line=fread($fp,4096)) $filecontent.=$line;
			fclose( $fp );

			//strip headers
			$pos=strpos($filecontent,"\r\n\r\n");
			$filecontent=substr($filecontent,$pos+4);
			}
		}
	
	return $filecontent;
	}

/* the URL with upgrade informations */
$url='http://download.orichalcum.it/upgrade/'.SW_VERSION;
$upgrdinfo=kMultidomainFileGetContents($url);

?>

<h1><?= $kaTranslate->translate(PAGE_NAME); ?></h1>
<br />
<?
if($upgrdinfo==false||$upgrdinfo=="") { ?>
	<ul class="mainopt">
	<li><?= $kaTranslate->translate('Upgrade:No updates available'); ?></li>
	</ul>
	<? }

else {
	$upgrdinfo=explode("\n",$upgrdinfo);
	for($i=0;isset($upgrdinfo[$i]);$i++) {
		$upgrdinfo[$i]=trim($upgrdinfo[$i]);
		}

	if(!is_numeric($upgrdinfo[0])||substr($upgrdinfo[1],0,29)!="http://download.orichalcum.it") {
		echo $kaTranslate->translate('Upgrade:Error occurred reading upgrade data');
		include_once("../inc/foot.inc.php");
		die();
		}

	if(!isset($_GET['upgrade'])) {
		?>
		<ul class="mainopt">
		<li><a href="?upgrade"><?= $kaTranslate->translate('Upgrade:Upgrade to Orichalcum'); ?> <?= $upgrdinfo[0]; ?></a></li>
		</ul>
		<div style="padding:0 10%;">
			<?
			for($i=2;isset($upgrdinfo[$i]);$i++) {
				echo $upgrdinfo[$i];
				}
			?><br />
			<p><a href="?upgrade" class="smallbutton"><?= $kaTranslate->translate('Upgrade:Start upgrade'); ?></a></p>
			</div>
		<? }
	
	else {
		if(!file_exists('tmp')) mkdir('tmp');
		else {
			kRemoveDir('tmp');
			mkdir('tmp');
			}
		$tmpfile='tmp/tmp'.time().'.tar.gz';
		file_put_contents($tmpfile,kMultidomainFileGetContents($upgrdinfo[1]));
		kTgzExtract($tmpfile,'tmp');
		include('tmp/install.php');
		kRemoveDir('tmp');
		}
	}
	?>

<?
include_once("../inc/foot.inc.php");
?>
