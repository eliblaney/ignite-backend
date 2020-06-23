<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/helper.php');

if(!defined('IgniteDashboard')) {
	IgniteHelper::error(15, "Direct access not permitted");
	exit;
}

if(isset($_GET['refresh_session']) && $_GET['refresh_session'] !== false) {
	IgniteHelper::refresh_session();
}

IgniteHelper::session();

$fullname = $_SESSION['firstname'] .' '. $_SESSION['lastname'];

IgniteHelper::logincheck();

date_default_timezone_set('America/Chicago');

$conn = IgniteHelper::db_connect();

if(isset($_POST['update_setting']) && strcmp($_POST['update_setting'], "false") && isset($_POST['value'])) {
	$setting_num = addslashes(htmlspecialchars($_POST['update_setting']));
	$value = addslashes(htmlspecialchars($_POST['value']));
	
	IgniteHelper::setSetting($conn, $setting_num, $value);
}

$settings = IgniteHelper::getSettings($conn);

$activity = IgniteHelper::getActivity($conn, 20);

$notifications = IgniteHelper::getNotifications($conn);
$notificationsCount = 0;
if($notifications) {
	foreach($notifications as $n) {
		if(isset($n->unread) && !strcmp($n->unread, "true")) {
			$notificationsCount = $notificationsCount + 1;
		}
	}
} else {
	if($notifications === null || $notifications === false) {
		$warning = "Could not retreive notifications.";
	}
}

if(isset($_GET['clear_notifications']) && $_GET['clear_notifications'] !== false) {
	foreach($notifications as $n) {
		$n->unread = "false";
	}
	IgniteHelper::setNotifications($conn, $notifications);
}
if(isset($_GET['delete_notifications']) && $_GET['delete_notifications'] !== false) {
	$notifications = array();
	IgniteHelper::setNotifications($conn, $notifications);
}

$notifications = array_reverse($notifications);

IgniteHelper::db_close($conn);

?>
<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?php if(!$page) { echo("Ignite "); } echo $title; if(isset($subpage)) { echo(" — ". $subpage); } else if($page) { echo(" — Ignite"); }?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" type="image/png" href="assets/images/icon/favicon.ico">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/css/themify-icons.css">
    <link rel="stylesheet" href="assets/css/metisMenu.css">
    <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
    <link rel="stylesheet" href="assets/css/slicknav.min.css">
    <!-- amchart css -->
    <link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
    <!-- others css -->
    <link rel="stylesheet" href="assets/css/typography.css">
    <link rel="stylesheet" href="assets/css/default-css.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link rel="stylesheet" href="assets/css/ignite.css">
	<link rel="stylesheet" href="assets/css/markdown.css">
    <link rel="stylesheet" href="assets/css/sweetalert.css">
    <!-- modernizr css -->
    <script src="assets/js/vendor/modernizr-2.8.3.min.js"></script>