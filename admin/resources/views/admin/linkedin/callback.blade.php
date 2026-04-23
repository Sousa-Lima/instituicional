<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LinkedIn OAuth</title>
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f4f4f5;
            color: #18181b;
        }

        main {
            max-width: 840px;
            margin: 48px auto;
            padding: 0 20px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #e4e4e7;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(24, 24, 27, 0.08);
        }

        h1 {
            margin: 0 0 16px;
            font-size: 28px;
        }

        p, li {
            line-height: 1.6;
        }

        .error {
            color: #b91c1c;
        }

        code, pre {
            font-family: "SFMono-Regular", Consolas, "Liberation Mono", Menlo, monospace;
            font-size: 14px;
        }

        pre {
            overflow-x: auto;
            background: #fafafa;
            border: 1px solid #e4e4e7;
            border-radius: 12px;
            padding: 16px;
            white-space: pre-wrap;
            word-break: break-word;
        }

        ul {
            padding-left: 20px;
        }
    </style>
</head>
<body>
    <main>
        <section class="card">
            @if ($error)
                <h1>Falha no OAuth do LinkedIn</h1>
                <p class="error">{{ $error }}</p>
                <p>Verifique o `client_id`, o `client_secret`, a Redirect URL do app no LinkedIn e o `state` da sessão do navegador.</p>
            @else
                <h1>Access Token obtido</h1>
                <p>O LinkedIn devolveu um token válido para o autor <code>{{ $authorUrn }}</code>.</p>

                <ul>
                    <li>Redirect URI: <code>{{ $redirectUri }}</code></li>
                    <li>Scopes: <code>{{ implode(' ', $scopes) }}</code></li>
                    @if ($expiresAt)
                        <li>Expira em: <code>{{ $expiresAt->toDateTimeString() }}</code></li>
                    @endif
                </ul>

                <p>Cadastre o token no Docker Swarm com:</p>
                <pre>docker secret rm {{ $secretName }} 2&gt;/dev/null || true
printf '%s' '{{ $accessToken }}' | docker secret create {{ $secretName }} -
docker stack deploy -c deploy/slc.yaml slc</pre>

                <p>Token retornado:</p>
                <pre>{{ $accessToken }}</pre>
            @endif
        </section>
    </main>
</body>
</html>