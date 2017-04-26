<?php

define('BASE_DIR', getcwd());

include BASE_DIR . '/include/config.inc.php';

$base_url = parse_url($site_url, PHP_URL_PATH);

if (!@mysql_connect($db_host, $db_user, $db_password)) {
	die('Could not connect to MySQL server.');
}

if (!mysql_select_db($db_name)) {
	die('Could not select database.');
}

mysql_set_charset('utf8');

?>