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

class SEFControllerWords extends SEFController
{
    /**
     * constructor (registers additional tasks to methods)
     * @return void
     */
    function __construct()
    {
        parent::__construct();
        
        $this->registerTask('add', 'edit');
    }

    function display()
    {
        JRequest::setVar( 'view', 'words' );
        
        parent::display();
    }
    
    function edit()
    {
        JRequest::setVar( 'view', 'word' );
        JRequest::setVar( 'hidemainmenu', 1 );
        
        parent::display();
    }
    
    function save()
    {
        $model = $this->getModel('words');

        if ($model->store()) {
            $msg = '';
        } else {
            $msg = JText::_( 'Error Saving Words' ) . ': ' . $model->getError();
        }
        
        $this->setRedirect('index.php?option=com_sef&controller=words', $msg);
    }

    function remove()
    {
		$model = $this->getModel('words');
		
		if(!$model->delete()) {
			$msg = JText::_( 'Error: One or More Words Could not be Deleted' );
		} else {
			$msg = '';
		}

		$this->setRedirect( 'index.php?option=com_sef&controller=words', $msg );
    }
}
?>
