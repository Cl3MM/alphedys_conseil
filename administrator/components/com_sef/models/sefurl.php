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

class SEFModelSEFUrl extends JModel
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
                $query = "SELECT * FROM `#__sefurls` WHERE `id` = '{$this->_id}'";
                $this->_db->setQuery( $query );
                $this->_data = $this->_db->loadObject();
            }
        }
        if (!$this->_data) {
            $sefConfig =& SEFConfig::getConfig();

            $this->_data = new stdClass();
            $this->_data->id = 0;
            $this->_data->cpt = null;
            $this->_data->sefurl = null;
            $this->_data->origurl = null;
            $this->_data->Itemid = null;
            $this->_data->metadesc = null;
            $this->_data->metakey = null;
            $this->_data->metatitle = null;
            $this->_data->metalang = null;
            $this->_data->metarobots = null;
            $this->_data->metagoogle = null;
            $this->_data->canonicallink = null;
            $this->_data->dateadd = null;
            $this->_data->enabled = 1;
            $this->_data->locked = 0;
            $this->_data->sef = 1;
            $this->_data->sm_indexed = ($sefConfig->sitemap_indexed ? 1 : 0);
            $this->_data->sm_date = date('Y-m-d');
            $this->_data->sm_frequency = $sefConfig->sitemap_frequency;
            $this->_data->sm_priority = $sefConfig->sitemap_priority;
        }

        return $this->_data;
    }

    function getLists()
    {
        $lists = array();

        if (empty($this->_data)) {
            $this->getData();
        }

        $lists['sm_indexed'] = '<input type="checkbox" name="sm_indexed" value="1" '.($this->_data->sm_indexed ? 'checked="checked" ' : '').'/>';

        $lists['sm_date'] = JHTML::calendar($this->_data->sm_date, 'sm_date', 'sm_date', '%Y-%m-%d', 'size="13"');

        $freqs[] = JHTML::_('select.option', 'always', 'always');
        $freqs[] = JHTML::_('select.option', 'hourly', 'hourly');
        $freqs[] = JHTML::_('select.option', 'daily', 'daily');
        $freqs[] = JHTML::_('select.option', 'weekly', 'weekly');
        $freqs[] = JHTML::_('select.option', 'monthly', 'monthly');
        $freqs[] = JHTML::_('select.option', 'yearly', 'yearly');
        $freqs[] = JHTML::_('select.option', 'never', 'never');
        $lists['sm_frequency'] = JHTML::_('select.genericlist', $freqs, 'sm_frequency', 'class="inputbox" size="1"', 'value', 'text', $this->_data->sm_frequency);

        $priorities[] = JHTML::_('select.option', '0.0', '0.0');
        $priorities[] = JHTML::_('select.option', '0.1', '0.1');
        $priorities[] = JHTML::_('select.option', '0.2', '0.2');
        $priorities[] = JHTML::_('select.option', '0.3', '0.3');
        $priorities[] = JHTML::_('select.option', '0.4', '0.4');
        $priorities[] = JHTML::_('select.option', '0.5', '0.5');
        $priorities[] = JHTML::_('select.option', '0.6', '0.6');
        $priorities[] = JHTML::_('select.option', '0.7', '0.7');
        $priorities[] = JHTML::_('select.option', '0.8', '0.8');
        $priorities[] = JHTML::_('select.option', '0.9', '0.9');
        $priorities[] = JHTML::_('select.option', '1.0', '1.0');
        $lists['sm_priority'] = JHTML::_('select.genericlist', $priorities, 'sm_priority', 'class="inputbox" size="1"', 'value', 'text', $this->_data->sm_priority);

        // Get linked words
        $words = $this->_getWords();
        $wordOpts = array();
        if (is_array($words)) {
            foreach ($words as $word) {
                $wordOpts[] = JHTML::_('select.option', $word->id, $word->word);
            }
        }
        $lists['words'] = JHTML::_('select.genericlist', $wordOpts, 'words', 'class="inputbox" size="10" multiple="multiple"', 'value', 'text');

        return $lists;
    }

    function _getWords()
    {
        $words = array();
        if ($this->_id != 0) {
            $query = "SELECT `w`.`id`, `w`.`word` FROM `#__sefwords` AS `w` INNER JOIN `#__sefurlword_xref` AS `x` ON `w`.`id` = `x`.`word` WHERE `x`.`url` = '{$this->_id}'";
            $this->_db->setQuery($query);
            $words = $this->_db->loadObjectList();
        }

        return $words;
    }

    function store()
    {
        $row =& $this->getTable();

        $data = JRequest::get('post');

        // Handle the enabled, sef and locked checkboxes
        if (!isset($data['enabled'])) {
            $data['enabled'] = '0';
        }
        if (!isset($data['sef'])) {
            $data['sef'] = '0';
        }
        if (!isset($data['locked'])) {
            $data['locked'] = '0';
        }

        // Handle the sitemap index checkbox
        if (!isset($data['sm_indexed'])) {
            $data['sm_indexed'] = '0';
        }

        // Bind the form fields to the table
        if (!$row->bind($data)) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Make sure the record is valid
        if (!$row->check()) {
            $this->setError($row->_error);
            return false;
        }

        // Set the priority according to Itemid
        if ($row->Itemid != '') {
            $row->priority = _COM_SEF_PRIORITY_DEFAULT_ITEMID;
        }
        else {
            $row->priority = _COM_SEF_PRIORITY_DEFAULT;
        }

        // Store the table to the database
        if (!$row->store()) {
            $this->setError( $row->getError() );
            return false;
        }

        // Handle the words references
        // remove the current bindings
        $this->_db->setQuery("DELETE FROM `#__sefurlword_xref` WHERE `url` = '{$row->id}'");
        if (!$this->_db->query()) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // find all the IDs for words
        if (!empty($data['wordsArray'])) {
            $ids = array_map('trim', explode("\n", $data['wordsArray']));
            if (!empty($ids)) {
                for ($i = 0, $n = count($ids); $i < $n; $i++) {
                    if (!is_numeric($ids[$i])) {
                        // Try to find the word in DB
                        $this->_db->setQuery("SELECT `id` FROM `#__sefwords` WHERE `word` = '{$ids[$i]}' LIMIT 1");
                        $id = $this->_db->loadResult();
                        if (!$id) {
                            // Add word to DB
                            $this->_db->setQuery("INSERT INTO `#__sefwords` (`word`) VALUES ('{$ids[$i]}')");
                            if (!$this->_db->query()) {
                                $this->setError($this->_db->getErrorMsg());
                                return false;
                            }
                            $ids[$i] = $this->_db->insertid();
                        }
                        else {
                            // Word found
                            $ids[$i] = $id;
                        }
                    }
                }

                // Now we should have all the words in database with corresponding IDs in array
                $query = "INSERT INTO `#__sefurlword_xref` (`word`, `url`) VALUES ";
                for ($i = 0, $n = count($ids); $i < $n; $i++) {
                    if ($i > 0) {
                        $query .= ', ';
                    }
                    $query .= "('{$ids[$i]}', '{$row->id}')";
                }
                $this->_db->setQuery($query);
                if (!$this->_db->query()) {
                    $this->setError($this->_db->getErrorMsg());
                    return false;
                }
            }
        }

        // check if there's old url to save to Moved Permanently table
        $unchanged = JRequest::getVar('unchanged');
        if (!empty($unchanged)) {
            $row =& $this->getTable('MovedUrl');
            $row->old = $unchanged;
            $row->new = JRequest::getVar('sefurl');

            // pre-save checks
            if (!$row->check()) {
                $this->setError($row->getError());
                return false;
            }

            // save the changes
            if (!$row->store()) {
                $this->setError($row->getError());
                return false;
            }
        }

        return true;
    }

    function delete()
    {
        $cids = JRequest::getVar('cid', array(0), 'post', 'array');

        if (count($cids)) {
            $ids = implode(',', $cids);
            $query = "DELETE FROM `#__sefurls` WHERE `id` IN ($ids) AND `locked` = '0'";
            $this->_db->setQuery($query);
            if (!$this->_db->query()) {
                $this->setError($this->_db->getErrorMsg());
                return false;
            }
        }
        return true;
    }

    function setActive()
    {
        if( $this->_id == 0 ) {
            return false;
        }

        // Get the SEF URL for given id
        $row =& $this->getData();

        // Set priority to 0 for given id
        $query = "UPDATE `#__sefurls` SET `priority` = '0' WHERE `id` = '{$this->_id}' LIMIT 1";
        $this->_db->setQuery( $query );
        if( !$this->_db->query() ) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        // Set priority to 100 for every other same SEF URL
        $query = "UPDATE `#__sefurls` SET `priority` = '100' WHERE (`sefurl` = '{$row->sefurl}') AND (`id` != '{$this->_id}')";
        $this->_db->setQuery( $query );
        if( !$this->_db->query() ) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return true;
    }

    function setEnabled($state)
    {
        if( $this->_id == 0 ) {
            return false;
        }

        $state = intval($state);
        $query = "UPDATE `#__sefurls` SET `enabled` = '$state' WHERE `id` = '{$this->_id}' LIMIT 1";
        $this->_db->setQuery( $query );
        if( !$this->_db->query() ) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return true;
    }

    function setLocked($state)
    {
        if( $this->_id == 0 ) {
            return false;
        }

        $state = intval($state);
        $query = "UPDATE `#__sefurls` SET `locked` = '$state' WHERE `id` = '{$this->_id}' LIMIT 1";
        $this->_db->setQuery( $query );
        if( !$this->_db->query() ) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return true;
    }

    function setSEF($state)
    {
        if( $this->_id == 0 ) {
            return false;
        }

        $state = intval($state);
        $query = "UPDATE `#__sefurls` SET `sef` = '$state' WHERE `id` = '{$this->_id}' LIMIT 1";
        $this->_db->setQuery( $query );
        if( !$this->_db->query() ) {
            $this->setError($this->_db->getErrorMsg());
            return false;
        }

        return true;
    }

}
?>