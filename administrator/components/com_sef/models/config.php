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

class SEFModelConfig extends JModel
{
    function __construct()
    {
        parent::__construct();
    }

    function &getLists()
    {
        $db =& JFactory::getDBO();
        $sefConfig = SEFConfig::getConfig();

        $std_opt = 'class="inputbox" size="2"';

        $lists['enabled']        = JHTML::_('select.booleanlist', 'enabled',         $std_opt, $sefConfig->enabled);
        $lists['lowerCase']      = JHTML::_('select.booleanlist', 'lowerCase',       $std_opt, $sefConfig->lowerCase);
        $lists['disableNewSEF']  = JHTML::_('select.booleanlist', 'disableNewSEF',   $std_opt, $sefConfig->disableNewSEF);
        $lists['dontRemoveSid']  = JHTML::_('select.booleanlist', 'dontRemoveSid',   $std_opt, $sefConfig->dontRemoveSid);
        $lists['setQueryString'] = JHTML::_('select.booleanlist', 'setQueryString',  $std_opt, $sefConfig->setQueryString);
        $lists['parseJoomlaSEO'] = JHTML::_('select.booleanlist', 'parseJoomlaSEO',  $std_opt, $sefConfig->parseJoomlaSEO);
        $lists['checkJunkUrls']  = JHTML::_('select.booleanlist', 'checkJunkUrls',   $std_opt, $sefConfig->checkJunkUrls);
        $lists['preventNonSefOverwrite']    = JHTML::_('select.booleanlist', 'preventNonSefOverwrite', $std_opt, $sefConfig->preventNonSefOverwrite);

        $basehrefs[] = JHTML::_('select.option', _COM_SEF_BASE_HOMEPAGE,    JText::_('Yes - always use only base URL'));
        $basehrefs[] = JHTML::_('select.option', _COM_SEF_BASE_CURRENT,     JText::_('Yes - always use full SEO URL'));
        $basehrefs[] = JHTML::_('select.option', _COM_SEF_BASE_NONE,        JText::_('No - disable base href generation'));
        $basehrefs[] = JHTML::_('select.option', _COM_SEF_BASE_IGNORE,      JText::_('No - leave original'));
        $lists['check_base_href'] = JHTML::_('select.genericlist', $basehrefs, 'check_base_href', 'class="inputbox" size="1"', 'value', 'text', $sefConfig->check_base_href);
        
        // www and non-www handling
        $wwws[] = JHTML::_('select.option', _COM_SEF_WWW_NONE,          JText::_('Don\'t handle'));
        $wwws[] = JHTML::_('select.option', _COM_SEF_WWW_USE_WWW,       JText::_('Always use www domain'));
        $wwws[] = JHTML::_('select.option', _COM_SEF_WWW_USE_NONWWW,    JText::_('Always use non-www domain'));
        $lists['wwwHandling'] = JHTML::_('select.genericlist', $wwws, 'wwwHandling', 'class="inputbox" size="1"', 'value', 'text', $sefConfig->wwwHandling);
        
        if( SEFTools::JoomFishInstalled() ) {
            $jfrouterEnabled = JPluginHelper::isEnabled('system', 'jfrouter');
            
            // lang placement
            $langPlacement[] = JHTML::_('select.option', _COM_SEF_LANG_PATH,   JText::_('include in path'));
            $langPlacement[] = JHTML::_('select.option', _COM_SEF_LANG_SUFFIX, JText::_('add as suffix'));
            $langPlacement[] = JHTML::_('select.option', _COM_SEF_LANG_DOMAIN, JText::_('use different domains'));
            $langPlacement[] = JHTML::_('select.option', _COM_SEF_LANG_NONE,   JText::_('do not add'));
            $lists['langPlacement'] = JHTML::_('select.genericlist', $langPlacement, 'langPlacement', 'class="inputbox" size="1"', 'value', 'text', $sefConfig->langPlacement);
            
            // Prepare main language array
            $mainlangs = array();
            $mainlangs[] = JHTML::_('select.option', '0', JText::_('(none)'));
    
            // language domains and main language
            $db->setQuery("SELECT `id`, `shortcode`, `name` FROM `#__languages` WHERE `active` = '1' ORDER BY `ordering`");
            $langs = $db->loadObjectList();
            if( @count(@$langs) ) {
                $uri =& JURI::getInstance();
                $host = $uri->getHost();
                
                foreach($langs as $lang) {
                    $l = new stdClass();
                    $l->code = $lang->shortcode;
                    $l->name = $lang->name;
                    $l->value = isset($sefConfig->jfSubDomains[$lang->shortcode]) ? $sefConfig->jfSubDomains[$lang->shortcode] : $host;
                    
                    // domain list
                    $langlist[] = $l;
                    
                    // main language list
                    $mainlangs[] = JHTML::_('select.option', $l->code, $l->name);
                }
                //$lists['jfSubDomains'] = '<table width="100%" border="0" cellpadding="0" cellspacing="0"><tr>'. implode('</tr><tr>', $langlist) .'</tr></table>';
                $lists['jfSubDomains'] = $langlist;
            }
            
            // Create the main language list
            $lists['mainLanguage'] = JHTML::_('select.genericlist', $mainlangs, 'mainLanguage', 'class="inputbox" size="1"', 'value', 'text', $sefConfig->mainLanguage);
            
            $disabled = '';
            if ($jfrouterEnabled) {
                $disabled = ' disabled="disabled"';
            }

            $lists['jfBrowserLang']     = JHTML::_('select.booleanlist', 'jfBrowserLang', $std_opt.$disabled, $sefConfig->jfBrowserLang);
            $lists['jfLangCookie']      = JHTML::_('select.booleanlist', 'jfLangCookie',  $std_opt.$disabled, $sefConfig->jfLangCookie);
        }
        
        $lists['record404']         = JHTML::_('select.booleanlist', 'record404',           $std_opt, $sefConfig->record404);
        $lists['msg404']            = JHTML::_('select.booleanlist', 'showMessageOn404',    $std_opt, $sefConfig->showMessageOn404);
        $lists['use404itemid']      = JHTML::_('select.booleanlist', 'use404itemid',        $std_opt, $sefConfig->use404itemid);
        $lists['nonSefRedirect']    = JHTML::_('select.booleanlist', 'nonSefRedirect',      $std_opt, $sefConfig->nonSefRedirect);
        $lists['useMoved']          = JHTML::_('select.booleanlist', 'useMoved',            $std_opt, $sefConfig->useMoved);
        $lists['useMovedAsk']       = JHTML::_('select.booleanlist', 'useMovedAsk',         $std_opt, $sefConfig->useMovedAsk);
        $lists['alwaysUseLang']     = JHTML::_('select.booleanlist', 'alwaysUseLang',       $std_opt, $sefConfig->alwaysUseLang);
        $lists['translateNames']    = JHTML::_('select.booleanlist', 'translateNames',      $std_opt, $sefConfig->translateNames);
        $lists['contentUseIndex']   = JHTML::_('select.booleanlist', 'contentUseIndex',     $std_opt, $sefConfig->contentUseIndex);
        $lists['allowUTF']          = JHTML::_('select.booleanlist', 'allowUTF',            $std_opt, $sefConfig->allowUTF);
        $lists['excludeSource']     = JHTML::_('select.booleanlist', 'excludeSource',       $std_opt, $sefConfig->excludeSource);
        $lists['reappendSource']    = JHTML::_('select.booleanlist', 'reappendSource',      $std_opt, $sefConfig->reappendSource);
        $lists['ignoreSource']      = JHTML::_('select.booleanlist', 'ignoreSource',        $std_opt, $sefConfig->ignoreSource);
        $lists['appendNonSef']      = JHTML::_('select.booleanlist', 'appendNonSef',        $std_opt, $sefConfig->appendNonSef);
        $lists['transitSlash']      = JHTML::_('select.booleanlist', 'transitSlash',        $std_opt, $sefConfig->transitSlash);
        $lists['useCache']          = JHTML::_('select.booleanlist', 'useCache',            $std_opt, $sefConfig->useCache);
        $lists['numberDuplicates']  = JHTML::_('select.booleanlist', 'numberDuplicates',    $std_opt, $sefConfig->numberDuplicates);
        $lists['autoCanonical']     = JHTML::_('select.booleanlist', 'autoCanonical',       $std_opt, $sefConfig->autoCanonical);
        $lists['cacheRecordHits']   = JHTML::_('select.booleanlist', 'cacheRecordHits',     $std_opt, $sefConfig->cacheRecordHits);
        $lists['cacheShowErr']      = JHTML::_('select.booleanlist', 'cacheShowErr',        $std_opt, $sefConfig->cacheShowErr);
        $lists['sefComponentUrls']  = JHTML::_('select.booleanlist', 'sefComponentUrls',    $std_opt, $sefConfig->sefComponentUrls);
        $lists['versionChecker']    = JHTML::_('select.booleanlist', 'versionChecker',      $std_opt, $sefConfig->versionChecker);
        $lists['artioFeedDisplay']  = JHTML::_('select.booleanlist', 'artioFeedDisplay',      $std_opt, $sefConfig->artioFeedDisplay);
        $lists['external_nofollow']     = JHTML::_('select.booleanlist', 'external_nofollow',   $std_opt, $sefConfig->external_nofollow);
        $lists['internal_enable']       = JHTML::_('select.booleanlist', 'internal_enable',     $std_opt, $sefConfig->internal_enable);
        $lists['internal_nofollow']     = JHTML::_('select.booleanlist', 'internal_nofollow',   $std_opt, $sefConfig->internal_nofollow);
        $lists['internal_newwindow']    = JHTML::_('select.booleanlist', 'internal_newwindow',  $std_opt, $sefConfig->internal_newwindow);
        $lists['cacheSize']         = '<input type="text" name="cacheSize" size="10" class="inputbox" value="'.$sefConfig->cacheSize.'" />';
        $lists['cacheMinHits']      = '<input type="text" name="cacheMinHits" size="10" class="inputbox" value="'.$sefConfig->cacheMinHits.'" />';
        $lists['junkWords']         = '<input type="text" name="junkWords" size="60" class="inputbox" value="'.$sefConfig->junkWords.'" />';
        $lists['junkExclude']       = '<input type="text" name="junkExclude" size="60" class="inputbox" value="'.$sefConfig->junkExclude.'" />';
        $lists['internal_maxlinks'] = '<input type="text" name="internal_maxlinks" size="10" class="inputbox" value="'.$sefConfig->internal_maxlinks.'" />';
        
        $lists['tag_generator']     = '<input type="text" name="tag_generator" size="60" class="inputbox" value="'.$sefConfig->tag_generator.'" />';
        $lists['tag_googlekey']     = '<input type="text" name="tag_googlekey" size="60" class="inputbox" value="'.$sefConfig->tag_googlekey.'" />';
        $lists['tag_livekey']       = '<input type="text" name="tag_livekey" size="60" class="inputbox" value="'.$sefConfig->tag_livekey.'" />';
        $lists['tag_yahookey']      = '<input type="text" name="tag_yahookey" size="60" class="inputbox" value="'.$sefConfig->tag_yahookey.'" />';

        $lists['artioUserName']     = '<input type="text" name="artioUserName" size="60" class="inputbox" value="'.$sefConfig->artioUserName.'" />';
        $lists['artioPassword']     = '<input type="password" name="artioPassword" size="60" class="inputbox" value="'.$sefConfig->artioPassword.'" />';
        $lists['artioDownloadId']   = '<input type="text" name="artioDownloadId" size="60" class="inputbox" value="'.$sefConfig->artioDownloadId.'" />';

        $lists['trace']             = JHTML::_('select.booleanlist', 'trace', $std_opt, $sefConfig->trace);
        $lists['traceLevel']        = '<input type="text" name="traceLevel" size="2" class="inputbox" value="'.$sefConfig->traceLevel.'" />';
        
        $useSitenameOpts[] = JHTML::_('select.option', _COM_SEF_SITENAME_BEFORE,    JText::_('Yes - before page title'));
        $useSitenameOpts[] = JHTML::_('select.option', _COM_SEF_SITENAME_AFTER,     JText::_('Yes - after page title'));
        $useSitenameOpts[] = JHTML::_('select.option', _COM_SEF_SITENAME_NO,        JText::_('No'));
        $lists['use_sitename']  = JHTML::_('select.genericlist', $useSitenameOpts, 'use_sitename', 'class="inputbox" size="1"', 'value', 'text', $sefConfig->use_sitename);
        
        $lists['enable_metadata']       = JHTML::_('select.booleanlist', 'enable_metadata',    $std_opt, $sefConfig->enable_metadata);
        $lists['prefer_joomsef_title']  = JHTML::_('select.booleanlist', 'prefer_joomsef_title',    $std_opt, $sefConfig->prefer_joomsef_title);
        $lists['sitename_sep']          = '<input type="text" name="sitename_sep" size="10" class="inputbox" value="'.$sefConfig->sitename_sep.'" />';
        $lists['rewrite_keywords']      = JHTML::_('select.booleanlist', 'rewrite_keywords',    $std_opt, $sefConfig->rewrite_keywords);
        $lists['rewrite_description']   = JHTML::_('select.booleanlist', 'rewrite_description',    $std_opt, $sefConfig->rewrite_description);
        $lists['prevent_dupl']          = JHTML::_('select.booleanlist', 'prevent_dupl',    $std_opt, $sefConfig->prevent_dupl);
        
        // Sitemap
        $lists['sitemap_filename']  = '<input type="text" name="sitemap_filename" size="20" class="inputbox" value="'.$sefConfig->sitemap_filename.'" /> .xml';
        $lists['sitemap_indexed']   = JHTML::_('select.booleanlist', 'sitemap_indexed',    $std_opt, $sefConfig->sitemap_indexed);
        $lists['sitemap_pingauto']  = JHTML::_('select.booleanlist', 'sitemap_pingauto',    $std_opt, $sefConfig->sitemap_pingauto);
        $lists['sitemap_yahooid']   = '<input type="text" name="sitemap_yahooId" size="50" class="inputbox" value="'.$sefConfig->sitemap_yahooId.'" />';
        $lists['sitemap_services']  = '<textarea cols="50" rows="4" name="sitemap_services">'.implode("\n", $sefConfig->sitemap_services).'</textarea>';
        
        $freqs = array();
        $freqs[] = JHTML::_('select.option', 'always', 'always');
        $freqs[] = JHTML::_('select.option', 'hourly', 'hourly');
        $freqs[] = JHTML::_('select.option', 'daily', 'daily');
        $freqs[] = JHTML::_('select.option', 'weekly', 'weekly');
        $freqs[] = JHTML::_('select.option', 'monthly', 'monthly');
        $freqs[] = JHTML::_('select.option', 'yearly', 'yearly');
        $freqs[] = JHTML::_('select.option', 'never', 'never');
        $lists['sitemap_frequency'] = JHTML::_('select.genericlist', $freqs, 'sitemap_frequency', 'class="inputbox" size="1"', 'value', 'text', $sefConfig->sitemap_frequency);
        
        $priorities = array();
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
        $lists['sitemap_priority'] = JHTML::_('select.genericlist', $priorities, 'sitemap_priority', 'class="inputbox" size="1"', 'value', 'text', $sefConfig->sitemap_priority);

        $aliases[] = JHTML::_('select.option', '0', JText::_('Full Title'));
        $aliases[] = JHTML::_('select.option', '1', JText::_('Title Alias'));
        $lists['useAlias'] = JHTML::_('select.radiolist', $aliases, 'useAlias', $std_opt, 'value', 'text', $sefConfig->useAlias);

        // get a list of the static content items for 404 page
        $query = "SELECT id, title"
        ."\n FROM #__content"
        ."\n WHERE sectionid = 0 AND title != '404'"
        ."\n AND catid = 0"
        ."\n ORDER BY ordering"
        ;

        $db->setQuery( $query );
        $items = $db->loadObjectList();

        $options = array(JHTML::_('select.option', 0, '('.JText::_('Custom 404 Page').')'));
        $options[] = JHTML::_('select.option', 9999999, '('.JText::_('Front Page').')');

        // assemble menu items to the array
        foreach ( $items as $item ) {
            $options[] = JHTML::_('select.option', $item->id, $item->title);
        }

        $lists['page404'] = JHTML::_('select.genericlist', $options, 'page404', 'class="inputbox" size="1"', 'value', 'text', $sefConfig->page404 );

        // Get the menu selection list
        $selections = JHTML::_('menu.linkoptions');
        $lists['itemid404'] = JHTML::_('select.genericlist', $selections, 'itemid404', 'class="inputbox" size="15"', 'value', 'text', $sefConfig->itemid404 );
        
        $sql="SELECT `id`, `introtext` FROM `#__content` WHERE `title` = '404'";
        $row = null;
        $db->setQuery($sql);
        $row = $db->loadObject();

        $lists['txt404'] = isset($row->introtext) ? $row->introtext : JText::_('ERROR_DEFAULT_404');

        $this->_lists = $lists;

        return $this->_lists;
    }

