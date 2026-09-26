/*
 * Finding something on a menu.
 *
 * The same control serves the guest menu, the online page, the cashier's
 * counter and menu management: type a few letters, or pick a course, and
 * everything else steps out of the way. The page says which elements are
 * what through data attributes, so this file knows nothing about any one
 * page's markup:
 *
 *   [data-menu-filter]        the whole thing
 *   [data-filter-search]      the text box
 *   [data-filter-clear]       the button that empties it
 *   [data-filter-chip]        a course button, with data-category ('' = all)
 *   [data-filter-item]        one dish, with data-name and data-category
 *   [data-filter-group]       a section that hides once it is empty
 *   [data-filter-empty]       the "nothing matches" line
 *   [data-filter-count]       optional: how many are showing
 */
(function () {
    'use strict';

    function setUp(root) {
        var search = root.querySelector('[data-filter-search]');
        var clear  = root.querySelector('[data-filter-clear]');
        var chips  = Array.prototype.slice.call(root.querySelectorAll('[data-filter-chip]'));

        // The dishes may live outside the filter's own box, further down the page.
        var scope = root.dataset.menuFilter
            ? document.querySelector(root.dataset.menuFilter)
            : root;

        if (!scope) return;

        // These two may sit with the filter or down among the dishes.
        var empty = root.querySelector('[data-filter-empty]') || scope.querySelector('[data-filter-empty]');
        var count = root.querySelector('[data-filter-count]') || scope.querySelector('[data-filter-count]');

        var items  = Array.prototype.slice.call(scope.querySelectorAll('[data-filter-item]'));
        var groups = Array.prototype.slice.call(scope.querySelectorAll('[data-filter-group]'));

        if (!items.length) return;

        var category = '';

        function normalise(text) {
            // Arabic is written with and without its short vowels, and with
            // several shapes of the same letter. Fold them so a search for
            // "شاي" finds "شايّ" and "احمد" finds "أحمد".
            return (text || '')
                .toLowerCase()
                .replace(/[ً-ْـ]/g, '')
                .replace(/[أإآٱ]/g, 'ا')
                .replace(/ى/g, 'ي')
                .replace(/ة/g, 'ه')
                .trim();
        }

        function apply() {
            var needle = normalise(search ? search.value : '');
            var filtering = needle !== '' || category !== '';
            var showing = 0;

            items.forEach(function (item) {
                var byName = !needle || normalise(item.dataset.name).indexOf(needle) !== -1;
                var byCategory = !category || item.dataset.category === category;
                var hit = byName && byCategory;

                item.hidden = !hit;
                if (hit) showing++;
            });

            groups.forEach(function (group) {
                // With nothing typed and no course picked, the page stands as
                // it was written — an empty category still needs to be there
                // for whoever is about to put something in it.
                group.hidden = filtering && !group.querySelector('[data-filter-item]:not([hidden])');
            });

            if (empty) empty.hidden = !filtering || showing > 0;
            if (count) count.textContent = showing;
            if (clear) clear.hidden = !needle;
        }

        if (search) {
            search.addEventListener('input', apply);

            // Enter would submit the form this box sometimes sits in.
            search.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') event.preventDefault();
                if (event.key === 'Escape' && search.value) {
                    search.value = '';
                    apply();
                }
            });
        }

        if (clear) {
            clear.addEventListener('click', function () {
                search.value = '';
                apply();
                search.focus();
            });
        }

        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                category = chip.dataset.category || '';
                chips.forEach(function (other) {
                    other.classList.toggle('is-on', other === chip);
                    other.setAttribute('aria-pressed', other === chip ? 'true' : 'false');
                });
                apply();
            });
        });

        apply();
    }

    function start() {
        document.querySelectorAll('[data-menu-filter]').forEach(setUp);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
