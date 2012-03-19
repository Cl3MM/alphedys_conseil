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

class SEFViewWords extends JView
{
	function display($tpl = null)
	{
	    $icon = 'manage-words.png';
		JToolBarHelper::title(JText::_('JoomSEF Words Manager'), $icon);
		
        $this->assign($this->getModel());
        
        JToolBarHelper::addNew();
		JToolBarHelper::editList();
		JToolBarHelper::deleteList(JText::_('Are you sure you want to delete selected words?'));
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
