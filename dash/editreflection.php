<?php
define("IgniteDashboard", TRUE);

$title = "Reflections";
$page = 1;
$subpage = "Edit";
$parentlink = "reflections.php";

require("master1a.php");
?>

<link href="assets/css/bootstrap-markdown-editor.css" rel="stylesheet">

<style>
	#editor-options {
		height: 25px;
		line-height: 25px;
		text-align: center;
		margin: 10px;
	}
	.swal-button--nobody {
		background-color: #fff;
		color: #777;
		border: 1px solid #777;
	}
	.swal-button--nobody:hover {
		background-color: #f8f8f8 !important;
	}
</style>

<?php
require('master1b.php');

$showHelp = strcmp($settings[1], "false");

$conn = IgniteHelper::db_connect();

$user = IgniteHelper::getUser($conn, $_SESSION['id']);
$user_canedit = IgniteHelper::hasPermission($user, "edit");
$user_candelete = IgniteHelper::hasPermission($user, "delete");
$user_canassign = IgniteHelper::hasPermission($user, "assign");

$users = IgniteHelper::getAdminUsers($conn);

$day = htmlspecialchars($_GET['day']);
$lang = htmlspecialchars($_GET['lang']);
$faith = htmlspecialchars($_GET['faith']);
$flags = htmlspecialchars($_GET['flags']);

// A reflection is deletable if it is not a default reflection (en/0/0) and it has been saved to the database
$can_delete = true;

$d = IgniteHelper::getDay($conn, $day, $lang, $faith, $flags);
if(!$d) { $d = IgniteHelper::getDay($conn, $day, $lang, $faith, 0); $can_delete = false; } // If it got this far, it hasn't been saved yet
if(!$d) $d = IgniteHelper::getDay($conn, $day, $lang, 0, $flags);
if(!$d) $d = IgniteHelper::getDay($conn, $day, $lang, 0, 0);
if(!$d) $d = IgniteHelper::getDay($conn, $day, "en", $faith, $flags);
if(!$d) $d = IgniteHelper::getDay($conn, $day, "en", $faith, 0);
if(!$d) $d = IgniteHelper::getDay($conn, $day, "en", 0, $flags);
if(!$d) $d = IgniteHelper::getDay($conn, $day, "en", 0, 0);

$can_delete = $can_delete && ((strcmp($lang, "en")) || ($faith > 0) || ($flags > 0));

$assigned = IgniteHelper::getAssignment($conn, $day);
if($assigned) {
	if(!strcmp($assigned, '0')) {
		$assigned = false;
	} else {
		foreach($users as $u) {
			if(!strcmp($assigned, $u->id)) {
				$assigned = $u->firstname .' '. $u->lastname;
				break;
			}
		}
	}
}

IgniteHelper::db_close($conn);

if(!$d || $d == null) {
	$error = true;
	echo('<div class="alert alert-danger" role="alert">Could not load reflection data.</div>');
} else if(!isset($_GET['day']) || !isset($_GET['lang']) || !isset($_GET['faith']) || !isset($_GET['flags'])) {
	$error = true;
	echo('<div class="alert alert-danger" role="alert">Could not load reflection data.</div>');
}
if(!$error) {
?>

<form action="reflections.php" method="post">
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
			<a href="javascript:cancelReflectionEdit();" class="mx-2 px-3 py-2 btn btn-outline-secondary">Close</a>
			<?php } else { ?>
			<a href="javascript:cancelReflectionEdit();" class="mx-2 px-3 py-2 btn btn-outline-success">Close</a>
			<?php } ?>
			<div class="btn-group ml-5 px-3">
				<button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Faith</button>
				<div class="dropdown-menu">
					<a class="optionbtn dropdown-item <?php if($faith == 0) echo('active'); ?>" href="<?php echo("editreflection.php?day=$day&lang=$lang&faith=0&flags=$flags"); ?>">Catholic</a>
					<a class="optionbtn dropdown-item <?php if($faith == 1) echo('active'); ?>" href="<?php echo("editreflection.php?day=$day&lang=$lang&faith=1&flags=$flags"); ?>">Christian</a>
					<a class="optionbtn dropdown-item <?php if($faith == 2) echo('active'); ?>" href="<?php echo("editreflection.php?day=$day&lang=$lang&faith=2&flags=$flags"); ?>">Other</a>
					<a class="optionbtn dropdown-item <?php if($faith == 3) echo('active'); ?>" href="<?php echo("editreflection.php?day=$day&lang=$lang&faith=3&flags=$flags"); ?>">Nonreligious</a>
				</div>
			</div>
			<div class="btn-group mx-1">
				<button type="button" class="btn btn-outline-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Language</button>
				<div class="dropdown-menu">
					<a class="optionbtn dropdown-item <?php if(!strcmp("en", 0)) echo('active'); ?>" href="<?php echo("editreflection.php?day=$day&lang=en&faith=$faith&flags=$flags"); ?>">English</a>
					<a class="optionbtn dropdown-item <?php if(!strcmp("en", 0)) echo('active'); ?>" href="<?php echo("editreflection.php?day=$day&lang=es&faith=$faith&flags=$flags"); ?>">Spanish</a>
				</div>
			</div>
			<?php if($user_canassign) { ?>
			<a href="javascript:assign(<?php echo($day); ?>);" id="assignbtn" class="mx-2 px-3 py-2 btn btn-outline-warning">
			<?php if($assigned) echo("Assigned to ". $assigned);
					else echo("Assign"); ?>
			</a>
			<?php } ?>
			<?php if($can_delete && $user_canedit && $user_candelete) { ?>
			<a href="javascript:deleteReflection(<?php echo($d->id); ?>);" class="mx-2 px-3 py-2 btn btn-outline-danger">Delete</a>
			<?php } ?>
		</div>
	</div>
	<div class="row">
		<div class="col-sm-<?php if($showHelp) echo('8'); else echo('12'); ?>">
			<textarea name="text" id="reflectionsEditor"><?php echo($d->content); ?></textarea>
		</div>
		<?php if($user_canedit && $showHelp) { ?>
		<div class="col-sm-4">
			<div class="card my-3">
				<div class="card-body">
					<h5 class="card-title">How do I use this?</h5>
					<p class="card-text">This editor uses Markdown, for quick and easy writing. Click below to learn how to write in Markdown.</p>
					<a href="https://commonmark.org/help/" target="_blank" class="btn btn-outline-primary">Learn more</a>
				</div>
			</div>
			<div class="card my-3">
				<div class="card-body">
					<h5 class="card-title">Want more?</h5>
					<p class="card-text">You can copy/paste Markdown from more advanced editors such as Dillinger or StackEdit.</p>
					<a href="https://dillinger.io/" target="_blank" class="btn btn-outline-primary">Open Dillinger</a>
				</div>
			</div>
		</div>
		<?php } ?>
	</div>
