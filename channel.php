<?php
// Test review
include 'include/main2.inc.php';

include BASE_DIR . '/include/session_handler.inc.php';

$error = false;

if (isset($_GET['channel_id'])) {

	$channel_id = @strval($_GET['channel_id']);

	$sql = "
		SELECT *
		FROM " . TABLE_PREFIX . "users
		WHERE login = '" . mysql_real_escape_string($channel_id) . "'
	";

	$result = mysql_query($sql);

	if (!mysql_num_rows($result)) {

		$error = true;

	}

	$channel = mysql_fetch_assoc($result);

	mysql_free_result($result);

} else {

	$error = true;

}

$must_agree = false;

if (isset($_GET['agree'])) {

	setcookie('agree');

} elseif (!isset($_COOKIE['agree'])) {

	$must_agree = true;

}

header('Content-Type: text/html; charset=UTF-8');

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?php echo ($error) ? 'Channel not found' : htmlspecialchars($channel['name']) . '&#039;s broadcasting channel'; ?> - YouBroadcast</title>
<meta name="description" content="<?php echo ($error) ? 'Entertainment broadcasting channels' : htmlspecialchars($channel['channel_description_short']); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="<?php echo $base_url; ?>favicon.ico" type="image/x-icon">
<link rel="publisher" href="https://plus.google.com/b/101445634496143961211/101445634496143961211/">
<script type="text/javascript">
<!--
var baseUrl = '<?php echo $site_url; ?>';
var channelId = '<?php echo ($error) ? '' : $channel_id; ?>';
-->
</script>
<link media="all" href="<?php echo $base_url; ?>css/default.min.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php echo $base_url; ?>js/XMLHttpRequest.min.js" defer="defer"></script>
<script type="text/javascript" src="<?php echo $base_url; ?>js/main.min.js" defer="defer"></script>
<script src="https://apis.google.com/js/platform.js" async defer></script>
</head>
<body>
<div id="container">

	<div id="header"><img src="<?php echo $base_url; ?>images/broadcast.png" width="32" height="32" alt="YouBroadcast Logo"><h1 id="channel"><?php echo ($error) ? 'Channel not found' : htmlspecialchars($channel['name']) . '&#039;s broadcasting channel'; ?></h1></div>

	<div id="main">

		<div id="col1">

			<?php if (!$error): ?>
			<div id="refresh"><span class="arr">&uarr;</span> <a href="">Refresh</a></div>

			<div id="auto"><span class="arr">&larr;</span> Auto-refresh: <span id="count">0:00</span></div>
			<?php endif; ?>

			<div id="video-container">

				<script type="text/javascript">
				<!--
					<?php if ($error) : ?>
						document.write('<p>The requested channel could not be found.</p><p>This might be because:</p><ul><li>You have typed the web address incorrectly, or</li><li>the channel you were looking for may have been moved or deleted.</li></ul><p>Try exploring <a href="<?php echo $base_url; ?>channel/joey/">Joey&#039;s broadcasting channel</a>.</p>');
					<?php elseif ($must_agree) : ?>
						document.write('<p>You are going to navigate to the page containing sounds.</p><p><a href="<?php echo parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH); ?>?agree" rel="nofollow">Agree</a>');
					<?php else : ?>
						document.write('<div id="player"></div>');

						var tag = document.createElement('script');
						tag.src = "https://www.youtube.com/iframe_api";
						var firstScriptTag = document.getElementsByTagName('script')[0];
						firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);

						function onYouTubeIframeAPIReady() {
							var interval = setInterval(function() {
								if (document.readyState === 'complete') {
									clearInterval(interval);
									playbackTry(false);
								}    
							}, 100);
						}

					<?php endif; ?>
				-->
				</script>

				<noscript>

					<p>JavaScript should be enabled to use the website.</p>

				</noscript>

			</div>

			<?php if (!$error): ?><div id="current"><span class="arr">&darr;</span> <span id="title"></span></div><?php endif; ?>

		</div>

		<div id="col2">

			<?php if (!$error): ?>
			<div id="next-caption"><span class="arr">&rarr;</span> NEXT</div>

			<div id="next"></div>
			<?php endif; ?>

		</div>

	</div>

	<?php if (!$error): ?>
	<div id="footer">

		<h2>About the channel</h2>

		<p><?php echo htmlspecialchars($channel['channel_description']); ?></p>

		<h3>Contact</h3>

		<p>If you have questions contact <?php echo htmlspecialchars($channel['name']); ?> through <a href="https://www.youtube.com/channel/<?php echo $channel['youtube_channel_id']; ?>/about">their YouTube channel</a>.</p>

		<div class="social">

			<div class="g-plusone" data-href="<?php echo $site_url; ?>channel/<?php echo $channel['login']; ?>/"></div>

		</div>

	</div>
	<?php endif; ?>

</div>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-64808436-1', 'auto');
  ga('send', 'pageview');

</script>
</body>
</html>
