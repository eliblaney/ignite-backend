<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/constants.php');
require_once(__ROOT__.'/helper.php');

if(empty($_POST["cltToken"])) {
	// ERROR 0: INVALID CLIENT TOKEN
	IgniteHelper::error(0, "Invalid request.");
}

$cltToken = $_POST["cltToken"];
if(strcmp($cltToken, IgniteConstants::SECRET_TOKEN) != 0) {
	// ERROR 0: INVALID CLIENT TOKEN
	IgniteHelper::error(0, "Invalid request.");
}

$err = 0;

if(empty($_POST["uid"])) {
	$err = $err | 1;
}

if(empty($_POST["email"])) {
	$err = $err | 2;
}

if(empty($_POST["name"])) {
	$err = $err | 4;
}

if(empty($_POST["createdAt"])) {
	$err = $err | 8;
}

if(!isset($_POST["faith"])) {
	$err = $err | 16;
}

if($err > 0) {
	// ERROR 1 ERROR 2 ERROR 3 ERROR 4 ERROR 5 ERROR 6 ERROR 7 ERROR 8 ERROR 9 ERROR 10 ERROR 11 ERROR 12 ERROR 13 ERROR 14 ERROR 15 ERROR 16 ERROR 17 ERROR 18 ERROR 19 ERROR 20 ERROR 21 ERROR 22 ERROR 23 ERROR 24 ERROR 25 ERROR 26 ERROR 27 ERROR 28 ERROR 29 ERROR 30 ERROR 31: MISSING ARGUMENTS
	IgniteHelper::error($err, "Missing arguments.");
}

$uid = htmlspecialchars($_POST["uid"]);
$email = htmlspecialchars($_POST["email"]);
$name = htmlspecialchars($_POST["name"]);
$faith = htmlspecialchars($_POST["faith"]);
$createdAt = htmlspecialchars($_POST["createdAt"]);

$conn = IgniteHelper::db_connect();
$sql = "INSERT INTO users (uid, email, name, faith, createdAt, startedAt, fasts) VALUES ('$uid', '$email', '$name', '$faith', '$createdAt', NULL, NULL)";
if(!mysqli_query($conn, $sql)) {
	// ERROR 33: FAILED TO REGISTER USER
	IgniteHelper::db_close($conn);
	IgniteHelper::error(33, "Failed to register user.");
	exit;
}
IgniteHelper::db_close($conn);

$json = [
	'success' => '1',
	'uid' => $uid
];

header('Content-Type: application/json;charset=utf-8');
die(json_encode($json));
?>
