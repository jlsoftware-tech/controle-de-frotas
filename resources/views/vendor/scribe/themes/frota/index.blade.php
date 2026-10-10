@php
    use Knuckles\Scribe\Tools\WritingUtils as u;
@endphp
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{!! $metadata['title'] !!}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&family=Public+Sans:wght@400;500;600;700&display=swap">

    <script>
        (function () {
            try {
                var theme = localStorage.getItem('frota-theme');
                if (theme === 'light' || theme === 'dark') {
                    document.documentElement.setAttribute('data-theme', theme);
                }
            } catch (e) {}
        })();
    </script>

    <style>
        @include('scribe::themes.frota.style')
    </style>

@if($tryItOut['enabled'] ?? true)
    <script>
        var tryItOutBaseUrl = "{!! $tryItOut['base_url'] ?? $baseUrl !!}";
        var useCsrf = Boolean({!! $tryItOut['use_csrf'] ?? null !!});
        var csrfUrl = "{!! $tryItOut['csrf_url'] ?? null !!}";
    </script>
    <script src="{{ u::getVersionedAsset($assetPathPrefix.'js/tryitout.js') }}"></script>
@endif
</head>

<body data-languages="{{ json_encode(array_values($metadata['example_languages'] ?? [])) }}">

<button type="button" class="menu-button" id="menu-button" aria-controls="sidebar" aria-expanded="false">
    <span aria-hidden="true">☰</span> Menu
</button>

<div class="shell">
    @include('scribe::themes.frota.sidebar')

    <div class="main">
        <section class="intro">
            {!! $intro !!}

            {!! $auth !!}
        </section>

        @include('scribe::themes.frota.groups')

        @if(trim($append) !== '')
            <section class="intro">
                {!! $append !!}
            </section>
        @endif
    </div>
</div>

<script>
    @include('scribe::themes.frota.script')
</script>
</body>
</html>
