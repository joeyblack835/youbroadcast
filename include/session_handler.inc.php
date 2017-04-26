<?php

/*
 * Extended version with browser info
 * Created: 11.10.2013
 * Last revised: 30.12.2016
 */

ini_set('session.auto_start', '0');
ini_set('session.gc_probability', '1');
ini_set('session.gc_divisor', '100');
ini_set('session.gc_maxlifetime', $session_lifetime);
ini_set('session.use_cookies', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.cookie_domain', COOKIE_DOMAIN);
ini_set('session.use_trans_sid', '0');

session_set_save_handler('sh_open', 'sh_close', 'sh_read', 'sh_write', 'sh_destroy', 'sh_gc');

session_start();

function sh_open() {

	return true;

}

function sh_close() {

	return true;

}

function sh_read($id) {
	global $session_lifetime;

	$additional_where = '';

	if (isset($session_lifetime)) {

		$additional_where = "AND updated + INTERVAL $session_lifetime SECOND > NOW()";

	}

	$sql = "
		SELECT data
		FROM " . TABLE_PREFIX . "sessions
		WHERE id = '$id' $additional_where
	";

	$result = mysql_query($sql);

	if ($row = mysql_fetch_row($result)) {

		return $row[0];

	}

	return '';

}

function sh_write($id, $data) {

	$s_data = mysql_real_escape_string($data);

	// browser info
	$s_last_request_uri 	= mysql_real_escape_string($_SERVER['REQUEST_URI']);
	$s_user_agent 		= mysql_real_escape_string($_SERVER['HTTP_USER_AGENT']);
	$s_accept_language	= mysql_real_escape_string(@$_SERVER['HTTP_ACCEPT_LANGUAGE']);
	$s_ip			= mysql_real_escape_string($_SERVER['REMOTE_ADDR']);

	$sql = "
		REPLACE INTO " . TABLE_PREFIX . "sessions VALUES (
			'$id', '$s_data', NOW(),
			'$s_last_request_uri', '$s_user_agent', '$s_accept_language', '$s_ip'
		)
	";

	mysql_query($sql);

	if (mysql_affected_rows()) {

		return true;

	}

	return false;

}

function sh_destroy($id) {

	$sql = "
		DELETE FROM " . TABLE_PREFIX . "sessions
		WHERE id = '$id'
	";

	mysql_query($sql);

	return true;

}

function sh_gc($maxlifetime) {

	$sql = "
		DELETE FROM " . TABLE_PREFIX . "sessions
		WHERE updated + INTERVAL $maxlifetime SECOND < NOW()
	";

	mysql_query($sql);

	return true;

}

?>