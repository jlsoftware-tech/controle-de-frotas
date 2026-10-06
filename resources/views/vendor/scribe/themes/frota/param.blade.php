@php
    $html ??= [];
    $class = $html['class'] ?? null;
    $type ??= null;
    $required ??= false;
    $deprecated ??= false;
    $description ??= '';
    $example ??= null;
    $enumValues ??= null;
    $hasChildren ??= false;
    $isInput ??= true;
    $fullName ??= $name;

    $descriptionText = trim((string) $description);
    $exampleText = null;
    if ($example !== null && $example !== '' && ! is_array($example)) {
        $exampleText = is_bool($example) ? ($example ? 'true' : 'false') : (string) $example;
    }
@endphp
<div class="param">
    <div class="param-id">
        <code class="pn">{{ $name }}</code>
        @if($type)<span class="pt">{{ $type }}</span>@endif
        @if($isInput)
            @if($required)<span class="req">obrigatório</span>@else<span class="opt">opcional</span>@endif
            @if($deprecated)<span class="dep">obsoleto</span>@endif
        @endif
    </div>
    <div class="param-body">
        @if($descriptionText !== '')
            <div class="param-desc">{!! Parsedown::instance()->text($descriptionText) !!}</div>
        @endif
        @if(! empty($enumValues))
            <div class="param-meta"><span>Valores aceitos:</span> @foreach($enumValues as $enumValue)<code>{{ $enumValue }}</code>@endforeach</div>
        @endif
        @if($exampleText !== null)
            <div class="param-meta"><span>Exemplo:</span> <code>{{ $exampleText }}</code></div>
        @endif

        @if($isInput && ! $hasChildren)
            @php
                $isList = Str::endsWith((string) $type, '[]');
                $inputName = str_replace('[]', '.0', $fullName);
                $baseType = $isList ? substr($type, 0, -2) : $type;
                // Ignore the first '[]': the frontend takes care of it
                while (Str::endsWith((string) $baseType, '[]')) {
                    $inputName .= '.0';
                    $baseType = substr($baseType, 0, -2);
                }
                // When the body is an array, the item names will be ".0.thing"
                $inputName = ltrim($inputName, '.');
                $inputType = match ($baseType) {
                    'number', 'integer' => 'number',
                    'file' => 'file',
                    default => 'text',
                };
            @endphp
            @if($type === 'boolean')
                <label class="try-radio" data-endpoint="{{ $endpointId }}" style="display: none">
                    <input type="radio" name="{{ $inputName }}"
                           value="{{ $component === 'body' ? 'true' : 1 }}"
                           data-endpoint="{{ $endpointId }}"
                           data-component="{{ $component }}" @if($class)class="{{ $class }}"@endif>
                    <code>true</code>
                </label>
                <label class="try-radio" data-endpoint="{{ $endpointId }}" style="display: none">
                    <input type="radio" name="{{ $inputName }}"
                           value="{{ $component === 'body' ? 'false' : 0 }}"
                           data-endpoint="{{ $endpointId }}"
                           data-component="{{ $component }}" @if($class)class="{{ $class }}"@endif>
                    <code>false</code>
                </label>
            @elseif($isList)
                <input type="{{ $inputType }}" class="try-input" style="display: none"
                       @if($inputType === 'number')step="any"@endif
                       name="{{ $inputName.'[0]' }}" @if($class)class="{{ $class }}"@endif
                       data-endpoint="{{ $endpointId }}"
                       data-component="{{ $component }}">
                <input type="{{ $inputType }}" class="try-input" style="display: none"
                       name="{{ $inputName.'[1]' }}" @if($class)class="{{ $class }}"@endif
                       data-endpoint="{{ $endpointId }}"
                       data-component="{{ $component }}">
            @else
                <input type="{{ $inputType }}" class="try-input" style="display: none"
                       @if($inputType === 'number')step="any"@endif
                       name="{{ $inputName }}" @if($class)class="{{ $class }}"@endif
                       data-endpoint="{{ $endpointId }}"
                       value="{!! (isset($example) && (is_string($example) || is_numeric($example))) ? e($example) : '' !!}"
                       data-component="{{ $component }}">
            @endif
        @endif
    </div>
</div>
