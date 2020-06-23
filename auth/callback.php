<?php

session_start();

define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/vendor/autoload.php');
require_once(__ROOT__.'/constants.php');
require_once(__ROOT__.'/helper.php');

$state = "login";

if(!array_key_exists('state', $_REQUEST) || ($_REQUEST['state'] !== $state)) {
	// ERROR 8: STATE ERROR
    IgniteHelper::error(8, 'State does not match.');
}

if(array_key_exists('error', $_REQUEST)) {
	// ERROR 9: OKTA CALLBACK REQUEST ERROR
    IgniteHelper::error(9, $_REQUEST['error']);
}

if(array_key_exists('code', $_REQUEST)) {
    $exchange = exchangeCode($_REQUEST['code']);
	
	$jwt = $exchange->access_token;
	
	$jwtVerifier = (new Okta\JwtVerifier\JwtVerifierBuilder())
		->setDiscovery(new Okta\JwtVerifier\Discovery\Oauth())
		->setAdaptor(new Okta\JwtVerifier\Adaptors\FirebasePhpJwt())
		->setAudience(IgniteConstants::OKTA_AUDIENCE)
		->setClientId(IgniteConstants::OKTA_CLIENT_ID)
		->setIssuer(IgniteConstants::OKTA_ISSUER)
		->build();
	
	try {
		$jwt = $jwtVerifier->verify($jwt);
	} catch(Exception $e){
		// ERROR 10: COULD NOT VERIFY JSON WEB KEY (JWT)
		IgniteHelper::error(10, $e->getMessage());
	}
	
	$jwt_json = $jwt->toJson();
	
	if(strcmp($jwt_json->iss, IgniteConstants::OKTA_ISSUER) || strcmp($jwt_json->aud, IgniteConstants::OKTA_AUDIENCE) || strcmp($jwt_json->cid, IgniteConstants::OKTA_CLIENT_ID)) {
		// ERROR 10: COULD NOT VERIFY JSON WEB KEY (JWT)
		IgniteHelper::error(10, "Could not verify.");
	}
	
	$milliseconds = round(microtime(true) * 1000);
	if($millseconds > $jwt->getExpirationTime(false)) {
		// ERROR 11: TOKEN HAS EXPIRED
		IgniteHelper::error(11, "Token has expired.");
	}
	
	// JWT verification successful, now verify user with database
	
	$email = $jwt_json->sub;
	
	$conn = IgniteHelper::db_connect();
	if(!$conn) {
		IgniteHelper::error(12, "MySQL Error: " . mysqli_connect_error());
	}
	
	$userFound = false;
	$authorized = false;
	$id = -1;
	$firstname = "";
	$lastname = "";
	$avatar = "";
	$sql = "SELECT * FROM admin_users";
	$result = mysqli_query($conn, $sql);

	if (mysqli_num_rows($result) > 0) {
		// output data of each row
		while($row = mysqli_fetch_assoc($result)) {
			if(!strcmp($row["email"], $email)) {
				$userFound = true;
				$permissions = json_decode($row["permissions"]);
				$authorized = IgniteHelper::hasPermission($permissions, "login");
				if($authorized) {
					$id = $row["id"];
					$firstname = $row["firstname"];
					$lastname = $row["lastname"];
					$avatar = $row["avatar"];
				}
			}
		}
	} else {
		// ERROR 13: NO USER FOUND
		IgniteHelper::error(13, "No user found.");
	}

	IgniteHelper::db_close($conn);
	
	if(!$userFound) {
		// ERROR 13: NO USER FOUND
		IgniteHelper::error(13, "No user found.");
	}
	
	if(!$authorized) {
		// ERROR 14: USER NOT AUTHORIZED
		IgniteHelper::error(14, "User is not authorized to perform that function.");
	}
	
	$_SESSION['CREATED'] = time();
	$_SESSION['LAST_ACTIVITY'] = time();
	$_SESSION['login'] = true;
	$_SESSION['id'] = htmlspecialchars($id);
	$_SESSION['email'] = htmlspecialchars($email);
	$_SESSION['firstname'] = htmlspecialchars($firstname);
	$_SESSION['lastname'] = htmlspecialchars($lastname);
	$_SESSION['avatar'] = htmlspecialchars($avatar);
	
	header('Location: ../dash/');
}

function exchangeCode($code) {
    $authHeaderSecret = base64_encode(IgniteConstants::OKTA_CLIENT_ID .':'. IgniteConstants::OKTA_CLIENT_SECRET);
    $query = http_build_query([
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => IgniteConstants::OKTA_CALLBACK
    ]);
    $headers = [
        'Authorization: Basic ' . $authHeaderSecret,
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded',
        'Connection: close',
        'Content-Length: 0'
    ];
    $url = IgniteConstants::OKTA_ISSUER . '/' . IgniteConstants::API_VERSION . '/token?' . $query;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, 1);
    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if(curl_error($ch)) {
        $httpcode = 500;
    }
    curl_close($ch);
    return json_decode($output);
}

?>