</form>


<?php
}

require("master2a.php");
?>

<script src="assets/js/ace/ace.js"></script>
<script src="assets/js/bootstrap-markdown-editor.js"></script>
<script type="text/javascript">
	$('#reflectionsEditor').markdownEditor({
  theme: "github",
  fullscreen: false,
  preview: true,
  onPreview: function (content, callback) {

    $.ajax({
      url: 'parser.php',
      type: 'POST',
      dataType: 'html',
      data: {content: content},
    })
    .done(function(result) {
      callback(result);
    });

  },
  imageUpload: true,
  uploadPath: 'upload.php',
  height: '500px',
});
</script>
<script type="text/javascript">
	var override = false;

	function unloadPage() {
		if(!override){
			return "You have unsaved changes on this page. Are you sure you want to leave?";
		}
	}

	window.onbeforeunload = unloadPage;

	function setOverride() {
		override = true;
	}

	$("#save").click(function() {
		setOverride();
	});

	$(".optionbtn").click(function() {
		setOverride();
	});

	function cancelReflectionEdit() {
		setOverride();
		window.location = "reflections.php";
	}

	<?php if($can_delete && $user_canedit && $user_candelete) { ?>
	function deleteReflection(dayid) {
		swal("Delete", "Are you sure you want to delete this reflection variation?", {
			dangerMode: true,
			buttons: true,
		}).then((value) => {
			switch(value) {
				case true:
					var request = new XMLHttpRequest();
					request.open('POST', 'reflections.php', true);
					request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');


					request.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200) {
							setOverride();
							window.location = "reflections.php";
						}
					};

					request.onerror = function() {
						// There was a connection error of some sort
						console.log("WARNING: Could not delete reflection");
					};

					request.send('delete_reflection=true&id=' + dayid);
					break;
				default:
					break;
			}
		});
	}
	<?php } ?>

	<?php if($user_canassign) { ?>
	function assign(dayid) {
		<?php
		echo('var users = {');
		foreach($users as $u) {
			echo("'$u->id':'$u->firstname $u->lastname',");
		}
		echo('}');
		?>

		swal({
			title: "Assign Reflection",
			text: "Choose who you would like to assign this reflection to.",
			buttons: {
				<?php
				$superuser = true;
				foreach($users as $u) {
					if($superuser) {
						$superuser = false;
						continue;
					}
					if(IgniteHelper::hasPermission($u->permissions, "edit")) {
						echo("user". uniqid() . htmlspecialchars($u->firstname . $u->lastname) .':{"text":"'. htmlspecialchars($u->firstname) .' '. htmlspecialchars($u->lastname) .'","value":"'. htmlspecialchars($u->id) .'"},
');
					}
				}
				?>

				nobody: {
					text: 'Nobody',
					value: '0'
				},
				cancel: true
			},
			closeOnEsc: true,
			closeOnClickOutside: true
		}).then((value) => {
			if(value != null) {
				var request = new XMLHttpRequest();
				request.open('POST', 'reflections.php', true);
				request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');


				request.onreadystatechange = function() {
					if (this.readyState == 4 && this.status == 200) {
						if(value == 0) {
							swal('Success', "You have successfully unassigned this reflection.", "success", {timer: 2500});
							document.getElementById('assignbtn').innerText = "Assign";
						} else {
							swal('Success', "You have successfully assigned this reflection to " + users['' + value], "success", {timer: 2500});
							document.getElementById('assignbtn').innerText = "Assigned to " + users['' + value];
						}
					}
				};

				request.onerror = function() {
					// There was a connection error of some sort
					console.log("WARNING: Could not assign reflection");
				};

				request.send('assign_reflection=true&dayid=' + dayid + '&user=' + value);
			}
		});
	}
	<?php } ?>


</script>
<?php
require("master2b.php");
?>
