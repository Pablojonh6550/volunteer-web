<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Plataforma que conecta ONGs a voluntários: doações, serviços e voluntariado perto de você.">

        {{-- Aplica o tema salvo (ou o do sistema) antes da renderização, evitando "flash" de tema. --}}
        <script>
            (function () {
                try {
                    var appearance = localStorage.getItem('appearance') || 'system';
                    var dark = appearance === 'dark' || (appearance === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    document.documentElement.classList.toggle('dark', dark);
                } catch (e) {}
            })();
        </script>

        <style>
            html { background-color: oklch(1 0 0); }
            html.dark { background-color: oklch(0.145 0 0); }
        </style>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <title>{{ config('app.name', 'Volunteer Web') }}</title>

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts'])
    </head>
    <body class="font-sans antialiased">
        <noscript>Esta aplicação precisa de JavaScript habilitado.</noscript>
        <div id="app"></div>
    </body>
</html>
