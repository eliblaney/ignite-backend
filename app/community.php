<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/constants.php');
require_once(__ROOT__.'/helper.php');

if(empty($_POST["cltToken"])) {
	// ERROR 0: INVALID CLIENT TOKEN
	IgniteHelper::error(0, "Invalid request.");
	exit;
}

$cltToken = $_POST["cltToken"];
if($cltToken !== IgniteConstants::SECRET_TOKEN) {
	// ERROR 0: INVALID CLIENT TOKEN
	IgniteHelper::error(0, "Invalid request.");
	exit;
}

$err = 0;

if(empty($_POST["action"])) {
	// ERROR 1: MISSING ARGUMENTS
	$err = 1;
	IgniteHelper::error($err, "Missing arguments.");
	exit;
}

$action = $_POST["action"];
/* Available actions:
 * geti(id)				-- get community from `id`
 * getj(joincode)		-- get community from `joincode`
 * init(name, user)*	-- create community named `name` with unique joincode with owner `user`
 * ignite(id, start)*	-- set retreat start date for community `id`
 * join(id, user)*		-- add `user` to community `id`, and sets the community column for `user`
// chat(id, user, msg)	-- (disabled) submit chat message `msg` from `user` to community `id`
 * delete(id)*			-- delete community `id`, only owner can do this
 * remove(id, user)*	-- remove `user` from community `id`, only owner should be able to do this
 *                         * can only be called before the community ignites with ignite(id)
 */
$valid_actions = array('geti', 'getj', 'init', 'ignite', 'join', 'delete', 'remove');
if(!in_array($action, $valid_actions)) {
	// ERROR 34: INVALID ACTION
	IgniteHelper::error(34, "Invalid action");
	exit;
}

if($action === "geti") {

	if(empty($_POST["id"])) {
		// ERROR 2: MISSING ARGUMENTS
		IgniteHelper::error(2, "Missing arguments.");
		exit;
	}

	$conn = IgniteHelper::db_connect();

	$id = addslashes(htmlspecialchars($_POST['id']));

	$sql = "SELECT * FROM communities WHERE id='$id'";
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

	// ERROR 35: COULD NOT FIND COMMUNITY
	IgniteHelper::db_close($conn);
	IgniteHelper::error(35, "Could not find community.");
	exit;
} // geti

if($action === "getj") {

	if(empty($_POST["joincode"])) {
		IgniteHelper::error(2, "Missing arguments.");
		exit;
	}	

	$joinCode = addslashes(htmlspecialchars($_POST["joincode"]));

	$conn = IgniteHelper::db_connect();
	
	$sql = "SELECT * FROM communities WHERE joincode='$joinCode'";
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

	// ERROR 35: COULD NOT FIND COMMUNITY
	IgniteHelper::db_close($conn);
	IgniteHelper::error(35, "Could not find community.");
	exit;
} // getj

if($action === "init") {

	if(empty($_POST["name"])) {
		$err = $err | 2;
	}

	if(empty($_POST["user"])) {
		$err = $err | 4;
	}

	if($err > 0) {
		// ERROR 2 ERROR 4 ERROR 6: MISSING ARGUMENTS
		// if errors 3, 5, or 7 show up here then that's really weird
		IgniteHelper::error($err, "Missing arguments.");
		exit;
	}	

	$name = addslashes(htmlspecialchars($_POST["name"]));
	$uid = addslashes(htmlspecialchars($_POST["user"]));

	$conn = IgniteHelper::db_connect();

	$user = IgniteHelper::getAppUser($conn, $uid);

	if(!$user) {
		// ERROR 38: NO USER FOUND
		IgniteHelper::error(38, "No user found");
		exit;
	}

	$members = json_encode(array($uid));
	$joincode = IgniteHelper::uniqueJoinCode($conn);

	$sql = "INSERT INTO communities (name, members, createdAt, joincode) VALUES ('$name', '$members', '". str_replace('+00:00', 'Z', gmdate('c')) ."', '$joincode')";
	$result = mysqli_query($conn, $sql);
	if($result) {
		$sql = "SELECT id FROM communities WHERE joincode='$joincode'";
		$result = mysqli_query($conn, $sql);
		if(mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_assoc($result)) {
				$id = $row['id'];
				$user_id = $user['id'];
				$sql = "UPDATE users SET community='$id' WHERE id='$user_id'";
				$success = mysqli_query($conn, $sql);
				$row['success'] = $success ? '1' : '0';
				
				IgniteHelper::db_close($conn);
				header('Content-Type: application/json;charset=utf-8');
				die(json_encode($row));
				exit;
			}
		} else {
			// ERROR 35: COULD NOT FIND COMMUNITY
			IgniteHelper::db_close($conn);
			IgniteHelper::error(35, "Could not find community.");
			exit;
		}
	}

	// ERROR 36: COULD NOT CREATE COMMUNITY
	IgniteHelper::db_close($conn);
	IgniteHelper::error(36, "Could not create community.");
	exit;
} // init

