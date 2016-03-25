<?php

$entity = elgg_extract('entity', $vars);
if (!$entity instanceof ElggEntity) {
	return;
}

$details = menus_access_get_tooltip($entity);

$content = elgg_format_element('p', ['class' => 'elgg-output'], $details);

if ($entity->canEdit()) {
	$content .= elgg_view_form('access/edit', [], [
		'entity' => $entity,
	]);
}

echo elgg_view_module('aside', $entity->getDisplayName(), $content, [
	'class' => 'menus-access-module',
]);