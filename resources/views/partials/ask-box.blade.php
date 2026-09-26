{{-- The manager's assistant. Admin only; the route checks that too.

     A bubble in the corner rather than a keyboard shortcut: nobody
     discovers Ctrl+K on their own, and the answers read better as a
     conversation you can scroll back through than as one box that
     replaces its contents every time you ask. --}}

<button type="button" class="ask-fab" id="ask-open"
        aria-label="{{ __('Ask a question') }}" aria-expanded="false" aria-controls="ask">
    <svg class="ask-fab__talk" viewBox="0 0 22 22" fill="none" aria-hidden="true">
        <path d="M4 4.8h14a1.8 1.8 0 0 1 1.8 1.8v7.2a1.8 1.8 0 0 1-1.8 1.8H9.4L5.2 19v-3.4H4a1.8 1.8 0 0 1-1.8-1.8V6.6A1.8 1.8 0 0 1 4 4.8Z"
              stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
        <circle cx="7.6" cy="10.2" r="1" fill="currentColor"/>
        <circle cx="11" cy="10.2" r="1" fill="currentColor"/>
        <circle cx="14.4" cy="10.2" r="1" fill="currentColor"/>
    </svg>
    <svg class="ask-fab__shut" viewBox="0 0 22 22" fill="none" aria-hidden="true">
        <path d="M6 6l10 10M16 6 6 16" stroke="currentColor" stroke-width="1.9" stroke-linecap="round"/>
    </svg>
    <span class="ask-fab__label">{{ __('Ask') }}</span>
</button>

<section class="ask" id="ask" hidden role="dialog" aria-labelledby="ask-title">
    <header class="ask__head">
        <span class="ask__avatar" aria-hidden="true">S</span>
        <span class="ask__who">
            <strong id="ask-title">{{ __('Ask SRMS') }}</strong>
            <small>{{ __('Takings, orders, staff — just ask') }}</small>
        </span>
        <button type="button" class="ask__close" id="ask-close" aria-label="{{ __('Close') }}">
            <svg viewBox="0 0 18 18" fill="none" aria-hidden="true">
                <path d="M5 5l8 8M13 5l-8 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
        </button>
    </header>

    <div class="ask__thread" id="ask-thread" aria-live="polite"></div>

    <form class="ask__compose" id="ask-form" autocomplete="off">
        <input type="text" id="ask-input" spellcheck="false" maxlength="120"
               placeholder="{{ __('Revenue today? Who is absent?') }}"
               aria-label="{{ __('Ask a question') }}">
        <button type="submit" class="ask__send" aria-label="{{ __('Send') }}">
            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <path d="M17 10 3.5 4.5 5.6 10l-2.1 5.5L17 10Z"
                      stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
            </svg>
        </button>
    </form>
</section>

