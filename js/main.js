var seconds = 0;
var countdownInterval;
var pingTimeout;
var _lockPlaybackTry = false;
var _isPing = false;
var player;

function countdown() {
	if (seconds <= 0) {
		clearInterval(countdownInterval);
		countdownInterval = null;
		clearTimeout(pingTimeout);
		playbackTry(false);
	} else {
		seconds--;
		document.getElementById('count').innerHTML = secToTime(seconds);
	}
}

function secToTime(sec) {

	m = parseInt(sec/60);
	s = parseInt(sec%60);
	if (s < 10) s = '0' + s;

	return '' + m + ':' + s;

}

function playbackShow() {

	if (this.readyState == 4) {

		if (this.status == 200 && this.responseText.length > 0) {

			xmlDoc = parseXML(this.responseText);

			if (xmlParseError === false && playbackIsValidXmlDoc(xmlDoc)) {

				// current video
				var current = xmlDoc.getElementsByTagName('current')[0];
				if (_getTextContent(current.getElementsByTagName('status')[0]) == 'OK') {
					var videoId = _getTextContent(current.getElementsByTagName('videoid')[0]);
					if (!_isPing || !player || player.getVideoData()['video_id'] != videoId) {
						var end = _getTextContent(current.getElementsByTagName('end')[0]);
						if (end != '0') {
							end = '&end=' + end;
						} else {
							end = '';
						}
						if (!player) {
							player = new YT.Player('player', {
								width: 480,
								height: 360,
								videoId: videoId,
								playerVars: {
									autoplay: 1,
									start: _getTextContent(current.getElementsByTagName('start')[0]),
									endSeconds: end,
								}
							});
						} else {
							player.loadVideoById({
								'videoId': videoId,
								'startSeconds': _getTextContent(current.getElementsByTagName('start')[0]),
								'endSeconds': end
							});
						}
					}
					document.getElementById('title').innerHTML = _getTextContent(current.getElementsByTagName('title')[0]);
				} else {
					document.getElementById('title').innerHTML = '';
					// TODO: handle no current video
				}

				// countdown
				var refresh = _getTextContent(current.getElementsByTagName('refresh')[0]);
				if (!countdownInterval || Math.abs(refresh - seconds) > 5) {
					clearInterval(countdownInterval);
					seconds = refresh;
					document.getElementById('count').innerHTML = secToTime(seconds);
					countdownInterval = setInterval(countdown, 1000);
				}

				// next video
				var next = xmlDoc.getElementsByTagName('next')[0];
				if (_getTextContent(next.getElementsByTagName('status')[0]) == 'OK') {
					document.getElementById('next').innerHTML = '<img src="' + _getTextContent(next.getElementsByTagName('thumb')[0]) + '" alt="" />' + _getTextContent(next.getElementsByTagName('title')[0]);
				} else {
					document.getElementById('next').innerHTML = '';
					// TODO: handle no next video
				}

			} else {
				// TODO: handle xmlParseError
			}

		} else if (!countdownInterval) {
			seconds = 5;
			document.getElementById('count').innerHTML = secToTime(seconds);
			countdownInterval = setInterval(countdown, 1000);
		}

		pingTimeout = setTimeout(function() {
			playbackTry(true);
		}, 30000);
		_lockPlaybackTry = false;
//		logg('playbackTry is free!');
	}

}

function playbackTry(ping) {
	if (!_lockPlaybackTry) {
		if (!ping || seconds > 5) {
//			logg('playbackTry: ' + ping);
			_lockPlaybackTry = true;
			_isPing = ping;
			loadXMLDoc(baseUrl + 'get_tracks.php?channel_id=' + channelId + '&amp;' + Math.random(), playbackShow);
		}
	} else {
//		logg('playbackTry: ' + ping + ' -- is busy!');
	}
}

function playbackIsValidXmlDoc(xmlDoc) {
	return true;
}
