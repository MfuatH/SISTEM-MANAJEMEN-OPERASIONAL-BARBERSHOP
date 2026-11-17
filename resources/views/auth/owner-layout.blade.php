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
        button:disabled { opacity: .55; cursor: default; }
        a { color: #164b39; }
        .wide { width: min(62rem, calc(100% - 2rem)); }
        .button { display: inline-block; padding: .55rem .9rem; border-radius: .3rem; background: #164b39; color: white; text-decoration: none; }
        .secondary { background: #edf2ed; color: #164b39; }
        .table-scroll { overflow-x: auto; }
        .income-report { width: min(76rem, calc(100% - 2rem)); }
        .income-report input[type="search"] { max-width: 28rem; }
        .number { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .report-time { white-space: nowrap; }
        caption { text-align: left; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { border-bottom: 1px solid #d8dfd9; padding: .65rem .7rem; }
        .actions { display: flex; flex-wrap: wrap; align-items: center; gap: .65rem; }
        nav.actions { margin-top: 1rem; }
        .field { margin-bottom: 1rem; }
        fieldset { border: 1px solid #d8dfd9; border-radius: .5rem; margin: 1rem 0; padding: 1rem; min-width: 0; }
        legend { font-weight: 600; }
        .entry-row { display: grid; align-items: start; grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr)); gap: .8rem; border-bottom: 1px solid #d8dfd9; padding-bottom: .75rem; margin-bottom: .75rem; }
        .entry-row p { margin: 0; }
        .entry-row [data-remove] { justify-self: start; }
        .entry-columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; align-items: start; }
        @media (max-width: 48rem) { .entry-columns { grid-template-columns: minmax(0, 1fr); gap: 0; } }
        .error { color: #972e29; }
        :focus-visible { outline: 2px solid #164b39; outline-offset: 3px; }
    </style>
</head>
<body>
    <main class="@yield('main-class')">@yield('content')</main>
</body>
</html>
