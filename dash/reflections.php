<?php
define("IgniteDashboard", TRUE);

$title = "Reflections";
$page = 1;

require("master1.php");

if(isset($_POST['delete_reflection']) && strcmp($_POST['delete_reflection'], "false") && isset($_POST['id'])) {
	$conn = IgniteHelper::db_connect();
	
	$id = addslashes(htmlspecialchars($_POST['id']));
	
	$_day = IgniteHelper::getDayById($conn, $id);
	IgniteHelper::logActivity($conn, "bg4", "trash", "$fullname deleted a Day $_day variation.", "");
	
	$sql = "DELETE FROM days WHERE id='$id'";
	mysqli_query($conn, $sql);
	
	IgniteHelper::db_close($conn);
}

if(isset($_POST['assign_reflection']) && strcmp($_POST['assign_reflection'], "false") && isset($_POST['dayid']) && isset($_POST['user'])) {
	$dayid = addslashes(htmlspecialchars($_POST['dayid']));
	$user = addslashes(htmlspecialchars($_POST['user']));
	$conn = IgniteHelper::db_connect();
	
	$assignments = IgniteHelper::getData($conn, "assignments");
	$assignments->$dayid = $user;
	$assignments;
	IgniteHelper::setData($conn, "assignments", $assignments);
	
	if(strcmp($user, "0")) { // if the reflection is not being unassigned (i.e. being actually assigned to somebody)
		IgniteHelper::sendNotification($conn, $user, "New Assignment: Day $dayid", "reflections.php", "ti-flag btn-warning");
		
		$_day = IgniteHelper::getDayById($conn, $dayid);
		$_user = IgniteHelper::getUserById($conn, $user);
		$_user_fullname = $_user->firstname .' '. $_user->lastname;
		IgniteHelper::logActivity($conn, "bg3", "flag", "$fullname assigned Day $_day.", "Day $_day was assigned to $_user_fullname by $fullname.");
	} else {
		$_day = IgniteHelper::getDayById($conn, $dayid);
		IgniteHelper::logActivity($conn, "bg3", "flag", "$fullname unassigned Day $_day.", "");
	}
	
	IgniteHelper::db_close($conn);
}

if(isset($_POST['save'])) {
	$day = addslashes(htmlspecialchars($_POST['day']));
	$lang = addslashes(htmlspecialchars($_POST['lang']));
	$faith = addslashes(htmlspecialchars($_POST['faith']));
	$flags = addslashes(htmlspecialchars($_POST['flags']));
	$content = addslashes(htmlspecialchars($_POST['text']));
	
	$conn = IgniteHelper::db_connect();
	if(IgniteHelper::setDay($conn, $day, $lang, $faith, $flags, $content)) {
		echo('<script type="text/javascript">var save = '. $day .';</script>');
	}
	
	IgniteHelper::db_close($conn);
}

?>
<div class="row mb-3"><div style="margin: 0 auto;"><h1>Choose Day</h1></div></div>
<?php
$day = 0;
for($i = 1; $i <= 6; $i++) {
?>
<div class="row">
	<?php
	for($j = 1; $j <= 10; $j++) {
		if(++$day > 51) break;
	?>
	<div style="margin: 5px auto;" class="col-5 col-xl-1">
		<a href="editreflection.php?day=<?php echo($day); ?>&lang=en&faith=0&flags=0">
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
if(save) {
	swal("Saved!", "Content for Day " + save + " has been saved.", "success", {timer: 2500});
}
</script>
<?php
require("master2b.php");
?>
