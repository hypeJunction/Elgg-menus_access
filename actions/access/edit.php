<?php

$guid = get_input('guid');
$entity = get_entity($guid);

if (!$entity || !$entity->canEdit()) {
	register_error(elgg_echo('actionunauthorized'));
	forward('', '403');
}

$access_id = get_input('access_id', $entity->access_id);

$entity->access_id = $access_id;
if ($entity->save()) {
	if (elgg_is_xhr()) {
		echo json_encode([
			'guid' => $entity->guid,
			'icon' => elgg_view_icon(menus_access_get_icon($entity)),
			'label' => get_readable_access_level($entity->access_id),
			'title' => strip_tags(menus_access_get_tooltip($entity)),
		]);
	}
	system_message(elgg_echo('menus:access:success'));
} else {
	register_error(elgg_echo('mnus:access:fail'));
}
