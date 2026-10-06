@php
    use Knuckles\Scribe\Tools\Utils as u;

    $methodBySlug = [];
    foreach ($groupedEndpoints as $group) {
        foreach ($group['endpoints'] as $endpoint) {
            $methodBySlug[$endpoint->fullSlug()] = $endpoint->httpMethods[0];
        }
    }
    $methodLabel = ['DELETE' => 'DEL', 'PATCH' => 'PATCH'];
@endphp
<nav class="sidebar" id="sidebar" aria-label="Navegação da documentação">
    <div class="brand">
        @if($metadata['logo'] != false)
            <img src="{{ $metadata['logo'] }}" alt="logo" class="brand-logo">
        @else
            <span class="brand-mark" aria-hidden="true">{{ mb_strtoupper(mb_substr(strip_tags($metadata['title']), 0, 1)) }}</span>
        @endif
        <span class="brand-name">{!! $metadata['title'] !!}</span>
    </div>
    <p class="brand-meta">
        {{ u::trans('scribe::labels.base_url') }}: <code>{{ $baseUrl }}</code>
    </p>

    <input type="search" class="nav-search" id="nav-search" placeholder="{{ u::trans('scribe::labels.search') }}…" aria-label="{{ u::trans('scribe::labels.search') }}" autocomplete="off">

    <div class="nav-actions">
        <button type="button" class="nav-action" id="expand-all">Expandir tudo</button>
        <button type="button" class="nav-action" id="collapse-all">Recolher tudo</button>
    </div>

    <div class="nav-list" id="nav-list">
        @foreach($headings as $h1)
            <div class="nav-group is-collapsed" data-group="{!! $h1['slug'] !!}">
                <div class="nav-title-row">
                    <a class="nav-title" href="#{!! $h1['slug'] !!}" data-spy="{!! $h1['slug'] !!}">{!! $h1['name'] !!}</a>
                    @if(count($h1['subheadings']) > 0)
                        <button type="button" class="nav-chevron" aria-expanded="false" aria-label="Expandir {{ strip_tags($h1['name']) }}">
                            <svg viewBox="0 0 20 20" width="14" height="14" aria-hidden="true"><path d="M5 7.5l5 5 5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                    @endif
                </div>
                @foreach($h1['subheadings'] as $h2)
                    @php $method = $methodBySlug[$h2['slug']] ?? null; @endphp
                    <a class="nav-item" href="#{!! $h2['slug'] !!}" data-spy="{!! $h2['slug'] !!}">
                        @if($method)
                            <span class="m m-{{ strtolower($method) }}">{{ $methodLabel[$method] ?? $method }}</span>
                        @endif
                        <span class="nav-label">{!! $h2['name'] !!}</span>
                    </a>
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="nav-foot">
        <ul>
            @if($metadata['postman_collection_url'])
                <li><a href="{!! $metadata['postman_collection_url'] !!}">{!! u::trans('scribe::links.postman') !!}</a></li>
            @endif
            @if($metadata['openapi_spec_url'])
                <li><a href="{!! $metadata['openapi_spec_url'] !!}">{!! u::trans('scribe::links.openapi') !!}</a></li>
            @endif
        </ul>
        <div class="nav-foot-row">
            <span>{{ $metadata['last_updated'] }}</span>
            <button type="button" class="theme-toggle" id="theme-toggle">Alternar tema</button>
        </div>
    </div>
</nav>
