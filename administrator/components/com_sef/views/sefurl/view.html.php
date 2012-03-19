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

jimport('joomla.application.component.view');

class SEFViewSEFUrl extends JView
{
    function display($tpl = null)
    {
        //get the data
        $sef      =& $this->get('Data');
        $lists    = $this->get('Lists');
        $isNew    = ($sef->id < 1);

        $text = $isNew ? JText::_( 'New' ) : JText::_( 'Edit' );
        JToolBarHelper::title('JoomSEF - ' .  JText::_( 'SEF URL' ).' [ ' . $text.' ]', 'url-edit.png' );
        JToolBarHelper::save();
        if ($isNew)  {
            JToolBarHelper::cancel();
        } else {
            // for existing items the button is renamed `close`
            JToolBarHelper::cancel('cancel', 'Close');
        }

        $this->assignRef('sef', $sef);
        $this->assignRef('lists', $lists);
        
        JHTML::_('behavior.tooltip');
        
        // Load JS
        $document = & JFactory::getDocument();
        $document->addScript('components/com_sef/assets/js/words.js');
        $document->addStyleSheet('components/com_sef/assets/css/words.css');

        parent::display($tpl);
    }
    
}
?>