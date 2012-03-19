<form action="index.php" method="post" name="adminForm" autocomplete="off">

<fieldset>
	<div style="float: right">
		<button type="button" onclick="submitbutton('saveid');window.top.setTimeout('window.parent.document.getElementById(\'sbox-window\').close()', 200);">
			<?php echo JText::_( 'Save' );?></button>
		<button type="button" onclick="window.parent.document.getElementById('sbox-window').close();">
			<?php echo JText::_( 'Cancel' );?></button>
	</div>
	<div class="configuration" >
	   <?php $txt = !empty($this->ext->name) ? $this->ext->name : $this->ext->component->name; ?>
	   <?php echo $txt; ?>
	</div>
</fieldset>

<fieldset>
	<legend>
		<?php echo JText::_( 'Configuration' );?>
	</legend>
	<table class="admintable">
	   <tr>
	       <td class="key"><?php echo JText::_('Download ID'); ?></td>
	       <td>
	           <input type="text" name="downloadid" id="downloadid" size="60" value="<?php echo $this->ext->params->get('downloadId', ''); ?>" />
	       </td>
	   </tr>
	</table>
</fieldset>

<input type="hidden" name="controller" value="extension" />
<input type="hidden" name="option" value="com_sef" />
<input type="hidden" name="ext" value="<?php echo $this->ext->option; ?>" />
<input type="hidden" name="tmpl" value="component" />
<input type="hidden" name="task" value="" />
<?php echo JHTML::_( 'form.token' ); ?>
</form>