if($action === "ignite") {

	if(empty($_POST["id"])) {
		// ERROR 2: MISSING ARGUMENTS
		IgniteHelper::error(2, "Missing arguments.");
		exit;
	}

	if(empty($_POST["start"])) {
		// ERROR 4: MISSING ARGUMENTS
		IgniteHelper::error(4, "Missing arguments.");
		exit;
	}

	$conn = IgniteHelper::db_connect();

	$id = addslashes(htmlspecialchars($_POST['id']));
	$start = addslashes(htmlspecialchars($_POST['start']));

	/*
	 * This function is called when the group leader sets
	 * the start date, not necessarily on the day that the
	 * retreat starts. So keep the join code.
	 *
	// remove join code
	$sql = "UPDATE communities SET joincode=NULL WHERE id='$id'";
	$result = mysqli_query($conn, $sql);

	if(!$result) {
		// ERROR 38: COULD NOT REMOVE JOIN CODE
		IgniteHelper::db_close($conn);
		IgniteHelper::error(38, "Could not remove join code.");
		exit;
	}
	 */

	// each member -> set startedAt to now
	$sql = "SELECT members FROM communities WHERE id='$id'";
	$result = mysqli_query($conn, $sql);
	if(mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);

		// members should be [user1uid, user2uid, user3uid, ...]
		$members = json_decode($row['members']);

		$success = true;
		foreach($members as $m) {
			// $m == user uid
			$u = IgniteHelper::getAppUser($conn, $m);	
			$u_id = $u['id'];

			$sql = "UPDATE users SET startedAt='$start' WHERE id='$u_id'";
			$success = mysqli_query($conn, $sql) && $success;
		}

		$sql = "UPDATE communities SET startedAt='$start' WHERE id='$id'";
		$success = mysqli_query($conn, $sql) && $success;

		$row['success'] = $success;

		IgniteHelper::db_close($conn);
		header('Content-Type: application/json;charset=utf-8');
		die(json_encode($row));
		exit;
	} else {
		// ERROR 35: COULD NOT FIND COMMUNITY
		IgniteHelper::db_close($conn);
		IgniteHelper::error(35, "Could not find community.");
		exit;
	}
} // ignite

if($action === "join") {

	if(empty($_POST["id"])) {
		$err = $err | 2;
	}

	if(empty($_POST["user"])) {
		$err = $err | 4;
	}

	if($err > 0) {
		// ERROR 2 ERROR 4 ERROR 6: MISSING ARGUMENTS
		// if errors 3, 5, or 7 show up here then that's really weird
		IgniteHelper::error($err, "Missing arguments.");
		exit;
	}	

	$conn = IgniteHelper::db_connect();

	$id = addslashes(htmlspecialchars($_POST['id']));
	$userid = addslashes(htmlspecialchars($_POST['user']));

	$user = IgniteHelper::getAppUser($conn, $userid);

	if(!$user) {
		// ERROR 38: NO USER FOUND
		IgniteHelper::error(38, "No user found");
		exit;
	}

	$sql = "SELECT members, startedAt FROM communities WHERE id='$id'";
	$result = mysqli_query($conn, $sql);
	if(mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);

		// members should be [user1uid, user2uid, user3uid, ...]
		$members = json_decode($row['members']);
		// if group leader already set start date, we need that
		$startedAt = $row['startedAt'];

		// add new user
		$members[] = $userid;
		$json_members = json_encode($members);

		$sql = "UPDATE communities SET members='$json_members' WHERE id='$id'";

		$success = mysqli_query($conn, $sql);

		$user_id = $user['id'];
		$sql = "UPDATE users SET community='$id', startedAt='$startedAt' WHERE id='$user_id'";
		$success = mysqli_query($conn, $sql) && $success;

		if($success) {
			$row['success'] = '1';
			$row['members'] = json_encode($members);
			$row['startedAt'] = json_encode($startedAt);
			IgniteHelper::db_close($conn);
			header('Content-Type: application/json;charset=utf-8');
			die(json_encode($row));
			exit;
		} else {
			// ERROR 37: COULD NOT JOIN USER TO COMMUNITY
			IgniteHelper::db_close($conn);
			IgniteHelper::error(37, "Could not join user to community.");
			exit;
		}
	} else {
		// ERROR 35: COULD NOT FIND COMMUNITY
		IgniteHelper::db_close($conn);
		IgniteHelper::error(35, "Could not find community.");
		exit;
	}

	// should not reach this point, but just in case:
	IgniteHelper::db_close($conn);

} // join

