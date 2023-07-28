<?php
/**
 * @version    4.0
 * @package    Simple RSS Feed Reader (module)
 * @author     JoomlaWorks - https://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2023 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/* Here we call the stylesheet template.css from a folder called 'css' and located at the same directory with this template file. */
$filePath = JURI::root(true).str_replace(JPATH_SITE, '', dirname(__FILE__));

if (version_compare(JVERSION, '1.6', 'ge')) {
    $document->addStyleSheet($filePath.'/css/template.css?v=4.0');
} else {
    $app = JFactory::getApplication();
    if ($app->getCfg('caching')) {
        echo '<link href="'.$filePath.'/css/template.css?v=4.0" rel="stylesheet" />';
    } else {
        $document->addStyleSheet($filePath.'/css/template.css?v=4.0');
    }
}

?>
<div class="srfrContainer <?php echo $moduleclass_sfx; ?>">

    <?php if($feedsBlockPreText): ?>
    <p class="srfrPreText"><?php echo $feedsBlockPreText; ?></p>
    <?php endif; ?>

    <?php if (isset($output) && count($output)): ?>
    <ul class="srfrList">
        <?php foreach($output as $key=>$feed): ?>
        <li class="srfrRow<?php echo ($key%2) ? ' srfrRowIsEven' : ' srfrRowIsOdd'; ?>">
            <a target="_blank" href="<?php echo $feed->itemLink; ?>">
                <?php if($feedItemTitle): ?>
                <?php echo $feed->itemTitle; ?>
                <?php endif; ?>

                <?php if($feedItemDescription): ?>
                <span>
                    <?php if($feedItemTitle): ?>
                    <b class="srfrTitle"><?php echo $feed->itemTitle; ?></b>
                    <?php endif; ?>

                    <?php if($feedItemDescription || $feed->feedImageSrc): ?>
                    <?php if($feed->feedImageSrc): ?>
                    <img class="srfrImage" src="<?php echo $feed->feedImageSrc; ?>" alt="<?php echo $feed->itemTitle; ?>" />
                    <?php endif; ?>

                    <?php if($feedItemDescription): ?>
                    <?php echo $feed->itemDescription; ?>
                    <?php endif; ?>

                    <br /><br />
                    <?php endif; ?>

                    <?php if($feedTitle): ?>
                    <?php echo JText::_('MOD_JW_SRFR_SOURCE'); ?>: <b class="srfrFeedSourcePopup"><?php echo $feed->feedTitle; ?></b>
                    <br />
                    <?php endif; ?>

                    <?php if($feedItemDate): ?>
                    <?php echo JText::_('MOD_JW_SRFR_CREATED_ON'); ?>: <b><?php echo $feed->itemDate; ?></b>
                    <br />
                    <?php endif; ?>
                </span>
                <?php endif; ?>
            </a>
            <?php if($feedTitle || $feedItemDate): ?>
            <div class="srfrFeedDetails">
                <?php if($feedTitle): ?>
                <span class="srfrFeedSource">
                    <a target="_blank" href="<?php echo $feed->siteURL; ?>"><?php echo $feed->feedTitle; ?></a>
                </span>
                <?php endif; ?>

                <?php if($feedTitle && $feedItemDate): ?> | <?php endif; ?>

                <?php if($feedItemDate): ?>
                <span class="srfrFeedItemDate"><?php echo $feed->itemDate; ?></span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <?php if($feedsBlockPostText): ?>
    <p class="srfrPostText"><?php echo $feedsBlockPostText; ?></p>
    <?php endif; ?>

    <?php if($feedsBlockPostLink): ?>
    <p class="srfrPostTextLink"><a href="<?php echo $feedsBlockPostLinkURL; ?>"><?php echo $feedsBlockPostLinkTitle; ?></a></p>
    <?php endif; ?>
</div>

<div class="clr"></div>
