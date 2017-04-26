<?php

header('Content-Type: text/xml');

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);

include 'include/main2.inc.php';

include BASE_DIR . '/include/lib/tracks.inc.php';

include BASE_DIR . '/include/session_handler.inc.php';


// check if channel exists

$error = false;

if (isset($_GET['channel_id'])) {

	$channel_id = @strval($_GET['channel_id']);

	$s_channel_id = mysql_real_escape_string($channel_id);

	$sql = "
		SELECT *
		FROM " . TABLE_PREFIX . "users
		WHERE login = '$s_channel_id'
	";

	$result = mysql_query($sql);

	if (!mysql_num_rows($result)) {

		$error = true;

	}

} else {

	$error = true;

}

if ($error) {

	$s = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$s.= '<error code="404" />';

	print $s;
	exit;

}


// two-pass algorithm

for ($i = 0; $i < 2; $i++) {


	// get current & next videos

	$filter_fixed = ($i == 0) ? 'AND ! fixed' : '';

	$sql = "
		SELECT *, IF (start_time <= FROM_UNIXTIME($_SERVER[REQUEST_TIME]), 'current', 'next') AS flag, UNIX_TIMESTAMP(start_time) AS start_time_ts
		FROM " . TABLE_PREFIX . "tracks
		WHERE channel_id = '$s_channel_id' AND DATE_ADD(start_time, INTERVAL length SECOND) > FROM_UNIXTIME($_SERVER[REQUEST_TIME]) $filter_fixed
		ORDER BY start_time, ! fixed
	";

	$result = mysql_query($sql);

	$videos = array();

	while ($a = mysql_fetch_assoc($result)) {

		if ($a['flag'] == 'current' && isset($videos['current']) && !$a['fixed']) continue;

		if ($a['flag'] == 'next' && isset($videos['next'])) break;

		$videos[$a['flag']] = $a;

	}

	mysql_free_result($result);


	if ($i == 0) {

		if (!isset($videos['next'])) {


			// autoplay

			tracks_autoplay($channel_id);

		}

	} else {


		// not to show the next video if it starts earlier than the current one ends

		if (isset($videos['current']) && isset($videos['next'])) {

			if (!$videos['next']['fixed'] && $videos['current']['start_time_ts'] + $videos['current']['length'] > $videos['next']['start_time_ts'] ) {

				unset($videos['next']);

			}

		}


		// current video

		if (isset($videos['current'])) {

			$videos['current']['status'] = 'OK';
			$videos['current']['start'] = $_SERVER['REQUEST_TIME'] - $videos['current']['start_time_ts'] + $videos['current']['start'];
			$videos['current']['refresh'] = $videos['current']['start_time_ts'] + $videos['current']['length'] - $_SERVER['REQUEST_TIME'];

		} else {


			// no video found

			$videos['current']['status'] = 'NO';
			$videos['current']['refresh'] = '300'; // 5 minutes
			$videos['current']['videoid'] = '';
			$videos['current']['title'] = '';
			$videos['current']['start'] = '0';
			$videos['current']['end'] = '0';

		}


		// next video

		if (isset($videos['next'])) {

			$videos['next']['status'] = 'OK';
			$videos['next']['thumb'] = 'https://i1.ytimg.com/vi/' . $videos['next']['videoid'] . '/default.jpg';

			if ($videos['current']['status'] == 'NO' || !$videos['current']['fixed']) {
				$videos['current']['refresh'] = $videos['next']['start_time_ts'] - $_SERVER['REQUEST_TIME'];
			}

		} else {


		        // no video found

			$videos['next']['status'] = 'NO';
			$videos['next']['thumb'] = '';
			$videos['next']['title'] = '';

		}


		// output

		$s = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
		$s.= '<tracks>' . "\n";
		$s.= '<current>' . "\n";
		$s.= '<status>' . $videos['current']['status'] . '</status>' . "\n";
		$s.= '<refresh>' . $videos['current']['refresh'] . '</refresh>' . "\n";
		$s.= '<videoid>' . $videos['current']['videoid'] . '</videoid>' . "\n";
		$s.= '<title>' . htmlspecialchars($videos['current']['title']) . '</title>' . "\n";
		$s.= '<start>' . $videos['current']['start'] . '</start>' . "\n";
		$s.= '<end>' . $videos['current']['end'] . '</end>' . "\n";
		$s.= '</current>' . "\n";
		$s.= '<next>' . "\n";
		$s.= '<status>' . $videos['next']['status'] . '</status>' . "\n";
		$s.= '<thumb>' . $videos['next']['thumb'] . '</thumb>' . "\n";
		$s.= '<title>' . htmlspecialchars($videos['next']['title']) . '</title>' . "\n";
		$s.= '</next>' . "\n";
		$s.= '</tracks>';

		print $s;

	}

}
