<?php $options->setDefault('single', true) ?>
<?php $options->setDefault('name_method', 'getNameAndCount') ?>

<div class="sortHandle">

<?php if ($options->object->getRawValue() instanceof Member): ?>
<?php $activeShogo = opShogoUtil::getActiveShogo($options->object->id) ?>
<?php if (null !== $activeShogo): ?>
<p class="photo shogo-image">
<?php echo op_image_tag_sf_image($activeShogo->File) ?>
</p>
<?php endif ?>
<?php endif ?>

<p class="photo">
<?php $imgParam = array('size' => '180x180', 'alt' => $options->object->getName()) ?>
<?php $nameMethod = $options->name_method ?>
<?php if ($options->object): ?>
<?php echo op_image_tag_sf_image($options->object->getImageFileName(), $imgParam) ?>
<?php else: ?>
<?php echo op_image_tag('no_image.gif', $imgParam) ?>
<?php endif; ?>
</p>
<p class="text"><?php echo $options->object->$nameMethod() ?></p>
</div>
