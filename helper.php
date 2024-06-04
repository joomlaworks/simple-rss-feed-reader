<?php
/**
 * @version    4.0
 * @package    Simple RSS Feed Reader (module)
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2024 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

class SimpleRssFeedReaderHelper
{
    public function getFeeds($feedsArray, $totalFeedItems, $perFeedItems, $feedTimeout, $dateFormat, $wordLimit, $cacheLocation, $cacheTime, $imageHandling, $riWidth)
    {

        /*
            Legend for '$imageHandling':
            0 - no images
            1 - fetch first image only and hide others
            2 - fetch and resize first image only and hide others
        */

        // Check if the cache folder exists
        $cacheFolderPath = JPATH_SITE.'/'.$cacheLocation;
        if (file_exists($cacheFolderPath) && is_dir($cacheFolderPath)) {
            // all OK
        } else {
            mkdir($cacheFolderPath);
        }

        $feeds = self::multiRequest($feedsArray, $cacheTime);
        $parsedFeeds = self::parseFeeds($feeds);
        $feedItemsArray = array();

        if (is_array($parsedFeeds) && count($parsedFeeds)) {
            foreach ($parsedFeeds as $feed) {
                $feedElements = [];
                foreach ($feed->feedItems as $key => $item) {
                    if ($key < $perFeedItems) {
                        // Create an object to store feed elements
                        $feedElements[$key] = new stdClass();

                        $feedElements[$key]->itemTitle          = $item->title;
                        $feedElements[$key]->itemLink           = trim($item->link);
                        $feedElements[$key]->itemDate           = strftime($dateFormat, strtotime($item->pubDate));
                        $feedElements[$key]->itemDateRSS        = $item->pubDate;
                        $feedElements[$key]->itemDescription    = $item->description;
                        $feedElements[$key]->feedImageSrc       = '';

                        $feedElements[$key]->feedTitle          = self::wordLimiter($feed->feedTitle, 10);
                        $feedElements[$key]->feedURL            = $feed->feedSubscribeUrl;
                        $feedElements[$key]->siteURL            = $feed->feedLink;

                        // Give each feed an index based on date
                        $itemDateIndex = strftime('%Y%m%d%H%M%S', strtotime($item->pubDate)).'_'.$key;

                        // Pass all feed objects to an array
                        $feedItemsArray[$itemDateIndex] = $feedElements[$key];
                    }
                }
            }

            // Reverse sort by key (=feed date)
            krsort($feedItemsArray);

            // Limit output
            $outputArray = array();
            $counter = 0;
            foreach ($feedItemsArray as $feedItem) {
                if ($counter >= $totalFeedItems) {
                    continue;
                }

                // Clean up the feed title
                $feedItem->itemTitle = trim(htmlentities($feedItem->itemTitle, ENT_QUOTES, 'utf-8'));

                // Determine if an image reference exists in the feed description
                if ($imageHandling == 1 || $imageHandling == 2) {
                    $feedImage = self::getFirstImage($feedItem->itemDescription);

                    // If it does, copy, resize and store it locally
                    if (isset($feedImage) && $feedImage['ext']) {

                        // first remove the img tag from the description
                        $feedItem->itemDescription = str_replace($feedImage['tag'], '', trim($feedItem->itemDescription));

                        // then resize and/or assign to variable
                        if ($imageHandling == 2) {
                            $feedItem->feedImageSrc = 'https://images.weserv.nl/?url='.$feedImage['src'].'&w='.$riWidth;
                        } else {
                            $feedItem->feedImageSrc = $feedImage['src'];
                        }
                    } else {
                        $feedItem->feedImageSrc = '';
                    }
                }

                // Strip out images from the description
                $feedItem->itemDescription = preg_replace("#<img.+?>#s", "", $feedItem->itemDescription);

                // Word limit
                if ($wordLimit) {
                    $feedItem->itemDescription = self::wordLimiter($feedItem->itemDescription, $wordLimit);
                }

                $outputArray[] = $feedItem;
                $counter++;
            }

            return $outputArray;
        }
    }

    // Get array of feeds
    public function multiRequest($data, $cacheTime)
    {
        ini_set('max_execution_time', 120); // Set max_execution_time to 120
        $result = array();
        foreach ($data as $id => $url) {
            $url = trim($url);
            if ($url) {
                $feed = self::getFile($url, $cacheTime, $subFolderName = 'feeds');
                $result[$id] = JFile::read($feed);
            }
        }
        return $result;
    }

    // Parse array of feeds
    public function parseFeeds($feeds)
    {
        $feedContents = array();
        foreach ($feeds as $key => $feed) {
            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($feed);

            // If the feed didn't parse, display a warning and skip it
            if ($xml === false) {
                $msg = "Failed loading XML...\n";
                foreach (libxml_get_errors() as $error) {
                    $msg .= "\n" . $error->message;
                }
                JFactory::getApplication()->enqueueMessage($msg);
                libxml_clear_errors();
                continue;
            }

            $feedContents[$key] = new stdClass();

            if (is_object($xml) && $items = $xml->xpath("/rss/channel/item")) {
                $feedContents[$key]->feedSubscribeUrl = $feed;
                $feedContents[$key]->feedTitle = $xml->channel->title;
                $feedContents[$key]->feedLink = $xml->channel->link;
                $feedContents[$key]->feedPubDate = $xml->channel->pubDate;
                $feedContents[$key]->feedDescription = $xml->channel->description;
                foreach ($items as $item) {
                    $feedContents[$key]->feedItems[] = $item;
                }
            } elseif (is_object($xml) && $items = $xml->xpath("/*[local-name()='feed' and namespace-uri()='http://www.w3.org/2005/Atom'] /*[local-name()='entry' and namespace-uri()='http://www.w3.org/2005/Atom']")) {
                $feedContents[$key]->feedSubscribeUrl = $feed;
                $feedContents[$key]->feedTitle = (string)$xml->title;
                $feedContents[$key]->feedLink = (string)$xml->link->attributes()->href;
                $feedContents[$key]->feedPubDate = (string)$xml->updated;
                $feedContents[$key]->feedDescription = (string)$xml->subtitle;
                foreach ($items as $item) {
                    $tmp = new stdClass();
                    $tmp->title = (string)$item->title;
                    if ($item->link->count() > 1) {
                        foreach ($item->link as $link) {
                            if ((string)$link->attributes()->rel == 'alternate') {
                                $tmp->link = (string)$link->attributes()->href;
                            }
                        }
                    } else {
                        $tmp->link = (string)$item->link->attributes()->href;
                    }
                    $tmp->pubDate = (string)$item->updated;
                    $tmp->description = (!empty($item->content)) ? $item->content : $item->summary;
                    $tmp->author = (string)$item->author->name;
                    $feedContents[$key]->feedItems[] = $tmp;
                }
            }
        }
        return $feedContents;
    }

    // Word Limiter
    public function wordLimiter($str, $limit = 100, $end_char = '[&#8230;]')
    {
        if (trim($str) == '') {
            return $str;
        }
        $str = strip_tags($str);
        preg_match('/\s*(?:\S*\s*){'. (int) $limit .'}/', $str, $matches);
        if (strlen($matches[0]) == strlen($str)) {
            $end_char = '';
        }
        return rtrim($matches[0]).$end_char;
    }

    // Grab the first image in a string
    public function getFirstImage($string)
    {
        // find images
        $regex = "#<img.+?>#s";
        if (preg_match_all($regex, $string, $matches, PREG_PATTERN_ORDER) > 0) {
            $img = array();
            // Entire <img> tag
            $img['tag'] = $matches[0][0];
            // Image src
            if (preg_match("#src=(\"|').+?(\"|')#s", $img['tag'], $imgSrc)) {
                $img['src'] = str_replace(['src="', 'src=\''], '', $imgSrc[0]);
                $img['src'] = str_replace(['"', '\''], '', $img['src']);
            } else {
                $img['src'] = false;
            }
            // Is this a real content image?
            if (preg_match("#\.(jpg|jpeg|png|gif)#is", $img['src'], $imgExt)) {
                $img['ext'] = true;
            } else {
                $img['ext'] = false;
            }
            return $img;
        }
    }

    // Get remote file
    public function getFile($url, $cacheTime = 3600, $subFolderName = '', $extensionName = 'mod_jw_srfr')
    {
        jimport('joomla.filesystem.file');

        // Stream context defaults
        $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36';
        $defaultStreamContext = stream_context_set_default([
            'http' => [
                'user_agent' => $userAgent
            ],
            "ssl" => [
                "verify_peer" => false,
                "verify_peer_name" => false
            ]
        ]);

        // Check cache folder
        if ($subFolderName) {
            $cacheFolderPath = JPATH_SITE.'/cache/'.$extensionName.'/'.$subFolderName;
        } else {
            $cacheFolderPath = JPATH_SITE.'/cache/'.$extensionName;
        }

        if (file_exists($cacheFolderPath) && is_dir($cacheFolderPath)) {
            // all OK
        } else {
            mkdir($cacheFolderPath);
        }

        $url = trim($url);

        if (substr($url, 0, 4) == "http") {
            $turl = explode("?", $url);
            $matchComponents = array("#(http|https)\:\/\/#s","#www\.#s");
            $replaceComponents = array("","");
            $turl = preg_replace($matchComponents, $replaceComponents, $turl[0]);
            $turl = str_replace(array("/","-","."), array("_","_","_"), $turl);
            $tmpFile = $cacheFolderPath.'/'.urlencode($turl).'_'.md5($url).'.cache';
        } else {
            $tmpFile = $cacheFolderPath.'/cached_'.md5($url);
        }

        // Check if a cached copy exists otherwise create it
        if (file_exists($tmpFile) && is_readable($tmpFile) && (filemtime($tmpFile) + $cacheTime) > time()) {
            $result = $tmpFile;
        } else {
            // Get file
            $feedOutput = '';
            if (substr($url, 0, 4) == "http") {
                // remote file
                if (ini_get('allow_url_fopen')) {
                    // file_get_contents
                    $feedOutput = file_get_contents($url);
                } elseif (in_array('curl', get_loaded_extensions())) {
                    // cURL
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_HEADER, false);
                    curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    $feedOutput = curl_exec($ch);
                    curl_close($ch);
                } else {
                    // fsockopen
                    $readURL = parse_url($url);
                    $relativePath = (isset($readURL['query'])) ? $readURL['path']."?".$readURL['query'] : $readURL['path'];
                    $fp = fsockopen($readURL['host'], 80, $errno, $errstr, 5);
                    if ($fp) {
                        $out = "GET ".$relativePath." HTTP/1.1\r\n";
                        $out .= "Host: ".$readURL['host']."\r\n";
                        $out .= "Connection: Close\r\n\r\n";
                        fwrite($fp, $out);
                        $header = '';
                        $body = '';
                        do {
                            $header .= fgets($fp, 128);
                        } while (strpos($header, "\r\n\r\n") === false); // get the header data
                        while (!feof($fp)) {
                            $body .= fgets($fp, 128);
                        } // get the actual content
                        fclose($fp);
                        $feedOutput = $body;
                    }
                }

                // Cleanup the content received
                $feedOutput = preg_replace("#(\r\n|\n|\r|\t|\s+|<!--(.*?)-->)#s", " ", $feedOutput);
                JFile::write($tmpFile, $feedOutput);
                $result = $tmpFile;
            } else {
                // local file
                $result = $url;
            }
        }

        return $result;
    }
} // END CLASS
