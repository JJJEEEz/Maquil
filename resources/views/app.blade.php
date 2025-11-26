<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Favicon: script will pick light or dark version based on user preference -->
        <script>
            (function(){
                try {
                    var stored = localStorage.getItem('darkMode');
                    var isDark = null;
                    if (stored !== null) {
                        isDark = stored === 'true';
                    } else if (window.matchMedia) {
                        isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    }
                    var href = isDark ? '{{ asset('favicon-dark.svg') }}' : '{{ asset('favicon.svg') }}';
                    var link = document.createElement('link');
                    link.id = 'favicon';
                    link.rel = 'icon';
                    link.type = 'image/svg+xml';
                    link.href = href;
                    document.head.appendChild(link);

                    window.addEventListener('storage', function(e){
                        if (e.key === 'darkMode') {
                            var el = document.getElementById('favicon');
                            if (el) el.href = (e.newValue === 'true') ? '{{ asset('favicon-dark.svg') }}' : '{{ asset('favicon.svg') }}';
                        }
                    });
                } catch (e) { console.error(e); }
            })();
        </script>

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
