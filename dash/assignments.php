<?php
define("IgniteDashboard", TRUE);

$title = "Assignments";
$page = 3;

require("master1.php");

$conn = IgniteHelper::db_connect();

$assignments = IgniteHelper::getAssignments($conn, $_SESSION['id']);

IgniteHelper::db_close($conn);
?>

<div class="row">
	<div style="margin: 0 auto; background-color: #fff; border-radius: 10px;" class="col-lg-6 text-center">
		<div class="card">
			<div class="card-body">
				<div class="d-sm-flex flex-wrap justify-content-between mb-4 align-items-center">
					<h4 class="header-title mb-0">Your Assignments</h4>
				</div>
					<a href="">
						<div class="member-box">
							<?php
							if(sizeof($assignments) > 0) {
							foreach($assignments as $day) { ?>
							<a href="editreflection.php?day=<?php echo($day); ?>&lang=en&faith=0&flags=0">
								<div class="s-member">
									<div class="media align-items-center" style="text-align: left;">
										<div class="media-body ml-5">
											<p>Day <?php echo($day); ?></p>
										</div>
										<div style="position: absolute; right: 0;" class="tm-social mr-5 pr-4">
											<i style="font-size: 32px; color: #888;" class="fa fa-angle-right"></i>
										</div>
									</div>
								</div>
							</a>
							<?php }
							} else { ?>
							<p class="lead">You have no active assignments.</p>
							<?php } ?>
						</div>
					</a>
			</div>
		</div>
	</div>
</div>


<?php
require("master2.php");
?>