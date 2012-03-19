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

class SEFModelWord extends JModel
{
    /**
     * Constructor that retrieves the ID from the request
     *
     * @access    public
     * @return    void
     */
    function __construct()
    {
        parent::__construct();

        $array = JRequest::getVar('cid',  0, '', 'array');
        $this->setId((int)$array[0]);
    }

    function setId($id)
    {
        // Set id and wipe data
        $this->_id      = $id;
        $this->_data    = null;
    }

    function &getData()
    {
        // Load the data
        if (empty($this->_data)) {
            if ($this->_id != 0) {
                $query = "SELECT * FROM `#__sefwords` WHERE `id` = '{$this->_id}'";
                $this->_db->setQuery( $query );
                $this->_data = $this->_db->loadObject();
            }
        }
        if (!$this->_data) {
            $this->_data = new stdClass();
            $this->_data->id = 0;
            $this->_data->word = '';
        }

        return $this->_data;
    }
    
    function getLists()
    {
        $lists = array();
        
        if (empty($this->_data)) {
            $this->getData();
        }
        
        // Word
        $lists['word'] = '<input type="text" name="word" id="word" class="inputbox" size="40" value="'.$this->_data->word.'" />';
        
        // Get linked words
        $urls = $this->_getUrls();
        $urlOpts = array();
        if (is_array($urls)) {
            foreach ($urls as $url) {
                $urlOpts[] = JHTML::_('select.option', $url->id, $url->sefurl);
            }
        }
        $lists['urls'] = JHTML::_('select.genericlist', $urlOpts, 'urls', 'class="inputbox" size="10" multiple="multiple"', 'value', 'text');
        
        return $lists;
    }
    
    function _getUrls()
    {
        $words = array();
        if ($this->_id != 0) {
            $query = "SELECT `u`.`id`, `u`.`sefurl` FROM `#__sefurls` AS `u` INNER JOIN `#__sefurlword_xref` AS `x` ON `u`.`id` = `x`.`url` WHERE `x`.`word` = '{$this->_id}'";
            $this->_db->setQuery($query);
            $words = $this->_db->loadObjectList();
        }
        
        return $words;
    }
}
?>