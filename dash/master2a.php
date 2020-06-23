<?php
if(!defined('IgniteDashboard')) {
	define('__ROOT__', dirname(dirname(__FILE__)));
	require_once(__ROOT__.'/helper.php');
	
	IgniteHelper::error(15, "Direct access not permitted");
	exit;
}
?>
			</div>
        </div>
        <!-- main content area end -->
        <!-- footer area start-->
        <footer>
            <div class="footer-area">
				<p>&copy; 2019 Ignite. Designed by <a href="https://eliblaney.com">Pro Web Development</a>.</p>
            </div>
        </footer>
        <!-- footer area end-->
    </div>
    <!-- page container area end -->
    <!-- offset area start -->
    <div class="offset-area">
        <div class="offset-close"><i class="ti-close"></i></div>
        <ul class="nav offset-menu-tab">
            <li><a class="active" data-toggle="tab" href="#activity">Activity</a></li>
            <li><a data-toggle="tab" href="#settings">Settings</a></li>
        </ul>
        <div class="offset-content tab-content">
            <div id="activity" class="tab-pane fade in show active">
                <div class="recent-activity">
                  	<?php foreach($activity as $a) { ?>
                    <div class="timeline-task">
                        <div class="icon <?php echo($a->bg) ?>">
                            <i class="timeline-icon fa fa-<?php echo($a->icon) ?>"></i>
                        </div>
                        <div class="tm-title">
                            <h4><?php echo($a->subject) ?></h4>
                            <span class="time"><i class="ti-time"></i><?php echo(IgniteHelper::prettyDate($a->timestamp)); ?></span>
                        </div>
                        <p><?php echo($a->action) ?></p>
                    </div>
                    <? } ?>
                </div>
            </div>
            <div id="settings" class="tab-pane fade">
                <div class="offset-settings">
                    <h4>General Settings</h4>
                    <div class="settings-list">
                        <div class="s-settings">
                            <div class="s-sw-title">
                                <h5>Email Notifications</h5>
                                <div class="s-switch">
                                    <input type="checkbox" id="setting0" />
                                    <label for="setting0">Toggle</label>
                                </div>
                            </div>
                            <p>Receive emails when you are given an assignment or other activity concerning you has occurred.</p>
                        </div>
                        <div class="s-settings">
                            <div class="s-sw-title">
                                <h5>Editor Help</h5>
                                <div class="s-switch">
                                    <input type="checkbox" id="setting1" />
                                    <label for="setting1">Toggle</label>
                                </div>
                            </div>
                            <p>Show extra information for help using Markdown.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- offset area end -->
    <!-- jquery latest version -->
    <script src="assets/js/vendor/jquery-2.2.4.min.js"></script>
    <!-- bootstrap 4 js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="assets/js/owl.carousel.min.js"></script>
    <script src="assets/js/metisMenu.min.js"></script>
    <script src="assets/js/jquery.slimscroll.min.js"></script>
    <script src="assets/js/jquery.slicknav.min.js"></script>

    <!-- start chart js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js"></script>
    <!-- start highcharts js -->
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <!-- start zingchart js -->
    <script src="https://cdn.zingchart.com/zingchart.min.js"></script>
    <script>
        zingchart.MODULESDIR = "https://cdn.zingchart.com/modules/";
        ZC.LICENSE = ["569d52cefae586f634c54f86dc99e6a9", "ee6b7db5b51705a13dc2339db3edaf6d"];
    </script>
    <!-- all line chart activation -->
    <script src="assets/js/line-chart.js"></script>
    <!-- all pie chart -->
    <script src="assets/js/pie-chart.js"></script>
    <!-- others plugins -->
    <script src="assets/js/plugins.js"></script>
    <script src="assets/js/scripts.js"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
    <script src="assets/js/sesstime.js"></script>
    <script type="text/javascript">
		(function($) {
			$("#notifications-menu").click(function() {
				$("#notifications-count").css("display", "none");

				var request = new XMLHttpRequest();
				request.open('GET', 'index.php?clear_notifications=true', true);

				request.onerror = function() {
				  // There was a connection error of some sort
					console.log("WARNING: Could not clear notifications");
				};

				request.send();
			});
			
			var pushSetting = function(num, value) {
				var request = new XMLHttpRequest();
				request.open('POST', 'index.php', true);
				request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');

				request.onerror = function() {
					// There was a connection error of some sort
					console.log("WARNING: Could not update setting " + num);
				};

				request.send('update_setting=' + num + '&value=' + value);
			}
			
			$("#setting0").click(function() {
				pushSetting(0, $(this).is(":checked"));
			});
			
			$("#setting1").click(function() {
				pushSetting(1, $(this).is(":checked"));
			});
			
			<?php for($i = 0; $i < sizeof($settings); $i++) { ?>
			$("#setting<?php echo($i); ?>").attr("checked", <?php echo($settings[$i]); ?>);
			<?php } ?>
		})(jQuery);
	</script>