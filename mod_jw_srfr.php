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

// JoomlaWorks reference parameters
$mod_name             = "mod_jw_srfr";
$mod_copyrights_start = "\n\n<!-- JoomlaWorks \"Simple RSS Feed Reader\" Module (v4.0) starts here -->\n";
$mod_copyrights_end   = "\n<!-- JoomlaWorks \"Simple RSS Feed Reader\" Module (v4.0) ends here -->\n\n";

// B/C
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}

// API
$app      = JFactory::getApplication();
$document = JFactory::getDocument();
$user     = JFactory::getUser();
$aid      = $user->get('aid');
$template = JRequest::getCmd('template');

// Assign paths
$sitePath = JPATH_SITE;
$siteUrl  = JURI::root(true);

// Module parameters
$moduleclass_sfx              = $params->get('moduleclass_sfx', '');
$mod_template                 = $params->get('template', 'default');
$srfrFeeds                    = $params->get('srfrFeeds');
$srfrFeedsArray               = explode("\n", $srfrFeeds);
$perFeedItems                 = $params->get('perFeedItems', 5);
$totalFeedItems               = $params->get('totalFeedItems', 10);
$feedTimeout                  = $params->get('feedTimeout', 5);
$feedOrder                    = $params->get('feedOrder', 'date_desc');
$feedTitle                    = $params->get('feedTitle', 1);
$feedItemTitle                = $params->get('feedItemTitle', 1);
$feedItemDate                 = $params->get('feedItemDate', 1);
$feedItemDateFormat           = $params->get('feedItemDateFormat', '%b %e, %Y | %H:%M %P');
$feedItemDescription          = $params->get('feedItemDescription', 1);
$feedItemDescriptionWordlimit = $params->get('feedItemDescriptionWordlimit', 40);
$feedItemImageHandling        = $params->get('feedItemImageHandling', 2);
$feedItemImageResizeWidth     = $params->get('feedItemImageResizeWidth', 200);
$feedItemReadMore             = $params->get('feedItemReadMore', 1);
$feedsBlockPreText            = $params->get('feedsBlockPreText');
$feedsBlockPostText           = $params->get('feedsBlockPostText');
$feedsBlockPostLink           = $params->get('feedsBlockPostLink');
$feedsBlockPostLinkURL        = $params->get('feedsBlockPostLinkURL');
$feedsBlockPostLinkTitle      = $params->get('feedsBlockPostLinkTitle');
$srfrCacheTime                = $params->get('srfrCacheTime', 30) * 60;
$cacheLocation                = 'cache/'.$mod_name;

// Includes
require_once(dirname(__FILE__).'/helper.php');

// Fetch content
$srfr = new SimpleRssFeedReaderHelper();
$output = $srfr->getFeeds($srfrFeedsArray, $totalFeedItems, $perFeedItems, $feedTimeout, $feedItemDateFormat, $feedItemDescriptionWordlimit, $cacheLocation, $srfrCacheTime, $feedItemImageHandling, $feedItemImageResizeWidth, $feedOrder);

// Output content with template
echo $mod_copyrights_start;

if ($template) {
    require JPATH_SITE.'/templates/'.$template.'/html/'.$mod_name.'/'.$mod_template.'/default.php';
} else {
    require JModuleHelper::getLayoutPath($mod_name, $mod_template.'/default');
}

echo $mod_copyrights_end;

// END
