<?php
	/*include_once("classes/simple_html_dom.php");

	$parser = new simple_html_dom();*/

	include_once("classes/url.class.php");
	include_once("classes/sah.class.php");

	$parameters = array(
		"action",
		"token",
		"id",
		"offset",
		"email",
		"password"
		);

	foreach ($parameters as $parameter) {
		if (isset($_GET[$parameter]) && $_GET[$parameter] != "") {
			$$parameter = $_GET[$parameter];
		} else if (isset($_POST[$parameter]) && $_POST[$parameter] != "") {
			$$parameter = $_POST[$parameter];
		} else {
			$$parameter = null;
		}
	}


	function checkToken($token) {
		if (!isset($token) || $token == "") {
			$array = array(
				"error" => "parametersmissing",
				"description" => "Make sure the parameter token is filled in."
			);
			echo json_encode($array);

			return false;
		} else {
			return true;
		}
	}

	header("Content-Type: application/json");
	header("Access-Control-Allow-Origin: http://swagger.ownprojects.info");

	if (isset($action)){
		$action = explode("/", $action)[1];
		if ($action != "" && $action != " ") {
			switch($action) {
				case "afspraken":
					if (checkToken($token)) {
						echo json_encode($sah->krijgAfspraken($token));
					}
					break;

				case "ingelogd":
					if (checkToken($token)) {
						$ingelogd = $sah->isIngelogd($token);
						if ($ingelogd == "true") {
							$array = array(
								"ingelogd" => "true"
							);
						} else {
							$array = array(
								"ingelogd" => "false"
							);
						}
						echo json_encode($array);
					}
					break;

				case "login":
					if (isset($email, $password)) {
						$username = $email;
						$password = $password;
						$response = $sah->login($username, $password);
						echo $response;
					} else {
						$array = array(
							"error" => "parametersmissing",
							"description" => "Make sure the parameters email and password are filled in."
							);
						echo json_encode($array);
					}
				break;

				case "loon":
					if (checkToken($token)) {
						echo json_encode($sah->loon($token));
					}
				break;
					
				case "prikbord_actueel":
					if (checkToken($token)) {
						if ($offset == null) {
							$offset = 0;
						}
						$encoded = json_encode($sah->prikbordActueel($token, $offset));
						$encoded = str_replace("\u00a0", " ", $encoded);
						echo $encoded;
					}
				break;

				case "prikbord_item":
					if (checkToken($token)) {
						if (isset($id) && is_numeric($id)) {
							$encoded = json_encode($sah->prikbordItem($token,$id));
							$encoded = str_replace("\u00a0", " ", $encoded);
							echo $encoded;
						}
					}
				break;

				case "prikbord_kan_niet":
					if (checkToken($token)) {
						if (isset($id) && is_numeric($id)) {
							$encoded = json_encode($sah->prikbordKanNiet($token,$id));
							$encoded = str_replace("\u00a0", " ", $encoded);
							echo $encoded;
						}
					}
					break;

				/*case "prikbord_afgesloten":
					if (isset($_GET['token'])) {
						echo $sah->actueelPrikbord($_GET['token']);
					} else {
						$array = array(
							"error" => "parametersmissing",
							"description" => "Make sure the parameter token is filled in."
							);
						echo json_encode($array);
					}
				break;*/

				/*case "prikbord_beide":
					if (isset($_GET['token'])) {
						echo $sah->actueelPrikbord($_GET['token']);
					} else {
						$array = array(
							"error" => "parametersmissing",
							"description" => "Make sure the parameter token is filled in."
							);
						echo json_encode($array);
					}
				break;*/

				case "timeout": 
					if (checkToken($token)) {
						//$sah->timeout($token);
						echo json_encode($sah->timeout($token));
					}
				break;

				case "werkbonnen": 
					if (checkToken($token)) {
						echo json_encode($sah->werkbonnen($token));
					}
				break;
				
			}
		} else {
			$array = array(
				"error" => "noaction",
				"description" => "No action selected"
				);
			echo json_encode($array);
		}
	} 

?>