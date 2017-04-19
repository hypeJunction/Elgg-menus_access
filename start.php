<?php

/**
 * Access Menu Item
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2015, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', 'menus_access_init');

/**
 * Initialize the plugin
 * @return void
 */
function menus_access_init() {

	elgg_register_plugin_hook_handler('register', 'menu:entity', 'menus_access_entity_menu_setup', 999);

	elgg_register_ajax_view('menus/access');

	elgg_extend_view('elgg.js', 'menus/access.js');

	elgg_register_action('access/edit', __DIR__ . '/actions/access/edit.php');
}

/**
 * Reorganize entity menu
 *
 * @param string         $hook   "register"
 * @param string         $type   "menu:entity"
 * @param ElggMenuItem[] $return Menu
 * @param array          $params Hook params
 * @return ElggMenuItem[]
 */
function menus_access_entity_menu_setup($hook, $type, $return, $params) {

	$entity = elgg_extract('entity', $params);

	foreach ($return as &$item) {

		if ($item->getName() == 'location') {
			unset($item);
			continue;
		}

		if ($item->getName() == 'access') {
			$level = get_readable_access_level($entity->access_id);
			$parent = $item->getParentName();
			if (elgg_is_active_plugin('menus_api')) {
				if ($parent) {
					$item->setText($level);
				} else {
					$item->setText('');
				}
				$item->setData('icon', menus_access_get_icon($entity));
			} else {
				$text = elgg_view_icon(menus_access_get_icon($entity));
				if ($parent) {
					$text .= elgg_format_element('span', ['class' => 'elgg-menu-label'], $level);
				}
				$item->setText($text);
			}

			$title = strip_tags(menus_access_get_tooltip($entity));
			$item->setTooltip($title);

			$item->setData('subsection', 'access');
			$item->setHref("ajax/view/menus/access?guid=$entity->guid");
			$item->addLinkClass('elgg-lightbox');
			$item->{"data-guid"} = $entity->guid;
			$item->{"data-colorbox-opts"} = json_encode([
				maxWidth => '600px',
			]);
		}
	}

	return $return;
}

/**
 * Get an icon representing access level
 *
 * @param ElggEntity $entity Entity
 * @return string
 */
function menus_access_get_icon(ElggEntity $entity) {
	$access_id = $entity->access_id;
	switch ($access_id) {
		case ACCESS_FRIENDS :
			$icon = 'user';
			break;
		case ACCESS_PUBLIC :
		case ACCESS_LOGGED_IN :
			$icon = 'globe';
			break;
		case ACCESS_PRIVATE :
			$icon = 'lock';
			break;
		default:
			$collection = get_access_collection($access_id);
			$owner = get_entity($collection->owner_guid);
			if ($owner instanceof ElggGroup) {
				$icon = 'users';
			} else {
				$icon = 'cog';
			}
			break;
	}

	$params = ['entity' => $entity];
	return elgg_trigger_plugin_hook('access:icon', $entity->getType(), $params, $icon);
}

/**
 * Get access level details
 * 
 * @param ElggEntity $entity Entity
 * @return string
 */
function menus_access_get_tooltip(ElggEntity $entity) {
	$viewer = elgg_get_logged_in_user_entity();
	$owner = $entity->getOwnerEntity();
	$owner_link = elgg_view('output/url', [
		'text' => $owner->getDisplayName(),
		'href' => $owner->getURL(),
	]);

	$rel = $owner->guid == $viewer->guid ? elgg_echo('menus:access:rel:yours') : elgg_echo('menus:access:rel:owner', [$owner_link]);

	switch ($entity->access_id) {
		case ACCESS_FRIENDS :
			$details = elgg_echo('menus:access:friends', [$rel]);
			break;

		case ACCESS_PUBLIC :
			$details = elgg_echo('menus:access:public');
			break;

		case ACCESS_LOGGED_IN :
			$details = elgg_echo('menus:access:logged_in');
			break;

		case ACCESS_PRIVATE :
			$details = elgg_echo('menus:access:private', [$rel]);
			break;

		default:
			$collection = get_access_collection($entity->access_id);
			$collection_owner = get_entity($collection->owner_guid);
			if ($collection_owner instanceof ElggGroup) {
				$rel = elgg_view('output/url', [
					'text' => $collection_owner->getDisplayName(),
					'href' => "groups/members/$collection_owner->guid",
				]);
				$details = elgg_echo('menus:access:group', [$rel]);
			} else if ($collection_owner->guid == $viewer->guid) {
				$rel = elgg_view('output/url', [
					'text' => $collection->name,
					'href' => "collections/owner/$collection_owner->username",
				]);
				$details = elgg_echo('menus:access:collection', [$rel]);
			} else {
				$details = elgg_echo('menus:access:limited', [$rel]);
			}
			break;
	}

	$level = get_readable_access_level($entity->access_id);
	$tooltip = "<b>$level</b>: $details";
	$params = ['entity' => $entity];
	return elgg_trigger_plugin_hook('access:tooltip', $entity->getType(), $params, $tooltip);
}
