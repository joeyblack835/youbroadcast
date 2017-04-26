<?php

$cache['tracks'] = array();

function tracks_autoplay($channel_id, $shuffle = true) {


	// lock table

	$sql = "LOCK TABLES " . TABLE_PREFIX . "tracks WRITE";

	mysql_query($sql);


	// check whether there is a next track

	$s_channel_id = mysql_real_escape_string($channel_id);

	$sql = "
		SELECT id
		FROM " . TABLE_PREFIX . "tracks
		WHERE channel_id = '$s_channel_id' AND start_time > FROM_UNIXTIME($_SERVER[REQUEST_TIME]) AND ! fixed
	";

	$result = mysql_query($sql);

	if (mysql_num_rows($result)) {


		// if yes, it is no need to autoplay

		return;

	}


	// loop with two iterations: one - to set the current track (if needed), the second - to set the next track

	for ($i=0; $i<2; $i++) {


		// get the end time of the top track

		$sql = "
			SELECT UNIX_TIMESTAMP(start_time) + length, start_time
			FROM " . TABLE_PREFIX . "tracks
			WHERE channel_id = '$s_channel_id' AND ! fixed
			ORDER BY start_time DESC
			LIMIT 1
		";

		$result = mysql_query($sql);


		// it will be a start time of the next track

		list($new_start_time) = mysql_fetch_row($result);


		// if the start time is in the past, set it to now

		if ($new_start_time <= $_SERVER['REQUEST_TIME']) {

			$new_start_time = $_SERVER['REQUEST_TIME'];

		}


		// select finished track from the bottom of the stack or random finished track (if shuffle)

		$order = ($shuffle) ? 'rand' : 'start_time';

		$sql = "
			SELECT id, RAND() rand
			FROM " . TABLE_PREFIX . "tracks
			WHERE channel_id = '$s_channel_id' AND (UNIX_TIMESTAMP(start_time) + length) < $_SERVER[REQUEST_TIME] AND ! fixed
			ORDER BY $order
			LIMIT 1
		";

		$result = mysql_query($sql);

		list($id) = mysql_fetch_row($result);


		// move the selected track to the top of the stack

		$sql = "
			UPDATE " . TABLE_PREFIX . "tracks
			SET start_time = FROM_UNIXTIME($new_start_time)
			WHERE id = $id
		";

		mysql_query($sql);


		// exit if the next track was set during the first iteration

		if ($new_start_time > $_SERVER['REQUEST_TIME']) {

			break;

		}

	}


	// unlock table

	$sql = 'UNLOCK TABLES';

	mysql_query($sql);

}

function prepare_tracks($ids) {
	global $cache;

	$ids = (array)$ids;

	if (!count($ids)) return;

	$s_ids = join(', ',$ids);

	$sql = "
		SELECT *
		FROM " . TABLE_PREFIX . "tracks
		WHERE id IN ($s_ids)
	";

	$result = mysql_query($sql);

	while ($a = mysql_fetch_assoc($result)) {

		$cache['tracks'][$a['id']] = $a;

	}

	mysql_free_result($result);

}

function search_tracks(&$vars) {

	$filter = '';

	$sql = "
		SELECT id
		FROM " . TABLE_PREFIX . "tracks
		$filter
		ORDER BY start_time DESC, id DESC
	";

	$result = mysql_query($sql);

	$ids = array();

	while (list($id) = mysql_fetch_row($result)) {

		$ids[] = $id;

	}

	mysql_free_result($result);

	return $ids;

}

?>