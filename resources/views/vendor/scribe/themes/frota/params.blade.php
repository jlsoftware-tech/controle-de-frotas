@php
    $isInput ??= true;
    $level ??= 0;
@endphp
@foreach($fields as $name => $field)
    @if($name === '[]')
        @php
            $arrayDescription = "O corpo da requisição é um array (`{$field['type']}`)";
            $arrayDescription .= ! empty($field['description']) ? ', representando '.lcfirst($field['description']).'.' : '.';
            if (count($field['__fields'])) {
                $arrayDescription .= ' Cada item possui as seguintes propriedades:';
            }
        @endphp
        <div class="param-note">{!! Parsedown::instance()->text($arrayDescription) !!}</div>
        @include('scribe::themes.frota.params', [
            'fields' => $field['__fields'], 'endpointId' => $endpointId, 'isInput' => $isInput, 'level' => $level + 1,
        ])
    @elseif(! empty($field['__fields']))
        <details class="nest">
            <summary>
                @include('scribe::themes.frota.param', [
                    'name' => $name, 'fullName' => $field['name'], 'type' => $field['type'] ?? 'string',
                    'required' => $field['required'] ?? false, 'deprecated' => $field['deprecated'] ?? false,
                    'description' => $field['description'] ?? '', 'example' => $field['example'] ?? '',
                    'enumValues' => $field['enumValues'] ?? null, 'endpointId' => $endpointId,
                    'hasChildren' => true, 'component' => 'body', 'isInput' => $isInput,
                ])
            </summary>
            <div class="nest-body">
                @include('scribe::themes.frota.params', [
                    'fields' => $field['__fields'], 'endpointId' => $endpointId, 'isInput' => $isInput, 'level' => $level + 1,
                ])
            </div>
        </details>
    @else
        @include('scribe::themes.frota.param', [
            'name' => $name, 'fullName' => $field['name'], 'type' => $field['type'] ?? 'string',
            'required' => $field['required'] ?? false, 'deprecated' => $field['deprecated'] ?? false,
            'description' => $field['description'] ?? '', 'example' => $field['example'] ?? '',
            'enumValues' => $field['enumValues'] ?? null, 'endpointId' => $endpointId,
            'hasChildren' => false, 'component' => 'body', 'isInput' => $isInput,
        ])
    @endif
@endforeach
