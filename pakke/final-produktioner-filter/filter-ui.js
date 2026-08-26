/*!
 * Final Film — kategorivælger til /produktioner/
 * ------------------------------------------------------------------
 * Filtrerer eksisterende DOM-elementer. Rører hverken produktionernes
 * markup, klasser eller styling — den skjuler og viser dem.
 *
 * Kontrakt: hvert produktionselement skal have
 *   data-main="video"                       (én hovedkategori, slug)
 *   data-types="brandingfilm,kampagne"      (0..n typer, slugs, kommasepareret)
 * Valgfrit:
 *   data-search="titel kunde"               (ellers bruges elementets tekst)
 *
 * Brug:  FinalFilter.init({ mains: [...] })
 */
(function (window, document) {
  'use strict';

  var DEFAULTS = {
    root:  '.ff-filter',              // filterbjælken
    grid:  '.produktioner-grid',      // det eksisterende grid
    item:  '.produktioner-grid-item', // det eksisterende produktionselement
    batch: 24,                        // antal der vises ad gangen
    rootMargin: '700px 0px',          // hvor tidligt næste hold hentes
    hiddenClass: 'ff-hidden',
    mains: null,                      // [{slug:'', label:'Alle'}, ...] — udledes hvis null
    labels: null,                     // {slug: 'Pænt navn'} for typer
    onFilter: null                    // callback(synligeElementer) — fx AOS.refreshHard()
  };

  function norm(s) {
    return (s || '').toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
  }
  function slugToLabel(slug) {
    return slug.replace(/-/g, ' ').replace(/^./, function (c) { return c.toUpperCase(); });
  }

  var FinalFilter = {
    init: function (options) {
      var o = Object.assign({}, DEFAULTS, options || {});
      var root = document.querySelector(o.root);
      var grid = document.querySelector(o.grid);
      if (!root || !grid) return null;

      var items = Array.prototype.slice.call(grid.querySelectorAll(o.item)).map(function (el) {
        return {
          el: el,
          main: el.dataset.main || '',
          types: (el.dataset.types || '').split(',').map(function (t) { return t.trim(); }).filter(Boolean),
          search: norm(el.dataset.search || el.textContent)
        };
      });
      if (!items.length) return null;

      // hovedkategorier: brug konfiguration, ellers udled fra DOM
      var mains = o.mains;
      if (!mains) {
        var seen = [];
        items.forEach(function (it) { if (it.main && seen.indexOf(it.main) === -1) seen.push(it.main); });
        mains = [{ slug: '', label: 'Alle' }].concat(seen.map(function (s) {
          return { slug: s, label: slugToLabel(s) };
        }));
      }

      var state = { main: '', type: '', q: '', shown: o.batch, open: false };
      var io = null;
      var els = {
        seg:    root.querySelector('[data-ff="seg"]'),
        toggle: root.querySelector('[data-ff="toggle"]'),
        panel:  root.querySelector('[data-ff="panel"]'),
        chips:  root.querySelector('[data-ff="chips"]'),
        search: root.querySelector('[data-ff="search"]'),
        input:  root.querySelector('[data-ff="input"]'),
        clear:  root.querySelector('[data-ff="clear"]'),
        empty:  document.querySelector('[data-ff="empty"]'),
        loader: document.querySelector('[data-ff="loader"]')
      };

      function matches(it, ignoreType) {
        if (state.main && it.main !== state.main) return false;
        if (!ignoreType && state.type && it.types.indexOf(state.type) === -1) return false;
        if (state.q && it.search.indexOf(norm(state.q)) === -1) return false;
        return true;
      }

      function renderSeg() {
        els.seg.innerHTML = mains.map(function (m) {
          var n = items.filter(function (it) { return !m.slug || it.main === m.slug; }).length;
          return '<button type="button" data-main="' + m.slug + '" aria-pressed="' + (state.main === m.slug) + '">' +
                 m.label + '<span class="ff-n">' + n + '</span></button>';
        }).join('');
      }

      function renderChips() {
        var base = items.filter(function (it) { return matches(it, true); });
        var counts = {};
        base.forEach(function (it) {
          it.types.forEach(function (t) { counts[t] = (counts[t] || 0) + 1; });
        });
        var types = Object.keys(counts).sort(function (a, b) {
          return counts[b] - counts[a] || a.localeCompare(b, 'da');
        });
        var label = function (t) { return (o.labels && o.labels[t]) || slugToLabel(t); };

        els.chips.innerHTML = types.map(function (t) {
          return '<button type="button" data-type="' + t + '" aria-pressed="' + (state.type === t) + '">' +
                 label(t) + '<span class="ff-n">' + counts[t] + '</span></button>';
        }).join('');

        els.toggle.innerHTML = state.type
          ? label(state.type) + '<span class="ff-x" role="img" aria-label="Ryd type">×</span>'
          : 'Type<span class="ff-n">' + types.length + '</span>' +
            '<svg class="ff-chev" viewBox="0 0 24 24" fill="none" aria-hidden="true">' +
            '<path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        els.toggle.classList.toggle('is-set', !!state.type);
        els.toggle.setAttribute('aria-expanded', String(state.open));
        els.panel.dataset.open = String(state.open);
        els.panel.inert = !state.open;
      }

      function renderItems() {
        var shown = 0, visible = [];
        items.forEach(function (it) {
          var hit = matches(it, false);
          var show = hit && shown < state.shown;
          if (hit) shown++;
          it.el.classList.toggle(o.hiddenClass, !show);
          if (show) visible.push(it.el);
        });
        var total = items.filter(function (it) { return matches(it, false); }).length;
        if (els.empty)  els.empty.hidden  = total > 0;
        if (els.loader) els.loader.hidden = state.shown >= total;
        if (typeof o.onFilter === 'function') o.onFilter(visible);
        watchLoader();
      }

      function syncUrl() {
        var p = new URLSearchParams(window.location.search);
        state.main ? p.set('kategori', state.main) : p.delete('kategori');
        state.type ? p.set('type', state.type)     : p.delete('type');
        state.q.trim() ? p.set('q', state.q.trim()) : p.delete('q');
        var qs = p.toString();
        window.history.replaceState(null, '', window.location.pathname + (qs ? '?' + qs : ''));
      }

      function render() {
        renderSeg(); renderChips(); renderItems(); syncUrl();
        if (els.search) els.search.dataset.filled = state.q.trim() ? 'true' : 'false';
      }

      function readUrl() {
        var p = new URLSearchParams(window.location.search);
        var m = p.get('kategori');
        if (m && mains.some(function (x) { return x.slug === m; })) state.main = m;
        if (p.get('type')) { state.type = p.get('type'); state.open = true; }
        if (p.get('q') && els.input) { state.q = p.get('q'); els.input.value = state.q; }
      }

      function watchLoader() {
        if (!io || !els.loader) return;
        io.unobserve(els.loader);
        if (els.loader.hidden) return;
        window.requestAnimationFrame(function () {
          if (!els.loader.hidden) io.observe(els.loader);
        });
      }

      function loadMore() {
        var total = items.filter(function (it) { return matches(it, false); }).length;
        if (state.shown >= total) return;
        state.shown += o.batch;
        renderItems();
      }

      /* --- lyttere --- */
      els.seg.addEventListener('click', function (e) {
        var b = e.target.closest('button'); if (!b) return;
        state.main = b.dataset.main; state.type = ''; state.shown = o.batch; render();
      });
      els.chips.addEventListener('click', function (e) {
        var b = e.target.closest('button'); if (!b) return;
        state.type = state.type === b.dataset.type ? '' : b.dataset.type;
        state.shown = o.batch;
        if (state.type && window.matchMedia('(max-width:600px)').matches) state.open = false;
        render();
      });
      els.toggle.addEventListener('click', function (e) {
        if (e.target.closest('.ff-x')) { state.type = ''; state.shown = o.batch; render(); return; }
        state.open = !state.open; render();
      });
      if (els.input) {
        var t;
        els.input.addEventListener('input', function (e) {
          clearTimeout(t);
          var v = e.target.value;
          t = setTimeout(function () { state.q = v; state.shown = o.batch; render(); }, 160);
        });
      }
      if (els.clear) {
        els.clear.addEventListener('click', function () {
          els.input.value = ''; state.q = ''; state.shown = o.batch; render(); els.input.focus();
        });
      }
      root.addEventListener('click', function (e) {
        if (e.target.closest('[data-ff="reset"]')) {
          state = { main: '', type: '', q: '', shown: o.batch, open: false };
          if (els.input) els.input.value = '';
          render();
        }
      });
      document.addEventListener('click', function (e) {
        if (e.target.closest('[data-ff="reset"]') && !root.contains(e.target)) {
          state = { main: '', type: '', q: '', shown: o.batch, open: false };
          if (els.input) els.input.value = '';
          render();
        }
      });

      /* --- indlæs flere når bunden nærmer sig ---
         Observeren udsender kun ved ændring. Er zonen stadig i syne efter
         en indlæsning, skal den derfor observeres på ny — ellers stopper
         det efter første hold. */
      if (els.loader && 'IntersectionObserver' in window) {
        io = new IntersectionObserver(function (entries) {
          if (entries.some(function (en) { return en.isIntersecting; })) {
            loadMore();
            watchLoader();
          }
        }, { rootMargin: o.rootMargin });
      }
      var manual = document.querySelector('[data-ff="more"]');
      if (manual) manual.addEventListener('click', loadMore);

      readUrl();
      render();
      return { state: state, render: render, loadMore: loadMore };
    }
  };

  window.FinalFilter = FinalFilter;
})(window, document);
