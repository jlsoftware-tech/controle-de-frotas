{{--
    Página informativa: documentação da API ainda não foi gerada.
    Exiba esta view na rota que normalmente serviria a documentação
    (ex: /docs) enquanto o Scribe não tiver gerado os arquivos.
--}}
    <!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentação da API indisponível</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            padding: 24px;
        }

        .card {
            max-width: 560px;
            width: 100%;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        }

        .icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #f59e0b1a;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 24px;
        }

        h1 {
            font-size: 22px;
            margin: 0 0 12px;
            color: #f8fafc;
        }

        p {
            line-height: 1.6;
            color: #94a3b8;
            margin: 0 0 20px;
        }

        .command-box {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 8px;
            padding: 16px 18px;
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
            font-size: 14px;
            color: #34d399;
            overflow-x: auto;
            white-space: nowrap;
            margin-bottom: 20px;
        }

        .command-box::before {
            content: "$ ";
            color: #64748b;
        }

        .footer-note {
            font-size: 13px;
            color: #64748b;
            margin: 0;
        }

        code {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 13px;
            color: #fbbf24;
        }
    </style>
</head>
<body>
<div class="card">
    <div class="icon">⚠️</div>
    <h1>Documentação da API ainda não foi gerada</h1>
    <p>
        Esta página exibiria a documentação da API, mas os arquivos
        ainda não foram gerados pelo Scribe. Execute o comando abaixo
        no terminal, na raiz do projeto, para gerá-los:
    </p>
    <div class="command-box">sail artisan scribe:generate</div>
    <p class="footer-note">
        Após a execução, recarregue esta página para acessar a
        documentação. Se o comando falhar, confira se o serviço
        <code>sail</code> está em execução (<code>./vendor/bin/sail up -d</code>).
    </p>
</div>
</body>
</html>
