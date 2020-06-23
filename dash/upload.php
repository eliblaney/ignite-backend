<?php

define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/constants.php');

class UploadHelper {
	
	static function upload($audioguide, $files) {
		$folder = "images";
		if($audioguide) {
			$folder = "audioguides";
		}
		$uploadedFiles = array();
		
		foreach ($files as $file) {
			$allowed = IgniteConstants::ALLOWED_FILE_TYPES;
			if($audioguide) {
				$allowed = IgniteConstants::ALLOWED_MEDIA_TYPES;
			}
			$filename = uniqid() . $file["name"];
			$filetype = $file["type"];
			$filesize = $file["size"];
			if($file["error"]) {
				continue;
			}

			// Verify file extension
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			if(!array_key_exists($ext, $allowed)) {
				// Error: Not a valid file format
				continue;
			}

			// Verify file size - 5MB maximum
			$maxsize = IgniteConstants::MAX_UPLOAD_SIZE * 1024 * 1024;
			if($filesize > $maxsize)  {
				// Error: File size is larger than the allowed limit.
				continue;
			}

			// Verify MYME type of the file
			if(in_array($filetype, $allowed)){
				// Check whether file exists before uploading it
				if(!file_exists("$folder/" . $filename)){
					move_uploaded_file($file["tmp_name"], "$folder/" . urlencode($filename));
				}
				$uploadedFiles[] = IgniteConstants::API_LINK . "/dash/$folder/" . urlencode($filename);
			}
		}
		
		return $uploadedFiles;
	}
	
}

// Check if the form was submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
	$audioguide = isset($_POST['audioguide']) && $_POST['audioguide'] !== false;
    if (!empty($_FILES && (!isset($silentUpload) || !silentUpload))) {
		echo json_encode(UploadHelper::upload($audioguide, $_FILES));
	}
}
?>