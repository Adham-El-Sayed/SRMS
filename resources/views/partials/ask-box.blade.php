{{-- The manager's question box. Admin only; the route checks that too. --}}
<button type="button" class="ask-open" id="ask-open" aria-label="{{ __('Ask a question') }}">
    <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
        <circle cx="9" cy="9" r="6" stroke="currentColor" stroke-width="1.8"/>
        <path d="M13.5 13.5 17 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
    </svg>
    <span>{{ __('Ask') }}</span>
    <kbd>Ctrl K</kbd>
</button>

<div class="ask" id="ask" hidden>
    <div class="ask__box" role="dialog" aria-modal="true" aria-labelledby="ask-label">

        <label class="ask__field" for="ask-input">
            <span class="sr-only" id="ask-label">{{ __('Ask a question') }}</span>
            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true">
                <circle cx="9" cy="9" r="6" stroke="currentColor" stroke-width="1.8"/>
                <path d="M13.5 13.5 17 17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            <input type="text" id="ask-input" autocomplete="off" spellcheck="false" maxlength="120"
                   placeholder="{{ __('Revenue today? Who is absent?') }}">
            <button type="button" class="ask__close" id="ask-close" aria-label="{{ __('Close') }}">esc</button>
        </label>

        <div class="ask__body" id="ask-body" aria-live="polite"></div>
    </div>
</div>

@once
    @push('scripts')
    <script>
        /*
         * Opens on Ctrl+K (or the button), asks the server, shows the answer.
         * The server understands a fixed set of questions; anything else comes
         * back as "I don't know that one" with a few that it does.
         */
        (function () {
            'use strict';

            var panel = document.getElementById('ask');
            var input = document.getElementById('ask-input');
            var body = document.getElementById('ask-body');
            var url = @json(route('ask'));

            if (!panel || !input) return;

            var waiting = null;
            var asked = '';

            function escapeHtml(text) {
                var box = document.createElement('div');
                box.textContent = text == null ? '' : String(text);
                return box.innerHTML;
            }

            function open() {
                panel.hidden = false;
                input.focus();
                input.select();
                if (!body.innerHTML) showExamples();
            }

            function close() { panel.hidden = true; }

            function showExamples(list) {
                var examples = list || @json((new \App\Services\AskService())->examples());

                body.innerHTML =
                    '<p class="ask-hint">' + escapeHtml(@json(__('Try one of these:'))) + '</p>' +
                    '<div class="ask-examples">' +
                    examples.map(function (e) {
                        return '<button type="button" class="ask-example">' + escapeHtml(e) + '</button>';
                    }).join('') +
                    '</div>';
            }

            function show(data) {
                if (data.kind === 'unknown') {
                    body.innerHTML = '<p class="ask-hint">' + escapeHtml(data.answer) + '</p>';
                    var holder = document.createElement('div');
                    body.appendChild(holder);
                    showExamplesInto(data.examples || []);
                    return;
                }

                var html = '<div class="ask-answer' + (data.kind === 'text' || data.kind === 'none' ? ' ask-answer--text' : '') + '">'
                    + escapeHtml(data.answer) + '</div>';

                if (data.detail) html += '<div class="ask-detail">' + escapeHtml(data.detail) + '</div>';

                if (data.link) {
                    html += '<a class="ask-link" href="' + escapeHtml(data.link.url) + '">'
                        + escapeHtml(data.link.label) + '</a>';
                }

                body.innerHTML = html;
            }

            function showExamplesInto(examples) {
                var wrap = document.createElement('div');
                wrap.className = 'ask-examples';

                examples.forEach(function (e) {
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'ask-example';
                    button.textContent = e;
                    wrap.appendChild(button);
                });

                body.appendChild(wrap);
            }

            function ask() {
                var q = input.value.trim();

                if (q === '') { showExamples(); return; }
                if (q === asked) return;

                asked = q;
                body.innerHTML = '<p class="ask-working">' + escapeHtml(@json(__('Looking…'))) + '</p>';

                fetch(url + '?q=' + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (data) {
                        if (data) show(data);
                        else body.innerHTML = '<p class="ask-hint">' + escapeHtml(@json(__('Something went wrong. Please try again.'))) + '</p>';
                    })
                    .catch(function () {
                        body.innerHTML = '<p class="ask-hint">' + escapeHtml(@json(__('Something went wrong. Please try again.'))) + '</p>';
                    });
            }

            input.addEventListener('input', function () {
                clearTimeout(waiting);
                waiting = setTimeout(ask, 280);
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') { clearTimeout(waiting); ask(); }
            });

            body.addEventListener('click', function (e) {
                var example = e.target.closest('.ask-example');
                if (!example) return;
                input.value = example.textContent;
                asked = '';
                ask();
            });

            document.getElementById('ask-open').addEventListener('click', open);
            document.getElementById('ask-close').addEventListener('click', close);
            panel.addEventListener('click', function (e) { if (e.target === panel) close(); });

            document.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    panel.hidden ? open() : close();
                }
                if (e.key === 'Escape' && !panel.hidden) close();
            });
        })();
    </script>
    @endpush
@endonce
