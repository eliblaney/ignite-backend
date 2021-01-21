<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/constants.php');

class IgniteHelper {

	static function error($code, $message) {
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'Ignite/') !== false) {
			header('Content-Type: application/json;charset=utf-8');
			$json = [
				'success' => '0',
				'error' => "$code",
				'message' => "$message"
			];
			die(json_encode($json));
		} else {
			header("Location: ../dash/error.php?code=$code&message=$message");
			exit;
		}
	}

	static function db_connect() {
		return mysqli_connect(IgniteConstants::MYSQL_HOST, IgniteConstants::MYSQL_USER, IgniteConstants::MYSQL_PASS, IgniteConstants::MYSQL_DB);
	}

	static function db_close($conn) {
		return mysqli_close($conn);
	}

	static function getAppUser($conn, $userid) {
		$userid = addslashes(htmlspecialchars($userid));
		$sql = "SELECT * FROM `users` WHERE uid='$userid'";
		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) > 0) {
			// output data of each row
			if($row = mysqli_fetch_assoc($result)) {
				return $row;
			}
		} else {
			return false;
		}
	}

	static function uniqueJoinCode($conn, $isPublic) {
		$isDuplicate = true;
		$joincode = "";
		$codeLength = 6;
		do {
			$joincode = substr(md5(microtime()), rand(0, 26), $codeLength);
			$sql = "SELECT id FROM communities WHERE joincode='$joincode'";
			$isDuplicate = mysqli_num_rows(mysqli_query($conn, $sql)) > 0;
		} while($isDuplicate);

		return $prefix . $joincode;
	}

	static function getUser($conn, $userid) {
		$sql = "SELECT * FROM `admin_users` WHERE id='$userid'";
		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) > 0) {
			// output data of each row
			if($row = mysqli_fetch_assoc($result)) {
				return json_decode('{"id":"'. $row['id'] .'","email":"'. $row['email'] .'","fname":"'. $row['firstname'] .'","lname":"'. $row['lastname'] .'","notifications":'. $row['notifications'] .',"permissions":'. $row['permissions'] .'}');
			}
		} else {
			return false;
		}
	}

	static function getUserById($conn, $id) {
		$sql = "SELECT email, firstname, lastname FROM `admin_users` WHERE id='$id'";
		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) > 0) {
			if($row = mysqli_fetch_assoc($result)) {
				return json_decode('{"email":"'. $row['email'] .'","firstname":"'. $row['firstname'] .'","lastname":"'. $row['lastname'] .'"}');
			}
		} else {
			return false;
		}
	}

	static function hasPermission($permissions, $p) {
		if(isset($permissions->op) && $permissions->op && strcmp($permissions->op, "false")) return true;
		return (isset($permissions->$p) && $permissions->$p && strcmp($permissions->$p, "false"));
	}

	static function session() {
		session_start();

		if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > IgniteConstants::SESSION_TIMEOUT)) {
			// last request was more than 1 hour ago
			session_unset();     // unset $_SESSION variable for the run-time
			session_destroy();   // destroy session data in storage
			header('Location: https://eliblaney.com/ignite/api/' . IgniteConstants::API_VERSION . '/auth/');
		}
		IgniteHelper::refresh_session();
	}

	static function refresh_session() {
		$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
		if (!isset($_SESSION['CREATED'])) {
			$_SESSION['CREATED'] = time();
		} else if (time() - $_SESSION['CREATED'] > IgniteConstants::SESSION_TIMEOUT) {
			// session started more than 1 hour ago
			session_regenerate_id(true);    // change session ID for the current session and invalidate old session ID
			$_SESSION['CREATED'] = time();  // update creation time
		}
	}

	static function logincheck() {
		if(!isset($_SESSION['login']) || $_SESSION['login'] == false) {
			if(isset($_SESSION['login'])) {
				session_unset();     // unset $_SESSION variable for the run-time
				session_destroy();   // destroy session data in storage
			}
			header('Location: https://eliblaney.com/ignite/api/' . IgniteConstants::API_VERSION . '/auth/');
			die('Please login first.');
			exit;
		}
	}

	static function getNotifications($conn) {
		return IgniteHelper::getNotificationsFor($conn, $_SESSION['id']);
	}

	static function getNotificationsFor($conn, $user) {
		$sql = "SELECT `notifications` FROM `admin_users` WHERE `id`='" . $user ."'";
		$result = mysqli_query($conn, $sql);

		if (mysqli_num_rows($result) > 0) {
			// output data of each row
			while($row = mysqli_fetch_assoc($result)) {
				return json_decode($row["notifications"]);
			}
		} else {
			return false;
		}
	}

	static function getSettings($conn) {
		return IgniteHelper::getSettingsFor($conn, $_SESSION['id']);
	}

	static function getSettingsFor($conn, $user) {
		$sql = "SELECT `settings` FROM `admin_users` WHERE `id`='" . $user ."'";
		$result = mysqli_query($conn, $sql);

		if (mysqli_num_rows($result) > 0) {
			// output data of each row
			while($row = mysqli_fetch_assoc($result)) {
				return json_decode($row["settings"]);
			}
		} else {
			return false;
		}
	}

	static function setSetting($conn, $setting_num, $value) {
		return IgniteHelper::setSettingFor($conn, $_SESSION['id'], $setting_num, $value);
	}

	static function setSettingFor($conn, $user, $setting_num, $value) {
		$settings = IgniteHelper::getSettingsFor($conn, $user);

		$settings[$setting_num] = $value;

		$sql = "UPDATE admin_users SET settings='" . addslashes(json_encode($settings)) . "' WHERE id=" . $user;
		mysqli_query($conn, $sql);
		return $sql;
	}

	static function setNotifications($conn, $notifications) {
		return IgniteHelper::setNotificationsFor($conn, $_SESSION['id'], $notifications);
	}

	static function setNotificationsFor($conn, $user, $notifications) {
		$sql = "UPDATE admin_users SET notifications='" . addslashes(json_encode($notifications)) . "' WHERE id=" . $user;
		mysqli_query($conn, $sql);
		return $sql;
	}

	static function sendNotification($conn, $user, $subject, $link, $classes) {
		$notifications = IgniteHelper::getNotificationsFor($conn, $user);
		$time = time();
		array_push($notifications, json_decode('{"unread":"true","subject":"'. $subject .'","timestamp":"'. $time. '","link":"'. $link .'","classes":"'. $classes .'"}'));
		return IgniteHelper::setNotificationsFor($conn, $user, $notifications);
	}

	static function getUsers($conn) {
		$sql = "SELECT id, email, firstname, lastname, permissions FROM `admin_users` WHERE 1";
		$result = mysqli_query($conn, $sql);
		$users = [];
		if (mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_assoc($result)) {
				array_push($users, json_decode('{"id":"'. $row['id'] .'","email":"'. $row['email'] .'","firstname":"'. $row['firstname'] .'","lastname":"'. $row['lastname']  .'","permissions":'. $row['permissions'] .'}'));
			}
			return $users;
		} else {
			return false;
		}
	}

	static function getDayAny($conn, $day, $lang, $religion, $flag) {
		$d = IgniteHelper::getDay($conn, $day, $lang, $faith, $flags);
		if(!$d) $d = IgniteHelper::getDay($conn, $day, $lang, $faith, 0);
		if(!$d) $d = IgniteHelper::getDay($conn, $day, $lang, 0, $flags);
		if(!$d) $d = IgniteHelper::getDay($conn, $day, $lang, 0, 0);
		if(!$d) $d = IgniteHelper::getDay($conn, $day, "en", $faith, $flags);
		if(!$d) $d = IgniteHelper::getDay($conn, $day, "en", $faith, 0);
		if(!$d) $d = IgniteHelper::getDay($conn, $day, "en", 0, $flags);
		if(!$d) $d = IgniteHelper::getDay($conn, $day, "en", 0, 0);
		return $d;
	}

	static function getDay($conn, $day, $lang, $religion, $flag) {
		$sql = "SELECT id, content FROM `days` WHERE day='$day' AND lang='$lang' AND religion='$religion' AND flag='$flag'";
		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) > 0) {
			if($row = mysqli_fetch_assoc($result)) {
				return json_decode('{"id":"'. $row['id'] .'","day":"'. $day .'","content":"'. str_replace(array("\r\n", "\n"), '\\n', $row['content']) .'"}');
			}
		} else {
			return false;
		}
	}

	static function getDayById($conn, $id) {
		$sql = "SELECT id, day FROM `days` WHERE id='$id'";
		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) > 0) {
			if($row = mysqli_fetch_assoc($result)) {
				return $row['day'];
			}
		} else {
			return false;
		}
	}

	static function setDay($conn, $day, $lang, $religion, $flags, $content) {
		$sql = "";
		$c = addslashes(htmlspecialchars($content));
		$d = IgniteHelper::getDay($conn, $day, $lang, $religion, $flags);
		if($d) {
			// update
			$id = $d->id;
			$sql = "UPDATE days SET content='$c' WHERE id='$id'";

			$fullname = $_SESSION['firstname'] .' '. $_SESSION['lastname'];
			IgniteHelper::logActivity($conn, "bg1", "edit", "$fullname edited Day $day.", "");
		} else {
			// insert
			$sql = "INSERT INTO days (day, lang, religion, flag, content) VALUES('$day', '$lang', '$religion', '$flags', '$c')";

			$fullname = $_SESSION['firstname'] .' '. $_SESSION['lastname'];
			IgniteHelper::logActivity($conn, "bg2", "fire", "$fullname created a new Day $day variation.", "");
		}
		return mysqli_query($conn, $sql);
	}

	static function setAudio($conn, $day, $lang, $religion, $flags, $audio) {
		$sql = "";
		$a = htmlspecialchars($audio);
		$d = IgniteHelper::getDay($conn, $day, $lang, $religion, $flags);
		if($d) {
			// update
			$id = $d->id;
			$sql = "UPDATE days SET audio='$a' WHERE id='$id'";
		} else {
			// insert
			// content cannot be null, set to empty string
			$sql = "INSERT INTO days (day, lang, religion, flag, content, audio) VALUES('$day', '$lang', '$religion', '$flags', '', '$a')";
		}
		return mysqli_query($conn, $sql);
	}

	static function getAudio($conn, $day, $lang, $religion, $flags, $any = false) {
		$d = IgniteHelper::getDay($conn, $day, $lang, $religion, $flags);
		if($any) {
			$d = IgniteHelper::getDayAny($conn, $day, $lang, $religion, $flags);
		}
		$id = '-1';
		if($d) {
			$id = $d->id;
		} else {
			return false;
		}
		$sql = "SELECT audio FROM `days` WHERE id='$id'";
		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) > 0) {
			if($row = mysqli_fetch_assoc($result)) {
				return $row['audio'];
			}
		} else {
			return false;
		}
	}

	static function getData($conn, $key) {
		$sql = "SELECT data FROM `admin_data` WHERE name='$key'";
		$result = mysqli_query($conn, $sql);
		if (mysqli_num_rows($result) > 0) {
			if($row = mysqli_fetch_assoc($result)) {
				return json_decode($row['data']);
			}
		} else {
			return false;
		}
	}

	static function setData($conn, $key, $data) {
		$sql = "UPDATE admin_data SET data='". addslashes(json_encode($data)) ."' WHERE name='". addslashes($key) ."'";
		return mysqli_query($conn, $sql);

	}

	static function getAssignment($conn, $dayid) {
		$assignments = IgniteHelper::getData($conn, "assignments");
		$day = ''. $dayid;
		if(isset($assignments->$day)) {
			return $assignments->$day;
		}
		return false;
	}

	static function getAssignments($conn, $userid) {
		$assignments = IgniteHelper::getData($conn, "assignments");
		$days = array();
		foreach($assignments as $day => $user) {
			if(!strcmp($userid, $user)) {
				array_push($days, $day);
			}
		}
		return $days;
	}

	static function logActivity($conn, $bg, $icon, $subject, $action) {
		$userid = $_SESSION['id'];
		$sql = "INSERT INTO admin_history (userid, timestamp, classes, subject, action) VALUES('$userid', '". time() ."', '{\"bg\":\"$bg\",\"icon\":\"$icon\"}', '$subject', '$action')";
		return mysqli_query($conn, $sql);
	}

	static function getActivity($conn, $limit) {
		$sql = "SELECT * FROM admin_history ORDER BY timestamp DESC LIMIT $limit";
		$result = mysqli_query($conn, $sql);
		$activity = [];
		if (mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_assoc($result)) {
				array_push($activity, json_decode('{"userid":"'. $row['userid'] .'","timestamp":"'. $row['timestamp'] .'","bg":"'. json_decode($row['classes'])->bg .'","icon":"'.json_decode($row['classes'])->icon . '","subject":"' . $row['subject'] . '","action":"' . $row['action'] .'"}'));
			}
			return $activity;
		} else {
			return false;
		}
	}

	static function prettyDate($date) {
		$current = time();
		$datediff = $date - $current;

		$today = new DateTime("today", new DateTimeZone("America/Chicago"));

		$match_date = new DateTime(date("c", $date), new DateTimeZone("America/Chicago"));
		$match_date->setTime(0, 0, 0);

		$diff = $today->diff($match_date);
		$difference = (integer) $diff->format("%R%a"); // Extract days count in interval

		$t = date("n/j/y", htmlspecialchars($date));
		if($difference == 0) {
			$hours = floor(-$datediff/(60*60));
			if($hours < 7) {
				$minutes = floor(-$datediff/(60));
				if($minutes < 60) {
					if($minutes == 0) {
						$t = "Just now";
					} else if($minutes == 1) {
						$t = "$minutes minute ago";
					} else if($minutes < 0) {
						$t = "In the future";
					} else {
						$t = "$minutes minutes ago";
					}
				} else {
					if($hours == 1) {
						$t = "$hours hour ago";
					} else {
						$t = "$hours hours ago";
					}
				}
			} else {
				$t = date("g:ia", htmlspecialchars($date));
			}
		} else if($difference == -1) {
			$t = "Yesterday, " . date("g:ia", htmlspecialchars($date));
		} else if($difference > -7 && difference < 0) {
			$t = (-1 * $difference) . " days ago";
		} else if($difference == 1) {
			$t = "Tomorrow";
		} else if($difference > 1) {
			$t = "In the future";
		}

		return $t;
	}

	static function email($to, $name, $subject, $message, $html = true) {
		$from = "Ignite<ignite@eliblaney.com>";
		$headers = "";
		if($html) {
			$headers .= 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
		}
		$headers .= 'From: '.$from."\r\n".
					'Reply-To: '.$from."\r\n" .
					'X-Mailer: PHP/' . phpversion();

		if($html) {
			$content .= "<!DOCTYPE html> <html> <head> <link rel='preconnect' href='https://fonts.gstatic.com'> <link href='https://fonts.googleapis.com/css2?family=Raleway:wght@100;400&display=swap' rel='stylesheet'> <style> body { font-family: Raleway, sans-serif; } #header { height: 100px; display: flex; flex-direction: row; justify-content: flex-start; align-items: center; width: 100%; padding-bottom: 10px; margin-bottom: 20px; border-bottom: 1px solid #888; } #ignite { font-weight: 100; color: #111; } #logo { height: 100px; width: auto; border-radius: 25px; margin-right: 30px; } #contents { color: #1a1a1a; } #greeting, #farewell { line-height: 3em; } #footer { border-top: 1px solid #888; padding-top: 10px; margin-top: 20px; text-align: center; color: #777; } a { color: #5bf; } </style> </head> \r\n <body> <div id='header'> <img id='logo' src='https://eliblaney.com/ignite/images/IgniteLogo.png' /> <h1 id='ignite'>Ignite</h1> </div> <div id='contents'> <p id='greeting'> Dear $name, </p> <div id='message'> <p>\r\n";
			$content .= $message;
			$content .= "\r\n</p> </div> <p id='farewell'> Sincerely, the Ignite Team. </p> </div> <div id='footer'> <p> Ignite &copy; 2021. All rights reserved. </p> <p> Created by Eli Blaney and Paul Martin. </p> </div> </body> </html>";
		} else {
			$content = $message;
		}

		return mail($to, $subject, $content, $headers);
	}

}

?>
