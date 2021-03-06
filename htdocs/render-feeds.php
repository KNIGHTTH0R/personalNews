<?php

	// Setup
	$projectConfigUrl = 'config/config.php';
	require_once($projectConfigUrl);

	// +++++ Functions +++++++

	// get the rootUrl
	function getRootUrl($url) {
		$url = explode('/', $url);		// explode original url
		$url = $url[2];					// simply use rootUrl
		return $url;
	}

	// check if there are channel or entry-nodes and filter them (channel=rss / entry=atom)
	function checkFormat($xml) {
		foreach($xml->children() as $key=>$value) {
			switch($key) {
				case 'channel':
					return 'rss';
					break;
				case 'entry' :
					return 'atom';
					break;
			}
		}
	}

	// get the Feed
	function getFeed($content, $currentChannelKey) {
		global $itemDescriptionLength;

		foreach($content as $key=>$value) {

			// compute selected channel only (default if checkCurrentChannel decides)
			if($key == $currentChannelKey) {
				foreach($value as $feedUrl) {
					$xml = file_get_contents($feedUrl['url']);			// get url from json
					$xml = simplexml_load_string($xml);					// load rss to object

					$feedFormat = checkFormat($xml);					// let's check, if this is a rss or atom-feed

					// get values from feed (depending on the result of $feedFormat)
					if($feedFormat === 'rss') {
						// get data to push to every feedItem
						$xmlAuthorLink = $xml->channel[0]->link;
						$xmlAuthorLink = getRootUrl($xmlAuthorLink);					// get source-link from feed
						$xmlAuthorDescription = $xmlAuthorLink;							// get description from feed
						$xmlAuthorIcon = '//' . $xmlAuthorLink . "/favicon.ico";		// set up favicon from sourcelink

						foreach($xml->channel[0]->item as $item) {
							$feedItems[] = array(
								'itemAuthorLink' => '//' . $xmlAuthorLink,								// get authorlink (from feed)
								'itemAuthorDescription' => $xmlAuthorDescription,						// get author (from feed)
								'itemAuthorIcon' => $xmlAuthorIcon,										// get authorIcon (from feed)
								'itemLink' => strip_tags($item->link),									// get the link
								'itemTitle' => strip_tags($item->title),								// get the title
								'itemTimestamp' => strtotime($item->pubDate),							// get timestamp to make timeline sortable
								'itemDate' => date("d.m.Y (H:i)", strtotime($item->pubDate)),			// get releasedate an transform to readable date
								'itemDescription' => shortenText(strip_tags($item->description), $itemDescriptionLength)	// get description of item (usually news-short-description)
							);
						}
					} elseif($feedFormat === 'atom') {

						// get data to push to every feedItem
						$xmlAuthorLink = $xml->link['href'];						// extract href from element
						$xmlAuthorLink = getRootUrl($xmlAuthorLink);				// get source-link from feed
						$xmlAuthorDescription = $xmlAuthorLink;						// get description from feed
						$xmlAuthorIcon = '//' . $xmlAuthorLink . "/favicon.ico";	// set up favicon from sourcelink

						foreach($xml->entry as $item) {
							$feedItems[] = array(
								'itemAuthorLink' => '//' . $xmlAuthorLink,								// get authorlink (from feed)
								'itemAuthorDescription' => $xmlAuthorDescription,						// get author (from feed)
								'itemAuthorIcon' => $xmlAuthorIcon,										// get authorIcon (from feed)
								'itemLink' => strip_tags($item->id),									// get the link
								'itemTitle' => strip_tags($item->title),								// get the title
								'itemTimestamp' => strtotime($item->updated),							// get timestamp to make timeline sortable
								'itemDate' => date("d.m.Y (H:i)", strtotime($item->updated)),			// get releasedate an transform to readable date
								'itemDescription' => shortenText(strip_tags($item->content))			// get description of item (usually news-short-description)
							);
						}

					}
				}
			}

		}
		return $feedItems;
	}

	// filter feedItems with blacklist
	function filterFeed($feedItems) {
		global $blacklistItems;

		foreach($feedItems as $feedItem => $key) {
			foreach($blacklistItems as $blacklistItem) {
				$keysCombined = $key['itemTitle'] .' '. $key['itemDescription']; 	// were combining the text to search both keys in one go
				if(strpos($keysCombined, $blacklistItem) !== FALSE) {
					$feedItems[$feedItem]['itemBlacklistHit'] = $blacklistItem;	// if one blacklistItem is in the keys, the array is expanded with it
				}
			}
		}
		return $feedItems;
	}

	// sort feed by releaseDate/timestamp
	function sortFeed($feedItems) {
		foreach ($feedItems as $feedItem => $key) {
			$itemTimestamp[$feedItem] = $key['itemTimestamp'];
		}
		array_multisort($itemTimestamp, SORT_DESC, $feedItems);
		return $feedItems;
	}

	function shortenText($text) {
		global $itemDescriptionLength;
		global $readMoreIcon;
		$text = preg_replace('!\s+!', ' ', $text);	// remove unnesseccary whitespace
		if(strlen($text) > $itemDescriptionLength) {
			$text = substr($text, 0, strpos($text,'.',$itemDescriptionLength)) . ". " . $readMoreIcon;
		}
		return $text;
	}

	// render Output
	function renderFeed($feedItems) {
		//var_dump($feedItems);
		foreach ($feedItems as $feedItem) {
			if($feedItem['itemBlacklistHit']) {
				// output if part of feedItemTitle is in blacklist
			} else {
				// standard ouput of feed
				echo '<li id="' . $feedItem['itemTimestamp'] . '">';	// add timestamp to use as anchor for unread news
				echo 	'<div>';
				echo 		'<a href="' . $feedItem['itemAuthorLink'] . '" class="icon" rel="noopener" target="pn-blank"><img src="' . $feedItem['itemAuthorIcon'] . '" alt="' . $feedItem['itemAuthorDescription'] . '" height="32" width="32" /></a>';
				echo 	'</div>';
				echo 	'<div>';
				echo		'<header>';
				echo			'<h2 class="title"><a href="' .  $feedItem['itemLink'] . '" rel="noopener" target="pn-blank">' . $feedItem['itemTitle'] .'</a></h2>';
				echo			'<p class="info"><span class="date">' . $feedItem['itemDate'] . '</span> / <a href="' . $feedItem['itemAuthorLink'] . '" class="source">' . $feedItem['itemAuthorDescription'] . '</a></p>';
				echo		'</header>';
				echo		'<p class="excerpt"><a href="' .  $feedItem['itemLink'] . '" rel="noopener" target="pn-blank">' . $feedItem['itemDescription'] . '</a></p>';
				echo	'</div>';
				echo	'<div>';
				echo	'</div>';
				echo '</li>';
			}
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

?>


<!-- output -->
<ul id="feed-items">
	<?php
		$currentChannelKey = checkCurrentChannel($channelItems);
		$feedItems = getFeed($content, $currentChannelKey);
		$feedItems = filterFeed($feedItems);
		$feedItems = sortFeed($feedItems);
		renderFeed($feedItems);
	?>
</ul>
