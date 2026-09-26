/*
 * SRMS live updates for staff pages.
 *
 *  - Every few seconds asks the server for the numbers behind the navigation
 *    badges (kitchen orders, waiter alerts, payments waiting).
 *  - Plays a chime when a new order arrives and a different one for a waiter
 *    call. The sound is synthesised, so there is no audio file to load.
 *  - Refreshes any board on the page (an element with data-live-url) when
 *    its contents change, without reloading the page.
 *  - If the session has expired, stops polling and sends the user to sign in.
 *
 * Browsers only allow sound after the user has interacted with the page, so
 * the bell in the top bar shows a dot until the first click or key press.
 */
(function () {
    'use strict';

    var config = window.SRMS_LIVE || {};
    if (!config.pulseUrl) return;

    var INTERVAL = 5000;
    var KEY_SOUND = 'srms.sound';
    var KEY_LAST_ORDER = 'srms.lastOrderSeen';
    var KEY_LAST_ALERT = 'srms.lastAlertSeen';

    var stopped = false;
    var boards = [];
    var audio = null;
    var busy = 0;          // actions in flight: don't redraw underneath them

    /* ---------- storage that never throws ---------- */

    function store(key, value) {
        try {
            if (value === undefined) return window.localStorage.getItem(key);
            window.localStorage.setItem(key, String(value));
        } catch (e) { return null; }
        return null;
    }

    /* ---------- sound ---------- */

    function soundOn() {
        return store(KEY_SOUND) !== 'off';
    }

    function audioContext() {
        if (audio) return audio;
        var Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) return null;
        audio = new Ctx();
        return audio;
    }

    function unlockAudio() {
        var ctx = audioContext();
        if (ctx && ctx.state === 'suspended') ctx.resume();
        updateBell();
    }

    function tone(ctx, freq, start, length, volume) {
        var osc = ctx.createOscillator();
        var gain = ctx.createGain();
        osc.type = 'sine';
        osc.frequency.value = freq;
        gain.gain.setValueAtTime(0.0001, start);
        gain.gain.exponentialRampToValueAtTime(volume, start + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, start + length);
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(start);
        osc.stop(start + length + 0.05);
    }

    function ring(kind) {
        if (!soundOn()) return;
        var ctx = audioContext();
        if (!ctx || ctx.state !== 'running') return;
        var t = ctx.currentTime + 0.02;
        if (kind === 'alert') {
            // Waiter call: three quick, higher beeps.
            tone(ctx, 1046.5, t, 0.14, 0.25);
            tone(ctx, 1046.5, t + 0.2, 0.14, 0.25);
            tone(ctx, 1046.5, t + 0.4, 0.14, 0.25);
        } else {
            // New order: a warm two-note chime, played twice.
            tone(ctx, 659.3, t, 0.35, 0.3);
            tone(ctx, 880.0, t + 0.18, 0.5, 0.3);
            tone(ctx, 659.3, t + 0.9, 0.35, 0.3);
            tone(ctx, 880.0, t + 1.08, 0.5, 0.3);
        }
    }

    function updateBell() {
        var bell = document.getElementById('sound-toggle');
        if (!bell) return;
        var on = soundOn();
        var ctx = audio;
        var locked = on && (!ctx || ctx.state !== 'running');
        bell.classList.toggle('is-off', !on);
        bell.classList.toggle('is-locked', locked);
        bell.setAttribute('aria-pressed', on ? 'true' : 'false');
        bell.title = !on ? config.text.soundOff : (locked ? config.text.soundLocked : config.text.soundOn);
    }

    /* ---------- toast ---------- */

    function toast(message, href) {
        var box = document.getElementById('live-toasts');
        if (!box) return;
        var el = document.createElement(href ? 'a' : 'div');
        el.className = 'live-toast';
        el.textContent = message;
        if (href) el.href = href;
        box.appendChild(el);
        setTimeout(function () { el.classList.add('is-leaving'); }, 6000);
        setTimeout(function () { el.remove(); }, 6600);
    }

    /* ---------- session expiry ---------- */

    function handleAuthFailure(response) {
        var signedOut = response.status === 401 || response.status === 419 ||
            (response.redirected && /\/login(\?|$)/.test(response.url));
        if (!signedOut) return false;
        if (!stopped) {
            stopped = true;
            toast(config.text.signedOut);
            setTimeout(function () { window.location.href = config.loginUrl; }, 1500);
        }
        return true;
    }

    function getJson(url) {
        return fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
            cache: 'no-store'
        }).then(function (response) {
            if (handleAuthFailure(response)) return null;
            if (!response.ok) return null;
            return response.json();
        }).catch(function () { return null; });
    }

    /* ---------- navigation badges + "something new" ---------- */

    function setBadge(name, count) {
        document.querySelectorAll('[data-pulse="' + name + '"]').forEach(function (badge) {
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.hidden = !(count > 0);
        });
    }

    /*
     * A navigation section is usually closed, so it carries the total of the
     * counts inside it: the kitchen's orders and the waiting alerts add up on
     * the section's own badge, and nothing waiting goes unnoticed.
     */
    function rollUpBadges() {
        document.querySelectorAll('[data-badge-sum]').forEach(function (badge) {
            var group = badge.closest('[data-nav-group]');
            if (!group) return;

            var total = 0;

            group.querySelectorAll('.nav-menu [data-pulse]').forEach(function (child) {
                if (!child.hidden) total += parseInt(child.textContent, 10) || 0;
            });

            badge.textContent = total > 99 ? '99+' : String(total);
            badge.hidden = total === 0;
        });
    }

    function checkNew(key, latest, onNew) {
        if (typeof latest !== 'number') return;
        var seen = store(key);
        if (seen === null) { store(key, latest); return; }   // first visit: nothing is "new"
        if (latest > Number(seen)) {
            store(key, latest);                                // other tabs won't ring again
            onNew();
        }
    }

    function pulse() {
        if (stopped) return Promise.resolve();
        return getJson(config.pulseUrl).then(function (data) {
            if (!data) return;

            setBadge('kitchen', data.kitchen);
            if ('alerts' in data) setBadge('alerts', data.alerts);
            if ('online' in data) setBadge('online', data.online);
            if ('payments' in data) setBadge('payments', data.payments);

            var somethingNew = false;

            checkNew(KEY_LAST_ORDER, data.latest_order_id, function () {
                somethingNew = true;
                ring('order');
                toast(config.text.newOrder, config.kitchenUrl);
            });

            checkNew(KEY_LAST_ALERT, data.latest_alert_id, function () {
                somethingNew = true;
                ring('alert');
                toast(config.text.newAlert, config.alertsUrl);
            });

            rollUpBadges();

            if (somethingNew) refreshBoards();
        });
    }

    /* ---------- boards ---------- */

    /*
     * A board is left alone while an action it started is still in flight, and
     * while someone is typing into it — redrawing then would either swallow the
     * click or wipe what they were writing. It is NOT judged by whether it
     * holds a disabled button: a button disabled by its own click would keep
     * the board frozen for good.
     */
    function boardIsBusy(board) {
        if (busy > 0) return true;
        var active = document.activeElement;
        return !!(active && board.el.contains(active) &&
            /^(INPUT|SELECT|TEXTAREA)$/.test(active.tagName));
    }

    function refreshBoard(board) {
        return getJson(board.el.dataset.liveUrl).then(function (data) {
            if (!data || data.signature === board.signature) return;
            if (boardIsBusy(board)) return;
            board.signature = data.signature;
            board.el.innerHTML = data.html;
            board.el.dispatchEvent(new CustomEvent('live:updated', { bubbles: true }));
        });
    }

    function refreshBoards() {
        return Promise.all(boards.map(refreshBoard));
    }

    /* ---------- loop ---------- */

    function tick() {
        if (stopped) return;
        Promise.all([pulse(), refreshBoards()]).then(function () {
            if (!stopped) setTimeout(tick, INTERVAL);
        });
    }

    function start() {
        document.querySelectorAll('[data-live-url]').forEach(function (el) {
            boards.push({ el: el, signature: null });
        });

        var bell = document.getElementById('sound-toggle');
        if (bell) {
            bell.addEventListener('click', function () {
                store(KEY_SOUND, soundOn() ? 'off' : 'on');
                unlockAudio();
                if (soundOn()) ring('order');   // let them hear what it sounds like
            });
        }

        ['pointerdown', 'keydown', 'touchstart'].forEach(function (evt) {
            document.addEventListener(evt, unlockAudio, { once: false, passive: true });
        });

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) { pulse(); refreshBoards(); }
        });

        updateBell();
        tick();
    }

    window.SRMSLive = {
        refreshNow: function () { return Promise.all([pulse(), refreshBoards()]); },
        handleAuthFailure: handleAuthFailure,

        /*
         * Called before a page's own action starts; the returned function is
         * called once it finishes. Boards hold still in between. A safety
         * timer releases the hold if a page ever forgets to, so a lost
         * release can never freeze the board the way it used to.
         */
        hold: function () {
            busy++;
            var done = false;
            var release = function () {
                if (done) return;
                done = true;
                busy = Math.max(0, busy - 1);
            };
            setTimeout(release, 15000);
            return release;
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
