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

class SEFControllerAjax extends SEFController
{
    function __construct()
    {
        parent::__construct();
    }

    function findWords()
    {
        $req = JRequest::getString('req');
        
        if (empty($req)) {
            echo '[]';
            jexit();
        }
        
        $db =& JFactory::getDBO();
        $req = $db->getEscaped($req).'%';
        $db->setQuery("SELECT * FROM `#__sefwords` WHERE `word` LIKE '{$req}' ORDER BY `word` LIMIT 10");
        $words = $db->loadObjectList();
        
        if (empty($words)) {
            echo '[]';
            jexit();
        }
        
        $objs = array();
        foreach ($words as $word) {
            $word->word = str_replace("'", "\'", $word->word);
            $objs[] = "{ text: '{$word->word}', data: '{$word->id}' }";
        }
        
        echo '[' . implode(', ', $objs) . ']';
        jexit();
    }

    function findUrls()
    {
        $req = JRequest::getString('req');
        
        if (empty($req)) {
            echo '[]';
            jexit();
        }
        
        $db =& JFactory::getDBO();
        $req = '%'.$db->getEscaped($req).'%';
        $db->setQuery("SELECT * FROM `#__sefurls` WHERE `sefurl` LIKE '{$req}' ORDER BY `sefurl` LIMIT 10");
        $urls = $db->loadObjectList();
        
        if (empty($urls)) {
            echo '[]';
            jexit();
        }
        
        $objs = array();
        foreach ($urls as $url) {
            $objs[] = "{ text: '{$url->sefurl}', data: '{$url->id}' }";
        }
        
        echo '[' . implode(', ', $objs) . ']';
        jexit();
    }
}

?>