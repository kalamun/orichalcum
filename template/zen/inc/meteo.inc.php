<?
/* prendo le info metereologiche dall'RSS di Enel per il comune di Ravenna */
//$rss=file("http://146.133.2.68/meteo/inc/rss_tabella_risultati.asp?idComune=IT039014");
//$rss=file("http://magazine.enel.it/meteo/inc/rss_tabella_risultati.asp?idComune=IT039014");
//$rss=explode("<![CDATA[",implode($rss));

#usage:
$r = new HTTPRequest('http://magazine.enel.it/meteo/inc/rss_tabella_risultati.asp?idComune=IT039014');
$rss=$r->DownloadToString();

class HTTPRequest
{
   var $_fp;        // HTTP socket
   var $_url;        // full URL
   var $_host;        // HTTP host
   var $_protocol;    // protocol (HTTP/HTTPS)
   var $_uri;        // request URI
   var $_port;        // port
  
   // scan url
   function _scan_url()
   {
       $req = $this->_url;
      
       $pos = strpos($req, '://');
       $this->_protocol = strtolower(substr($req, 0, $pos));
      
       $req = substr($req, $pos+3);
       $pos = strpos($req, '/');
       if($pos === false)
           $pos = strlen($req);
       $host = substr($req, 0, $pos);
      
       if(strpos($host, ':') !== false)
       {
           list($this->_host, $this->_port) = explode(':', $host);
       }
       else
       {
           $this->_host = $host;
           $this->_port = ($this->_protocol == 'https') ? 443 : 80;
       }
      
       $this->_uri = substr($req, $pos);
       if($this->_uri == '')
           $this->_uri = '/';
   }
  
   // constructor
   function HTTPRequest($url)
   {
       $this->_url = $url;
       $this->_scan_url();
   }
  
   // download URL to string
   function DownloadToString()
   {
       $crlf = "\r\n";
      
       // generate request
       $req = 'GET ' . $this->_uri . ' HTTP/1.0' . $crlf
           .    'Host: ' . $this->_host . $crlf
           .    $crlf;
      
       // fetch
       $this->_fp = fsockopen(($this->_protocol == 'https' ? 'ssl://' : '') . $this->_host, $this->_port);
       fwrite($this->_fp, $req);
	   $response="";
       while(is_resource($this->_fp) && $this->_fp && !feof($this->_fp))
           $response .= fread($this->_fp, 1024);
       fclose($this->_fp);
      
       // split header and body
       $pos = strpos($response, $crlf . $crlf);
       if($pos === false)
           return($response);
       $header = substr($response, 0, $pos);
       $body = substr($response, $pos + 2 * strlen($crlf));
      
       // parse headers
       $headers = array();
       $lines = explode($crlf, $header);
       foreach($lines as $line)
           if(($pos = strpos($line, ':')) !== false)
               $headers[strtolower(trim(substr($line, 0, $pos)))] = trim(substr($line, $pos+1));
      
       // redirection?
       if(isset($headers['location']))
       {
           $http = new HTTPRequest($headers['location']);
           return($http->DownloadToString($http));
       }
       else
       {
           return($body);
       }
   }
}

$rss=explode("<![CDATA[",$rss);
$i=1;
echo '<table>';
foreach($rss as $line) {
	if(preg_match("/.*<table.*Previsioni valide alle ore (11|12|13|14|15):\d{2}.*/",$line)) {
		$i++;
		$data=preg_replace("/<table.*Previsioni valide alle ore (11|12|13|14|15):\d{2}.*(\d{2})\/(\d{2})\/(\d{4}).*/","$2/$3/$4",$line);
		$temperatura=preg_replace("/.*Temperatura:.*?(\d{1,2}&deg; C).*/","$1",$line);
		$gif=preg_replace("/.*img_copertura\/(.*?.gif).*/","$1",$line);
		echo '<td>';
		echo $data.'<br />';
		echo '<img src="'.kGetTemplateDir().'img/meteo/'.$gif.'" /><br />';
		echo $temperatura.'<br />';
		echo '</td>';
		}
	if($i>2) break;
	}
echo '</table>';
?>