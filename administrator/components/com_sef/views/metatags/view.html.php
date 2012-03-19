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

class SEFViewMetaTags extends JView
{
	function display($tpl = null)
	{
	    $icon = 'manage-tags.png';
		JToolBarHelper::title(JText::_('JoomSEF Meta Tags Manager'), $icon);
		
        $this->assign($this->getModel());
        
        JToolBarHelper::save();
        JToolBarHelper::apply();
        JToolBarHelper::spacer();
        JToolBarHelper::back('Back', 'index.php?option=com_sef');
        
		// Get data from the model
        $this->assignRef('items', $this->get('Data'));
        $this->assignRef('total', $this->get('Total'));
        $this->assignRef('lists', $this->get('Lists'));
        $this->assignRef('pagination', $this->get('Pagination'));
        
        JHTML::_('behavior.tooltip');
        
		parent::display($tpl);
	}

}
