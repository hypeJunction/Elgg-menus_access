require(['elgg', 'jquery', 'elgg/Ajax', 'elgg/lightbox'], function (elgg, $, Ajax, lightbox) {

	function init() {
		var ajax = new Ajax();
		$(document).on('submit', '.elgg-form-access-edit', function (e) {
			e.preventDefault();
			var $form = $(this);
			ajax.action('access/edit?' + $form.serialize(), {
				beforeSend: function(e) {
					$form.find('[type="submit"]').prop('disabled', true).addClass('elgg-state-disabled');
				},
				complete: function(e) {
					$form.find('[type="submit"]').prop('disabled', false).removeClass('elgg-state-disabled');
				}
			}).done(function (data) {
				var $item = $('.elgg-menu-item-access a[data-guid="' + data.guid + '"]');
				$item.find('.elgg-icon').replaceWith(data.icon);
				$item.find('.elgg-menu-label').text(data.label);
				$item.attr('title', data.title);
				lightbox.close();
			});
		});
	}

	elgg.register_hook_handler('init', 'system', init);

});