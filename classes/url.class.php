<?php

/**
* 
*/

class url
{

	function __construct()
	{
	}

	public function get($url, $variables = null) {
		$ch =  curl_init();

		//curl_setopt($ch, CURLINFO_HEADER_OUT, true);

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

		if(isset($variables['refer'])) {       
			curl_setopt( $ch, CURLOPT_REFERER, $variables['refer']);
		}

		if (isset($variables['headers'])) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $variables['headers']);
		}

		if (isset($variables['login']) || isset($variables['token'])) {
			$token = (isset($variables['login']) ? $variables['login'] : $variables['token']);
			//curl_setopt($ch, CURLOPT_COOKIESESSION,  1);
			curl_setopt($ch, CURLOPT_COOKIEJAR,  "/tmp/tokens/".$token);
			curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/tokens/".$token);
		}

		if (isset($variables['useragent'])) {
			curl_setopt($ch, CURLOPT_USERAGENT, $variables['useragent']);
		}

		curl_setopt($ch, CURLOPT_HEADER, 1);

		$result = curl_exec($ch);

		//$headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT );
		//echo $headerSent.PHP_EOL."<br>"."<br>";

		if (isset($variables['login']) || isset($variables['token'])) {
			$token = (isset($variables['login']) ? $variables['login'] : $variables['token']);
			$headers = explode(PHP_EOL, $result);
			foreach ($headers as $key => $header) {
				if (preg_match("/Set-Cookie/", $header)) {
					$cookie = explode(":", $header)[1];
					$fp = fopen("/tmp/tokens/".$token, "w+");
					fwrite($fp, $cookie);
					fclose($fp);
					break;
				}
			}
		}

		curl_close($ch);

		return $result;
	}

	public function post($url, $variables = null) {
		$ch =  curl_init();

		//curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $variables['post']);


		if(isset($variables['refer'])) {       
			curl_setopt( $ch, CURLOPT_REFERER, $variables['refer']);
		}

		if (isset($variables['login']) || isset($variables['token'])) {
			$token = (isset($variables['login']) ? $variables['login'] : $variables['token']);
			//curl_setopt($ch, CURLOPT_COOKIESESSION,  1);
			curl_setopt($ch, CURLOPT_COOKIEJAR,  "/tmp/tokens/".$token);
			curl_setopt($ch, CURLOPT_COOKIEFILE, "/tmp/tokens/".$token);
		}
		
		if (isset($variables['useragent'])) {
			curl_setopt($ch, CURLOPT_USERAGENT, $variables['useragent']);
		}

		if (isset($variables['headers'])) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $variables['headers']);
		}

		curl_setopt($ch, CURLOPT_HEADER, 1);

		$result = curl_exec($ch);

		//$headerSent = curl_getinfo($ch, CURLINFO_HEADER_OUT );
		//echo $headerSent.PHP_EOL."<br>"."<br>";

		if (isset($variables['login']) || isset($variables['token'])) {
			$token = (isset($variables['login']) ? $variables['login'] : $variables['token']);
			$headers = explode(PHP_EOL, $result);
			foreach ($headers as $key => $header) {
				if (preg_match("/Set-Cookie/", $header)) {
					$cookie = explode(":", $header)[1];
					$fp = fopen("/tmp/tokens/".$token, "w+");
					fwrite($fp, $cookie);
					fclose($fp);
					break;
				}
			}
		}
		
		curl_close($ch);
		return $result;
	}
}

$url = new url();