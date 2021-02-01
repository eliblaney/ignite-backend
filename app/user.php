<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/constants.php');
require_once(__ROOT__.'/helper.php');

if(empty($_POST['cltToken'])) {
	// ERROR 0: INVALID CLIENT TOKEN
	IgniteHelper::error(0, 'Invalid request.');
	exit;
}

$cltToken = $_POST['cltToken'];
if($cltToken !== IgniteConstants::SECRET_TOKEN) {
	// ERROR 0: INVALID CLIENT TOKEN
	IgniteHelper::error(0, 'Invalid request.');
	exit;
}

$err = 0;

if(empty($_POST['action'])) {
	// ERROR 1: MISSING ARGUMENTS
	$err = 1;
	IgniteHelper::error($err, 'Missing arguments.');
	exit;
}

$action = $_POST['action'];
/* Available actions:
 * geti(id)							-- get user from `id`
 * getu(uid)						-- get user from `uid`
 * post(uid, date, day, priv, data)	-- create post `data` by user `uid` on date `date` with privacy `priv` on retreat day `day`
 * read(uid, day, page)				-- read all posts having privacy at least `priv` from perspective of user `uid` sorted by closest to retreat day `day`
 * delp(id)							-- delete post `id`
 * word(uid, word)					-- set user `uid` to have word `word`
 * susc(uid, data)					-- set binary suscipe `data` for user `uid`
 * exit(uid) 						-- leave current community
 * cont(uid, subject, message) 		-- send contact message
 */
if($action !== 'geti' && $action !== 'getu' && $action !== 'post' && $action !== 'read' && $action !== 'delp' && $action !== 'word' && $action !== 'susc' && $action !== 'exit' && $action !== 'cont') {
	// ERROR 34: INVALID ACTION
	IgniteHelper::error(34, 'Invalid action');
	exit;
}

if($action === 'geti') {

	if(empty($_POST['id'])) {
		// ERROR 2: MISSING ARGUMENTS
		IgniteHelper::error(2, 'Missing arguments.');
		exit;
	}

	$conn = IgniteHelper::db_connect();

	$id = addslashes(htmlspecialchars($_POST['id']));

	$sql = "SELECT * FROM users WHERE id='$id'";
	$result = mysqli_query($conn, $sql);

	if(mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			$row['success'] = '1';

			IgniteHelper::db_close($conn);
			header('Content-Type: application/json;charset=utf-8');
			die(json_encode($row));
			exit;
		}
	}

	// ERROR 38: NO USER FOUND
	IgniteHelper::db_close($conn);
	IgniteHelper::error(38, 'No user found.');
	exit;
} // geti

if($action === 'getu') {

	if(empty($_POST['uid'])) {
		// ERROR 2: MISSING ARGUMENTS
		IgniteHelper::error(2, 'Missing arguments.');
		exit;
	}

	$conn = IgniteHelper::db_connect();

	$id = addslashes(htmlspecialchars($_POST['uid']));

	$sql = "SELECT * FROM users WHERE uid='$id'";
	$result = mysqli_query($conn, $sql);

	if(mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			$row['success'] = '1';

			IgniteHelper::db_close($conn);
			header('Content-Type: application/json;charset=utf-8');
			die(json_encode($row));
			exit;
		}
	}

	// ERROR 38: NO USER FOUND
	IgniteHelper::db_close($conn);
	IgniteHelper::error(38, 'No user found.');
	exit;


} // getu

if($action === 'post') {

	$err = 0;

	if(empty($_POST['uid'])) {
		$err = $err | 2;
	}

	if(empty($_POST['date'])) {
		$err = $err | 4;
	}

	if(empty($_POST['day'])) {
		$err = $err | 4;
	}

	if(!isset($_POST['priv'])) {
		$err = $err | 8;
	}

	if(empty($_POST['data'])) {
		$err = $err | 16;
	}

	if($err > 0) {
		// ERROR 2 ERROR 4 ERROR 6 ERROR 8 ERROR 10 ERROR 12 ERROR 14 ERROR 16
		// ERROR 18 ERROR 20 ERROR 22 ERROR 24 ERROR 26 ERROR 28 ERROR 30:
		// MISSING ARGUMENTS
		IgniteHelper::error($err, 'Missing arguments.');
		exit;
	}

	$uid = addslashes(htmlspecialchars($_POST['uid']));
	$date = addslashes(htmlspecialchars($_POST['date']));
	$day = addslashes(htmlspecialchars($_POST['day']));
	$priv = addslashes(htmlspecialchars($_POST['priv']));
	$data = $_POST['data'];

	$conn = IgniteHelper::db_connect();

	$sql = "SELECT community FROM users WHERE uid='$uid'";
	$result = mysqli_query($conn, $sql);

	$community = false;
	if(mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);
		$community = $row['community'];
	}

	// if community is false, no user exists
	// if community is null, user hasn't started retreat
	// either way, they shouldn't be posting
	if(!$community || $community === null) {
		// ERROR 38: NO USER FOUND
		IgniteHelper::db_close($conn);
		IgniteHelper::error(38, 'No user found.');
		exit;
	}

	// check validity of base64 with strict decoding
	if(!base64_decode($data, true)) {
		// ERROR 39: INVALID BASE64 STRING
		IgniteHelper::error(39, 'Invalid base64 string');
		exit;
	}

	$sql = "INSERT INTO posts (user, date, day, privacy, community, data) VALUES ('$uid', '$date', '$day', '$priv', '$community', '$data')";
	$result = mysqli_query($conn, $sql);

	$success = $result ? '1':'0';
	$object = new stdClass();
	$object->success = $success;

	IgniteHelper::db_close($conn);
	header('Content-Type: application/json;charset=utf-8');
	die(json_encode($object));
	exit;
}

