<?php
	/**
	* 
	*/
	class SAH extends url
	{
		private $lastPage = "";
		private $userAgent;

		function __construct()
		{
			$this->userAgent = "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36";
		}

		public function isIngelogd($token) {
			$url = "https://nl.sah3.net/students/calendar";
			$variables = array(
				"token" => $token,
				"useragent" => $this->userAgent
				);
			$request = $this->get($url,$variables);
			//echo $request;
			$this->lastPage = $request;

			if (preg_match("/Afspraken/", $request)) {
				return "true";
			} else {
				return "false";
			}			
		}

		public function krijgAfspraken($token, $week = "", $jaar = "") {
			$url = "https://nl.sah3.net/students/calendar";
			$startDate = "";

			if ($week != "" && $jaar != "") {
				if (is_numeric($week) && is_numeric($jaar)) {
					$timeNow = time();
					$thisYear = date("Y", time());
					$thisWeek = date("W", time());

					$weeksBetween = 0;
					$yearsBetween = 0;

					if ($jaar > 2000 && $jaar < 2200) {
						$yearsBetween = $thisYear - $jaar;
						echo $yearsBetween."<br>";
					}

					if ($week > 0 && $week < 53) {
						$weeksBetween = $thisWeek - $week;
						echo $weeksBetween."<br>";
					}

					$timeToSubstract = ($yearsBetween * 31556926) + ($weeksBetween * 604800) + 604800;
					$timeNow = $timeNow - $timeToSubstract;
					$timeNow = $timeNow - ((date("w", $timeNow) - 1) * 86400);

					$startDate = date("Y-m-d", $timeNow);
				}
			} else if ($week != "") {
				if (is_numeric($week)) {
					$timeNow = time();
					$thisWeek = date("W", time());

					$weeksBetween = 0;

					if ($week > 0 && $week < 53) {
						$weeksBetween = $thisWeek - $week;
						echo $weeksBetween."<br>";
					}

					$timeToSubstract = ($weeksBetween * 604800) + 604800;
					$timeNow = $timeNow - $timeToSubstract;
					$timeNow = $timeNow - ((date("w", $timeNow) - 1) * 86400);

					$startDate = date("Y-m-d", $timeNow);
				}
			} else if ($jaar != "") {
				if (is_numeric($jaar)) {
					$timeNow = time();
					$thisYear = date("Y", time());
					$yearsBetween = 0;

					if ($jaar > 2000 && $jaar < 2200) {
						$yearsBetween = $thisYear - $jaar;
						echo $yearsBetween."<br>";
					}

					$timeToSubstract = ($yearsBetween * 31556926) + 604800;
					$timeNow = $timeNow - $timeToSubstract;
					$timeNow = $timeNow - ((date("w", $timeNow) - 1) * 86400);

					$startDate = date("Y-m-d", $timeNow);
				}
			}

			if ($startDate != "") {
				$url .= "?start_day=".$startDate;
			}

			//$url = "https://nl.sah3.net/students/calendar?start_day=2015-9-30";
			$variables = array(
				"token" => $token,
				"useragent" => $this->userAgent
				);
			$html = $this->get($url,$variables);

			$html = str_replace("&#8211;", "-", $html);
			$html = html_entity_decode(str_replace('&nbsp;', ":", htmlentities($html)));

			//echo $html;
			
			$klanten = array();

			$afspraken = explode("<article", $html);
			$all = array();

			foreach ($afspraken as $key => $afspraak) {
				if ($key != 0) {
					if ($key == (count($afspraken) - 1)) {
						$all[] = explode("</article", $afspraak)[0];
					} else {
						$all[] = $afspraak;
					}
				}
			}

			foreach ($all as $afspraak) {
				$dag = "";
				$datum = "";

				if (preg_match('/<p class="appointment-day">(?P<variabele>[\w|\s]+)</', $afspraak, $matches)) {
					$dag = $matches['variabele'];
				}

				if (preg_match('/<p class="appointment-date">(?P<variabele>[\d|\s|\w]+)</', $afspraak, $matches)) {
					$datum = $matches['variabele'];
				}

				$klant = explode('<div class="appointment-person">', $afspraak);

				foreach ($klant as $key => $info) {
					if ($key == 0) {
						continue;
					}

					$item = array();

					$klantID = "";

					if (preg_match('/<a href="\/students\/appointments\/(?P<variabele>[\d]+)\/reschedule/', $info, $matches)) {
						$klantID = $matches['variabele'];
						$item['id'] = $klantID;
					}

					$item['dag'] = $dag;
					$item['datum'] = $datum;

					if (preg_match('/<p class="appointment-name">(?P<variabele>[\w|\.|\s]+)</', $info, $matches)) {
						$item['klant_naam'] = $matches['variabele'];
					}

					if (preg_match('/<span class="appointment-number">\((?P<variabele>[\d|\s]+)\)</', $info, $matches)) {
						$item['klant_nummer'] = $matches['variabele'];
					}

					if (preg_match('/<p class="appointment-time">(?P<variabele>[\d|\:]+-[\d|\:]+)/', $info, $matches)) {
						$begin = explode("-", $matches['variabele'])[0];
						$eind = explode("-", $matches['variabele'])[1];
						$item['start_tijd'] = $begin;
						$item['eind_tijd'] = $eind;
					}

					if (preg_match('/<div class="appointment-address">(?P<variabele>[\d|\w|\s|\n|\t|\:]+)</', $info, $matches)) {
						$adres = explode(PHP_EOL, $matches['variabele'])[2];
						$nieuwAdres = "";
						foreach (explode(" ", $adres) as $nieuw) {
							if ($nieuw != "") {
								if ($nieuwAdres == "") {
									$nieuwAdres = $nieuw;
								} else {
									$nieuwAdres .= " ".$nieuw;
								}
							}
						}
						$postcode = explode(PHP_EOL, $matches['variabele'])[4];
						$plaatsnaam = explode("::", $postcode)[1];
						$postcode = explode("::", $postcode)[0];
						$nieuwePostcode = "";
						foreach (explode(" ", $postcode) as $nieuw) {
							if ($nieuw != "") {
								if ($nieuwePostcode == "") {
									$nieuwePostcode = $nieuw;
								} else {
									$nieuwePostcode .= " ".$nieuw;
								}
							}
						}

						$item['adres'] = $nieuwAdres;
						$item['postcode'] = $nieuwePostcode;
						$item['plaatsnaam'] = $plaatsnaam;
					}

					if (preg_match('/<p class="appointment-membership-warning">Moet formulier tekenen<\/p>/', $info, $matches)) {
						$item['formulier'] = true;
					} else {
						$item['formulier'] = false;
					}

					
					if (preg_match_all('/>(?P<variabele>[\d]+)<\/p>/', $info, $matches)) {
						foreach ($matches['variabele'] as $key => $telefoon) {
							if (count($matches['variabele']) > 1) {
								$item['telefoon'][] = (strlen($matches['variabele'][$key]) > 4) ? $matches['variabele'][$key] : "";
							} else {
								$item['telefoon'][] = (strlen($matches['variabele'][$key]) > 4) ? $matches['variabele'][$key] : "";
							}
						}
					}

					if (!isset($item['telefoon'])) {
						$item['telefoon'][] = "";
					}

					if (preg_match('/<a href="mailto:(?P<variabele>[\w|\d|@|\.]+)"/', $info, $matches)) {
						$item['email'] = $matches['variabele'];
					} else {
						$item['email'] = "";
					}

					if (preg_match('/PIN: <\/span>(?P<variabele>[\d]+)<\/p>/', $info, $matches)) {
						$item['pin'] = $matches['variabele'];
					} else {
						$item['pin'] = "";
					}

					if (preg_match('/Capaciteiten: <\/span>(?P<variabele>[\w|\d|\s|,|\.]+)<\/p>/', $info, $matches)) {
						$item['capacitieten'] = $matches['variabele'];
					}

					if (preg_match_all("/Omschrijving: <\/span>(?P<variabele>(.*?|\s)+)\<\/p\>/", $info, $matches)) {
						$item['omschrijving'] = str_replace("<", "", $matches['variabele'][0]);
					}

					$klanten[] = $item;
				}
			}


			/*if (preg_match_all('/<span class="appointment-number">\((?P<variabele>[\d|\s]+)\)</', $html, $matches)) {
				foreach ($matches['variabele'] as $key => $variabele) {
					$klanten[$key]['klant_nummer'] = $variabele;
				}
			}*/
			return ($klanten);
			//echo json_encode($klanten);
		}

		public function login($username, $password) {
			$token = md5($username.time());
			$authToken = "";

			$variables = array(
				"login" => $token,
				"useragent" => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36"
			);

			$resp = $this->get("https://nl.sah3.net/login", $variables);
			//echo $resp."<br>"."<br>";


			$pattern = "/\<meta name=\"csrf-token\" content=\"(?P<variabele>(.*?|\s)+)\" \/\>/";

			if (preg_match_all($pattern, $resp, $matches)) {
				$authToken = urlencode($matches['variabele'][0]);
				//echo $authToken.PHP_EOL."<br>"."<br>";

				$url = "https://nl.sah3.net/sessions";
				$parameters = "email=$username";
				$parameters .= "&utf8=&#x2713;";
				$parameters .= "&password=$password";
				$parameters .= "&authenticity_token=$authToken";
				$parameters .= "&commit=Inloggen";

				$variables = array(
					"post" => $parameters,
					"login" => $token,
					"useragent" => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36",
					"headers" => ['Origin:https://nl.sah3.net', 'Referer:https://nl.sah3.net/login']
					);
				$request = $this->post($url,$variables);

				$headers = explode(PHP_EOL, $request);

				$loggedIn = false;
				foreach ($headers as $key => $header) {
					if (preg_match("/Set-Cookie/", $header)) {
						$loggedIn = true;
					}
				}

				if (!$loggedIn) {
					$request = "Email of wachtwoord is ongeldig";
				}

				//echo $request.PHP_EOL."<br>"."<br>";
			}

			/*/$variables = array(
				"login" => $token,
				"useragent" => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36"
			);*/

			//$resp = $this->get("https://nl.sah3.net/", $variables);
			//echo $resp.PHP_EOL."<br>"."<br>";

			//$resp = $this->get("https://nl.sah3.net/students/calendar", $variables);
			//echo $resp.PHP_EOL."<br>"."<br>";

			if (!preg_match('/Email of wachtwoord is ongeldig/', $request)) {
				$array = array(
					"token" => $token,
					"message" => "Je bent succesvol aangemeld, gebruik de token om verdere requests te kunnen maken."
					);
			} else {
				$array = array(
					"error" => "failedlogin",
					"description" => "Email of wachtwoord is ongeldig"
					);
			}

			return json_encode($array);
		}

		public function loon($token) {
			$url = "https://nl.sah3.net/students/wages";
			$variables = array(
				"token" => $token,
				"useragent" => $this->userAgent
				);
			$request = $this->get($url,$variables);
			$this->lastPage = $request;

			$wages = explode("<tr", $request);
			$items = array();
			foreach ($wages as $key => $wage) {
				$wageArray = array();
				if ($key == 0) {
					continue;
				}

				$regexArray = array(
					"maand" => "/class=\"wages-month\">(?P<variabele>(.*?|\s)+)\<\/td\>/",
					"aantal" => "/class=\"wages-work-orders\">(?P<variabele>(.*?|\s)+)\<\/td\>/",
					"gepland" => "/class=\"wages-planned\">(?P<variabele>(.*?|\s)+)\<\/td\>/",
					"ingediend" => "/class=\"wages-submitted\">(?P<variabele>(.*?|\s)+)\<\/td\>/",
					"goedgekeurd" => "/class=\"wages-approved\">(?P<variabele>(.*?|\s)+)\<\/td\>/",
					"salarisronde" => "/class=\"wages-batch\">(?P<variabele>(.*?|\s)+)\<\/td\>/",
					"bedrag" => "/class=\"wages-amount\">(?P<variabele>(.*?|\s)+)\<\/td\>/",
					);

				foreach ($regexArray as $key => $regex) {
					if (preg_match_all($regex, $wage, $matches)) {
						$wageArray[$key] = $matches['variabele'][0];
					}
				}

				$items[] = $wageArray;
			}

			return $items;
		}

		public function prikbordActueel($token, $offset = 0) {
			$url = "https://nl.sah3.net/students/pinboard_notes";
			$variables = array(
				"token" => $token,
				"useragent" => $this->userAgent
				);
			$request = $this->get($url,$variables);
			$this->lastPage = $request;

			$actueel = explode("section", $request)[1];
			$prikbordItems = explode("<tr", $actueel);
			$items = array();

			foreach ($prikbordItems as $key => $prikbordItem) {
				if ($key == 0 || $key == 1) {
					continue;
				}

				$itemID = 0;

				$item = array();

				if (preg_match_all("/href\=\"\/students\/pinboard_notes\/(?P<variabele>[\d]+)\"/", $prikbordItem, $matches)) {
					if ($offset != 0) {
						if ($matches['variabele'][0] < $offset) {
							continue;
						}
					}

					$itemID = $matches['variabele'][0];
					$item["id"] = $itemID;
				}
				
				if (preg_match_all("/class=\"capabilities\"\>(?P<variabele>(.*?|\s)+)\<\/td\>/", $prikbordItem, $matches)) {
					$item['typeafspraak'] = $matches['variabele'][0];
				}

				if (preg_match_all("/data-th=\"Adres\">(?P<variabele>(.*?|\s)+)\<\/td\>/", $prikbordItem, $matches)) {
					$item['adres'] = $matches['variabele'][0];
				}

				if (preg_match_all("/data-th=\"Tijd voor reactie\"\>(?P<variabele>(.*?|\s)+)\<\/td\>/", $prikbordItem, $matches)) {
					$item['reactietijd'] = $matches['variabele'][0];
				}
				
				//if (preg_match_all("/>Bekijken(?P<variabele>(.*?|\s)+)<\/a>/", $prikbordItem, $matches)) {
				if (preg_match_all("/>Bekijken<\/a>/", $prikbordItem, $matches)) {
					$item['gereageerd'] = false;
				} else {
					$item['gereageerd'] = true;
				}

				$items[] = $item;

				//echo htmlentities($prikbordItem)."<br><br><br><br>";
			}
			if (count($items) == 0) {
				$items = array(
					"error" => "noprikbord",
					"description" => "There are no prikbord items."
				);
			}

			return $items;
		}

		public function prikbordAfgesloten($token) {

		}

		public function prikbordKanNiet($token, $itemID) {
			$url = "https://nl.sah3.net/students/pinboard_notes/$itemID";
			$variables = array(
				"token" => $token,
				"useragent" => $this->userAgent
			);
			$request = $this->get($url,$variables);

			if (preg_match("/https\:\/\/nl.sah3.net\/students\/pinboard_notes/", $request)) {
				return array(
					"error" => "invalidID",
					"description" => "There is no item with the ID provided"
				);
			}

			$pattern = "/\<meta name=\"csrf-token\" content=\"(?P<variabele>(.*?|\s)+)\" \/\>/";

			if (preg_match_all($pattern, $request, $matches)) {
				$authToken = urlencode($matches['variabele'][0]);
				$url = "https://nl.sah3.net/students/pinboard_notes/$itemID";
				$parameters = "_method=put";
				$parameters .= "&utf8=&#x2713;";
				$parameters .= "&authenticity_token=$authToken";
				$parameters .= "&commit=Nee, ik kan deze afspraak niet doen";

				$variables = array(
					"post" => $parameters,
					"login" => $token,
					"useragent" => "Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0.2840.99 Safari/537.36"
				);
				$request = $this->post($url,$variables);
				return array(
					"ok" => "ok"
				);
			} else {
				return array(
					"error" => "Something wrong",
					"description" => "Something went wrong, try again later."
				);
			}
		}

		public function prikbordItem($token, $itemID) {
			$url = "https://nl.sah3.net/students/pinboard_notes/$itemID";
			$variables = array(
				"token" => $token,
				"useragent" => $this->userAgent
				);
			$request = $this->get($url,$variables);
			$this->lastPage = $request;

			if (preg_match("/https\:\/\/nl.sah3.net\/students\/pinboard_notes/", $request)) {
				return array(
					"error" => "invalidID",
					"description" => "There is no item with the ID provided"
					);
			}

 			$content = explode("dl", $request)[1];
 			$content = explode("<dd>", $content);

 			$item = array();

 			foreach ($content as $key => $info) {
 				if ($key == 0) {
 					continue;
 				}

 				$array = array(
 					1 => "omschrijving",
 					2 => "typeafspraak",
 					3 => "tijdsduur",
 					4 => "klant",
 					5 => "adres"
 					);

 				$item[$array[$key]] = explode("<", $info)[0];
 			}

			if (preg_match_all("/class=\"pinboard-response-embedded-map\" data-positions=\"\[(?P<variabele>(.*?|\s)+)\]\">/", $request, $matches)) {
				$item["coordinaten"] = $matches['variabele'][0];
			}

			if (preg_match("/<section class=\"pinboard-response-customer-availability\">(?P<variabele>(.*?|\s)+)<\/section>/", $request, $match)) {
				$stripped = $match[0];
				if (preg_match_all("/<tr>((.*?|\s)+)<\/tr>/", $stripped, $match)) {
					//print_r($match);
				}
			}
			

 			return $item;
		}

		public function timeout($token) {
			$url = "https://nl.sah3.net/students/time_offs";
			$variables = array(
				"token" => $token,
				"useragent" => $this->userAgent
				);
			$request = $this->get($url,$variables);
			$this->lastPage = $request;

			$timeouts = explode("<tr", $request);
			$items = array();
			foreach ($timeouts as $key => $timeout) {
				$timeoutArray = array();
				if ($key < 2) {
					continue;
				}

				$regexArray = array(
					"vanaf" => "/data-th=\"Vanaf\"\>(?P<variabele>(.*?|\s)+)\<\/td\>/",
					"tm" => "/data-th=\"Vanaf\"\>(?P<variabele>(.*?|\s)+)\<\/td\>/",
					"reden" => "/data-th=\"Reden\"\>(?P<variabele>(.*?|\s)+)\<\/td\>/"
					);

				foreach ($regexArray as $key => $regex) {
					if (preg_match_all($regex, $timeout, $matches)) {
						$timeoutArray[$key] = $matches['variabele'][0];
					}
				}
				
				$items[] = $timeoutArray;
			}

			return $items;
		}

		public function werkbonnen($token) {
			$url = "https://nl.sah3.net/students/work_orders";
			$variables = array(
				"token" => $token,
				"useragent" => $this->userAgent
				);
			$request = $this->get($url,$variables);
			$this->lastPage = $request;

			$werkbonnen = explode("<tr", $request);
			$items = array();
			foreach ($werkbonnen as $key => $werkbon) {
				$werkbonArray = array();
				if ($key == 0) {
					continue;
				}

				if (preg_match_all("/href=\"\/students\/work_orders\/(?P<variabele>[\d]+)\/edit\"/", $werkbon, $matches)) {
					$werkbonID = $matches['variabele'][0];
					$werkbonArray["id"] = $werkbonID;
				} else {
					$werkbonArray["id"] = "none";
				}

				if (preg_match_all("/data-th=\"Aanvang\"\>(?P<variabele>(.*?|\s)+)\<\/td\>/", $werkbon, $matches)) {
					$werkbonArray['aanvang'] = $matches['variabele'][0];
				}

				if (preg_match_all("/data-th=\"Klant\"\>(?P<variabele>(.*?|\s)+)\<\/td\>/", $werkbon, $matches)) {
					$werkbonArray['klant'] = $matches['variabele'][0];
				}

				if (preg_match_all("/data-th=\"Werkbonnummer\"\>(?P<variabele>(.*?|\s)+)\<\/td\>/", $werkbon, $matches)) {
					$nummer = trim($matches['variabele'][0]);
					if ($nummer == "&#8211;") {
						$nummer = "-";
					}
					$werkbonArray['werkbonnummer'] = $nummer;
				}

				if (preg_match_all("/data-th=\"Status\"\>(?P<variabele>(.*?|\s)+)\<\/td\>/", $werkbon, $matches)) {
					$werkbonArray['status'] = trim($matches['variabele'][0]);
				}

				if (isset($werkbonArray['status']) && $werkbonArray['status'] == "Ingediend") {
					$werkbonArray['ingediend'] = "true";
				} else {
					$werkbonArray['ingediend'] = "false"; 
				}

				$items[] = $werkbonArray;
			}

			return $items;
		}
	}

	$sah = new SAH();
?> 