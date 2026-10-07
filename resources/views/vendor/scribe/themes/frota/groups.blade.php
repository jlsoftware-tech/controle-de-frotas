@foreach($groupedEndpoints as $group)
    <section class="group is-collapsed" data-group="{!! Str::slug($group['name']) !!}">
        <header class="group-head" id="{!! Str::slug($group['name']) !!}">
            <button type="button" class="group-toggle" aria-expanded="false" aria-controls="group-body-{!! Str::slug($group['name']) !!}" aria-label="Expandir grupo {{ $group['name'] }}">
                <svg viewBox="0 0 20 20" width="18" height="18" aria-hidden="true"><path d="M5 7.5l5 5 5-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <div class="group-eyebrow">Grupo <span class="group-count">{{ count($group['endpoints']) }} {{ count($group['endpoints']) === 1 ? 'endpoint' : 'endpoints' }}</span></div>
            <h1>{!! $group['name'] !!}</h1>
            <div class="group-desc">{!! Parsedown::instance()->text($group['description'] ?? '') !!}</div>
        </header>

        <div class="group-body" id="group-body-{!! Str::slug($group['name']) !!}">
        @foreach($group['subgroups'] as $subgroupName => $subgroup)
            @if($subgroupName !== "")
                <div class="subgroup-head" id="{!! Str::slug($group['name']) !!}-{!! Str::slug($subgroupName) !!}">
                    <h2>{{ $subgroupName }}</h2>
                    @php($subgroupDescription = collect($subgroup)->first(fn ($e) => $e->metadata->subgroupDescription)?->metadata?->subgroupDescription)
                    @if($subgroupDescription)
                        {!! Parsedown::instance()->text($subgroupDescription) !!}
                    @endif
                </div>
            @endif
            @foreach($subgroup as $endpoint)
                @include('scribe::themes.frota.endpoint')
            @endforeach
        @endforeach
        </div>
    </section>
@endforeach
