<?php if ($navs): ?>
<ul class="<?php echo $type; ?>">
<?php foreach ($navs as $nav): ?>

<?php if ('community' === $type && '2' === $navId): ?>
<?php if (in_array(op_url_to_id($nav->uri), array('_community_join', '_community_quit'))): ?>
<?php continue ?>
<?php endif ?>
<?php endif ?>

<?php if (isset($navId)): ?>
<?php $uri = $nav->uri.'?id='.$navId; ?>
<?php else: ?>
<?php $uri = $nav->uri; ?>
<?php endif; ?>

<?php if (op_is_accessible_url($uri)): ?>
<li id="<?php echo $nav->type ?>_<?php echo op_url_to_id($nav->uri) ?>"><?php echo link_to($nav->caption, $uri); ?></li>
<?php endif; ?>

<?php endforeach; ?>
</ul>
<?php endif; ?>
