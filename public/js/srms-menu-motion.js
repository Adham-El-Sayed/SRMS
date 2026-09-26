/*
 * Movement on the menu.
 *
 * The brief was "professional, not obviously machine-made", and the tell of
 * a machine-made page is too much motion: everything bouncing, sliding and
 * pulsing at once. So this does very little, and does it quickly —
 * a dish settles into place as you reach it, a count ticks when it changes,
 * the order bar rises when there is finally something in it.
 *
 * Every dish animates once and never again. Anyone who has asked their
 * system for less motion gets none of it.
 */
(function () {
    'use strict';

    var calm = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)');

    if (calm && calm.matches) {
        document.documentElement.classList.add('is-calm');
        return;
    }

    /* ---------- a dish settles in as you reach it ---------- */

    function revealOnScroll() {
        // Not the favourites rail: it scrolls sideways, so its later cards
        // never meet the viewport going down the page and would sit faded
        // out until someone happened to swipe them into view.
        var cards = Array.prototype.slice.call(
            document.querySelectorAll('.dish, .menu-section__head')
        );

        if (!cards.length || !('IntersectionObserver' in window)) return;

        cards.forEach(function (card) { card.classList.add('will-settle'); });

        // Items arriving together are staggered, but only within a row's
        // worth — a long list should never feel like it is queueing.
        var batch = [];
        var flushing = null;

        function flush() {
            batch.forEach(function (card, index) {
                card.style.transitionDelay = Math.min(index * 45, 180) + 'ms';
                card.classList.add('has-settled');
            });
            batch = [];
            flushing = null;
        }

        var watcher = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;

                batch.push(entry.target);
                watcher.unobserve(entry.target);

                if (!flushing) flushing = window.requestAnimationFrame(flush);
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });

        cards.forEach(function (card) { watcher.observe(card); });

        // A card the filter hides and shows again should simply be there.
        document.addEventListener('live:filtered', function () {
            cards.forEach(function (card) { card.classList.add('has-settled'); });
        });
    }

    /* ---------- a number that changes says so, once ---------- */

    function tickOnChange(selector) {
        var el = document.querySelector(selector);
        if (!el) return;

        var last = el.textContent;

        new MutationObserver(function () {
            if (el.textContent === last) return;
            last = el.textContent;

            el.classList.remove('has-ticked');
            void el.offsetWidth;          // let the class actually restart
            el.classList.add('has-ticked');
        }).observe(el, { childList: true, characterData: true, subtree: true });
    }

    /* ---------- start ---------- */

    function start() {
        revealOnScroll();
        tickOnChange('#cart-bar-count');
        tickOnChange('#cart-bar-total');

        // The page has loaded: let the bar animate from here on, but never
        // on first paint, where it would look like a glitch.
        window.requestAnimationFrame(function () {
            document.documentElement.classList.add('is-ready');
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
