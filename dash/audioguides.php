<?php
define("IgniteDashboard", TRUE);

$title = "Audio Guides";
$page = 2;

require("master1.php");

if(isset($_POST['save'])) {
	$silentUpload = true;
	require("upload.php");
	
	$day = addslashes(htmlspecialchars($_POST['day']));
	$lang = addslashes(htmlspecialchars($_POST['lang']));
	$faith = addslashes(htmlspecialchars($_POST['faith']));
	$flags = addslashes(htmlspecialchars($_POST['flags']));
	$uploadedFiles = UploadHelper::upload(true, $_FILES);
	
	$conn = IgniteHelper::db_connect();
	if(IgniteHelper::setAudio($conn, $day, $lang, $faith, $flags, $uploadedFiles[0])) {
		echo('<script type="text/javascript">var save = '. $day .';</script>');
	}
	
	IgniteHelper::logActivity($conn, "bg1", "headphones", "$fullname edited audio for Day $day.", "");
	
	IgniteHelper::db_close($conn);
} else if(isset($_POST['delete_audio']) && strcmp($_POST['delete_audio'], "false") && isset($_POST['day']) && isset($_POST['lang']) && isset($_POST['faith']) && isset($_POST['flags'])) {
	$conn = IgniteHelper::db_connect();
	
	$__day = addslashes(htmlspecialchars($_POST['day']));
	$__lang = addslashes(htmlspecialchars($_POST['lang']));
	$__faith = addslashes(htmlspecialchars($_POST['faith']));
	$__flags = addslashes(htmlspecialchars($_POST['flags']));
	
	$sql = "UPDATE days SET audio=NULL WHERE day='$__day' AND lang='$__lang' AND religion='$__faith' AND flag='$__flags'";
	mysqli_query($conn, $sql);
	
	IgniteHelper::logActivity($conn, "bg4", "trash", "$fullname deleted audio for Day $__day.", "");
	
	IgniteHelper::db_close($conn);
}

?>

<div class="row mb-3"><div style="margin: 0 auto;"><h1>Choose Day</h1></div></div>

<?php
$day = 0;
for($i = 1; $i <= 4; $i++) {
?>
<div class="row">
	<?php
	for($j = 1; $j <= 10; $j++) {
		if(++$day > 40) break;
	?>
	<div style="margin: 5px auto;" class="col-5 col-xl-1">
		<a href="editaudio.php?day=<?php echo($day); ?>&lang=en&faith=0&flags=0">
			<div class="day-box text-center">
				<div class="day-box-inner">	
					<?php echo($day); ?>
				</div>
			</div>
		</a>
	</div>
	<?php
	}
	?>
</div>
<?php
}
?>



<?php
require("master2a.php");
?>
<script type="text/javascript">
if(typeof save !== 'undefined' && save) {
	swal("Saved!", "Audio for Day " + save + " has been saved.", "success", {timer: 2500});
}
</script>
<?php
require("master2b.php");
?>