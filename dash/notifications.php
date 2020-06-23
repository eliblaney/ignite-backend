<?php
define("IgniteDashboard", TRUE);

$title = "Notifications";
$page = 4;

require("master1.php");
?>

<div class="row">
	<div class="col-sm-9">
	<div style="margin: 0 auto; background-color: #fff; border-radius: 10px;" class="col-lg-6 text-center">
		<div class="nofity-list">
		   <?php
			if(sizeof($notifications) > 0) {
			$count = 0;
			foreach($notifications as $n) {
				if(++$count > IgniteConstants::NOTIFICATIONS_PAGE_MAX) {
					break;
				}
				?><a href="<?php echo(htmlspecialchars($n->link)); ?>" class="notify-item">
				<div class="notify-thumb"><i class="<?php echo(htmlspecialchars($n->classes)); ?>"></i></div>
				<div class="notify-text" style="text-align: left;">
					<!--<span style="font-size: 36px; color: #888; float: right;">></span>-->
					<i class="fa fa-angle-right" style="font-size: 32px; color: #888; float: right;"></i>
					<p class="<?php if(!strcmp($n->unread, "true")) { echo("new-notification"); } ?>"><?php echo(htmlspecialchars($n->subject)); ?></p>
					<span><?php
						$date = $n->timestamp;
						if(!strcmp($date, "424242")) {
							echo("Yay!"); // description text for celebratory notifications, replaces the date
						} else if(strcmp($date, "0")) {
							echo(IgniteHelper::prettyDate($date));
						}

						?></span>
				</div>
			</a>
			<?php }
			} else { ?>
				<p class="lead">You have no notifications.</p>
			<?php } ?>
		</div>
	</div>
	</div>
	<div class="col-sm-3" style="border-left: #888 solid 1px;">
		<a href="javascript:clearNotifications();"><div class="mt-3 mx-3 notification-setting-button">Mark notifications as read</div></a>
		<a href="javascript:deleteNotifications();"><div class="mt-3 mx-3 notification-setting-button">Clear notifications</div></a>
	</div>
</div>





<script type="text/javascript">
	function clearNotifications() {
		$("#notifications-count").css("display", "none");

		var request = new XMLHttpRequest();
		request.open('GET', 'index.php?clear_notifications=true', true);

		request.onloadend = function() {
			swal("All done", "You have marked all notifications as read.", "success");
		}
		
		request.onerror = function() {
		  // There was a connection error of some sort
			console.log("WARNING: Could not clear notifications");
		};

		request.send();
	}
	function deleteNotifications() {
		$("#notifications-count").css("display", "none");

		var request = new XMLHttpRequest();
		request.open('GET', 'index.php?delete_notifications=true', true);

		request.onloadend = function() {
			swal("All done", "You have cleared all of your notifications.", "success");
		}
		
		request.onerror = function() {
		  // There was a connection error of some sort
			console.log("WARNING: Could not delete notifications");
		};

		request.send();
	}
</script>

<?php
require("master2.php");
?>