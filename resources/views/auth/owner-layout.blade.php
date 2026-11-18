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
        label, input, select, textarea { display: block; }
        label { font-weight: 600; }
        input, select, textarea { box-sizing: border-box; width: 100%; margin: .3rem 0 .25rem; padding: .55rem .7rem; border: 1px solid #89968e; border-radius: .3rem; font: inherit; }
        button { padding: .55rem .9rem; border: 0; border-radius: .3rem; background: #164b39; color: white; font: inherit; cursor: pointer; }
        button:disabled { opacity: .55; cursor: default; }
        a { color: #164b39; }
        .wide { width: min(62rem, calc(100% - 2rem)); }
        .button { display: inline-block; padding: .55rem .9rem; border-radius: .3rem; background: #164b39; color: white; text-decoration: none; }
        .secondary { background: #edf2ed; color: #164b39; }
        .table-scroll { overflow-x: auto; }
        .report-page { width: min(76rem, calc(100% - 2rem)); }
        .report-page input[type="search"] { max-width: 28rem; }
        .recap-filters, .recap-totals { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 13rem), 1fr)); gap: 1rem; }
        .recap-filters .field { margin-bottom: .5rem; }
        .recap-totals div { border: 1px solid #d8dfd9; border-radius: .5rem; padding: .65rem .85rem; }
        .recap-totals dd { margin: .2rem 0 0; font-size: 1.2rem; font-weight: 600; font-variant-numeric: tabular-nums; }
        .dashboard-layout { display: grid; grid-template-columns: 10.5rem minmax(0, 1fr); gap: 1.75rem; }
        .dashboard-sidebar { border-right: 1px solid #d8dfd9; padding-right: 1rem; }
        .dashboard-sidebar ul, .dashboard-legend { margin: 0 0 1rem; padding: 0; list-style: none; }
        .dashboard-sidebar a { display: block; padding: .45rem .6rem; border-radius: .3rem; }
        .dashboard-sidebar a[aria-current="page"] { background: #edf2ed; font-weight: 600; text-decoration: none; }
        .dashboard-totals { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 14rem), 1fr)); gap: 1rem; }
        .dashboard-totals div { border: 1px solid #d8dfd9; border-radius: .5rem; padding: .75rem 1rem; }
        .dashboard-totals dd { margin: .2rem 0 0; font-size: 1.35rem; font-weight: 600; overflow-wrap: anywhere; }
        .dashboard-legend { display: flex; flex-wrap: wrap; gap: .75rem; }
        .dashboard-swatch { display: inline-block; width: .7rem; height: .7rem; margin-right: .35rem; border-radius: .15rem; }
        .dashboard-chart-section { --dashboard-income: #21664e; --dashboard-expenses: #a74c28; }
        .dashboard-chart-section [hidden] { display: none; }
        .dashboard-controls, .dashboard-series, .dashboard-chart-types label { display: flex; align-items: center; flex-wrap: wrap; gap: .55rem; }
        .dashboard-controls { justify-content: space-between; margin-bottom: 1rem; }
        .dashboard-chart-types { display: flex; flex-wrap: wrap; align-items: center; gap: .8rem; margin: 0; padding: .4rem .75rem .55rem; }
        .dashboard-chart-types input { width: auto; margin: 0; accent-color: #164b39; }
        .dashboard-series { align-self: end; }
        .dashboard-series button { padding: .4rem .65rem; border: 1px solid #89968e; background: white; color: #1b2522; }
        .dashboard-series button[aria-pressed="true"] { background: #edf2ed; border-color: #164b39; }
        .dashboard-series button[aria-pressed="false"] .dashboard-swatch { background: #abb3ad; }
        .dashboard-income { background: var(--dashboard-income); }
        .dashboard-expenses { background: var(--dashboard-expenses); }
        .dashboard-chart-area { display: grid; grid-template-columns: auto minmax(0, 1fr); column-gap: .5rem; margin-bottom: .85rem; }
        .dashboard-axis { display: flex; flex-direction: column; justify-content: space-between; text-align: right; height: 10rem; max-width: 7.5rem; overflow-wrap: anywhere; font-size: .8rem; }
        .dashboard-chart-scroll { overflow-x: auto; }
        .dashboard-chart { position: relative; display: flex; gap: .3rem; width: max-content; min-width: 100%; }
        .dashboard-line { position: absolute; inset: 0 0 auto; width: 100%; height: 10rem; pointer-events: none; }
        .dashboard-chart[data-type="line"] .dashboard-bar { visibility: hidden; }
        .dashboard-day { flex: 1 0 2rem; text-align: center; font-size: .8rem; }
        .dashboard-bars { height: 10rem; display: flex; justify-content: center; align-items: flex-end; gap: .15rem; border-bottom: 1px solid #89968e; background: repeating-linear-gradient(to top, transparent 0 calc(25% - 1px), #edf2ed calc(25% - 1px) 25%); }
        .dashboard-bar { display: block; width: .65rem; border-radius: .15rem .15rem 0 0; }
        @media (max-width: 48rem) {
            .owner-dashboard { padding: 1rem; }
            .dashboard-layout { grid-template-columns: minmax(0, 1fr); gap: 1rem; }
            .dashboard-sidebar { border-right: 0; border-bottom: 1px solid #d8dfd9; padding: 0 0 .75rem; }
            .dashboard-sidebar ul { display: flex; flex-wrap: wrap; }
        }
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
    @stack('scripts')
</body>
</html>
