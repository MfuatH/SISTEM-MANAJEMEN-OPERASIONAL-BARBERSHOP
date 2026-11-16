<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Barbershop</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f5f6f2; color: #1b2522; font: 1rem/1.5 Inter, system-ui, sans-serif; }
        main { box-sizing: border-box; width: min(28rem, calc(100% - 2rem)); padding: 2rem; background: white; border: 1px solid #d8dfd9; border-radius: .8rem; }
        h1 { margin: 0 0 1.25rem; font-size: 1.65rem; }
        label, input, select { display: block; }
        label { font-weight: 600; }
        input, select { box-sizing: border-box; width: 100%; margin: .3rem 0 .25rem; padding: .55rem .7rem; border: 1px solid #89968e; border-radius: .3rem; font: inherit; }
        button { padding: .55rem .9rem; border: 0; border-radius: .3rem; background: #164b39; color: white; font: inherit; cursor: pointer; }
        a { color: #164b39; }
        .wide { width: min(62rem, calc(100% - 2rem)); }
        .button { display: inline-block; padding: .55rem .9rem; border-radius: .3rem; background: #164b39; color: white; text-decoration: none; }
        .secondary { background: #edf2ed; color: #164b39; }
        .table-scroll { overflow-x: auto; }
        caption { text-align: left; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { border-bottom: 1px solid #d8dfd9; padding: .65rem .7rem; }
        .actions { display: flex; flex-wrap: wrap; align-items: center; gap: .65rem; }
        nav.actions { margin-top: 1rem; }
        .field { margin-bottom: 1rem; }
        .error { color: #972e29; }
        :focus-visible { outline: 2px solid #164b39; outline-offset: 3px; }
    </style>
</head>
<body>
    <main class="@yield('main-class')">@yield('content')</main>
</body>
</html>
