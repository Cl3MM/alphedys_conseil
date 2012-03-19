<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<?php if (true) : ?>
<div class="blog<?php echo $this->escape($this->params->get( 'pageclass_sfx' )); ?>">
	<div class="article_row">
		<div class="article_column">
<?php if ($this->params->get('show_title',1)) : ?>
			<div class="art-title">
				<h1 class="<?php echo $this->escape($this->params->get( 'pageclass_sfx' )); ?>">
	<?php if ($this->params->get('link_titles') && $this->article->readmore_link != '') : ?>
				<a href="<?php echo $this->article->readmore_link; ?>" class="<?php echo $this->escape($this->params->get( 'pageclass_sfx' )); ?>"><?php echo $this->escape($this->article->title); ?></a>
				<?php else : ?>
					<?php echo $this->escape($this->article->title); ?>
				<?php endif; ?>
	<?php endif; ?>
		<?php if ($this->params->get('show_pdf_icon')) : ?>
		<span align="right" width="100%" class="buttonheading">
		<?php echo JHTML::_('icon.pdf',  $this->article, $this->params, $this->access); ?>
		</span>
		<?php endif; ?>

		<?php if ( $this->params->get( 'show_print_icon' )) : ?>
		<span align="right" width="100%" class="buttonheading">
		<?php echo JHTML::_('icon.print_popup',  $this->article, $this->params, $this->access); ?>
		</span>
		<?php endif; ?>

		<?php if ($this->params->get('show_email_icon')) : ?>
		<span align="right" width="100%" class="buttonheading">
		<?php echo JHTML::_('icon.email',  $this->article, $this->params, $this->access); ?>
		</span>
		<?php endif; ?>
	</h1>
			</div>
			<?php echo $this->article->event->beforeDisplayContent; ?>
			<div class="article-content<?php echo $this->escape($this->params->get( 'pageclass_sfx' )); ?>">
			<?php if (isset ($this->article->toc)) : ?>
				<?php echo $this->article->toc; ?>
			<?php endif; ?>
			<?php echo $this->article->text; ?>
			</div>
	<?php echo $this->article->event->afterDisplayContent; ?>
		</div>
	</div>
</div>
<?php endif; ?>