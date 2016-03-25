<?php

$entity = elgg_extract('entity', $vars);

if (!$entity instanceof ElggEntity || !$entity->canEdit()) {
	return;
}

$container = $entity->getContainerEntity();
if ($container instanceof ElggGroup || $container instanceof ElggUser) {
	elgg_set_page_owner_guid($container->guid);
}

echo elgg_view_input('access', [
	'name' => 'access_id',
	'entity' => $entity,
	'label' => elgg_echo('access'),
]);

echo elgg_view_input('hidden', [
	'name' => 'guid',
	'value' => $entity->guid,
]);

echo elgg_view_input('submit', [
	'value' => elgg_echo('save'),
	'field_class' => 'elgg-foot',
]);