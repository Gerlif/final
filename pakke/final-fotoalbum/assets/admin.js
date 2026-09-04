/* Adminskærmen: vælg billeder, træk for at sortere, fjern.
   Den skjulte input #fa-ids holder rækkefølgen — den er det eneste,
   der gemmes. */

jQuery(function ($) {
	'use strict';

	var $grid = $('#fa-grid');
	var $ids  = $('#fa-ids');

	function opdaterFelt() {
		var liste = $grid.find('.fa-cell').map(function () {
			return $(this).data('id');
		}).get();
		$ids.val(liste.join(','));
		$grid.find('.fa-num').each(function (i) { $(this).text(i + 1); });
	}

	$grid.sortable({
		items: '.fa-cell',
		handle: '.fa-handle',
		placeholder: 'fa-placeholder',
		forcePlaceholderSize: true,
		update: opdaterFelt
	});

	$grid.on('click', '.fa-remove', function () {
		$(this).closest('.fa-cell').remove();
		opdaterFelt();
	});

	var vaelger;

	$('#fa-pick').on('click', function (e) {
		e.preventDefault();

		if (!vaelger) {
			vaelger = wp.media({
				title: 'Vælg billeder til albummet',
				button: { text: 'Tilføj til album' },
				library: { type: 'image' },
				multiple: 'add'
			});

			vaelger.on('select', function () {
				vaelger.state().get('selection').each(function (att) {
					var a = att.toJSON();
					if ($grid.find('[data-id="' + a.id + '"]').length) {
						return; // ligger der allerede
					}
					var src = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
					$grid.append(
						'<li class="fa-cell" data-id="' + a.id + '">' +
						'<img src="' + src + '" alt="">' +
						'<span class="fa-handle" title="Træk for at flytte">⋮⋮</span>' +
						'<button type="button" class="fa-remove" aria-label="Fjern">×</button>' +
						'<span class="fa-num"></span></li>'
					);
				});
				opdaterFelt();
			});
		}

		vaelger.open();
	});

	/* Træk-og-slip direkte på feltet: filerne sendes gennem WordPress'
	   egen uploader, så de havner i mediebiblioteket som alt andet. */
	var $drop = $('#fa-drop');

	$drop.on('dragover dragenter', function (e) {
		e.preventDefault();
		$drop.addClass('is-over');
	}).on('dragleave drop', function () {
		$drop.removeClass('is-over');
	}).on('drop', function (e) {
		e.preventDefault();
		var filer = e.originalEvent.dataTransfer.files;
		if (!filer || !filer.length) { return; }

		$.each(filer, function (_, fil) {
			var data = new FormData();
			data.append('action', 'upload-attachment');
			data.append('async-upload', fil);
			data.append('name', fil.name);
			data.append('_wpnonce', window._wpPluploadSettings.defaults.multipart_params._wpnonce);

			$.ajax({
				url: window.ajaxurl.replace('admin-ajax.php', 'async-upload.php'),
				type: 'POST',
				data: data,
				processData: false,
				contentType: false
			}).done(function (svar) {
				if (!svar || !svar.success) { return; }
				var a = svar.data;
				var src = (a.sizes && a.sizes.thumbnail) ? a.sizes.thumbnail.url : a.url;
				$grid.append(
					'<li class="fa-cell" data-id="' + a.id + '">' +
					'<img src="' + src + '" alt="">' +
					'<span class="fa-handle" title="Træk for at flytte">⋮⋮</span>' +
					'<button type="button" class="fa-remove" aria-label="Fjern">×</button>' +
					'<span class="fa-num"></span></li>'
				);
				opdaterFelt();
			});
		});
	});
});