    /**
	 * Method to store a record
	 *
	 * @access	public
	 * @return	boolean	True on success
	 */
    function store()
    {
        $db =& JFactory::getDBO();
        $sefConfig =& SEFConfig::getConfig();
        $sef_config_file = JPATH_COMPONENT . DS . 'configuration.php';

        // Unset the empty meta tags
        if (isset($_POST['metanames']) && is_array($_POST['metanames'])) {
            for ($i = 0, $n = count($_POST['metanames']); $i < $n; $i++) {
                if (empty($_POST['metanames'][$i])) {
                    unset($_POST['metanames'][$i]);
                    if (isset($_POST['metacontents'][$i])) {
                        unset($_POST['metacontents'][$i]);
                    }
                }
            }
            
            // Create the associative array of custom meta tags
            $_POST['customMetaTags'] = array_combine($_POST['metanames'], $_POST['metacontents']);
        }
        else {
            // No meta tags
            $_POST['customMetaTags'] = array();
        }
        
        // Parse the sitemap ping services
        if (isset($_POST['sitemap_services']) && !empty($_POST['sitemap_services'])) {
            $services = str_replace("\r", '', $_POST['sitemap_services']);
            $services = array_map('trim', explode("\n", $services));
            $_POST['sitemap_services'] = $services;
        }
        
        // Set values
        foreach($_POST as $key => $value) {
            $sefConfig->set($key, $value);
        }

        // 404
        $sql = 'SELECT id  FROM #__content WHERE `title` = "404"';
        $db->setQuery( $sql );

        $introtext = (get_magic_quotes_gpc() ? $_POST['introtext'] : addslashes($_POST['introtext']));
        if ($id = $db->loadResult()){
            $sql = 'UPDATE #__content SET introtext="'.$introtext.'",  modified ="'.date("Y-m-d H:i:s").'" WHERE `id` = "'.$id.'";';
        }
        else {
            $sql='SELECT MAX(id)  FROM #__content';
            $db->setQuery($sql);
            if ($max = $db->loadResult()) {
                $max++;
                $sql = 'INSERT INTO #__content (id, title, alias, introtext, `fulltext`, state, sectionid, mask, catid, created, created_by, created_by_alias, modified, modified_by, checked_out, checked_out_time, publish_up, publish_down, images, urls, attribs, version, parentid, ordering, metakey, metadesc, access, hits) '.
                'VALUES( "'.$max.'", "404", "404", "'.$introtext.'", "", "1", "0", "0", "0", "2004-11-11 12:44:38", "62", "", "'.date("Y-m-d H:i:s").'", "0", "62", "2004-11-11 12:45:09", "2004-10-17 00:00:00", "0000-00-00 00:00:00", "", "", "menu_image=-1\nitem_title=0\npageclass_sfx=\nback_button=\nrating=0\nauthor=0\ncreatedate=0\nmodifydate=0\npdf=0\nprint=0\nemail=0", "1", "0", "0", "", "", "0", "750");';
            }
        }

        $db->setQuery( $sql );
        if (!$db->query()) {
            echo "<script> alert('".addslashes($db->getErrorMsg())."'); window.history.go(-1); </script>\n";
            exit();
        }

        // Check the domains configuration
        if( count($sefConfig->jfSubDomains) ) {
            foreach($sefConfig->jfSubDomains as $code => $domain) {
                $domain = str_replace(array('http://', 'https://'), '', $domain);
                $domain = preg_replace('#/.*$#', '', $domain);
                $sefConfig->jfSubDomains[$code] = $domain;
            }
        }

        $config_written = $sefConfig->saveConfig(0);

        if( $config_written != 0 ) {
            return true;
        } else {
            return false;
        }
    }

}
?>
