<?php
/**
 * $Id: default.php 11917 2009-05-29 19:37:05Z ian $
 */
defined( '_JEXEC' ) or die( 'Restricted access' );

$cparams = JComponentHelper::getParams ('com_media');
?>
<div class="blog">
<?php if ($this->params->get('show_page_title')) : ?>
	<h1><?php echo $this->escape($this->params->get('page_title')); ?></h1>
<?php endif; ?>
	<div class="article_row">
		<div class="article_column">
			<h2 class="contact">Nous contacter</h2>
			<div id="contacts">
				<div id="contact-details">
					<h3>Par Mail</h3>
					<table border="0">
						<tr>
							<td class="first">Email :</td>
							<td><?php echo $this->contact->email_to; ?></td>
						</tr>
					</table>
					<h3>Par T&eacute;l&eacute;phone/Fax</h3>
					<table border="0">
						<tr>
							<td class="first">Tel :</td>
							<td><?php echo nl2br($this->escape($this->contact->telephone)); ?></td>
						</tr>
						<tr>
							<td class="first">Fax :</td>
							<td><?php echo nl2br($this->escape($this->contact->fax)); ?></td>
						</tr>
					</table>
					<h3>Par la poste</h3>
					<?php echo $this->loadTemplate('address'); ?>
					<h3>Directement par message</h3>
					<?php
					if ( $this->contact->params->get('show_email_form') && ($this->contact->email_to || $this->contact->user_id))
						echo $this->loadTemplate('form');
					?>
					<!--
					<h3>En Transport</h3>
					<div id="gmap" style="width: 500px; height: 350px; margin-left: 3em; margin-top: 2em;"></div>
					-->
				</div>
			</div>
		</div>
	</div>
</div>
<?php if (false) : ?>
<?php if ( $this->params->get( 'show_page_title', 1 ) && !$this->contact->params->get( 'popup' ) && $this->params->get('page_title') != $this->contact->name ) : ?>
	<div class="componentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
		<?php echo $this->params->get( 'page_title' ); ?>
	</div>
<?php endif; ?>
<div id="component-contact">
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="contentpaneopen<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
<?php if ( $this->params->get( 'show_contact_list' ) && count( $this->contacts ) > 1) : ?>
<tr>
	<td colspan="2" align="center">
		<form action="<?php echo JRoute::_('index.php') ?>" method="post" name="selectForm" id="selectForm">
		<?php echo JText::_( 'Select Contact' ); ?>:
			<?php echo JHTML::_('select.genericlist',  $this->contacts, 'contact_id', 'class="inputbox" onchange="this.form.submit()"', 'id', 'name', $this->contact->id);?>
			<input type="hidden" name="option" value="com_contact" />
		</form>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->contact->name && $this->contact->params->get( 'show_name' ) ) : ?>
<tr>
	<td width="100%" class="contentheading<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
		<?php echo $this->escape($this->contact->name); ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->contact->con_position && $this->contact->params->get( 'show_position' ) ) : ?>
<tr>
	<td colspan="2">
	<?php echo $this->escape($this->contact->con_position); ?>
	</td>
</tr>
<?php endif; ?>
<tr>
	<td>
		<table border="0" width="100%">
		<tr>
			<td></td>
			<td rowspan="2" align="right" valign="top">
			<?php if ( $this->contact->image && $this->contact->params->get( 'show_image' ) ) : ?>
				<div style="float: right;">
					<?php echo JHTML::_('image', 'images/stories' . '/'.$this->contact->image, JText::_( 'Contact' ), array('align' => 'middle')); ?>
				</div>
			<?php endif; ?>
			</td>
		</tr>
		<tr>
			<td>
				<?php echo $this->loadTemplate('address'); ?>
			</td>
		</tr>
		</table>
	</td>
	<td>&nbsp;</td>
</tr>
<?php if ( $this->contact->params->get( 'allow_vcard' ) ) : ?>
<tr>
	<td colspan="2">
	<?php echo JText::_( 'Download information as a' );?>
		<a href="<?php echo JURI::base(); ?>index.php?option=com_contact&amp;task=vcard&amp;contact_id=<?php echo $this->contact->id; ?>&amp;format=raw&amp;tmpl=component">
			<?php echo JText::_( 'VCard' );?></a>
	</td>
</tr>
<?php endif;
if ( $this->contact->params->get('show_email_form') && ($this->contact->email_to || $this->contact->user_id))
	echo $this->loadTemplate('form');
?>
</table>
</div>
<?php endif; ?>