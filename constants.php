<?php

class IgniteConstants {
	
	const API_VERSION = "v1";
	const API_LINK = "https://eliblaney.com/ignite/api/" . IgniteConstants::API_VERSION;
	
	// For app receiving reflection data
	const SECRET_TOKEN = "IGNITE_API_KEY";
	
	// MySQL details for tables "admin_users" and "days"
	const MYSQL_HOST = "MYSQL_HOST";
	const MYSQL_DB = "MYSQL_DB";
	const MYSQL_USER = "MYSQL_USER";
	const MYSQL_PASS = "MYSQL_PASS";

	// Dashboard settings
	
	// Okta details for admin dashboard login
	const OKTA_CLIENT_ID = "OKTA_CLIENT_ID";
	const OKTA_CLIENT_SECRET = "OKTA_CLIENT_SECRET";
	const OKTA_ISSUER = "OKTA_ISSUER";
	const OKTA_AUDIENCE = "api://default";
	const OKTA_CALLBACK = IgniteConstants::API_LINK . "/auth/callback.php";
	
	// Seconds before session will timeout
	const SESSION_TIMEOUT = 3600;
	
	// Maximum number of user notifications to show in menu
	const NOTIFICATIONS_MAX = 10;
	
	// Maximum number of user notifications to show on the Notifications page
	const NOTIFICATIONS_PAGE_MAX = 10;
	
	// Allowed file types to upload
	const ALLOWED_FILE_TYPES = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
	const ALLOWED_MEDIA_TYPES = array("mp3" => "audio/mp3"); //, "ogg" => "audio/ogg", "wav" => "audio/wav");
	
	// Maximum upload size (MB)
	const MAX_UPLOAD_SIZE = 10;
	
}

?>
