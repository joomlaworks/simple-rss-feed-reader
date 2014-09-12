<?php
/**
 * @version		3.3
 * @package		Simple RSS Feed Reader (module)
 * @author    	JoomlaWorks - http://www.joomlaworks.net
 * @copyright	Copyright (c) 2006 - 2014 JoomlaWorks Ltd. All rights reserved.
 * @license		GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die ;

require_once (dirname(__FILE__).'/base.php');

class JWElementTemplate extends JWElement
{

	public function fetchElement($name, $value, &$node, $control_name)
	{
		jimport('joomla.filesystem.folder');
		$modTemplatesPath = JPATH_SITE.'/modules/mod_jw_srfr/tmpl';
		$modTemplatesFolders = JFolder::folders($modTemplatesPath);
		$db = JFactory::getDBO();
		if (version_compare(JVERSION, '1.6', 'ge'))
		{
			$query = "SELECT template FROM #__template_styles WHERE client_id = 0 AND home = 1";
		}
		else
		{
			$query = "SELECT template FROM #__templates_menu WHERE client_id = 0 AND menuid = 0";
		}
		$db->setQuery($query);
		$template = $db->loadResult();
		$templatePath = JPATH_SITE.'/templates/'.$template.'/html/mod_jw_srfr';
		if (JFolder::exists($templatePath))
		{
			$templateFolders = JFolder::folders($templatePath);
			$folders = @array_merge($templateFolders, $modTemplatesFolders);
			$folders = @array_unique($folders);
		}
		else
		{
			$folders = $modTemplatesFolders;
		}
		sort($folders);
		$options = array();
		foreach ($folders as $folder)
		{
			$options[] = JHTML::_('select.option', $folder, $folder);
		}
		$fieldName = version_compare(JVERSION, '1.6', 'ge') ? $name : $control_name.'['.$name.']';
		return JHTML::_('select.genericlist', $options, $fieldName, '', 'value', 'text', $value);
	}

}

class JFormFieldTemplate extends JWElementTemplate
{
	var $type = 'template';
}

class JElementTemplate extends JWElementTemplate
{
	var $_name = 'template';
}
