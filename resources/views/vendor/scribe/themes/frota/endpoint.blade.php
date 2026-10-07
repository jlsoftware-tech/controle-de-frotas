@php
    use Knuckles\Scribe\Tools\Utils as u;
    /** @var Knuckles\Camel\Output\OutputEndpointData $endpoint */
    $endpointId = $endpoint->endpointId();
    $statusClass = fn (int $status) => $status >= 500 ? 'is-5xx' : ($status >= 400 ? 'is-4xx' : 'is-2xx');
@endphp

<article class="endpoint" id="{!! $endpoint->fullSlug() !!}">
    <div class="doc">
        <h2>{{ $endpoint->name() }}</h2>

        <div class="route">
            @foreach($endpoint->httpMethods as $method)
                <span class="verb verb-{{ strtolower($method) }}">{{ $method }}</span>
            @endforeach
            <code class="route-uri">{{ $endpoint->uri }}</code>
            <span class="tags">
                @if($endpoint->isAuthed())
                    <span class="tag tag-auth">requer autenticação</span>
                @endif
                @if($endpoint->metadata->deprecated !== false)
                    <span class="tag tag-dep">{{ $endpoint->metadata->deprecated === true ? 'obsoleto' : 'obsoleto: '.$endpoint->metadata->deprecated }}</span>
                @endif
            </span>
        </div>

        <div class="lead">{!! Parsedown::instance()->text($endpoint->metadata->description ?: '') !!}</div>

        <form id="form-{{ $endpointId }}" class="request-form" data-method="{{ $endpoint->httpMethods[0] }}"
              data-path="{{ $endpoint->uri }}"
              data-authed="{{ $endpoint->isAuthed() ? 1 : 0 }}"
              data-hasfiles="{{ $endpoint->hasFiles() ? 1 : 0 }}"
              data-isarraybody="{{ $endpoint->isArrayBody() ? 1 : 0 }}"
              autocomplete="off"
              onsubmit="event.preventDefault(); executeTryOut('{{ $endpointId }}', this);">

            <div class="section-head">
                <h3>{{ u::trans("scribe::endpoint.request") }}</h3>
                @if($metadata['try_it_out']['enabled'] ?? false)
                    <div class="try-actions">
                        <button type="button" class="btn btn-try" id="btn-tryout-{{ $endpointId }}"
                                onclick="tryItOut('{{ $endpointId }}');">{{ u::trans("scribe::try_it_out.open") }}</button>
                        <button type="button" class="btn btn-cancel" id="btn-canceltryout-{{ $endpointId }}"
                                onclick="cancelTryOut('{{ $endpointId }}');" hidden>{{ u::trans("scribe::try_it_out.cancel") }}</button>
                        <button type="submit" class="btn btn-send" id="btn-executetryout-{{ $endpointId }}"
                                data-initial-text="{{ u::trans("scribe::try_it_out.send") }}"
                                data-loading-text="{{ u::trans("scribe::try_it_out.loading") }}"
                                hidden>{{ u::trans("scribe::try_it_out.send") }}</button>
                    </div>
                @endif
            </div>

            @if(count($endpoint->headers))
                <h4 class="param-group">{{ u::trans("scribe::endpoint.headers") }}</h4>
                <div class="params">
                    @foreach($endpoint->headers as $name => $example)
                        @php
                            $htmlOptions = [];
                            if ($endpoint->isAuthed() && 'header' == $metadata['auth']['location'] && $metadata['auth']['name'] == $name) {
                                $htmlOptions = ['class' => 'auth-value'];
                            }
                        @endphp
                        @include('scribe::themes.frota.param', [
                            'name' => $name, 'type' => null, 'required' => true, 'deprecated' => false,
                            'description' => null, 'example' => $example, 'endpointId' => $endpointId,
                            'component' => 'header', 'isInput' => true, 'html' => $htmlOptions,
                        ])
                    @endforeach
                </div>
            @endif

            @if(count($endpoint->urlParameters))
                <h4 class="param-group">{{ u::trans("scribe::endpoint.url_parameters") }}</h4>
                <div class="params">
                    @foreach($endpoint->urlParameters as $attribute => $parameter)
                        @include('scribe::themes.frota.param', [
                            'name' => $parameter->name, 'type' => $parameter->type ?? 'string',
                            'required' => $parameter->required, 'deprecated' => $parameter->deprecated,
                            'description' => $parameter->description, 'example' => $parameter->example ?? '',
                            'enumValues' => $parameter->enumValues, 'endpointId' => $endpointId,
                            'component' => 'url', 'isInput' => true,
                        ])
                    @endforeach
                </div>
            @endif

            @if(count($endpoint->queryParameters))
                <h4 class="param-group">{{ u::trans("scribe::endpoint.query_parameters") }}</h4>
                <div class="params">
                    @foreach($endpoint->queryParameters as $attribute => $parameter)
                        @php
                            $htmlOptions = [];
                            if ($endpoint->isAuthed() && 'query' == $metadata['auth']['location'] && $metadata['auth']['name'] == $attribute) {
                                $htmlOptions = ['class' => 'auth-value'];
                            }
                        @endphp
                        @include('scribe::themes.frota.param', [
                            'name' => $parameter->name, 'type' => $parameter->type,
                            'required' => $parameter->required, 'deprecated' => $parameter->deprecated,
                            'description' => $parameter->description, 'example' => $parameter->example ?? '',
                            'enumValues' => $parameter->enumValues, 'endpointId' => $endpointId,
                            'component' => 'query', 'isInput' => true, 'html' => $htmlOptions,
                        ])
                    @endforeach
                </div>
            @endif

            @if(count($endpoint->nestedBodyParameters))
                <h4 class="param-group">{{ u::trans("scribe::endpoint.body_parameters") }}</h4>
                <div class="params">
                    @include('scribe::themes.frota.params', [
                        'fields' => $endpoint->nestedBodyParameters, 'endpointId' => $endpointId, 'isInput' => true, 'level' => 0,
                    ])
                </div>
            @endif
        </form>

        @if(count($endpoint->responseFields))
            <div class="section-head"><h3>{{ u::trans("scribe::endpoint.response") }}</h3></div>
            <h4 class="param-group">{{ u::trans("scribe::endpoint.response_fields") }}</h4>
            <div class="params">
                @include('scribe::themes.frota.params', [
                    'fields' => $endpoint->nestedResponseFields, 'endpointId' => $endpointId, 'isInput' => false, 'level' => 0,
                ])
            </div>
        @endif
    </div>

    <aside class="code" aria-label="Exemplos de {{ $endpoint->name() }}">
        <div class="code-inner">
            <section class="panel" id="example-requests-{!! $endpointId !!}">
                <div class="panel-head">
                    <b>{{ u::trans("scribe::endpoint.example_request") }}</b>
                    <div class="tabs" role="tablist">
                        @foreach($metadata['example_languages'] as $name => $language)
                            @php if (is_numeric($name)) { $name = $language; } @endphp
                            <button type="button" class="tab lang-tab" role="tab" data-lang="{{ $language }}">{{ $name }}</button>
                        @endforeach
                    </div>
                </div>
                <div class="box">
                    <div class="bar">
                        <span>{{ $endpoint->httpMethods[0] }} {{ $endpoint->uri }}</span>
                        <button type="button" class="copy" data-copy="request">Copiar</button>
                    </div>
                    @foreach($metadata['example_languages'] as $language)
                        <div class="{{ $language }}-example example" data-lang="{{ $language }}">
                            @include("scribe::partials.example-requests.$language")
                        </div>
                    @endforeach
                </div>
            </section>

            @if($endpoint->isGet() || $endpoint->hasResponses())
                <section class="panel" id="example-responses-{!! $endpointId !!}">
                    <div class="panel-head">
                        <b>{{ u::trans("scribe::endpoint.example_response") }}</b>
                    </div>
                    @if(count($endpoint->responses))
                        <div class="tabs res-tabs" role="tablist">
                            @foreach($endpoint->responses as $response)
                                <button type="button" class="tab res-tab {{ $statusClass((int) $response->status) }}" role="tab" data-index="{{ $loop->index }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                    <span class="dot"></span>{{ $response->status }}
                                </button>
                            @endforeach
                        </div>
                        <div class="box">
                            @foreach($endpoint->responses as $response)
                                <div class="res-panel" data-index="{{ $loop->index }}" @if(! $loop->first) hidden @endif>
                                    <div class="bar">
                                        <span class="stp {{ $statusClass((int) $response->status) }}">{{ $response->status }}</span>
                                        <button type="button" class="copy" data-copy="response">Copiar</button>
                                    </div>
                                    @if(count($response->headers))
                                        <details class="res-headers">
                                            <summary>Cabeçalhos</summary>
                                            <pre><code class="language-http">@foreach($response->headers as $header => $value)
{{ $header }}: {{ is_array($value) ? implode('; ', $value) : $value }}
@endforeach</code></pre>
                                        </details>
                                    @endif
                                    @if($response->isBinary())
                                        <pre><code>{!! u::trans("scribe::endpoint.responses.binary") !!} - {{ htmlentities(str_replace("<<binary>>", "", $response->content)) }}</code></pre>
                                    @elseif($response->status == 204)
                                        <pre><code>{!! u::trans("scribe::endpoint.responses.empty") !!}</code></pre>
                                    @else
                                        @php($parsed = json_decode($response->content))
                                        <pre><code class="language-json">{!! htmlentities($parsed != null ? json_encode($parsed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $response->content) !!}</code></pre>
                                    @endif
                                    @if($response->description)
                                        <div class="desc">{{ $response->description }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </section>
            @endif

            <section class="panel" id="execution-results-{{ $endpointId }}" hidden>
                <div class="panel-head">
                    <b>{{ u::trans("scribe::try_it_out.received_response") }}<span id="execution-response-status-{{ $endpointId }}"></span></b>
                </div>
                <div class="box">
                    <pre class="json"><code id="execution-response-content-{{ $endpointId }}"
                        data-empty-response-text="<{{ u::trans("scribe::endpoint.responses.empty") }}>"></code></pre>
                </div>
            </section>

            <section class="panel" id="execution-error-{{ $endpointId }}" hidden>
                <div class="panel-head"><b>{{ u::trans("scribe::try_it_out.request_failed") }}</b></div>
                <div class="box">
                    <pre><code id="execution-error-message-{{ $endpointId }}">{{ "\n\n".u::trans("scribe::try_it_out.error_help") }}</code></pre>
                </div>
            </section>
        </div>
    </aside>
</article>
