<?php
/**
 * @version    3.5
 * @package    Simple RSS Feed Reader (module)
 * @author     JoomlaWorks - http://www.joomlaworks.net
 * @copyright  Copyright (c) 2006 - 2016 JoomlaWorks Ltd. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

// no direct access
defined('_JEXEC') or die ;

require_once (dirname(__FILE__).'/base.php');

class JWElementHeader extends JWElement
{
	public function fetchElement($name, $value, &$node, $control_name)
	{

		$document = JFactory::getDocument();
		$document->addStyleSheet(JURI::root(true).'/modules/mod_jw_srfr/includes/elements/header.css');
		if (version_compare(JVERSION, '2.5.0', 'ge'))
		{
			return '<div class="jwHeaderContainer"><div class="jwHeaderContent">'.JText::_($value).'</div><div class="jwHeaderClr"></div></div>';
		}
		else
		{
			return '<div class="jwHeaderContainer15"><div class="jwHeaderContent">'.JText::_($value).'</div><div class="jwHeaderClr"></div></div>';
		}

	}

	public function fetchTooltip($label, $description, &$node, $control_name, $name)
	{
		return NULL;
	}

}

class JFormFieldHeader extends JWElementHeader
{
	var $type = 'header';
}

class JElementHeader extends JWElementHeader
{
	var $_name = 'header';
}
