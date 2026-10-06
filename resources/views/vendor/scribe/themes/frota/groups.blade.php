@foreach($groupedEndpoints as $group)
    <section class="group">
        <header class="group-head" id="{!! Str::slug($group['name']) !!}">
            <div class="group-eyebrow">Grupo <span class="group-count">{{ count($group['endpoints']) }} {{ count($group['endpoints']) === 1 ? 'endpoint' : 'endpoints' }}</span></div>
            <h1>{!! $group['name'] !!}</h1>
            <div class="group-desc">{!! Parsedown::instance()->text($group['description'] ?? '') !!}</div>
        </header>

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
    </section>
@endforeach
