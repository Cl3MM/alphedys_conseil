<?php
/** $Id: default_address.php 12387 2009-06-30 01:17:44Z ian $ */
defined( '_JEXEC' ) or die( 'Restricted access' );
?>
<?php if ( ( $this->contact->params->get( 'address_check' ) > 0 ) &&  ( $this->contact->address || $this->contact->suburb  || $this->contact->state || $this->contact->country || $this->contact->postcode ) ) : ?>
<table>
<?php if ( $this->contact->params->get( 'address_check' ) > 0 ) : ?>
	<tr>
		<td class="first">Adresse :</td>
		<td>
			<b><?php echo $this->escape($this->contact->name).'<br />'; ?></b>
			<?php if ( $this->contact->address && $this->contact->params->get( 'show_street_address' ) ) :
					echo nl2br($this->escape($this->contact->address)).'<br />'; 
			endif; ?>
			<?php endif; ?>
			<?php if ( $this->contact->postcode && $this->contact->params->get( 'show_postcode' ) ) : ?>
					<?php echo $this->escape($this->contact->postcode).', '; ?>
			<?php endif; ?>
			<?php if ( $this->contact->suburb && $this->contact->params->get( 'show_suburb' ) ) : ?>
					<?php echo $this->escape($this->contact->suburb).'<br />'; ?>
			<?php endif; ?>
			<?php if ( $this->contact->country && $this->contact->params->get( 'show_country' ) ) : ?>
					<?php echo $this->escape($this->contact->country); ?>
			<?php endif; ?>
		</td>
	</tr>
</table>
<?php endif; ?>
<?php if ( ($this->contact->email_to && $this->contact->params->get( 'show_email' )) || 
			($this->contact->telephone && $this->contact->params->get( 'show_telephone' )) || 
			($this->contact->fax && $this->contact->params->get( 'show_fax' )) || 
			($this->contact->mobile && $this->contact->params->get( 'show_mobile' )) || 
			($this->contact->webpage && $this->contact->params->get( 'show_webpage' )) ) : ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<?php if ( $this->contact->email_to && $this->contact->params->get( 'show_email' ) ) : ?>
<tr>
	<td width="<?php echo $this->contact->params->get( 'column_width' ); ?>" >
		<?php echo $this->contact->params->get( 'marker_email' ); ?>
	</td>
	<td>
		<?php echo $this->contact->email_to; ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->contact->telephone && $this->contact->params->get( 'show_telephone' ) ) : ?>
<tr>
	<td width="<?php echo $this->contact->params->get( 'column_width' ); ?>" >
		<?php echo $this->contact->params->get( 'marker_telephone' ); ?>
	</td>
	<td>
		<?php echo nl2br($this->escape($this->contact->telephone)); ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->contact->fax && $this->contact->params->get( 'show_fax' ) ) : ?>
<tr>
	<td width="<?php echo $this->contact->params->get( 'column_width' ); ?>" >
		<?php echo $this->contact->params->get( 'marker_fax' ); ?>
	</td>
	<td>
		<?php echo nl2br($this->escape($this->contact->fax)); ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->contact->mobile && $this->contact->params->get( 'show_mobile' ) ) :?>
<tr>
	<td width="<?php echo $this->contact->params->get( 'column_width' ); ?>" >
	<?php echo $this->contact->params->get( 'marker_mobile' ); ?>
	</td>
	<td>
		<?php echo nl2br($this->escape($this->contact->mobile)); ?>
	</td>
</tr>
<?php endif; ?>
<?php if ( $this->contact->webpage && $this->contact->params->get( 'show_webpage' )) : ?>
<tr>
	<td width="<?php echo $this->contact->params->get( 'column_width' ); ?>" >
	</td>
	<td>
		<a href="<?php echo $this->escape($this->contact->webpage); ?>" target="_blank">
			<?php echo $this->escape($this->contact->webpage); ?></a>
	</td>
</tr>
<?php endif; ?>
</table>
<?php endif; ?>
<?php if ( $this->contact->misc && $this->contact->params->get( 'show_misc' ) ) : ?>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td width="<?php echo $this->contact->params->get( 'column_width' ); ?>" valign="top" >
		<?php echo $this->contact->params->get( 'marker_misc' ); ?>
	</td>
	<td>
		<?php echo nl2br($this->contact->misc); ?>
	</td>
</tr>
</table>
<?php endif; ?>
