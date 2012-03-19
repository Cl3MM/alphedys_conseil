<?php // @version $Id: default.php 11917 2009-05-29 19:37:05Z ian $
defined('_JEXEC') or die('Restricted access');
$cparams = JComponentHelper::getParams ('com_media');
?>

<?php if ($this->params->get('show_page_title',1)) : ?>
<h1><?php echo $this->escape($this->params->get('page_title')); ?></h1>
<?php endif; ?>

<?php if ($this->params->def('show_description', 1) || $this->params->def('show_description_image', 1)) : ?>
	<div class="contentdescription<?php echo $this->escape($this->params->get('pageclass_sfx')); ?>">
	<?php if ($this->params->get('show_description_image') && $this->section->image) : ?>
	<img src="<?php echo $this->baseurl . '/' . $this->escape($cparams->get('image_path')).'/'.$this->escape($this->section->image); ?>" class="image_<?php echo $this->escape($this->section->image_position); ?>" />
	<?php endif; ?>

	<?php if ($this->params->get('show_description') && $this->section->description) :
		echo $this->section->description;
	endif; ?>

	<?php if ($this->params->get('show_description_image') && $this->section->image) : ?>
	<div class="spacer_img">&nbsp;</div>
	<?php endif; ?>
	</div>
<?php endif; ?>

<?php if ($this->params->def('show_categories', 1) && count($this->categories)) : ?>
<div id="section">
	<?php foreach ($this->categories as $category) :
		if (!$this->params->get('show_empty_categories') && !$category->numitems) :
			continue;
		endif; ?>
	<div class="specs">
		<img src="<?php echo $this->baseurl . '/' . $this->escape($cparams->get('image_path')).'/'.$this->escape($category->image); ?>" class="image_<?php echo $this->escape($category->image_position); ?>" />
		<h2><a href="<?php echo str_replace("&amp;layout=default","",str_replace("?layout=default","",$category->link)); ?>"><?php echo $this->escape($category->title); ?></a>
		<?php if ($this->params->get('show_cat_num_articles')) : ?>
			<span class="date">
			[<?php if ($category->numitems==1) {
			echo $category->numitems ." ". JText::_( 'item' );	}
			else {
			echo $category->numitems ." ". JText::_( 'items' );} ?>]
			</span>
			<?php endif; ?>
		</h2>
		<?php if ($this->params->def('show_category_description', 1) && $category->description) : ?>
			<?php echo substr($category->description, 0, 450); ?>
		<?php endif; ?>
	</div>
	<?php endforeach; ?>
	<div class="spacer">&nbsp;</div>
</div>
<?php endif; ?>


<?php if (false) : ?>
<?php if ($this->params->def('show_categories', 1) && count($this->categories)) : ?>
<div class="blog">
	<?php foreach ($this->categories as $category) :
		if (!$this->params->get('show_empty_categories') && !$category->numitems) :
			continue;
		endif; ?>
	<div class="article_row">
		<div class="article_column">
			<div class="art-title">
				<h2><a href="<?php echo str_replace("&amp;layout=default","",str_replace("?layout=default","",$category->link)); ?>"><?php echo $this->escape($category->title); ?></a>
				<?php if ($this->params->get('show_cat_num_articles')) : ?>
					<span class="date">
					[<?php if ($category->numitems==1) {
					echo $category->numitems ." ". JText::_( 'item' );	}
					else {
					echo $category->numitems ." ". JText::_( 'items' );} ?>]
					</span>
					<?php endif; ?>
				</h2>
			</div>
			<div class="art-content">
			<?php if ($this->params->def('show_category_description', 1) && $category->description) : ?>
				<?php echo $category->description; ?>
			<?php endif; ?>
			</div>
		</div>
	</div>
	<?php endforeach; ?>
</div>
<?php endif; ?>

<?php endif; ?>


