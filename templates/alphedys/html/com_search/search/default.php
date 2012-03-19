<?php // @version $Id: default.php 11917 2009-05-29 19:37:05Z ian $
defined('_JEXEC') or die('Restricted access');
?>

<?php if($this->params->get('show_page_title',1)) : ?>
<h1><?php echo $this->escape($this->params->get('page_title')) ?></h1>
<?php endif; ?>

<div id="blog">
	<div class="article_row">
		<div class="article_column">
<?php if (!$this->error) :
	echo $this->loadTemplate('results');
else :
	echo $this->loadTemplate('error');
endif; ?>

<?php echo $this->loadTemplate('form'); ?>
		</div>
	</div>
</div>
