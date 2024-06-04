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

class mod_jw_srfrInstallerScript
{
    /*
    public function preflight($type, $parent)
    {
        echo '<p>Anything here happens before the installation/update/uninstallation of the module.</p>';
    }

    public function install($parent)
    {
        echo '<p>The module has been installed.</p>';
    }

    public function uninstall($parent)
    {
        echo '<p>The module has been uninstalled.</p>';
    }

    public function update($parent)
    {
        echo '<p>The module has been updated to version' . $parent->get('manifest')->version . '.</p>';
    }
    */

    public function postflight($type, $parent)
    {
        //echo '<p>Anything here happens after the installation/update/uninstallation of the module.</p>';

        // Cleanups
        if (JFile::exists(JPATH_SITE.'/modules/mod_jw_srfr/redir.php')) {
            JFile::delete(JPATH_SITE.'/modules/mod_jw_srfr/redir.php');
        }

        // Rename XML file for Joomla 3.x
        if (JFile::exists(JPATH_SITE.'/modules/mod_jw_srfr/_mod_jw_srfr.xml')) {
            JFile::move(
                JPATH_SITE.'/modules/mod_jw_srfr/_mod_jw_srfr.xml',
                JPATH_SITE.'/modules/mod_jw_srfr/mod_jw_srfr.xml'
            );
        }
    }
}
