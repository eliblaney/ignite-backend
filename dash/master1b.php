</head>

<body>
    <!--[if lt IE 8]>
            <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="http://browsehappy.com/">upgrade your browser</a> to improve your experience.</p>
        <![endif]-->
    <!-- preloader area start -->
    <div id="preloader">
        <div class="loader"></div>
    </div>
    <!-- preloader area end -->
    <!-- page container area start -->
    <div id="page-container" class="page-container">
        <!-- sidebar menu area start -->
        <div class="sidebar-menu">
            <div class="sidebar-header">
                <div class="logo">
                    <a href="#"><h1 class="logotext">Ignite</h1></a>
                </div>
            </div>
            <div class="main-menu">
                <div class="menu-inner">
                    <nav>
                        <ul class="metismenu" id="menu">
                            <li <?php if($page == 0) { ?> class="active" <?php } ?>><a href="index.php" aria-expanded="true"><i class="ti-dashboard"></i><span>Dashboard</span></a></li>
                            <li <?php if($page == 1) { ?> class="active" <?php } ?>><a href="reflections.php"><i class="ti-write"></i><span>Reflections</span></a></li>
                            <li <?php if($page == 2) { ?> class="active" <?php } ?>><a href="audioguides.php"><i class="ti-headphone"></i><span>Audio Guides</span></a></li>
                            <li <?php if($page == 3) { ?> class="active" <?php } ?>><a href="assignments.php"><i class="ti-pencil"></i><span>Assignments</span></a></li>
                            <li <?php if($page == 4) { ?> class="active" <?php } ?>><a href="notifications.php"><i class="ti-bell"></i><span>Notifications</span></a></li>
                            <li <?php if($page == 5) { ?> class="active" <?php } ?>><a href="people.php"><i class="ti-user"></i><span>People</span></a></li>
                            <li <?php if($page == 6) { ?> class="active" <?php } ?>><a href="support.php"><i class="ti-help"></i><span>Support</span></a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
        <!-- sidebar menu area end -->
        <!-- main content area start -->
        <div class="main-content">
            <!-- header area start -->
            <div class="header-area">
                <div class="row align-items-center">
                    <!-- nav and search button -->
                    <div class="col-md-6 col-sm-8 clearfix">
                        <div class="nav-btn pull-left">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                    <!-- profile info & task notification -->
                    <div class="col-md-6 col-sm-4 clearfix">
                        <ul class="notification-area pull-right">
                            <li id="full-view"><i class="ti-fullscreen"></i></li>
                            <li id="full-view-exit"><i class="ti-zoom-out"></i></li>
                            <li class="dropdown">
                                <i id="notifications-menu" class="ti-bell dropdown-toggle" data-toggle="dropdown">
                               	<?php
								if($notificationsCount > 0) { ?>
                                    <span id="notifications-count"><?php echo($notificationsCount); ?></span>
                                <?php } ?>
                                </i>
                                <div class="dropdown-menu bell-notify-box notify-box">
                                    <span class="notify-title">You have <?php echo($notificationsCount); ?> new notification<?php if($notificationsCount != 1) { ?>s<?php } ?>. <a href="notifications.php">view all</a></span>
                                    <div class="nofity-list">
                                       <?php
										$count = 0;
										foreach($notifications as $n) {
											if(++$count > IgniteConstants::NOTIFICATIONS_MAX) {
												break;
											}
											?><a href="<?php echo(htmlspecialchars($n->link)); ?>" class="notify-item">
                                            <div class="notify-thumb"><i class="<?php echo(htmlspecialchars($n->classes)); ?>"></i></div>
                                            <div class="notify-text">
                                                <p class="<?php if(!strcmp($n->unread,"true")) { echo("new-notification"); } ?>"><?php echo(htmlspecialchars($n->subject)); ?></p>
                                                <span><?php
												 	$date = $n->timestamp;
											
													if(!strcmp($date, "424242")) {
														echo("Yay!");
													} else if(strcmp($date, "0")) {
														echo(IgniteHelper::prettyDate($date));
													}
													
													?></span>
                                            </div>
                                        </a>
										<?php }
										?>
                                    </div>
                                </div>
                            </li>
                            <li class="settings-btn">
                                <i class="ti-settings"></i>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- header area end -->
            <!-- page title area start -->
            <div class="page-title-area">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <div class="breadcrumbs-area clearfix">
                            <h4 class="page-title pull-left"><?php echo($title); ?></h4>
                            <ul class="breadcrumbs pull-left">
                                <li><a href="index.php">Home</a></li>
                                <?php
								if(isset($subpage)) {
								?>
                                <li><a href="<?php echo($parentlink)?>"><?php echo($title); ?></a></li>
                           		<li><span><?php echo($subpage); ?></span></li>
                           		<?php } else { ?>
                                <li><span><?php echo($title); ?></span></li>
                                <?php } ?>
                            </ul>
                        </div>
                    </div>
                    <div class="col-sm-6 clearfix">
                        <div class="user-profile pull-right">
                            <img class="avatar user-thumb" src="assets/images/author/avatar.png" alt="avatar">
                            <h4 class="user-name dropdown-toggle" data-toggle="dropdown"><?php echo($fullname); ?><i class="fa fa-angle-down"></i></h4>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="assignments.php">Assignments</a>
                                <a class="dropdown-item" href="support.php">Support</a>
                                <a class="dropdown-item" href="logout.php">Log Out</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- page title area end -->
            <div class="container-fluid main-content-inner" style="padding: 20px 50px;">
            
           <?php
				
				if(isset($warning)) {
					?>
					<div class="alert alert-warning alert-dismissible fade show py-3" role="alert">
						<p style="fontSize: 1.2rem;"><?php echo($warning); ?></p>
					 	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<?php
				}
				
			?>