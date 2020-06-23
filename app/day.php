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

if(empty($_POST["day"])) {
	$err = $err | 1;
}

if(empty($_POST["lang"])) {
	$err = $err | 2;
}

if(!isset($_POST["religion"])) {
	$err = $err | 4;
}

if($err > 0) {
	// ERROR 1 ERROR 2 ERROR 3 ERROR 4 ERROR 5 ERROR 6 ERROR 7: MISSING ARGUMENTS
	IgniteHelper::error($err, "Missing arguments.");
}

$flag = htmlspecialchars($_POST["flag"]);
if(empty($_POST["flag"])) {
	$flag = 0;
}

$day = htmlspecialchars($_POST["day"]);
$lang = htmlspecialchars($_POST["lang"]);
$religion = htmlspecialchars($_POST["religion"]);

$conn = IgniteHelper::db_connect();
$d = IgniteHelper::getDayAny($conn, $day, $lang, $religion, $flag);
if($d == null) {
	// ERROR 32: FAILED TO GET REFLECTION DATA
	IgniteHelper::error(32, "Failed to get reflection data.");
}
$audio = IgniteHelper::getAudio($conn, $day, $lang, $religion, $flag, true);
IgniteHelper::db_close($conn);

$json = [
	'success' => '1',
	'id' => $d->id,
	'day' => "$day",
	'lang' => "$lang",
	'religion' => "$religion",
	'flag' => "$flag",
	'content' => $d->content,
	'audio' => $audio 
];

header('Content-Type: application/json;charset=utf-8');
die(json_encode($json));
?>
