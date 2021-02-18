<?php
define("IgniteDashboard", TRUE);
define('__ROOT__', dirname(dirname(__FILE__)));
require_once(__ROOT__.'/helper.php');

$title = "Mail Blast";
$page = 7;

require("master1.php");

$conn = IgniteHelper::db_connect();

$num_all = IgniteHelper::getNumUsers($conn);
$num_active = IgniteHelper::getNumActiveUsers($conn);
$num_isolated = IgniteHelper::getNumIsolatedUsers($conn);

if(isset($_POST['blast']) && !empty($_POST['subject']) && !empty($_POST['message'])) {
	$users = [];
	$to = intval($_POST['to']);
	if($to == 1) {
		$users = IgniteHelper::getUsers($conn);
	} else if($to == 2) {
		$users = IgniteHelper::getActiveUsers($conn);
	} else if($to == 3) {
		$users = IgniteHelper::getIsolatedUsers($conn);
	} else if($to == 4) {
		$users = [['name'=>'Eli Blaney','email'=>'eliblaney@gmail.com']];
	} else {
		$error = "No selection";
	}

	$subject = $_POST['subject'];
	$message = preg_replace("/[\n\r]/", "</p><p>", $_POST['message']);

	$success = true;
	foreach($users as $u) {
		$success = IgniteHelper::email($u['email'], $u['name'], $subject, $message) && $success;
	}

	if(isset($error)) {
		echo '<div class="alert alert-danger" role="alert">';
		echo $error;
		echo '</div>';
	} else if($success) {
		echo '<div class="alert alert-success" role="alert">';
		echo 'Sent all messages successfully!';
		echo '</div>';
	} else {
		echo '<div class="alert alert-danger" role="alert">';
		echo 'Failed to send all messages successfully.';
		echo '</div>';
	}

}

IgniteHelper::db_close($conn);

?>

<h2>Mail Blast</h2>

<form action="" method="post">
<h4>Send to:</h4>
<select name="to" class="browser-default custom-select" style="max-width: 300px">
<option value="4">Only Eli</option>
<option value="1">All users (<?php echo $num_all; ?>)</option>
<option value="2">Active users (<?php echo $num_active; ?>)</option>
<option value="3">Isolated users (<?php echo $num_isolated; ?>)</option>
</select>
<h4 style="margin-top: 30px;">Subject:</h4>
<input name="subject" class="form-control rounded=0" style="max-width: 500px;" />
<h4 style="margin-top: 30px;">Message:</h4>
<p>Dear NAME,</p>
<textarea name="message" class="form-control rounded=0" style="max-width: 500px;">
</textarea>
<p>Sincerely, the Ignite Team.</p>

<button type="submit" name="blast" class="btn btn-primary">Send blast mail</button>

</form>

<?php
require("master2.php");
?>
