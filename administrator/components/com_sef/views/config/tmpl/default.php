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
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.html.pane');
$pane =& JPane::getInstance('Tabs');

$jfrouterLink = JText::_('Jfrouter Plugin');
if (SEFTools::JoomFishInstalled()) {
    $db =& JFactory::getDBO();
    $db->setQuery("SELECT `id` FROM `#__plugins` WHERE `folder` = 'system' AND `element` = 'jfrouter' LIMIT 1");
    $jfrouterId = $db->loadResult();
    if (!is_null($jfrouterId)) {
        $jfrouterLink = '<a href="index.php?option=com_plugins&view=plugin&client=site&task=edit&cid[]='.$jfrouterId.'">'.$jfrouterLink.'</a>';
    }
}
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<table class="adminheading">
		<tr><th>		
        <?php
		$config =& JFactory::getConfig();
		$sefConfig =& SEFConfig::getConfig();
		$lists = $this->lists;
		$sef_config_file = JPATH_COMPONENT . DS . 'configuration.php';
		echo 'ARTIO JoomSEF ' . JText::_('Configuration file') . (file_exists($sef_config_file) ? (is_writable($sef_config_file) ? (' <b><font color="green">'.JText::_('Writeable').'</font></b>') : (' <b><font color="red">'.JText::_('Unwriteable').'</font></b>')) : (' <b><font color="red">'.JText::_('Using Default Values').'</font></b>'));
		?>		
		</th></tr>
		</table>
		<?php if (!$config->getValue('sef')) {
			JError::raiseNotice('100', JText::sprintf('INFO_SEF_DISABLED', '<a href="index.php?option=com_config">', '</a>'));
		}
		$x = 0;
	    ?>
	    <script language="Javascript">
	    function submitbutton(pressbutton) {
	        <?php
	        jimport( 'joomla.html.editor' );
	        $editor =& JFactory::getEditor();
	        echo $editor->save('introtext');
	        ?>
	        submitform(pressbutton);
	    }
	    
	    function addMetaTag() {
	        var tbl = document.getElementById('tblMetatags');
	        if( !tbl ) {
	            return;
	        }
	        var tbody = tbl.getElementsByTagName('tbody')[0];
	        if( !tbody ) {
	            return;
	        }
	        
	        var row = document.createElement('tr');
	        var td1 = document.createElement('td');
	        td1.width = '200';
	        td1.innerHTML = '<input type="text" value="" size="40" name="metanames[]" />';
	        var td2 = document.createElement('td');
	        td2.width = '200';
	        td2.innerHTML = '<input type="text" value="" size="60" name="metacontents[]" />';
	        var td3 = document.createElement('td');
	        td3.innerHTML = '<input type="button" value="<?php echo JText::_('Remove Meta tag'); ?>" onclick="removeMetaTag(this);" />';
	        row.appendChild(td1);
	        row.appendChild(td2);
	        row.appendChild(td3);
	        tbody.appendChild(row);
	    }
	    
	    function removeMetaTag(el) {
	        var tbl = document.getElementById('tblMetatags');
	        if( !tbl ) {
	            return;
	        }
	        var tbody = tbl.getElementsByTagName('tbody')[0];
	        if( !tbody ) {
	            return;
	        }

	        while( el ) {
	            if( el.nodeName && (el.nodeName.toLowerCase() == 'tr') ) {
	                break;
	            }
	            el = el.parentNode;
	        }
	        
	        if( el.nodeName && (el.nodeName.toLowerCase() == 'tr') ) {
	           tbody.removeChild(el);
	        }
	    }

        function enableStatus(type)
        {
            var form = document.adminForm;
            if( !form ) {
                return;
            }
            
            form.statusType.value = type;
            submitbutton('enableStatus');
        }
        
        function disableStatus(type)
        {
            var form = document.adminForm;
            if( !form ) {
                return;
            }
            
            form.statusType.value = type;
            submitbutton('disableStatus');
        }
	    </script>
		
		<?php
		echo $pane->startPane('config-pane');
		echo $pane->startPanel(JText::_('Basic'), 'basic');
		?>
		
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('Basic Configuration'); ?></legend>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_JOOMSEF_ENABLED'),JText::_('Enabled'));?></td>
    	            <td width="200"><?php echo JText::_('JoomSEF Enabled');?>?</td>
    	            <td><?php echo $lists['enabled'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_DISABLE_NEW_SEF'),JText::_('Disable creation of new SEF URLs?'));?></td>
    	            <td><?php echo JText::_('Disable creation of new SEF URLs?');?></td>
    	            <td><?php echo $lists['disableNewSEF']; ?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_NUMBER_DUPLICATES'),JText::_('Number duplicate URLs?'));?></td>
    	            <td><?php echo JText::_('Number duplicate URLs?');?></td>
    	            <td><?php echo $lists['numberDuplicates']; ?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_REPLACE_CHAR'),JText::_('Replacement character'));?></td>
    	            <td><?php echo JText::_('Replacement character');?></td>
    	            <td><input type="text" name="replacement" value="<?php echo $sefConfig->replacement;?>" size="1" maxlength="1"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_PAGE_SEP_CHAR'),JText::_('Page spacer character'));?></td>
    	            <td><?php echo JText::_('Page spacer character');?></td>
    	            <td><input type="text" name="pagerep" value="<?php echo $sefConfig->pagerep;?>" size="1" maxlength="1"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_STRIP_CHAR'),JText::_('Strip characters'));?></td>
    	            <td><?php echo JText::_('Strip characters');?></td>
    	            <td><input type="text" name="stripthese" value="<?php echo $sefConfig->stripthese;?>" size="60" maxlength="255"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_FRIEND_TRIM_CHAR'),JText::_('Trim friendly characters'));?></td>
    	            <td><?php echo JText::_('Trim friendly characters');?></td>
    	            <td><input type="text" name="friendlytrim" value="<?php echo $sefConfig->friendlytrim;?>" size="60" maxlength="255"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_USE_ALIAS'),JText::_('Use Title Alias'));?></td>
    	            <td><?php echo JText::_('Use Title or Alias');?></td>
    	            <td><?php echo $lists['useAlias'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_SUFFIX'),JText::_('File suffix'));?></td>
    	            <td><?php echo JText::_('File suffix');?></td>
    	            <td><input type="text" name="suffix" value="<?php echo $sefConfig->suffix; ?>" size="10" maxlength="6"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_ADD_FILE'),JText::_('Default index file'));?></td>
    	            <td><?php echo JText::_('Default index file');?></td>
    	            <td><input type="text" name="addFile" value="<?php echo $sefConfig->addFile; ?>" size="60" maxlength="60"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_PAGE_TEXT'),JText::_('Page text'));?></td>
    	            <td><?php echo JText::_('Page text');?></td>
    	            <td><input type="text" name="pagetext" value="<?php echo $sefConfig->pagetext; ?>" size="30" maxlength="30"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_LOWERCASE'),JText::_('All lowercase'));?></td>
    	            <td><?php echo JText::_('All lowercase');?>?</td>
    	            <td><?php echo $lists['lowerCase'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_USE_SEC-CAT_INDEX'),JText::_('Use index file for sections and categories'));?></td>
    	            <td><?php echo JText::_('Use index for sections and categories');?></td>
    	            <td><?php echo $lists['contentUseIndex']; ?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_WWW_HANDLING'),JText::_('www and non-www domain handling'));?></td>
    	            <td><?php echo JText::_('www and non-www domain handling');?></td>
    	            <td><?php echo $lists['wwwHandling']; ?></td>
    	        </tr>
		      </table>
		  </fieldset>
		  
		  <?php
		  echo $pane->endPanel();
		  echo $pane->startPanel(JText::_('Advanced'), 'advanced');
		  ?>
		  
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('Advanced Configuration');?></legend>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20" valign="top"><?php echo JHTML::_('tooltip', JText::_('TT_ALLOW_UTF'), JText::_('Allow UTF-8 characters in URL'));?></td>
    	            <td width="200" valign="top"><?php echo JText::_('Allow UTF-8 characters in URL');?></td>
    	            <td><?php echo $lists['allowUTF'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td valign="top"><?php echo JHTML::_('tooltip', JText::_('TT_REPLACEMENTS'), JText::_('Non-ascii char replacements'));?></td>
    	            <td valign="top"><?php echo JText::_('Non-ascii char replacements');?></td>
    	            <td><textarea name="replacements" cols="40" rows="5"><?php echo $sefConfig->replacements;?></textarea></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_EXCLUDE_SOURCE'), JText::_('Exclude source info (Itemid)'));?></td>
    	            <td><?php echo JText::_('Exclude source info (Itemid)');?></td>
    	            <td><?php echo $lists['excludeSource'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_REAPPEND_SOURCE'), JText::_('Reappend source (Itemid)'));?></td>
    	            <td><?php echo JText::_('Reappend source (Itemid)');?></td>
    	            <td><?php echo $lists['reappendSource'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_IGNORE_SOURCE'), JText::_('Ignore multiple sources (Itemids)'));?></td>
    	            <td><?php echo JText::_('Ignore multiple sources (Itemids)');?></td>
    	            <td><?php echo $lists['ignoreSource'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_APPEND_NONSEF'), JText::_('Append non-SEF variables to URL'));?></td>
    	            <td><?php echo JText::_('Append non-SEF variables to URL');?></td>
    	            <td><?php echo $lists['appendNonSef'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_TRANSIT_SLASH'), JText::_('Be tolerant to trailing slash?'));?></td>
    	            <td><?php echo JText::_('Be tolerant to trailing slash?');?></td>
    	            <td><?php echo $lists['transitSlash'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_NONSEF_REDIRECT'), JText::_('Redirect nonSEF URLs to SEF?'));?></td>
    	            <td><?php echo JText::_('Redirect nonSEF URLs to SEF?');?></td>
    	            <td><?php echo $lists['nonSefRedirect'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_USE_MOVED'), JText::_('Use Moved Permanently redirection table?'));?></td>
    	            <td><?php echo JText::_('Use Moved Permanently redirection table?');?></td>
    	            <td><?php echo $lists['useMoved'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_USE_MOVED_ASK'), JText::_('Ask before saving URL to Moved Permanently table?'));?></td>
    	            <td><?php echo JText::_('Ask before saving URL to Moved Permanently table?');?></td>
    	            <td><?php echo $lists['useMovedAsk'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_DONT_REMOVE_SID'), JText::_('Do not remove SID from SEF URL?'));?></td>
    	            <td><?php echo JText::_('Do not remove SID from SEF URL?');?></td>
    	            <td><?php echo $lists['dontRemoveSid'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_SET_QUERY_STRING'), JText::_('Set server QUERY_STRING?'));?></td>
    	            <td><?php echo JText::_('Set server QUERY_STRING?');?></td>
    	            <td><?php echo $lists['setQueryString'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_PARSE_JOOMLA_SEO'), JText::_('Parse Joomla SEO links?'));?></td>
    	            <td><?php echo JText::_('Parse Joomla SEO links?');?></td>
    	            <td><?php echo $lists['parseJoomlaSEO'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_CHECK_JUNK_URLS'), JText::_('Filter variable values?'));?></td>
    	            <td><?php echo JText::_('Filter variable values?');?></td>
    	            <td><?php echo $lists['checkJunkUrls'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_JUNK_WORDS'), JText::_('Filter these words'));?></td>
    	            <td><?php echo JText::_('Filter these words');?>:</td>
    	            <td><?php echo $lists['junkWords'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_JUNK_EXCLUDE'), JText::_('Variables to exclude from filtering'));?></td>
    	            <td><?php echo JText::_('Variables to exclude from filtering');?>:</td>
    	            <td><?php echo $lists['junkExclude'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_PREVENT_NONSEF_OVERWRITE'), JText::_('Prevent non-SEF variables from overwriting the parsed ones'));?></td>
    	            <td><?php echo JText::_('Prevent non-SEF variables from overwriting the parsed ones');?>:</td>
    	            <td><?php echo $lists['preventNonSefOverwrite'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_CUSTOM_NONSEF'), JText::_('Custom non-SEF variables'));?></td>
    	            <td><?php echo JText::_('Custom non-SEF variables');?>:</td>
    	            <td><input type="text" name="customNonSef" value="<?php echo $sefConfig->customNonSef; ?>" size="60"></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_AUTO_CANONICAL'), JText::_('Automatic canonical link generation'));?></td>
    	            <td><?php echo JText::_('Automatic canonical link generation');?>:</td>
    	            <td><?php echo $lists['autoCanonical']; ?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_SEF_COMPONENT_URLS'), JText::_('SEF URLs using component template'));?></td>
    	            <td><?php echo JText::_('SEF URLs using component template');?>:</td>
    	            <td><?php echo $lists['sefComponentUrls']; ?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_CHECK_BASE_HREF'), JText::_('Set page base href value'));?></td>
    	            <td><?php echo JText::_('Set page base href value');?>:</td>
    	            <td><?php echo $lists['check_base_href']; ?></td>
    	        </tr>
    	        </table>
		    </fieldset>
    	        
			<fieldset class="adminform">
		      <legend><?php echo JText::_('URL Source Tracing');?></legend>
		      <table class="adminform">    	        
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20" valign="top"><?php echo JHTML::_('tooltip', JText::_('TT_TRACE'), JText::_('Trace URL Source'));?></td>
    	            <td width="200" valign="top"><?php echo JText::_('Enable URL source tracing?');?></td>
    	            <td><?php echo $lists['trace'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_TRACE_DEPTH'), JText::_('Tracing depth'));?></td>
    	            <td><?php echo JText::_('Tracing depth');?>:</td>
    	            <td><?php echo $lists['traceLevel'];?></td>
    	        </tr>
		      </table>
		  </fieldset>
		  
		  <?php
		  echo $pane->endPanel();
		  echo $pane->startPanel(JText::_('Cache'), 'cache');
		  ?>
		  
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('Cache Configuration');?></legend>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_USE_CACHE'), JText::_('Use cache?'));?></td>
    	            <td width="200"><?php echo JText::_('Use cache?');?></td>
    	            <td><?php echo $lists['useCache'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_CACHE_SIZE'), JText::_('Maximum cache size'));?></td>
    	            <td><?php echo JText::_('Maximum cache size');?>:</td>
    	            <td><?php echo $lists['cacheSize'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_CACHE_HITS'), JText::_('Minimum cache hits count'));?></td>
    	            <td><?php echo JText::_('Minimum cache hits count');?>:</td>
    	            <td><?php echo $lists['cacheMinHits'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_CACHE_RECORDHITS'), JText::_('Record hits for cached URLs'));?></td>
    	            <td><?php echo JText::_('Record hits for cached URLs');?>:</td>
    	            <td><?php echo $lists['cacheRecordHits'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_CACHE_SHOWERR'), JText::_('Display error if cache gets corrupted'));?></td>
    	            <td><?php echo JText::_('Display error if cache gets corrupted');?>:</td>
    	            <td><?php echo $lists['cacheShowErr'];?></td>
    	        </tr>
		      </table>
		  </fieldset>

		  <?php
		  echo $pane->endPanel();
		  echo $pane->startPanel(JText::_('Title and Meta Tags'), 'metatags');
		  ?>
		  
		  <div class="col width-50">
		      <fieldset class="adminform">
		          <legend><?php echo JText::_('Title and Meta Tags Configuration'); ?></legend>
    		      <table class="adminform">
        	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
        	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_ENABLE_METADATA'), JText::_('Enable MetaData generation'));?></td>
        	            <td width="200"><?php echo JText::_('Enable MetaData generation');?>:</td>
        	            <td><?php echo $lists['enable_metadata'];?></td>
        	        </tr>
        	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
        	            <td><?php echo JHTML::_('tooltip', JText::_('TT_PREFER_JOOMSEF_TITLE'), JText::_('Prefer JoomSEF titles'));?></td>
        	            <td><?php echo JText::_('Prefer JoomSEF titles');?>:</td>
        	            <td><?php echo $lists['prefer_joomsef_title'];?></td>
        	        </tr>
        	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
        	            <td><?php echo JHTML::_('tooltip', JText::_('TT_USE_SITENAME'), JText::_('Use sitename in page titles'));?></td>
        	            <td><?php echo JText::_('Use sitename in page titles');?>:</td>
        	            <td><?php echo $lists['use_sitename'];?></td>
        	        </tr>
        	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
        	            <td><?php echo JHTML::_('tooltip', JText::_('TT_SITENAME_SEPARATOR'), JText::_('Sitename separator'));?></td>
        	            <td><?php echo JText::_('Sitename separator');?>:</td>
        	            <td><?php echo $lists['sitename_sep'];?></td>
        	        </tr>
        	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
        	            <td><?php echo JHTML::_('tooltip', JText::_('TT_REWRITE_KEYWORDS'), JText::_('Rewrite keywords'));?></td>
        	            <td><?php echo JText::_('Rewrite keywords');?>:</td>
        	            <td><?php echo $lists['rewrite_keywords'];?></td>
        	        </tr>
        	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
        	            <td><?php echo JHTML::_('tooltip', JText::_('TT_REWRITE_DESC'), JText::_('Rewrite description'));?></td>
        	            <td><?php echo JText::_('Rewrite description');?>:</td>
        	            <td><?php echo $lists['rewrite_description'];?></td>
        	        </tr>
        	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
        	            <td><?php echo JHTML::_('tooltip', JText::_('TT_NO_SITENAME_DUPLICITY'), JText::_('Prevent sitename duplicity'));?></td>
        	            <td><?php echo JText::_('Prevent sitename duplicity');?>:</td>
        	            <td><?php echo $lists['prevent_dupl'];?></td>
        	        </tr>
        	      </table>
		      </fieldset>
		  </div>
		  <div class="col width-50">
		      <fieldset class="adminform">
		      <legend><?php echo JText::_('Global Meta Tags Configuration'); ?></legend>
		      <fieldset class="adminform">
		          <legend><?php echo JText::_('Standard').' '.JText::_('Meta Tags'); ?></legend>
    		      <table class="adminform">
        	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
        	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_TAG_GENERATOR'), JText::_('Generator tag'));?></td>
        	            <td width="200"><?php echo JText::_('Generator tag');?>:</td>
        	            <td><?php echo $lists['tag_generator'];?></td>
        	        </tr>
        	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
        	            <td><?php echo JHTML::_('tooltip', JText::_('TT_TAG_GOOGLE_KEY'), JText::_('Google key'));?></td>
        	            <td><?php echo JText::_('Google key');?>:</td>
        	            <td><?php echo $lists['tag_googlekey'];?></td>
        	        </tr>
        	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
        	            <td><?php echo JHTML::_('tooltip', JText::_('TT_TAG_LIVE_KEY'), JText::_('Live.com key'));?></td>
        	            <td><?php echo JText::_('Live.com key');?>:</td>
        	            <td><?php echo $lists['tag_livekey'];?></td>
        	        </tr>
        	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
        	            <td><?php echo JHTML::_('tooltip', JText::_('TT_TAG_YAHOO_KEY'), JText::_('Yahoo key'));?></td>
        	            <td><?php echo JText::_('Yahoo key');?>:</td>
        	            <td><?php echo $lists['tag_yahookey'];?></td>
        	        </tr>
        	      </table>
		      </fieldset>
		      <fieldset class="adminform">
		          <legend><?php echo JText::_('Custom').' '.JText::_('Meta Tags'); ?></legend>
		          <table class="adminform" id="tblMetatags">
		              <tr>
		                  <th width="200"><?php echo JText::_('Name'); ?></th>
		                  <th colspan="2"><?php echo JText::_('Content'); ?></th>
		              </tr>
		              <?php
		              // Custom meta tags
		              if (is_array($sefConfig->customMetaTags)) {
		                  foreach($sefConfig->customMetaTags as $name => $content) {
		                      ?>
		                      <tr>
		                          <td width="200"><input type="text" name="metanames[]" size="40" value="<?php echo $name; ?>" /></td>
		                          <td width="250"><input type="text" name="metacontents[]" size="60" value="<?php echo $content; ?>" /></td>
		                          <td><input type="button" value="<?php echo JText::_('Remove Meta tag'); ?>" onclick="removeMetaTag(this);" /></td>
		                      </tr>
		                      <?php
		                  }
		              }
		              ?>
		          </table>
		          <input type="button" value="<?php echo JText::_('Add Meta tag'); ?>" onclick="addMetaTag();" />
		      </fieldset>
		      </fieldset>
		  </div>
		  <div style="clear: both;"></div>
		  
		  <?php
		  echo $pane->endPanel();
		  echo $pane->startPanel(JText::_('SEO'), 'seo');
		  ?>
		  
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('SEO Configuration'); ?></legend>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_EXTERNAL_NOFOLLOW'), JText::_('Nofollow external links'));?></td>
    	            <td width="200"><?php echo JText::_('Nofollow external links');?>:</td>
    	            <td><?php echo $lists['external_nofollow'];?></td>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_INTERNAL_ENABLE'), JText::_('Enable Internal links'));?></td>
    	            <td width="200"><?php echo JText::_('Enable Internal links');?>:</td>
    	            <td><?php echo $lists['internal_enable'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_INTERNAL_NOFOLLOW'), JText::_('Nofollow internal links'));?></td>
    	            <td><?php echo JText::_('Nofollow internal links');?>:</td>
    	            <td><?php echo $lists['internal_nofollow'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_INTERNAL_NEWWINDOW'), JText::_('Open links in new window'));?></td>
    	            <td><?php echo JText::_('Open links in new window');?>:</td>
    	            <td><?php echo $lists['internal_newwindow'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_INTERNAL_MAXLINKS'), JText::_('Maximum links for each word'));?></td>
    	            <td><?php echo JText::_('Maximum links for each word');?>:</td>
    	            <td><?php echo $lists['internal_maxlinks'];?></td>
    	        </tr>
    	        </tr>
		      </table>
		  </fieldset>
		  
		  <?php
		  echo $pane->endPanel();
		  echo $pane->startPanel(JText::_('SiteMap'), 'sitemap');
		  ?>
		  
		  <div class="col width-50">
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('SiteMap Configuration'); ?></legend>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_SITEMAP_FILENAME'), JText::_('XML file name'));?></td>
    	            <td width="200"><?php echo JText::_('XML file name');?>:</td>
    	            <td><?php echo $lists['sitemap_filename'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_SITEMAP_INDEXED'), JText::_('Default Indexed'));?></td>
    	            <td width="200"><?php echo JText::_('Default Indexed');?>:</td>
    	            <td><?php echo $lists['sitemap_indexed'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_SITEMAP_FREQUENCY'), JText::_('Default Change frequency'));?></td>
    	            <td width="200"><?php echo JText::_('Default Change frequency');?>:</td>
    	            <td><?php echo $lists['sitemap_frequency'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_SITEMAP_PRIORITY'), JText::_('Default Priority'));?></td>
    	            <td width="200"><?php echo JText::_('Default Priority');?>:</td>
    	            <td><?php echo $lists['sitemap_priority'];?></td>
    	        </tr>
		      </table>
		  </fieldset>
		  </div>
		  <div class="col width-50">
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('Ping Configuration'); ?></legend>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_SITEMAP_PINGAUTO'), JText::_('Ping after XML generation'));?></td>
    	            <td width="200"><?php echo JText::_('Ping after XML generation');?>:</td>
    	            <td><?php echo $lists['sitemap_pingauto'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_SITEMAP_YAHOOID'), JText::_('Yahoo! Application ID'));?></td>
    	            <td width="200"><?php echo JText::_('Yahoo! Application ID');?>:</td>
    	            <td><?php echo $lists['sitemap_yahooid'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20" valign="top"><?php echo JHTML::_('tooltip', JText::_('TT_SITEMAP_SERVICES'), JText::_('Ping services'));?></td>
    	            <td width="200" valign="top"><?php echo JText::_('Ping services');?>:</td>
    	            <td><?php echo $lists['sitemap_services'];?></td>
    	        </tr>
		      </table>
		  </fieldset>
		  </div>
		  <div style="clear: both;"></div>
		  
          <?php if (SEFTools::JoomFishInstalled()) { ?>
		  <?php
		  echo $pane->endPanel();
		  echo $pane->startPanel(JText::_('JoomFish'), 'joomfish');
		  ?>
		  
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('JoomFish Related Configuration');?></legend>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_JF_LANG_PLACEMENT'), JText::_('Language integration'));?></td>
    	            <td width="200"><?php echo JText::_('Language integration');?></td>
    	            <td><?php echo $lists['langPlacement'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_JF_ALWAYS_USE_LANG'), JText::_('Always use language?'));?></td>
    	            <td><?php echo JText::_('Always use language?');?></td>
    	            <td><?php echo $lists['alwaysUseLang'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_JF_TRANSLATE'), JText::_('Translate URLs?'));?></td>
    	            <td><?php echo JText::_('Translate URLs?');?></td>
    	            <td><?php echo $lists['translateNames'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_JF_BROWSER_LANG'), JText::_('Get language from browser setting?'));?></td>
    	            <td><?php echo JText::_('Get language from browser setting?');?></td>
    	            <td><?php echo $lists['jfBrowserLang'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_JF_LANG_COOKIE'), JText::_('Save language to cookie?'));?></td>
    	            <td><?php echo JText::_('Save language to cookie?');?></td>
    	            <td><?php echo $lists['jfLangCookie'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_JF_MAIN_LANG'), JText::_('Main language'));?></td>
    	            <td><?php echo JText::_('Main language');?>:</td>
    	            <td><?php echo $lists['mainLanguage'];?></td>
    	        </tr>
		      </table>
		      
		      <?php
		      $jfrouterEnabled = JPluginHelper::isEnabled('system', 'jfrouter');
		      if( isset($lists['jfSubDomains']) ) {
		          $disabled = '';
		          if ($jfrouterEnabled) {
		              $disabled = 'disabled="disabled"';
		          }
		          ?>
		          <table class="adminform">
		          <tr>
		              <th width="20"><?php echo JHTML::_('tooltip', JText::_('TT_JF_DOMAIN'), JText::_('Domain configuration'));?></th>
		              <th width="200"><?php echo JText::_('Domain configuration'); ?></th>
		              <th colspan="2"></th>
		          </tr>
		          <?php
		          foreach( $lists['jfSubDomains'] as $l ) {
		              ?>
		              <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
        	              <td colspan="2"><?php echo $l->name;?></td>
        	              <td><input type="text" name="jfSubDomains[<?php echo $l->code; ?>]" class="inputbox" size="45" value="<?php echo $l->value; ?>" <?php echo $disabled; ?> /></td>
        	              <td></td>
    	              </tr>
		              <?php
		          }
		          ?>
		          </table>
		          <?php
		      }
		      ?>
		      
		      <?php
		      if ($jfrouterEnabled) {
		          $jftxt = sprintf(JText::_('JFROUTER_ENABLED'), $jfrouterLink);
		          $jfbutton = '<input type="button" onclick="disableStatus(\'jfrouter\');" value="' . JText::_('Disable Jfrouter Plugin') . '" />';
		      }
		      else {
		          $jftxt = JText::_('JFROUTER_DISABLED');
		          $jfbutton = '<input type="button" onclick="enableStatus(\'jfrouter\');" value="' . JText::_('Enable Jfrouter Plugin') . '" />';
		      }
		      ?>
		      <table class="adminform">
		          <tr>
		              <td><?php echo JText::_($jftxt); ?></td>
		          </tr>
		          <tr>
		              <td><?php echo JText::_('JFROUTER_INFO'); ?></td>
		          </tr>
		          <tr>
		              <td><?php echo $jfbutton; ?></td>
		          </tr>
		      </table>
		  </fieldset>
          <?php } ?>

		  <?php
		  echo $pane->endPanel();
		  echo $pane->startPanel(JText::_('404 Page'), '404');
		  ?>
		  
		  <div class="col width-50">
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('404 Page'); ?></legend>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_404_PAGE'), JText::_('404 Page'));?></td>
    	            <td width="200"><?php echo JText::_('404 Page');?></td>
    	            <td><?php echo $lists['page404'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_404_MESSAGE'), JText::_('Show 404 Message'));?></td>
    	            <td><?php echo JText::_('Show 404 Message');?></td>
    	            <td><?php echo $lists['msg404'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_404_RECORD_HITS'), JText::_('Record 404 page hits?'));?></td>
    	            <td><?php echo JText::_('Record 404 page hits?');?></td>
    	            <td><?php echo $lists['record404'];?></td>
    	        </tr>
		      </table>
		  </fieldset>
		  
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('Default 404 Page').' - '.JText::_('ItemID');?></legend>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20" valign="top"><?php echo JHTML::_('tooltip', JText::_('TT_USE_404_ITEMID'), JText::_('Use Itemid for Default 404 Page'));?></td>
    	            <td width="200" valign="top"><?php echo JText::_('Use Itemid for Default 404 Page');?></td>
    	            <td><?php echo $lists['use404itemid'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td valign="top"><?php echo JHTML::_('tooltip', JText::_('TT_SELECT_ITEMID'), JText::_('Select Itemid'));?></td>
    	            <td valign="top"><?php echo JText::_('Select Itemid');?></td>
    	            <td><?php echo $lists['itemid404'];?></td>
    	        </tr>
		      </table>
		  </fieldset>
		  </div>
		  
		  <div class="col width-50">
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('Custom 404 Page');?></legend>
    		  <?php
    		  // parameters : hidden field, content, width, height, cols, rows
    		  jimport( 'joomla.html.editor' );
    		  $editor =& JFactory::getEditor();
    		  echo $editor->display('introtext', $lists['txt404'], '450', '250', '50', '11');
    		  ?>
		  </fieldset>
		  </div>
		  <div class="clr"></div>
		  
		  <?php
		  echo $pane->endPanel();
		  echo $pane->startPanel(JText::_('Registration'), 'registration');
		  $x = 0;
		  ?>
		  
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('ARTIO JoomSEF Registration');?></legend>
		      <p><?php echo JText::_('INFO_REGISTRATION'); ?></p>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_ARTIO_DOWNLOAD_ID'), JText::_('JoomSEF Download ID'));?></td>
    	            <td width="200"><?php echo JText::_('JoomSEF Download ID');?>:</td>
    	            <td><?php echo $lists['artioDownloadId'];?></td>
    	        </tr>
		      </table>
		  </fieldset>

		  <?php $x = 0; ?>
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('ARTIO User Account'); ?></legend>
		      <p><?php echo JText::_('INFO_ACCOUNT'); ?></p>
		      <table class="adminform">
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_ARTIO_USERNAME'), JText::_('ARTIO Site Username'));?></td>
    	            <td width="200"><?php echo JText::_('ARTIO Site Username');?>:</td>
    	            <td><?php echo $lists['artioUserName'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td><?php echo JHTML::_('tooltip', JText::_('TT_ARTIO_PASSWORD'), JText::_('ARTIO Site Password'));?></td>
    	            <td><?php echo JText::_('ARTIO Site Password');?>:</td>
    	            <td><?php echo $lists['artioPassword'];?></td>
    	        </tr>
		      </table>
		  </fieldset>
		  
		  <fieldset class="adminform">
		      <legend><?php echo JText::_('ARTIO News'); ?></legend>
		      <table class="adminform">
                <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
                    <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_ARTIO_FEED'), JText::_('Display ARTIO Newsfeed'));?></td>
                    <td width="200"><?php echo JText::_('Display ARTIO Newsfeed');?>:</td>
                    <td><?php echo $lists['artioFeedDisplay'];?></td>
    	        </tr>
    	        <tr<?php $x++; echo (($x % 2) ? '':' class="row1"' );?>>
    	            <td width="20"><?php echo JHTML::_('tooltip', JText::_('TT_VERSION_CHECKER'),JText::_('Check for newer versions'));?></td>
    	            <td width="200"><?php echo JText::_('Check for newer versions');?>:</td>
    	            <td><?php echo $lists['versionChecker'];?></td>
    	        </tr>
		      </table>
		  </fieldset>

		  <?php
		  echo $pane->endPanel();
		  echo $pane->endPane();
		  ?>
		
		
		
<input type="hidden" name="id" value="" />
<input type="hidden" name="section" value="config" />
<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="config" />
<input type="hidden" name="statusType" value="" />
<input type="hidden" name="return" value="index.php?option=com_sef&amp;controller=config&amp;task=edit" />
<?php echo JHTML::_('form.token'); ?>
</form>
