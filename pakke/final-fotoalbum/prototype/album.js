/* ============================================================
   Final Film — fotoalbum
   Justeret gitter + lightbox. Ingen afhængigheder, ingen jQuery.

   Forventer markup som i frontend/markup.html:
   - <div class="fa-gal" data-album="..."> med <button class="fa-item">
   - hvert .fa-item har data-w, data-h (billedets naturlige mål) og
     data-full (URL til det store billede)
   Højde og bredde regnes ud herfra, så gitteret ikke hopper,
   mens billederne loader.
   ============================================================ */

(function () {
	'use strict';

	var GAP = 8;

	function rowHeight(width) {
		if (width < 620) return 150;
		if (width < 1000) return 210;
		return 260;
	}

	/* ---------------- Justeret gitter ---------------- */

	function layout(gal) {
		var items = Array.prototype.slice.call(gal.querySelectorAll('.fa-item'));
		if (!items.length) return;

		var W = gal.clientWidth;
		if (!W) return;
		var target = rowHeight(W);

		var rows = [], row = [], sum = 0;

		items.forEach(function (el) {
			var w = parseFloat(el.dataset.w) || 3;
			var h = parseFloat(el.dataset.h) || 2;
			var ar = w / h;
			row.push({ el: el, ar: ar });
			sum += ar;
			if (sum * target + GAP * (row.length - 1) >= W) {
				rows.push({ items: row, sum: sum, full: true });
				row = [];
				sum = 0;
			}
		});
		if (row.length) rows.push({ items: row, sum: sum, full: false });

		/* Byg rækkerne i et fragment, så der kun er én reflow */
		var frag = document.createDocumentFragment();
		rows.forEach(function (r) {
			var avail = W - GAP * (r.items.length - 1);
			/* Sidste række skaleres ikke op — den ville blive urimeligt høj */
			var h = r.full ? avail / r.sum : Math.min(target, avail / r.sum);
			var div = document.createElement('div');
			div.className = 'fa-row';
			r.items.forEach(function (c) {
				c.el.style.width = (c.ar * h) + 'px';
				c.el.style.height = h + 'px';
				div.appendChild(c.el);
			});
			frag.appendChild(div);
		});

		gal.innerHTML = '';
		gal.appendChild(frag);
		gal.setAttribute('data-ready', '');
	}

	function layoutAll() {
		document.querySelectorAll('.fa-gal').forEach(layout);
	}

	/* ---------------- Lightbox ---------------- */

	function Lightbox() {
		var el = document.querySelector('.fa-lb');
		if (!el) return null;

		var img = el.querySelector('.fa-lb-img'),
			count = el.querySelector('.fa-lb-count'),
			title = el.querySelector('.fa-lb-title'),
			strip = el.querySelector('.fa-lb-strip'),
			mid = el.querySelector('.fa-lb-mid'),
			stage = el.querySelector('.fa-lb-stage');

		var items = [], index = 0, lastFocus = null, albumName = '';

		function preload(n) {
			var it = items[(n + items.length) % items.length];
			if (it) { var p = new Image(); p.src = it.full; }
		}

		function show(n) {
			index = (n + items.length) % items.length;
			var it = items[index];

			img.style.opacity = 0;
			var pre = new Image();
			pre.onload = function () {
				img.src = it.full;
				img.alt = it.alt || '';
				img.style.opacity = 1;
			};
			pre.src = it.full;

			count.textContent = (index + 1) + ' / ' + items.length;

			Array.prototype.forEach.call(strip.children, function (b, i) {
				b.setAttribute('aria-current', String(i === index));
			});
			var cur = strip.children[index];
			if (cur) cur.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });

			/* Naboerne hentes på forhånd, så bladring føles øjeblikkelig */
			preload(index + 1);
			preload(index - 1);

			if (history.replaceState) {
				history.replaceState(null, '', '#foto-' + (index + 1));
			}
		}

		function open(list, n, name) {
			items = list;
			albumName = name || '';
			title.textContent = albumName;
			lastFocus = document.activeElement;

			el.toggleAttribute('data-single', items.length < 2);

			strip.innerHTML = items.map(function (it, i) {
				return '<button type="button" class="fa-lb-thumb" data-i="' + i + '">'
					+ '<img src="' + it.thumb + '" alt="" loading="lazy"></button>';
			}).join('');

			el.classList.add('is-open');
			document.body.classList.add('fa-lb-open');
			show(n);
			el.querySelector('.fa-lb-close').focus();
		}

		function close() {
			el.classList.remove('is-open');
			document.body.classList.remove('fa-lb-open');
			if (history.replaceState) {
				history.replaceState(null, '', location.pathname + location.search);
			}
			if (lastFocus && lastFocus.focus) lastFocus.focus();
		}

		function isOpen() { return el.classList.contains('is-open'); }

		el.addEventListener('click', function (e) {
			var th = e.target.closest('.fa-lb-thumb');
			if (th) { show(Number(th.dataset.i)); return; }
			if (e.target.closest('.fa-lb-prev')) { show(index - 1); return; }
			if (e.target.closest('.fa-lb-next')) { show(index + 1); return; }
			if (e.target.closest('.fa-lb-close')) { close(); return; }
			/* Klik ved siden af billedet lukker */
			if (e.target === stage || e.target === mid) close();
		});

		document.addEventListener('keydown', function (e) {
			if (!isOpen()) return;
			if (e.key === 'Escape') { close(); }
			else if (e.key === 'ArrowLeft') { show(index - 1); }
			else if (e.key === 'ArrowRight') { show(index + 1); }
			else if (e.key === 'Tab') {
				/* Hold fokus inde i lightboxen */
				var f = el.querySelectorAll('button');
				if (!f.length) return;
				var first = f[0], last = f[f.length - 1];
				if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
				else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
			}
		});

		/* Swipe: til siden bladrer, nedad lukker */
		var x0 = null, y0 = null;
		mid.addEventListener('pointerdown', function (e) { x0 = e.clientX; y0 = e.clientY; });
		mid.addEventListener('pointerup', function (e) {
			if (x0 === null) return;
			var dx = e.clientX - x0, dy = e.clientY - y0;
			if (Math.abs(dx) > 45 && Math.abs(dx) > Math.abs(dy)) show(index + (dx < 0 ? 1 : -1));
			else if (dy > 70 && Math.abs(dy) > Math.abs(dx)) close();
			x0 = y0 = null;
		});

		return { open: open, close: close, isOpen: isOpen };
	}

	/* ---------------- Kobling ---------------- */

	function itemsFrom(gal) {
		return Array.prototype.map.call(gal.querySelectorAll('.fa-item'), function (el) {
			var im = el.querySelector('img');
			return {
				full: el.dataset.full,
				thumb: im ? im.currentSrc || im.src : el.dataset.full,
				alt: im ? im.alt : ''
			};
		});
	}

	function init() {
		layoutAll();

		var lb = Lightbox();
		if (!lb) return;

		document.addEventListener('click', function (e) {
			var item = e.target.closest('.fa-item');
			if (!item) return;
			var gal = item.closest('.fa-gal');
			if (!gal) return;
			e.preventDefault();
			var list = itemsFrom(gal);
			var n = Array.prototype.indexOf.call(gal.querySelectorAll('.fa-item'), item);
			lb.open(list, n, gal.dataset.album || '');
		});

		/* Dybt link: final.dk/album/xyz/#foto-3 åbner billede 3 */
		var m = /^#foto-(\d+)$/.exec(location.hash);
		if (m) {
			var gal = document.querySelector('.fa-gal');
			if (gal) lb.open(itemsFrom(gal), Number(m[1]) - 1, gal.dataset.album || '');
		}

		var t;
		window.addEventListener('resize', function () {
			clearTimeout(t);
			t = setTimeout(layoutAll, 120);
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
