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

$sefConfig =& SEFConfig::getConfig();

jimport( 'joomla.html.pane');
$pane =& JPane::getInstance('Tabs');
?>

	<script language="javascript">
	<!--
	function submitbutton(pressbutton)
	{
	    var form = document.adminForm;
	    if (pressbutton == 'cancel') {
	        submitform( pressbutton );
	        return;
	    }
	    // do field validation
	    if (form.customurl.checked == true ) {
	        form.dateadd.value = "<?php echo date('Y-m-d'); ?>"
	    } else {
	        form.dateadd.value = "0000-00-00"
	    }
	    if (form.origurl.value == "") {
	        alert( "<?php echo JText::_('You must provide a URL for the redirection.'); ?>" );
	    } else {
	        if (form.origurl.value.match(/^index.php/)) {
	            <?php if( $sefConfig->useMoved ) { ?>
	            // Ask to save the changed url to Moved Permanently table
	            if( (form.sefurl.value != form.unchanged.value) && (form.id.value != "0" && form.id.value != "") ) {
	                <?php if( $sefConfig->useMovedAsk ) { ?>
	                if( !confirm("<?php echo JText::_('CONFIRM_AUTO_301'); ?>") ) {
	                    form.unchanged.value = "";
	                }
	                <?php } ?>
	            } else {
	                form.unchanged.value = "";
	            }
	            <?php } else { echo 'form.unchanged.value="";'; } ?>
	            
	            // Build the words array
	            var list = form.words;
	            var wordsArray = '';
	            for (var i = 0, n = list.length; i < n; i++) {
	                if (i > 0) {
	                    wordsArray += "\n";
	                }
	                wordsArray += list.options[i].value;
	            }
	            form.wordsArray.value = wordsArray;
	            
	            submitform( pressbutton );
	        } else {
	            alert( "<?php echo JText::_('The Old Non-SEF Url must begin with index.php'); ?>" );
	        }
	    }
	}
	
	function addWord()
	{
	    var word = document.adminForm.txtWord.value;
	    var id = document.adminForm.wordid.value;
	    var list = document.adminForm.words;
	    
	    // Check word length
	    if (word.length == 0) {
	        return;
	    }
	    
	    // Try to find the word in list (do not allow duplicities)
	    for (var i = 0, n = list.length; i < n; i++) {
	        if (list.options[i].text == word) {
	            // Found it
	            return;
	        }
	    }
	    
	    // Add the word
	    var newOpt;
	    if (id == '') {
	        newOpt = new Option(word, word);
	    }
	    else {
	        newOpt = new Option(word, id);
	    }
	    
	    try {
	        list.add(newOpt); // IE, Opera
	    }
	    catch(e) {
	        list.add(newOpt, null); // FF
	    }
	}
	
	function removeWords()
	{
	    var list = document.adminForm.words;
	    
	    for (var i = list.length - 1; i >= 0; i--) {
	        if (list.options[i].selected) {
	           list.remove(i);
	        }
	    }
	}
	//-->
	</script>
	<ul id="autocomplete" style="display: none;"><li>dummy</li></ul>
	
	<form action="index2.php" method="post" name="adminForm" id="adminForm">
	
	<?php
	echo $pane->startPane('sefurl-pane');
	echo $pane->startPanel(JText::_('URL'), 'url-panel');
	?>
	<fieldset class="adminform">
	   <legend><?php echo JText::_('URL'); ?></legend>
    	<table class="admintable">
    		<tr>
    			<td class="key"><?php echo JText::_('New SEF URL'); ?></td>
    			<td><input class="inputbox" type="text" size="100" name="sefurl" value="<?php echo $this->sef->sefurl; ?>">
    			<?php echo JHTML::_('tooltip', JText::_('TT_SEF_URL'), JText::_('New SEF URL')); ?>
    			</td>
    		</tr>
    		<tr>
    			<td class="key"><?php echo JText::_('Old Non-SEF Url');?></td>
    			<td align="left"><input class="inputbox" type="text" size="100" name="origurl" value="<?php echo $this->sef->origurl; ?>">
    			<?php echo JHTML::_('tooltip', JText::_('TT_ORIG_URL'), JText::_('Old Non-SEF Url'));?>
    			</td>
    		</tr>
    		<tr>
    			<td class="key"><?php echo JText::_('Itemid');?></td>
    			<td align="left"><input class="inputbox" type="text" size="10" name="Itemid" value="<?php echo $this->sef->Itemid; ?>">
    			<?php echo JHTML::_('tooltip', JText::_('TT_ITEMID'), JText::_('Itemid'));?>
    			</td>
    		</tr>		
    		<tr>
          		<td class="key"><?php echo JText::_('Save as Custom Redirect'); ?></td>
          		<td>
          			<input type="checkbox" name="customurl" value="0" checked="checked" />
          		</td>
    		</tr>
    		<tr>
    		  <td class="key"><?php echo JText::_('Enabled'); ?></td>
    		  <td>
    		      <input type="checkbox" name="enabled" value="1" <?php if ($this->sef->enabled) echo 'checked="checked"'; ?> />
    		      <?php echo JHTML::_('tooltip', JText::_('TT_URL_ENABLED'), JText::_('Enabled'));?>
    		  </td>
    		</tr>
    		<tr>
    		  <td class="key"><?php echo JText::_('SEF'); ?></td>
    		  <td>
    		      <input type="checkbox" name="sef" value="1" <?php if ($this->sef->sef) echo 'checked="checked"'; ?> />
    		      <?php echo JHTML::_('tooltip', JText::_('TT_URL_SEF'), JText::_('SEF'));?>
    		  </td>
    		</tr>
    		<tr>
    		  <td class="key"><?php echo JText::_('Locked'); ?></td>
    		  <td>
    		      <input type="checkbox" name="locked" value="1" <?php if ($this->sef->locked) echo 'checked="checked"'; ?> />
    		      <?php echo JHTML::_('tooltip', JText::_('TT_URL_LOCKED'), JText::_('Locked'));?>
    		  </td>
    		</tr>

		<?php $config =& SEFConfig::getConfig(); ?>
		<?php if ($config->trace) : ?>		
		<tr><th colspan="2"><?php echo JText::_('URL Source Tracing'); ?></th></tr>
		<tr>
		  <td valign="top" class="key"><?php echo JText::_('Trace Information'); ?>:</td>
		  <td align="left"><?php echo nl2br(htmlspecialchars($this->sef->trace)); ?>
		  </td>
		</tr>
		<?php endif; ?>
		</table>
	</fieldset>
	
	<?php
	echo $pane->endPanel();
	echo $pane->startPanel(JText::_('Meta Tags'), 'meta-panel');
	?>
	
	<fieldset class="adminform">
	   <legend><?php echo JText::_('Meta Tags'); ?></legend>
	   <table class="admintable">
		<tr><td colspan="2"><?php echo  JHTML::_('tooltip', JText::_('INFO_JOOMSEF_PLUGIN'), JText::_('JoomSEF Plugin Notice')); ?></td></tr>
		<tr>
		  <td class="key"><?php echo JText::_('Title'); ?>:</td>
		  <td align="left"><input class="inputbox" type="text" size="100" maxlength="255" name="metatitle" value="<?php echo htmlspecialchars($this->sef->metatitle); ?>">
		  </td>
		</tr>
		<tr>
		  <td class="key"><?php echo JText::_('Meta Descrition'); ?>:</td>
		  <td align="left"><input class="inputbox" type="text" size="100" maxlength="255" name="metadesc" value="<?php echo htmlspecialchars($this->sef->metadesc); ?>">
		  </td>
		</tr>
		<tr>
		  <td class="key"><?php echo JText::_('Meta Keywords'); ?>:</td>
		  <td align="left"><input class="inputbox" type="text" size="100" maxlength="255" name="metakey" value="<?php echo htmlspecialchars($this->sef->metakey); ?>">
		  </td>
		</tr>
		<tr>
		  <td class="key"><?php echo JText::_('Meta Content-Language'); ?>:</td>
		  <td align="left"><input class="inputbox" type="text" size="100" maxlength="30" name="metalang" value="<?php echo htmlspecialchars($this->sef->metalang); ?>">
		  </td>
		</tr>
		<tr>
		  <td class="key"><?php echo JText::_('Meta Robots'); ?>:</td>
		  <td align="left"><input class="inputbox" type="text" size="100" maxlength="30" name="metarobots" value="<?php echo htmlspecialchars($this->sef->metarobots); ?>">
		  </td>
		</tr>
		<tr>
		  <td class="key"><?php echo JText::_('Meta Googlebot'); ?>:</td>
		  <td align="left"><input class="inputbox" type="text" size="100" maxlength="30" name="metagoogle" value="<?php echo htmlspecialchars($this->sef->metagoogle); ?>">
		  </td>
		</tr>
		<tr>
		  <td class="key"><?php echo JText::_('Canonical Link'); ?>:</td>
		  <td align="left"><input class="inputbox" type="text" size="100" maxlength="255" name="canonicallink" value="<?php echo htmlspecialchars($this->sef->canonicallink); ?>">
		  </td>
		</tr>
	</table>
	</fieldset>
	
	<?php
	echo $pane->endPanel();
	echo $pane->startPanel(JText::_('SiteMap'), 'sitemap-panel');
	?>
	
	<fieldset class="adminform">
	   <legend><?php echo JText::_('SiteMap'); ?></legend>
	   <table class="admintable">
	       <tr>
	           <td class="key"><?php echo JText::_('Indexed'); ?></td>
	           <td><?php echo $this->lists['sm_indexed']; ?><?php echo JHTML::_('tooltip', JText::_('TT_SM_INDEXED'), JText::_('Indexed'));?></td>
	       </tr>
	       <tr>
	           <td class="key"><?php echo JText::_('Date'); ?></td>
	           <td><?php echo $this->lists['sm_date']; ?><?php echo JHTML::_('tooltip', JText::_('TT_SM_DATE'), JText::_('Date'));?></td>
	       </tr>
	       <tr>
	           <td class="key"><?php echo JText::_('Change frequency'); ?></td>
	           <td><?php echo $this->lists['sm_frequency']; ?><?php echo JHTML::_('tooltip', JText::_('TT_SM_FREQUENCY'), JText::_('Change frequency'));?></td>
	       </tr>
	       <tr>
	           <td class="key"><?php echo JText::_('Priority'); ?></td>
	           <td><?php echo $this->lists['sm_priority']; ?><?php echo JHTML::_('tooltip', JText::_('TT_SM_PRIORITY'), JText::_('Priority'));?></td>
	       </tr>
	   </table>
	</fieldset>
	
	<?php
	echo $pane->endPanel();
	echo $pane->startPanel(JText::_('Internal Links'), 'internal-panel');
	?>
	
	<fieldset class="adminform">
	   <legend><?php echo JText::_('Internal links'); ?></legend>
	   <table class="admintable">
	       <tr>
	           <td class="key" valign="top"><?php echo JText::_('Linked words'); ?></td>
	           <td><?php echo $this->lists['words']; ?></td>
	           <td valign="top"><input type="button" value="<?php echo JText::_('Remove selected'); ?>" onclick="removeWords();" /></td>
	       </tr>
	       <tr>
	           <td class="key" valign="top"><?php echo JText::_('Add word'); ?></td>
	           <td><input type="text" autocomplete="off" name="txtWord" id="txtWord" size="40" onblur="hideAutoComplete();" onkeydown="handleKey(event, addWord);" onkeyup="showAutoComplete(this, event, 'wordid', 'ajax', 'findWords');" /></td>
	           <td><input type="button" value="<?php echo JText::_('Add word'); ?>" onclick="addWord();" /></td>
	       </tr>
	   </table>
	   <input type="hidden" name="wordid" id="wordid" value="" />
	</fieldset>
	
	<?php
	echo $pane->endPanel();
	echo $pane->endPane();
	?>

<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="controller" value="sefurls" />
<input type="hidden" name="unchanged" value="<?php echo $this->sef->sefurl; ?>" />
<input type="hidden" name="dateadd" value="<?php echo $this->sef->dateadd; ?>" />
<input type="hidden" name="id" value="<?php echo $this->sef->id; ?>" />
<input type="hidden" name="wordsArray" value="" />

</form>
