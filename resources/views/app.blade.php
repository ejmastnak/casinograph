<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @routes
        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <noscript>
        <div style="padding: 0.75rem;">
            <h2 style="font-size: 1.5em; margin-bottom: 0.66em; font-weight: bold;">This website requires JavaScript.</h2>
            <p style="max-width: 42rem; line-height: 1.5; color: rgb(31 41 55);">
                I'm sorry that I have to require this—I tend to disable JavaScript myself—but I'm using a JavaScript framework (Vue.js) for this site and there's no way around it. I promise I won't do anything yucky; if you feel inspired to check for yourself, you can find the source code
                <a style="color: rgb(7 99 235); text-decoration-line: underline;" href="https://github.com/ejmastnak/casinograph">on GitHub</a>.
            </p>
        </div>
    </noscript>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
