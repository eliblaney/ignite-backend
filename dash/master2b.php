<?php
if(!defined('IgniteDashboard')) {
	define('__ROOT__', dirname(dirname(__FILE__)));
	require_once(__ROOT__.'/helper.php');
	
	IgniteHelper::error(15, "Direct access not permitted");
	exit;
}
?>


</body>
</html>