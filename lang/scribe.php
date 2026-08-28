<?php

return [
    'labels' => [
        'search' => 'Buscar',
        'base_url' => 'URL base',
    ],

    'auth' => [
        'none' => 'Esta API não requer autenticação.',
        'instruction' => [
            'query' => <<<'TEXT'
                Para autenticar requisições, inclua o parâmetro de query **`:parameterName`** na requisição.
                TEXT,
            'body' => <<<'TEXT'
                Para autenticar requisições, inclua o parâmetro **`:parameterName`** no corpo da requisição.
                TEXT,
            'query_or_body' => <<<'TEXT'
                Para autenticar requisições, inclua o parâmetro **`:parameterName`** na query string ou no corpo da requisição.
                TEXT,
            'bearer' => <<<'TEXT'
                Para autenticar requisições, inclua o cabeçalho **`Authorization`** com o valor **`"Bearer :placeholder"`**.
                TEXT,
            'basic' => <<<'TEXT'
                Para autenticar requisições, inclua o cabeçalho **`Authorization`** no formato **`"Basic {credentials}"`**.
                O valor de `{credentials}` deve ser seu usuário/id e sua senha, unidos por dois pontos (:), e então codificados em base64.
                TEXT,
            'header' => <<<'TEXT'
                Para autenticar requisições, inclua o cabeçalho **`:parameterName`** com o valor **`":placeholder"`**.
                TEXT,
        ],
        'details' => <<<'TEXT'
            Todos os endpoints autenticados estão marcados com o selo `requer autenticação` na documentação abaixo.
            TEXT,
    ],

    'headings' => [
        'introduction' => 'Introdução',
        'auth' => 'Autenticação de requisições',
    ],

    'endpoint' => [
        'request' => 'Requisição',
        'headers' => 'Cabeçalhos',
        'url_parameters' => 'Parâmetros de URL',
        'body_parameters' => 'Parâmetros de Corpo',
        'query_parameters' => 'Parâmetros de Query',
        'response' => 'Resposta',
        'response_fields' => 'Campos da Resposta',
        'example_request' => 'Exemplo de requisição',
        'example_response' => 'Exemplo de resposta',
        'responses' => [
            'binary' => 'Dados binários',
            'empty' => 'Resposta vazia',
        ],
    ],

    'try_it_out' => [
        'open' => 'Testar ⚡',
        'cancel' => 'Cancelar 🛑',
        'send' => 'Enviar Requisição 💥',
        'loading' => '⏱ Enviando...',
        'received_response' => 'Resposta recebida',
        'request_failed' => 'Requisição falhou com erro',
        'error_help' => <<<'TEXT'
            Dica: Verifique se você está conectado à rede corretamente.
            Se você for um mantenedor desta API, verifique se a API está em execução e se habilitou o CORS.
            Você pode verificar o console do DevTools para obter informações de depuração.
            TEXT,
    ],

    'links' => [
        'postman' => 'Ver coleção do Postman',
        'openapi' => 'Ver especificação OpenAPI',
    ],
];