if($action === "chat") {
	// disabled. in the future, can be used to record chat history if desired
} // chat

if($action === "delete") {

	if(empty($_POST["id"])) {
		// ERROR 2: MISSING ARGUMENTS
		IgniteHelper::error(2, "Missing arguments.");
		exit;
	}

	$conn = IgniteHelper::db_connect();

	$id = addslashes(htmlspecialchars($_POST['id']));

	$sql = "DELETE FROM communities WHERE id='$id'";
	$success = mysqli_query($conn, $sql);

	$obj = new stdClass();
	$obj->success = $success ? '1' : '0';
	$json = json_encode($obj);
	IgniteHelper::db_close($conn);
	die($json);
	exit;
} // delete

if($action === "remove") {

	if(empty($_POST["id"])) {
		$err = $err | 2;
	}

	if(empty($_POST["user"])) {
		$err = $err | 4;
	}

	if($err > 0) {
		// ERROR 2 ERROR 4 ERROR 6: MISSING ARGUMENTS
		// if errors 3, 5, or 7 show up here then that's really weird
		IgniteHelper::error($err, "Missing arguments.");
		exit;
	}	

	$conn = IgniteHelper::db_connect();

	$id = addslashes(htmlspecialchars($_POST['id']));
	$userid = addslashes(htmlspecialchars($_POST['user']));

	$user = IgniteHelper::getAppUser($conn, $userid);

	if(!$user) {
		// ERROR 38: NO USER FOUND
		IgniteHelper::error(38, "No user found");
		exit;
	}

	$sql = "SELECT members FROM communities WHERE id='$id'";
	$result = mysqli_query($conn, $sql);
	if(mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_assoc($result);

		// members should be [user1uid, user2uid, user3uid, ...]
		$members = json_decode($row['members']);

		// remove user by creating new list without the user. this way, we avoid null indices
		$newmembers = array();
		foreach($members as $m) {
			if($m === $userid) {
				continue;
			}
			$newmembers[] = $m;
		}

		$json_members = json_encode($newmembers);

		$sql = "UPDATE communities SET members='$json_members' WHERE id='$id'";

		$success = mysqli_query($conn, $sql);

		$user_id = $user['id'];
		$sql = "UPDATE users SET community=NULL WHERE id='$user_id'";
		$success = mysqli_query($conn, $sql) && $success;

		if($success) {
			$row['success'] = '1';
			$row['members'] = json_encode($newmembers);
			IgniteHelper::db_close($conn);
			header('Content-Type: application/json;charset=utf-8');
			die(json_encode($row));
			exit;
		} else {
			// ERROR 38: COULD NOT REMOVE USER FROM COMMUNITY
			IgniteHelper::db_close($conn);
			IgniteHelper::error(37, "Could not remove user from community.");
			exit;
		}
	} else {
		// ERROR 35: COULD NOT FIND COMMUNITY
		IgniteHelper::db_close($conn);
		IgniteHelper::error(35, "Could not find community.");
		exit;
	}

	// should not reach this point, but just in case:
	IgniteHelper::db_close($conn);

} // remove

// ERROR 999: UNKNOWN ERROR
IgniteHelper::error(999, "Unknown error");
?>
