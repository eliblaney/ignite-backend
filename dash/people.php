<?php
define("IgniteDashboard", TRUE);

$title = "People";
$page = 5;

require("master1.php");

$conn = IgniteHelper::db_connect();

$users = IgniteHelper::getAdminUsers($conn);
$app_users = IgniteHelper::getUsers($conn);
function compareUsers($a, $b) {
	return strcasecmp($a['name'], $b['name']);
}
usort($app_users, "compareUsers");

IgniteHelper::db_close($conn);
?>

<div class="row">
	<div style="margin: 0 auto; background-color: #fff; border-radius: 10px;" class="col-lg-6 text-center">
		<div class="card">
			<div class="card-body">
				<div class="d-sm-flex flex-wrap justify-content-between mb-4 align-items-center">
					<h4 class="header-title mb-0">Administrators</h4>
				</div>
				<div class="member-box">
					<?php
					$superuser = true;
					foreach($users as $u) {
						if($superuser) {
							$superuser = false;
							continue;
						}
						if(!IgniteHelper::hasPermission($u, "op") || !IgniteHelper::hasPermission($u, "reviewer")) {
							continue;
						}
					?>
					<div class="s-member">
						<div class="media align-items-center" style="text-align: left;">
							<img src="assets/images/users/<?php echo(htmlspecialchars($u['id']) .'.png'); ?>" class="pl-3 d-block ui-w-30 rounded-circle" alt="">
							<div class="media-body ml-5">
								<p><?php echo(htmlspecialchars($u['firstname']) .' '. htmlspecialchars($u['lastname'])); ?></p><span>Administrator</span>
							</div>
							<div class="tm-social">
								<?php // <a href="#"><i class="fa fa-phone"></i></a> ?>
								<a href="mailto:<?php echo(htmlspecialchars($u['email'])); ?>"><i class="fa fa-envelope"></i></a>
							</div>
						</div>
					</div>
					<?php
					}
					?>
				</div>
			</div>
			<div class="card-body">
				<div class="d-sm-flex flex-wrap justify-content-between mb-4 align-items-center">
					<h4 class="header-title mb-0">Reviewers</h4>
				</div>
				<div class="member-box">
					<?php
					$superuser = true;
					foreach($users as $u) {
						if($superuser) {
							$superuser = false;
							continue;
						}
						if(!IgniteHelper::hasPermission($u, "reviewer") || IgniteHelper::hasPermission($u, "op")) {
							continue;
						}
					?>
					<div class="s-member">
						<div class="media align-items-center" style="text-align: left;">
							<img src="assets/images/users/<?php echo(htmlspecialchars($u['id']) .'.png'); ?>" class="pl-3 d-block ui-w-30 rounded-circle" alt="">
							<div class="media-body ml-5">
								<p><?php echo(htmlspecialchars($u['firstname']) .' '. htmlspecialchars($u['lastname'])); ?></p><span>Reviewer</span>
							</div>
							<div class="tm-social">
								<?php // <a href="#"><i class="fa fa-phone"></i></a> ?>
								<a href="mailto:<?php echo(htmlspecialchars($u['email'])); ?>"><i class="fa fa-envelope"></i></a>
							</div>
						</div>
					</div>
					<?php
					}
					?>
				</div>
			</div>
			<div class="card-body">
				<div class="d-sm-flex flex-wrap justify-content-between mb-4 align-items-center">
					<h4 class="header-title mb-0">Users</h4>
				</div>
				<div class="member-box">
					<?php
					$superuser = true;
					foreach($app_users as $u) {
						/*
						if(IgniteHelper::hasPermission($u, "reviewer") || IgniteHelper::hasPermission($u, "op")) {
							continue;
						}
						 */
					?>
					<div class="s-member">
						<div class="media align-items-center" style="text-align: left;">
							<img src="assets/images/author/avatar.png" class="pl-3 d-block ui-w-30 rounded-circle" alt="">
							<div class="media-body ml-5">
							<p><?php echo(htmlspecialchars($u['name'])); ?></p><span>User since <?php echo(date('m/j/Y', strtotime($u['createdAt']))); ?></span>
							</div>
							<div class="tm-social">
								<?php // <a href="#"><i class="fa fa-phone"></i></a> ?>
								<a href="mailto:<?php echo(htmlspecialchars($u['email'])); ?>"><i class="fa fa-envelope"></i></a>
							</div>
						</div>
					</div>
					<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
</div>


<?php
require("master2.php");
?>