@once
    @push('scripts')
    <script>
        /*
         * A small chat panel. Questions go to the server, which understands a
         * fixed set of them and answers from ordinary queries; anything else
         * comes back as "I don't know that one" with a few that it does.
         *
         * The thread is kept in sessionStorage so moving between pages does
         * not throw away what was already asked. Stored as data rather than
         * markup, and re-rendered, so nothing from storage is ever written
         * into the page as HTML.
         */
        (function () {
            'use strict';

            var fab = document.getElementById('ask-open');
            var panel = document.getElementById('ask');
            var thread = document.getElementById('ask-thread');
            var form = document.getElementById('ask-form');
            var input = document.getElementById('ask-input');

            if (!fab || !panel || !thread || !form || !input) return;

            var url = @json(route('ask'));
            var EXAMPLES = @json((new \App\Services\AskService())->examples());
            var SAY = {
                hello: @json(__('Ask me about the restaurant. Here are a few to start with:')),
                looking: @json(__('Looking…')),
                broke: @json(__('Something went wrong. Please try again.'))
            };

            var KEY = 'srms.ask.thread';
            var LIMIT = 40;
            var turns = [];
            var busy = false;

            /* ---- what has been said ------------------------------------- */

            function remember() {
                try { sessionStorage.setItem(KEY, JSON.stringify(turns.slice(-LIMIT))); } catch (e) {}
            }

            function recall() {
                try {
                    var saved = JSON.parse(sessionStorage.getItem(KEY) || '[]');
                    if (Array.isArray(saved)) turns = saved.slice(-LIMIT);
                } catch (e) { turns = []; }
            }

            /* ---- drawing -------------------------------------------------
               Everything goes in through textContent. */

            function bubble(kind) {
                var el = document.createElement('div');
                el.className = 'ask-turn ask-turn--' + kind;
                return el;
            }

            function drawAsked(text) {
                var el = bubble('asked');
                el.textContent = text;
                thread.appendChild(el);
            }

            function drawExamples(list) {
                var wrap = document.createElement('div');
                wrap.className = 'ask-examples';

                (list || []).forEach(function (text) {
                    var chip = document.createElement('button');
                    chip.type = 'button';
                    chip.className = 'ask-example';
                    chip.textContent = text;
                    wrap.appendChild(chip);
                });

                thread.appendChild(wrap);
            }

            function drawAnswer(data) {
                var el = bubble('said');

                if (data.kind === 'unknown' || data.kind === 'note') {
                    var note = document.createElement('p');
                    note.className = 'ask-note';
                    note.textContent = data.answer;
                    el.appendChild(note);
                    thread.appendChild(el);
                    drawExamples(data.examples && data.examples.length ? data.examples : EXAMPLES);
                    return;
                }

                var headline = document.createElement('div');
                headline.className = 'ask-answer'
                    + (data.kind === 'text' || data.kind === 'none' ? ' ask-answer--text' : '');
                headline.textContent = data.answer;
                el.appendChild(headline);

                if (data.detail) {
                    var detail = document.createElement('div');
                    detail.className = 'ask-detail';
                    detail.textContent = data.detail;
                    el.appendChild(detail);
                }

                if (data.link && data.link.url) {
                    var link = document.createElement('a');
                    link.className = 'ask-link';
                    link.href = data.link.url;
                    link.textContent = data.link.label || data.link.url;
                    el.appendChild(link);
                }

                thread.appendChild(el);
            }

            function drawTurn(turn) {
                if (turn.role === 'asked') drawAsked(turn.text);
                else if (turn.role === 'hello') { drawHello(); }
                else drawAnswer(turn.data);
            }

            function drawHello() {
                var el = bubble('said');
                var note = document.createElement('p');
                note.className = 'ask-note';
                note.textContent = SAY.hello;
                el.appendChild(note);
                thread.appendChild(el);
                drawExamples(EXAMPLES);
            }

            function redraw() {
                thread.innerHTML = '';
                if (!turns.length) { drawHello(); return; }
                turns.forEach(drawTurn);
            }

            function toBottom() { thread.scrollTop = thread.scrollHeight; }

            /* ---- opening and closing ------------------------------------ */

            function open() {
                panel.hidden = false;
                fab.classList.add('is-open');
                fab.setAttribute('aria-expanded', 'true');
                redraw();
                toBottom();
                input.focus();
            }

            function close() {
                panel.hidden = true;
                fab.classList.remove('is-open');
                fab.setAttribute('aria-expanded', 'false');
            }

            /* ---- asking -------------------------------------------------- */

            function ask(question) {
                var q = (question || '').trim();
                if (q === '' || busy) return;

                busy = true;
                input.value = '';

                turns.push({ role: 'asked', text: q });
                drawAsked(q);

                var waiting = bubble('said');
                waiting.classList.add('ask-turn--waiting');
                waiting.textContent = SAY.looking;
                thread.appendChild(waiting);
                toBottom();

                fetch(url + '?q=' + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (data) {
                        waiting.remove();

                        if (!data) data = { kind: 'note', answer: SAY.broke, examples: EXAMPLES };

                        turns.push({ role: 'said', data: data });
                        turns = turns.slice(-LIMIT);
                        remember();

                        drawAnswer(data);
                        toBottom();
                    })
                    .catch(function () {
                        waiting.remove();
                        var data = { kind: 'note', answer: SAY.broke, examples: EXAMPLES };
                        turns.push({ role: 'said', data: data });
                        remember();
                        drawAnswer(data);
                        toBottom();
                    })
                    .then(function () { busy = false; input.focus(); });
            }

            /* ---- wiring --------------------------------------------------
               The chips are drawn and redrawn, so the thread listens for
               them rather than each one listening for itself. */

            form.addEventListener('submit', function (e) {
                e.preventDefault();
                ask(input.value);
            });

            thread.addEventListener('click', function (e) {
                var chip = e.target.closest('.ask-example');
                if (chip) ask(chip.textContent);
            });

            fab.addEventListener('click', function () {
                panel.hidden ? open() : close();
            });

            document.getElementById('ask-close').addEventListener('click', close);

            document.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    panel.hidden ? open() : close();
                    return;
                }
                if (e.key === 'Escape' && !panel.hidden) close();
            });

            recall();
        })();
    </script>
    @endpush
@endonce
