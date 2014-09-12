<?php
/**
 * @version		3.3
 * @package		Simple RSS Feed Reader (module)
 * @author    	JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class SimpleRssFeedReaderHelper {

	function getFeeds($feedsArray,$totalFeedItems,$perFeedItems,$feedTimeout,$dateFormat,$wordLimit,$cacheLocation,$cacheTime,$imageHandling,$riWidth){

		/*
			Legend for '$imageHandling':
			0 - no images
			1 - fetch first image only and hide others
			2 - fetch and resize first image only and hide others
		*/

		// API
		$mainframe = JFactory::getApplication();

		$cacheTime = $cacheTime*60;

		// Check if the cache folder exists
		$cacheFolderPath = JPATH_SITE.DS.$cacheLocation;
		if(file_exists($cacheFolderPath) && is_dir($cacheFolderPath)){
			// all OK
		} else {
			mkdir($cacheFolderPath);
		}

		$feeds = self::multiRequest($feedsArray,$cacheTime);
		$parsedFeeds = self::parseFeeds($feeds);
		$feedItemsArray = array();

		foreach($parsedFeeds as $feed){
			foreach($feed->feedItems as $key=>$item){
				// Create an object to store feed elements
				$feedElements[$key] = new JObject;

				$feedElements[$key]->itemTitle 			= $item->title;
				$feedElements[$key]->itemLink 			= $item->link;
				$feedElements[$key]->itemDate 			= strftime($dateFormat,strtotime($item->pubDate));
				$feedElements[$key]->itemDateRSS 		= $item->pubDate;
				$feedElements[$key]->itemDescription 	= $item->description;
				$feedElements[$key]->feedImageSrc		= '';

				$feedElements[$key]->feedTitle 			= self::wordLimiter($feed->feedTitle,10);
				$feedElements[$key]->feedURL			= $feed->feedSubscribeUrl;
				$feedElements[$key]->siteURL			= $feed->feedLink;

				// Give each feed an index based on date
				$itemDateIndex = strftime('%Y%m%d%H%M',strtotime($item->pubDate));

				// Pass all feed objects to an array
				$feedItemsArray[$itemDateIndex] = $feedElements[$key];
			}
		}

		// Reverse sort by key (=feed date)
		krsort($feedItemsArray);

		// Limit output
		$outputArray = array();
		$counter = 0;
		foreach($feedItemsArray as $feedItem){
			if($counter>=$totalFeedItems) continue;

			// Clean up the feed title
			$feedItem->itemTitle = trim(htmlentities($feedItem->itemTitle, ENT_QUOTES, 'utf-8'));
			
			// URL Redirect
			if($feedItemLinkRedirect){
				$feedItem->itemLink = $siteUrl.'/modules/mod_jw_srfr/redir.php?url='.urlencode($feedItem->itemLink);
			}

			// Determine if an image reference exists in the feed description
			if($imageHandling==1 || $imageHandling==2){
				$feedImage = self::getFirstImage($feedItem->itemDescription);

				// If it does, copy, resize and store it locally
				if(isset($feedImage) && $feedImage['ext']){

					// first remove the img tag from the description
					$feedItem->itemDescription = str_replace($feedImage['tag'],'',trim($feedItem->itemDescription));

					// then resize and/or assign to variable
					if($imageHandling==2){
						$feedItem->feedImageSrc = 'http://ir0.mobify.com/'.$riWidth.'/'.$feedImage['src'];
					} else {
						$feedItem->feedImageSrc = $feedImage['src'];
					}
				} else {
					$feedItem->feedImageSrc = '';
				}
			}

			// Strip out images from the description
			$feedItem->itemDescription = preg_replace("#<img.+?>#s","",$feedItem->itemDescription);

			// Word limit
			if($wordLimit){
				$feedItem->itemDescription = self::wordLimiter($feedItem->itemDescription,$wordLimit);
			}

			$outputArray[] = $feedItem;
			$counter++;
		}

		return $outputArray;
	}

	// Get array of feeds
	function multiRequest($data,$cacheTime) {
		// Set max_execution_time to 120
		ini_set('max_execution_time',120);

		$cacheTime = $cacheTime*60;

		$result = array();
		foreach ($data as $id => $url) {
			$feed = self::getFile($url,$cacheTime,$subFolderName='feeds');
			$result[$id] = JFile::read($feed);
		}
		return $result;
	}

	// Parse array of feeds
	function parseFeeds($feeds){
		$feedContents = array();
		foreach($feeds as $key=>$feed){
			libxml_use_internal_errors(true);
			$feedContents[$key] = new stdClass;
			$xml = simplexml_load_string($feed);
			if(is_object($xml) && $items = $xml->xpath("/rss/channel/item")) {
				$feedContents[$key]->feedSubscribeUrl = $feed;
				$feedContents[$key]->feedTitle = $xml->channel->title;
				$feedContents[$key]->feedLink = $xml->channel->link;
				$feedContents[$key]->feedPubDate = $xml->channel->pubDate;
				$feedContents[$key]->feedDescription = $xml->channel->description;
				foreach($items as $item){
					$feedContents[$key]->feedItems[] = $item;
				}
			} elseif(is_object($xml) && $items = $xml->xpath("/*[local-name()='feed' and namespace-uri()='http://www.w3.org/2005/Atom'] /*[local-name()='entry' and namespace-uri()='http://www.w3.org/2005/Atom']")) {
				$feedContents[$key]->feedSubscribeUrl = $feed;
				$feedContents[$key]->feedTitle = (string)$xml->title;
				$feedContents[$key]->feedLink = (string)$xml->link->attributes()->href;
				$feedContents[$key]->feedPubDate = (string)$xml->updated;
				$feedContents[$key]->feedDescription = (string)$xml->subtitle;
				foreach ($items as $item) {
					$tmp = new stdClass();
					$tmp->title = (string)$item->title;
					$tmp->link = (string)$item->link->attributes()->href;
					$tmp->pubDate = (string)$item->updated;
					$tmp->description = (!empty($item->content)) ? $item->content:$item->summary;
					$tmp->author = (string)$item->author->name;
					$feedContents[$key]->feedItems[] = $tmp;
				}
			}
		}
		return $feedContents;
	}

	// Word Limiter
	function wordLimiter($str,$limit=100,$end_char='[&#8230;]'){
		if (trim($str) == '') return $str;
		$str = strip_tags($str);
		preg_match('/\s*(?:\S*\s*){'. (int) $limit .'}/', $str, $matches);
		if (strlen($matches[0]) == strlen($str)) $end_char = '';
		return rtrim($matches[0]).$end_char;
	}

	// Grab the first image in a string
	function getFirstImage($string){
		// find images
		$regex = "#<img.+?>#s";
		if (preg_match_all($regex, $string, $matches, PREG_PATTERN_ORDER) > 0){
			$img = array();
			// Entire <img> tag
			$img['tag'] = $matches[0][0];
			// Image src
			if(preg_match("#src=\".+?\"#s",$img['tag'],$imgSrc)){
				$img['src'] = str_replace('src="','',$imgSrc[0]);
				$img['src'] = str_replace('"','',$img['src']);
			} else {
				$img['src'] = false;
			}
			// Is this a real content image?
			if(preg_match("#\.(jpg|jpeg|png|gif|bmp)#s",strtolower($img['src']),$imgExt)){
				$img['ext'] = true;
			} else {
				$img['ext'] = false;
			}
			return $img;
		}
	}

	// Get remote file
	function getFile($url, $cacheTime=3600, $subFolderName='', $extensionName='mod_jw_srfr'){

		jimport('joomla.filesystem.file');

		// Check cache folder
		if($subFolderName){
			$cacheFolderPath = JPATH_SITE.DS.'cache'.DS.$extensionName.DS.$subFolderName;
		} else {
			$cacheFolderPath = JPATH_SITE.DS.'cache'.DS.$extensionName;
		}

		if(file_exists($cacheFolderPath) && is_dir($cacheFolderPath)){
			// all OK
		} else {
			mkdir($cacheFolderPath);
		}

		$url = trim($url);

		if(substr($url,0,4)=="http"){
			$turl = explode("?", $url);
			$matchComponents = array("#(http|https)\:\/\/#s","#www\.#s");
			$replaceComponents = array("","");
			$turl = preg_replace($matchComponents,$replaceComponents,$turl[0]);
			$turl = str_replace(array("/","-","."),array("_","_","_"),$turl);
			$tmpFile = $cacheFolderPath.DS.urlencode($turl).'_'.md5($url).'.cache';
		} else {
			$tmpFile = $cacheFolderPath.DS.'cached_'.md5($url);
		}

		// Check if a cached copy exists otherwise create it
		if(file_exists($tmpFile) && is_readable($tmpFile) && (filemtime($tmpFile)+$cacheTime) > time()){

			$result = $tmpFile;

		} else {
			// Get file
			if(substr($url,0,4)=="http"){
				// remote file
				if(ini_get('allow_url_fopen')){
					// file_get_contents
					$fgcOutput = JFile::read($url);

					// cleanup the content received
					$fgcOutput = preg_replace("#(\n|\r|\s\s+|<!--(.*?)-->)#s", "", $fgcOutput);
					$fgcOutput = preg_replace("#(\t)#s", " ", $fgcOutput);

					JFile::write($tmpFile,$fgcOutput);
				} elseif(in_array('curl',get_loaded_extensions())) {
					// cURL
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, $url);
					curl_setopt($ch, CURLOPT_HEADER, false);
					curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					$chOutput = curl_exec($ch);
					curl_close($ch);
					JFile::write($tmpFile,$chOutput);
				} else {
					// fsockopen
					$readURL = parse_url($url);
					$relativePath = (isset($readURL['query'])) ? $readURL['path']."?".$readURL['query'] : $readURL['path'];
					$fp = fsockopen($readURL['host'], 80, $errno, $errstr, 5);
					if (!$fp) {
						JFile::write($tmpFile,'');
					} else {
						$out = "GET ".$relativePath." HTTP/1.1\r\n";
						$out .= "Host: ".$readURL['host']."\r\n";
						$out .= "Connection: Close\r\n\r\n";
						fwrite($fp, $out);
						$header = '';
						$body = '';
						do { $header .= fgets($fp,128); } while (strpos($header,"\r\n\r\n")=== false); // get the header data
						while (!feof($fp)) $body .= fgets($fp,128); // get the actual content
						fclose($fp);
						JFile::write($tmpFile,$body);
					}
				}

				$result = $tmpFile;

			} else {

				// local file
				$result = $url;

			}

		}

		return $result;
	}

} // END CLASS
