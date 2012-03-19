<?php
/**
 * SEF component for Joomla! 1.5
 *
 * @author      ARTIO s.r.o.
 * @copyright   ARTIO s.r.o., http://www.artio.cz
 * @package     JoomSEF
 * @version     3.1.0
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

class SEFViewSiteMap extends JView
{
	function display($tpl = null)
	{
	    $icon = 'manage-sitemap.png';
		JToolBarHelper::title(JText::_('JoomSEF SiteMap Manager'), $icon);
		
        $this->assign($this->getModel());
        
        JToolBarHelper::save();
        JToolBarHelper::apply();
        JToolBarHelper::spacer();
        JToolBarHelper::custom('index', 'publish', '', 'Index', false);
        JToolBarHelper::custom('unindex', 'unpublish', '', 'Unindex', false);
        JToolBarHelper::spacer();
        JToolBarHelper::custom('generatexml', 'xml', '', 'Generate XML', false);
        JToolBarHelper::spacer();
        JToolBarHelper::custom('pinggoogle', 'google', '', 'Ping Google', false);
        JToolBarHelper::custom('pingyahoo', 'yahoo', '', 'Ping Yahoo', false);
        JToolBarHelper::custom('pingbing', 'bing', '', 'Ping Bing', false);
        JToolBarHelper::custom('pingservices', 'services', '', 'Ping Services', false);
        JToolBarHelper::spacer();
        JToolBarHelper::back('Back', 'index.php?option=com_sef');
        
		// Get data from the model
        $this->assignRef('items', $this->get('Data'));
        $this->assignRef('total', $this->get('Total'));
        $this->assignRef('lists', $this->get('Lists'));
        $this->assignRef('pagination', $this->get('Pagination'));
        
        // Check the sitemap changed flag
        $sefConfig =& SEFConfig::getConfig();
        $file = JPATH_ROOT.DS.$sefConfig->sitemap_filename.'.xml';
        if ($sefConfig->sitemap_changed || !file_exists($file)) {
            JError::raiseNotice(100, JText::_('Your sitemap file is deprecated or not created. After you make all the changes, you should hit the Generate XML button.'));
        }
        
        JHTML::_('behavior.tooltip');
        
		parent::display($tpl);
	}

}