if($action === 'susc') {
	$err = 0;

	if(empty($_POST['uid'])) {
		$err = $err | 2;
	}

	if(empty($_POST['data'])) {
		$err = $err | 4;
	}

	if($err > 0) {
		// ERROR 2 ERROR 4 ERROR 6
		// MISSING ARGUMENTS
		IgniteHelper::error($err, 'Missing arguments.');
		exit;
	}

	$uid = addslashes(htmlspecialchars($_POST['uid']));
	$data = $_POST['data'];

	// check validity of base64 with strict decoding
	if(!base64_decode($data, true)) {
		// ERROR 39: INVALID BASE64 STRING
		IgniteHelper::error(39, 'Invalid base64 string');
		exit;
	}

	$conn = IgniteHelper::db_connect();

	$sql = "UPDATE users SET suscipe='$data' WHERE uid='$uid'";
	$result = mysqli_query($conn, $sql);

	$success = $result ? '1':'0';
	$object = new stdClass();
	$object->success = $success;

	IgniteHelper::db_close($conn);
	header('Content-Type: application/json;charset=utf-8');
	die(json_encode($object));
	exit;
}

if($action === 'read') {

	// number of posts to load for each page
	$itemsperpage = 5;

	$err = 0;

	if(empty($_POST['uid'])) {
		$err = $err | 2;
	}

	if(empty($_POST['day'])) {
		$err = $err | 4;
	}

	if(empty($_POST['page'])) {
		$err = $err | 8;
	}

	if($err > 0) {
		// ERROR 2 ERROR 4 ERROR 6 ERROR 8 ERROR 10 ERROR 12 ERROR 14: MISSING ARGUMENTS
		IgniteHelper::error($err, 'Missing arguments.');
		exit;
	}

	$uid = addslashes(htmlspecialchars($_POST['uid']));
	$day = addslashes(htmlspecialchars($_POST['day']));
	$page = addslashes(htmlspecialchars($_POST['page']));

	$conn = IgniteHelper::db_connect();

	$sql = "SELECT community FROM users WHERE uid='$uid'";
	$result = mysqli_query($conn, $sql);

	$community = false;
	if(mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);
		$community = $row['community'];
	}

	// if community is false, no user exists
	// if community is null, user hasn't started retreat
	// either way, they shouldn't have any posts
	if(!$community || $community === null) {
		// ERROR 38: NO USER FOUND
		IgniteHelper::db_close($conn);
		IgniteHelper::error(38, 'No user found.');
		exit;
	}

	$offset = ($page - 1) * $itemsperpage;

	// user has access if (1) they wrote it, or (2) it was written by someone in their community and has privacy >= 1, or (3) was written by someone with privacy >= 2
	// SQL UNION statements automatically filter to get distinct values
	$sql = "SELECT id, user, date, day, privacy, community, data, ($day-day) AS distance FROM posts WHERE user='$uid' UNION SELECT id, user, date, day, privacy, community, data, ($day-day) AS distance FROM posts WHERE privacy>='2' UNION SELECT id, user, date, day, privacy, community, data, ($day-day) AS distance FROM posts WHERE privacy>='1' AND community='$community' ORDER BY distance ASC LIMIT $offset,$itemsperpage";
	$result = mysqli_query($conn, $sql);

	if(mysqli_num_rows($result) > 0) {
		$object = new stdClass();
		$object->success = '1';
		$posts = array();
		while($row = mysqli_fetch_assoc($result)) {
			$posts[] = $row;
		}
		$object->posts = $posts;

		IgniteHelper::db_close($conn);
		header('Content-Type: application/json;charset=utf-8');
		die(json_encode($object));
		exit;
	}

	// ERROR 40: NO POST FOUND
	IgniteHelper::db_close($conn);
	IgniteHelper::error(40, 'No post found.');
	exit;
}

