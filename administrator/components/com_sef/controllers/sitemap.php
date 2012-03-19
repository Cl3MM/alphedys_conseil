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

class SEFControllerSiteMap extends SEFController
{
    /**
     * constructor (registers additional tasks to methods)
     * @return void
     */
    function __construct()
    {
        parent::__construct();
        
        $this->registerTask('apply', 'save');
    }

    function display()
    {
        JRequest::setVar( 'view', 'sitemap' );
        
        parent::display();
    }
    
    function save()
    {
        $model = $this->getModel('sitemap');

        if ($model->store()) {
            $msg = '';
        } else {
            $msg = JText::_( 'Error Saving SiteMap Data' ) . ': ' . $model->getError();
        }
        
        $task = JRequest::getCmd('task');
        $link = 'index.php?option=com_sef';
        if ($task == 'apply') {
            $link = 'index.php?option=com_sef&controller=sitemap';
        }

        $this->setRedirect($link, $msg);
    }
    
    function generateXml()
    {
        $model = $this->getModel('sitemap');

        if ($model->generateXml()) {
            $msg = JText::_( 'XML Generated' );
        } else {
            $msg = JText::_( 'Error Generating XML' ) . ': ' . $model->getError();
        }
        
        $this->setRedirect('index.php?option=com_sef&controller=sitemap', $msg);
    }
    
    function pingGoogle()
    {
        $model = $this->getModel('sitemap');
        $model->pingGoogle();
        $this->setRedirect('index.php?option=com_sef&controller=sitemap');
    }
    
    function pingYahoo()
    {
        $model = $this->getModel('sitemap');
        $model->pingYahoo();
        $this->setRedirect('index.php?option=com_sef&controller=sitemap');
    }
    
    function pingBing()
    {
        $model = $this->getModel('sitemap');
        $model->pingBing();
        $this->setRedirect('index.php?option=com_sef&controller=sitemap');
    }
    
    function pingServices()
    {
        $model = $this->getModel('sitemap');
        $model->pingServices();
        $this->setRedirect('index.php?option=com_sef&controller=sitemap');
    }
    
    function index()
    {
        $this->_setIndex(1);
    }
    
    function unindex()
    {
        $this->_setIndex(0);
    }
    
    function _setIndex($state)
    {
        $model =& $this->getModel('sitemap');
        
        $msg = '';
        if( !$model->setIndex($state) ) {
            $msg = JText::_( 'Error Saving URLs' );
        }
        
        $this->setRedirect( 'index.php?option=com_sef&controller=sitemap', $msg );
    }
    
}
?>
