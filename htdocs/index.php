<?php
	// Setup
	$projectConfigUrl = "config/config.php";
	require_once($projectConfigUrl);

	// Data-Environment
	$dataUrl = "data/data.json";					// Set the url to retrieve the data from
	$jsonContent = file_get_contents($dataUrl);		// Get the data
	$json = json_decode($jsonContent, true);		// (true) returns the json as array-structure

	// build array
	$content = $json['content'];					// Get content-array directly

	// +++++ Functions +++++++
	$channelUrlParameter = urldecode($_GET['channel']);		// get the channelparamter, if there's one
	$channelItems = array();								// collect all channels in array
	$feedItems = array();									// collect all feeds in array

	// get theme from session
	session_start();
	if(!empty($_SESSION['theme'])) {
		$theme = $_SESSION['theme'];
	} else {
		$theme = $themeDefault;
	}

	// get the rootUrl
	function getRootUrl($url) {
		$url = explode('/', $url);		// explode original url
		$url = $url[2];					// simply rootUrl
		return $url;
	}

	// get all channels and put them in array
	function getChannelItems($content) {
		$channelItems = array_keys($content);
		return $channelItems;
	}


	// build the channel-list
	function renderChannels($channelItems) {
		foreach($channelItems as $channelItem) {
			$channelItemParameter = urlencode($channelItem);
			$channelItemName = $channelItem;

			echo '<li>';
			echo '<a href="?channel=' . $channelItemParameter . '">' . $channelItemName . "</a>";
			echo '</li>';
		}
	}

	// check if channel is set via parameter and the paramter matches the channels from data/json (array)
	function checkCurrentChannel($channelItems) {
		$channelUrlParameter = urldecode($_GET['channel']);

		if(in_array($channelUrlParameter, $channelItems)) {
			return $channelUrlParameter;
		} else {
			return $channelItems[0];
		}
	}

	// get the RSS
	function getRSS($content, $currentChannelKey) {
		foreach($content as $key=>$value) {

			// compute selected channel only (default if checkCurrentChannel decides)
			if($key == $currentChannelKey) {
				foreach($value as $rssUrl) {
					$xml = file_get_contents($rssUrl['url']);			// get url from json
					$xml = simplexml_load_string($xml);					// load rss to object

					// get data to push to every feedItem
					$xmlAuthorLink = getRootUrl((string)$xml->channel[0]->link);			// get source-link from rss
					$xmlAuthorDescription = $xmlAuthorLink;									// get description from rss
					$xmlAuthorIcon = '//' . $xmlAuthorLink . "/favicon.ico";						// set up favicon from sourcelink

					foreach($xml->channel[0]->item as $item) {
						$feedItems[] = array(
							'itemAuthorLink' => '//' . $xmlAuthorLink,						// get authorlink (from feed)
							'itemAuthorDescription' => $xmlAuthorDescription,				// get author (from feed)
							'itemAuthorIcon' => $xmlAuthorIcon,								// get authorIcon (from feed)
							'itemLink' => strip_tags($item->link),							// get the link
							'itemTitle' => strip_tags($item->title),						// get the title
							'itemTimestamp' => strtotime($item->pubDate),					// get timestamp to make timeline sortable
							'itemDate' => date("d.m.Y (H:i)", strtotime($item->pubDate)),	// get releasedate an transform to readable date
							'itemDescription' => strip_tags($item->description)				// get description of item (usually news-short-description)
						);
					}
				}
			}

		}
		return $feedItems;
	}

	// sort RSS by releaseDate/timestamp
	function sortRss($feedItems) {
		foreach ($feedItems as $key => $row) {
			$itemTimestamp[$key] = $row['itemTimestamp'];
		}
		array_multisort($itemTimestamp, SORT_DESC, $feedItems);
		return $feedItems;
	}

	// render Output
	function renderRss($feedItems) {
		foreach ($feedItems as $feedItem) {
			echo '<li id="' . $feedItem['itemTimestamp'] . '">';	// add timestamp to use as anchor for unread news
			echo 	'<div>';
			echo 		'<a href="' . $feedItem['itemAuthorLink'] . '" class="icon" rel="noopener"><img src="' . $feedItem['itemAuthorIcon'] . '" alt="' . $feedItem['itemAuthorDescription'] . '" height="32" width="32" /></a>';
			echo 	'</div>';
			echo 	'<div>';
			echo 		'<h2 class="title"><a href="' .  $feedItem['itemLink'] . '" rel="noopener">' . $feedItem['itemTitle'] .'</a></h2>';
			echo 		'<p class="info"><span class="date">' . $feedItem['itemDate'] . '</span> / <a href="' . $feedItem['itemAuthorLink'] . '" class="source">' . $feedItem['itemAuthorDescription'] . '</a></p>';
			echo 		'<p class="excerpt"><a href="' .  $feedItem['itemLink'] . '" rel="noopener">' . $feedItem['itemDescription'] . '</a></p>';
			echo 	'</div>';
			echo	'<div>';
			echo	'</div>';
			echo '</li>';
		}
	}
?>

<!DOCTYPE html>
<html dir="ltr" lang="de" manifest="<?php if(isset($manifestUrl)) { echo($manifestUrl); }  ?>">
<head>
	<title><?php echo($projectTitle); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="description" content="<?php echo($projectdescription); ?>" />
	<meta name="language" content="de" />
	<meta name="MSSmartTagsPreventParsing" content="TRUE" />
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- Website as app -->
	<meta name="apple-mobile-web-app-capable" content="yes"/>
	<meta name="apple-mobile-web-app-status-bar-style" content="black"/>


	<!-- Short Names -->
	<meta name="apple-mobile-web-app-title" content="<?php echo($applicationName); ?>" />
	<meta name="application-name" content="<?php echo($applicationNameShort); ?>" />

	<!-- Icons -->
	<link rel="apple-touch-icon" href="apple-touch-icon-foto-114x114-precomposed.png" />
	<link rel="shortcut icon" href="favicon.ico" />

	<!-- CSS -->
	<style type="text/css">
		<?php require_once($cssUrl); ?>
	</style>

	<!-- JS -->
	<script type="text/javascript">
		<?php require_once($jsUrl); ?>
	</script>

	<!-- Mobile Manifest -->
	<link rel="manifest" href="manifest.json" />
</head>

<body id="home" class="<?php echo $theme; ?>">

	<!-- header -->
	<header id="application-head">
		<div>
			<a href="#" data-target="application-overlay" id="toggle-overlay"><i class="icon-menu"></i></a>
		</div>
		<div>
			<a href="#" id="logo"><img src="favicon.ico" alt="<?php echo($projectTitle); ?>" /></a>
		</div>
		<div>
			<form method="#" action="#">
				<input type="checkbox" id="theme-switcher" class="vh" <?php if($theme == $themeDark) { echo 'checked="checked"'; } ?> />
				<label for="theme-switcher"><i class="icon-moon"></i><i class="icon-sun"></i></label>
			</form>
		</div>
	</header>

	<div class="overlay js-hidden" id="application-overlay">
		 <h2><?php echo($applicationName); ?></h2>
		 <ul>
			<?php
				$channelItems = getChannelItems($content);
				renderChannels($channelItems);
			?>
		</ul>
	</div>

	<!-- content -->
	<main id="content">
		<ul>
			<?php
				$currentChannelKey = checkCurrentChannel($channelItems);
				$feedItems = getRss($content, $currentChannelKey);
				$feedItems = sortRss($feedItems);
				renderRss($feedItems);
			?>
		</ul>
	</main>

	<!-- footer -->
	<footer>
	</footer>
</body>
</html>