if($action === 'delp') {

	if(empty($_POST['id'])) {
		// ERROR 2: MISSING ARGUMENTS
		IgniteHelper::error(2, 'Missing arguments.');
		exit;
	}

	$conn = IgniteHelper::db_connect();

	$id = addslashes(htmlspecialchars($_POST['id']));

	$sql = "DELETE FROM posts WHERE id='$id'";
	$result = mysqli_query($conn, $sql);

	$success = $result ? '1' : '0';
	$object = new stdClass();
	$object->success = $success;

	IgniteHelper::db_close($conn);
	header('Content-Type: application/json;charset=utf-8');
	die(json_encode($object));
	exit;
}

if($action === 'word') {

	$err = 0;

	if(empty($_POST['uid'])) {
		$err = $err | 2;
	}

	if(empty($_POST['word'])) {
		$err = $err | 4;
	}

	if($err > 0) {
		// ERROR 2 ERROR 4 ERROR 6: MISSING ARGUMENTS
		IgniteHelper::error($err, 'Missing arguments.');
		exit;
	}

	$uid = addslashes(htmlspecialchars($_POST['uid']));
	$word = addslashes(htmlspecialchars($_POST['word']));

	$conn = IgniteHelper::db_connect();

	$sql = "SELECT word FROM users WHERE uid='$uid'";
	$result = mysqli_query($conn, $sql);

	if(mysqli_num_rows($result) < 1) {
		// ERROR 38: NO USER FOUND
		IgniteHelper::db_close($conn);
		IgniteHelper::error(38, 'No user found.');
		exit;
	}

	$sql = "UPDATE users SET word='$word' WHERE uid='$uid'";
	$result = mysqli_query($conn, $sql);

	$success = $result ? '1':'0';
	$object = new stdClass();
	$object->success = $success;

	IgniteHelper::db_close($conn);
	header('Content-Type: application/json;charset=utf-8');
	die(json_encode($object));
	exit;
}

if($action === 'exit') {

	$err = 0;

	if(empty($_POST['uid'])) {
		$err = $err | 2;
	}

	if($err > 0) {
		// ERROR 2: MISSING ARGUMENTS
		IgniteHelper::error($err, 'Missing arguments.');
		exit;
	}

	$uid = addslashes(htmlspecialchars($_POST['uid']));
	$conn = IgniteHelper::db_connect();
	$success = true;


	// Remove from members list of all participating communities
	$communities = [];
	$sql = "SELECT name, id, members FROM communities WHERE members LIKE '%$uid%'";
	$result = mysqli_query($conn, $sql);
	if(mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_assoc($result)) {
			$communities[] = $row;
		}
	}
	foreach($communities as $c) {
		$members = json_decode($c['members'], true);
		$members = json_encode(array_values(array_diff($members, array($uid))));

		$id = $c['id'];
		$sql = "UPDATE communities SET members='$members' WHERE id='$id'";
		$result = mysqli_query($conn, $sql);
		$success = $success && $result;
	}

	// Set community to NULL
	$sql = "UPDATE users SET community=NULL WHERE uid='$uid'";
	$result = mysqli_query($conn, $sql);
	$success = $success && $result;

	IgniteHelper::db_close($conn);
	header('Content-Type: application/json;charset=utf-8');
	die(json_encode(['success' => $success ? '1':'0']));
	exit;
}

if($action === 'cont') {

	$err = 0;

	if(empty($_POST['uid'])) {
		$err = $err | 2;
	}

	if(empty($_POST['subject'])) {
		$err = $err | 4;
	}

	if(empty($_POST['message'])) {
		$err = $err | 8;
	}

	if($err > 0) {
		// ERROR 2 ERROR 4 ERROR 6 ERROR 8: MISSING ARGUMENTS
		IgniteHelper::error($err, 'Missing arguments.');
		exit;
	}

	$uid = addslashes(htmlspecialchars($_POST['uid']));
	$subject = addslashes(htmlspecialchars($_POST['subject']));
	$message = str_replace("\n", "</p><p>", addslashes(htmlspecialchars($_POST['message'])));
	$conn = IgniteHelper::db_connect();

	$user = IgniteHelper::getAppUser($conn, $uid);

	if(!$user) {
		// ERROR 38: NO USER FOUND
		IgniteHelper::error(38, "No user found");
		exit;
	}

	$uname = $user['name'];
	$uemail = $user['email'];

	// TODO: Move to config
	$admins = [
		'Eli Blaney <eliblaney@gmail.com>',
		'Paul Martin <ptm45786@creighton.edu>'
	];

	$success =IgniteHelper::email(implode(',', $admins), 'Ignite Team', "Ignite Message from ${uname}",
		"${uname} has reached out to Ignite using the contact form in the app. Here are the details:</p><p><strong>From:</strong> ${uname}, <${uemail}></p><p><strong>Subject:</strong> ${subject}</p><p><strong>Message: </strong></p><p>${message}"
	);

	IgniteHelper::db_close($conn);
	header('Content-Type: application/json;charset=utf-8');
	die(json_encode(['success' => $success ? '1':'0']));
	exit;
}

// ERROR 999: UNKNOWN ERROR
IgniteHelper::error(999, 'Unknown error');
?>
