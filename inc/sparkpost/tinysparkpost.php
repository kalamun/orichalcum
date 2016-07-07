<?php /* (c) Kalamun.org - GNU/GPL 3 */

class TinySparkPost
{
	
	protected $endpoint = "api.sparkpost.com",
			  $apikey = "",
			  $protocol = 'https',
			  $port = 443,
			  $strictSSL = true,
			  $key = '',
			  $version = 'v1',
			  $vars = array();
	
	public function __construct($apikey)
	{
		$this->apikey = $apikey;
	}
	
	/* set recipients passing an array of arrays ['address']=("email"=>"", "name"=>""), ['substitutions']=(...) */
	public function setRecipients($recipients = array())
	{
		$this->vars['recipients'] = $recipients;
	}
	
	/* set campaign name */
	public function setCampaignName($string)
	{
		$this->vars['campaign_id'] = $string;
	}
	
	/* set campaign description */
	public function setCampaignDescription($string)
	{
		$this->vars['description'] = $string;
	}
	
	/* set metadata by passing an array of keys=>values */
	public function setMetadata($metadata)
	{
		$this->vars['metadata'] = $metadata;
	}
	
	/* set substitutions by passing an array of keys=>values */
	public function setSubstitutions($subst)
	{
		$this->vars['substitution_data'] = $subst;
	}
	
	/* set email content as an array of: html, text, subject, from, reply_to, headers, attachments, inline_images */
	public function setContent($subst)
	{
		$this->vars['content'] = $subst;
	}
	
	public function sendEmail()
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		curl_setopt($ch, CURLOPT_URL, $this->protocol.'://'.$this->endpoint.($this->port ? ':' . $this->port : '') . '/api/' . $this->version.'/transmissions');
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Authorization: '.$this->apikey));
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($this->vars));
		$response = curl_exec($ch);

		if(curl_error($ch))
		{
			var_dump($response);
			$response = false;
		} else {
			$response = json_decode($response);
			if(!empty($response) && !empty($response->results->id)) return $response->results->id;
			else {
				var_dump($response);
				$response = false;
			}
		}

		curl_close($ch); 
		return $response;
	}
	
}