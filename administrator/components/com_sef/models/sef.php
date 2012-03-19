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

jimport('joomla.application.component.model');

class SEFModelSEF extends JModel
{
    function getFeed()
    {
        $sefConfig =& SEFConfig::getConfig();
        
        if (!$sefConfig->artioFeedDisplay) {
            return '';
        }
        
        $options = array();
        $options['rssUrl'] = $sefConfig->artioFeedUrl;
        $options['cache_time'] = null;
        
        $rssDoc =& JFactory::getXMLparser('RSS', $options);
        
        if ($rssDoc == false) {
            return JText::_('Error connecting to RSS feed.');
        }
        
        $items = $rssDoc->get_items();
        
        if (count($items) == 0) {
            return JText::_('No items to display.');
        }
        
        $txt = '';
        for ($i = 0, $n = count($items); $i < $n; $i++)
        {
            $item =& $items[$i];
            
            $title = $item->get_title();
            $link = $item->get_link();
            $desc = $item->get_description();
            $date = $item->get_date('j. F Y');
            $author = $item->get_author();
            
            $txt .= '<div class="feed-item">';
            $txt .= '<div class="feed-title"><a href="'.$link.'" target="_blank">'.$title.'</a></div>';
            $txt .= '<div class="feed-text">'.$desc.'</div>';
            $txt .= '</div>';
        }
        
        return $txt;
    }
}

?>