<?php

if(!isset($_POST['content']) || empty($_POST['content'])) {
	die();
}

require('assets/php/parsedown.php');

$parsedown = new Parsedown();

$parsedown->setSafeMode(true);

?>
<div class="markdown">
<?php
	
echo $parsedown->text($_POST['content']);

?>
</div>