{{-- Two-letter locale toggle. Keeps the visitor on the page they were reading. --}}
<div class="lang-switch" role="group" aria-label="{{ __('Language') }}">
    <a href="{{ route('locale.switch', 'en') }}"
       class="{{ app()->getLocale() === 'en' ? 'is-on' : '' }}"
       hreflang="en" aria-label="English">EN</a>

    <a href="{{ route('locale.switch', 'ar') }}"
       class="{{ app()->getLocale() === 'ar' ? 'is-on' : '' }}"
       hreflang="ar" aria-label="العربية">ع</a>
</div>
