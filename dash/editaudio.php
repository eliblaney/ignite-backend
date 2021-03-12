<?php
define("IgniteDashboard", TRUE);

$title = "Audio Guides";
$page = 2;
$subpage = "Edit";
$parentlink = "audioguides.php";

require("master1a.php");
?>

<link href="css/bootstrap3_player.css" rel="stylesheet">

<?php
require('master1b.php');

$conn = IgniteHelper::db_connect();

$user = IgniteHelper::getUser($conn, $_SESSION['id']);
$user_canedit = IgniteHelper::hasPermission($user, "edit");
$user_candelete = IgniteHelper::hasPermission($user, "delete");

$users = IgniteHelper::getUsers($conn);

$day = htmlspecialchars($_GET['day']);
$lang = htmlspecialchars($_GET['lang']);
$faith = htmlspecialchars($_GET['faith']);
$flags = htmlspecialchars($_GET['flags']);

// A reflection is deletable if it is not a default reflection (en/0/0) and it has been saved to the database
$can_delete = true;

$d = IgniteHelper::getAudio($conn, $day, $lang, $faith, $flags);
if(!$d) { $d = IgniteHelper::getAudio($conn, $day, $lang, $faith, 0); $can_delete = false; } // If it got this far, it hasn't been saved yet
if(!$d) $d = IgniteHelper::getAudio($conn, $day, $lang, 0, $flags);
if(!$d) $d = IgniteHelper::getAudio($conn, $day, $lang, 0, 0);
if(!$d) $d = IgniteHelper::getAudio($conn, $day, "en", $faith, $flags);
if(!$d) $d = IgniteHelper::getAudio($conn, $day, "en", $faith, 0);
if(!$d) $d = IgniteHelper::getAudio($conn, $day, "en", 0, $flags);
if(!$d) $d = IgniteHelper::getAudio($conn, $day, "en", 0, 0);

IgniteHelper::db_close($conn);

/* if($d == null) {
	$error = true;
	echo('<div class="alert alert-danger" role="alert">Could not load audio data.</div>');
} else */ if(!isset($_GET['day']) || !isset($_GET['lang']) || !isset($_GET['faith']) || !isset($_GET['flags'])) {
	$error = true;
	echo('<div class="alert alert-danger" role="alert">Could not load audio data.</div>');
}

if(!$error) {
?>

<form action="audioguides.php" method="post" enctype="multipart/form-data">
	<div class="row mb-3 ml-2">
		<div id="editor-options">
			<?php if($user_canedit) { ?>
			<input id="save" name='save' type="submit" class="mr-2 px-5 py-2 btn btn-outline-success" value="Save">
			<?php } ?>
			<input type="hidden" name="day" value="<?php echo($day); ?>" />
			<input type="hidden" name="lang" value="<?php echo($lang); ?>" />
			<input type="hidden" name="faith" value="<?php echo($faith); ?>" />
			<input type="hidden" name="flags" value="<?php echo($flags); ?>" />
			<?php if($user_canedit) { ?>
			<a href="audioguides.php" class="mx-2 px-3 py-2 btn btn-outline-secondary">Close</a>
			<?php } else { ?>
			<a href="audioguides.php" class="mx-2 px-3 py-2 btn btn-outline-success">Close</a>
			<?php } ?>
			<div class="btn-group ml-5 px-3">
				<button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Faith</button>
				<div class="dropdown-menu">
					<a class="optionbtn dropdown-item <?php if($faith == 0) echo('active'); ?>" href="<?php echo("editaudio.php?day=$day&lang=$lang&faith=0&flags=$flags"); ?>">Catholic</a>
					<a class="optionbtn dropdown-item <?php if($faith == 1) echo('active'); ?>" href="<?php echo("editaudio.php?day=$day&lang=$lang&faith=1&flags=$flags"); ?>">Christian</a>
					<a class="optionbtn dropdown-item <?php if($faith == 2) echo('active'); ?>" href="<?php echo("editaudio.php?day=$day&lang=$lang&faith=2&flags=$flags"); ?>">Other</a>
					<a class="optionbtn dropdown-item <?php if($faith == 3) echo('active'); ?>" href="<?php echo("editaudio.php?day=$day&lang=$lang&faith=3&flags=$flags"); ?>">Nonreligious</a>
				</div>
			</div>
			<div class="btn-group mx-1">
				<button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Language</button>
				<div class="dropdown-menu">
					<a class="optionbtn dropdown-item <?php if(!strcmp("en", 0)) echo('active'); ?>" href="<?php echo("editaudio.php?day=$day&lang=en&faith=$faith&flags=$flags"); ?>">English</a>
					<a class="optionbtn dropdown-item <?php if(!strcmp("en", 0)) echo('active'); ?>" href="<?php echo("editaudio.php?day=$day&lang=es&faith=$faith&flags=$flags"); ?>">Spanish</a>
				</div>
			</div>
			<?php if($can_delete && $user_canedit && $user_candelete && $d) { ?>
			<a href="javascript:deleteAudio(<?php echo("$day, '$lang', $faith, $flags"); ?>);" class="mx-2 px-3 py-2 btn btn-outline-danger">Delete</a>
			<?php } ?>
		</div>
	</div>
	<div class="row mt-4"><h3>Contemplation</h3></div>
	<div class="row mt-3">
		<div class="input-group col-sm-4">
		  	<div class="input-group-prepend">
				<span class="input-group-text" id="audiofileUpload">Upload</span>
		 	</div>
		 	<div class="custom-file">
				<input type="file" class="custom-file-input" name="audiofile" id="audiofile" accept="audio/mp3" aria-describedby="audiofileUpload">
				<label class="custom-file-label" for="audiofile" id="audiofileText">Choose audio</label>
			</div>
		</div>
	</div>
	<?php if($d) { ?>
	<div class="row m-4">
		<audio controls>
			<source src="<?php echo($d); ?>" type="audio/mpeg" />
			<a href="<?php echo($d); ?>">Click here to listen.</a>
        </audio>
	</div>
	<?php } ?>
</form>


<?php
}

require("master2a.php");
?>

<script src="js/bootstrap3_player.js"></script>

<script type="text/javascript">
	$(function() {
		$("#audiofile").change(function() {
			$("#audiofileText").html(document.getElementById("audiofile").files[0].name);
		});
	});
</script>

<?php if($can_delete && $user_canedit && $user_candelete) { ?>
<script type="text/javascript">
	function deleteAudio(day, lang, faith, flags) {
		swal("Delete", "Are you sure you want to delete this audio guide?", {
			dangerMode: true,
			buttons: true,
		}).then((value) => {
			switch(value) {
				case true:
					var request = new XMLHttpRequest();
					request.open('POST', 'audioguides.php', true);
					request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

					
					request.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200) {
							window.location = "audioguides.php";
						}
					};

					request.onerror = function() {
						// There was a connection error of some sort
						console.log("WARNING: Could not delete audio guide");
					};

					request.send('delete_audio=true&day=' + day + '&lang=' + lang + '&faith=' + faith + '&flags=' + flags);
					break;
				default:
					break;
			}
		});
	}
</script>

<?php
}

require("master2b.php");
?>
