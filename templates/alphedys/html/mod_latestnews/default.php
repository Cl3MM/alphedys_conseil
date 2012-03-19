<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>

<ul class="rightmenuitem<?php echo $params->get('moduleclass_sfx'); ?>">
<?php foreach ($list as $item) :  ?>
	<li class="rightmenuitem<?php echo $params->get('moduleclass_sfx'); ?>">
		<a href="<?php echo $item->link; ?>" class="rightmenuitem<?php echo $params->get('moduleclass_sfx'); ?>">
			<?php echo $item->text; ?></a>
	</li>
<?php endforeach; ?>
</ul>