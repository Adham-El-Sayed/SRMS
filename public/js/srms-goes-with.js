/*
 * "People also order…"
 *
 * The pairs come from the page as a small map of product id to the ids most
 * often ordered alongside it, worked out from real baskets. This file only
 * decides what to show: it reads each dish's name, price and picture off the
 * card already on the page, so nothing is sent twice.
 *
 * The buttons it draws are ordinary .add-button elements, which both menus
 * already listen for, so a suggestion is added exactly like any other dish.
 */
(function () {
    'use strict';

    function cardFor(id) {
        var dish = document.querySelector('[data-dish="' + id + '"]');
        if (!dish) return null;

        var button = dish.querySelector('.add-button');
        if (!button) return null;        // sold out, or the page cannot order

        var image = dish.querySelector('.dish__image img');

        return {
            id: id,
            // The stored name carries both languages; the button also carries
            // the one this reader should see.
            name: button.dataset.productLabel || button.dataset.productName,
            fullName: button.dataset.productName,
            price: parseFloat(button.dataset.productPrice),
            image: image ? image.getAttribute('src') : null
        };
    }

    function escapeHtml(text) {
        var box = document.createElement('div');
        box.textContent = text === null || text === undefined ? '' : String(text);
        return box.innerHTML;
    }

    function money(amount) {
        return amount.toFixed(2) + ' ' + (window.__t ? __t('EGP') : 'EGP');
    }

    /**
     * @param {Element}  container  where to draw, hidden when there is nothing
     * @param {number[]} basket     product ids already in the order
     * @param {number}   howMany
     */
    function render(container, basket, howMany) {
        if (!container) return;

        var pairs = window.SRMS_PAIRS || {};
        var limit = howMany || 3;
        var taken = {};
        var picked = [];

        basket.forEach(function (id) { taken[id] = true; });

        // Walk the basket in order, taking each dish's best partner first, so
        // three burgers do not fill the row with the same three drinks.
        for (var round = 0; round < limit && picked.length < limit; round++) {
            for (var i = 0; i < basket.length && picked.length < limit; i++) {
                var suggestions = pairs[basket[i]] || [];
                var id = suggestions[round];

                if (!id || taken[id]) continue;

                var card = cardFor(id);
                if (!card) continue;

                taken[id] = true;
                picked.push(card);
            }
        }

        if (!picked.length) {
            container.hidden = true;
            container.innerHTML = '';
            return;
        }

        var html =
            '<p class="goes-with__title">' +
                (window.__t ? __t('People also order') : 'People also order') +
            '</p><div class="goes-with__row">';

        picked.forEach(function (card) {
            html +=
                '<div class="goes-with__item">' +
                    (card.image
                        ? '<img src="' + escapeHtml(card.image) + '" alt="" loading="lazy">'
                        : '<span class="goes-with__blank">🍽</span>') +
                    '<div class="goes-with__text">' +
                        '<span class="goes-with__name">' + escapeHtml(card.name) + '</span>' +
                        '<span class="goes-with__price">' + money(card.price) + '</span>' +
                    '</div>' +
                    '<button type="button" class="add-button goes-with__add"' +
                        ' data-product-id="' + card.id + '"' +
                        ' data-product-name="' + escapeHtml(card.fullName) + '"' +
                        ' data-product-label="' + escapeHtml(card.name) + '"' +
                        ' data-product-price="' + card.price + '"' +
                        ' aria-label="' + (window.__t ? __t('Add') : 'Add') + ' ' + escapeHtml(card.name) + '">+</button>' +
                '</div>';
        });

        container.innerHTML = html + '</div>';
        container.hidden = false;
    }

    window.SRMSGoesWith = { render: render };
})();
