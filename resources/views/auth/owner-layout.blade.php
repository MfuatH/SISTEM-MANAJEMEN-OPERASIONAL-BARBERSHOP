<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | Barbershop Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        /* =========================================================
           THEME: Modern Classic Barbershop
           Ganti warna/font cukup dari blok :root ini.
           ========================================================= */
        :root {
            --primary: #1c1c1e;          /* charcoal */
            --primary-hover: #2e2e32;
            --accent: #c9a24b;           /* emas/kuningan */
            --accent-hover: #b38d3a;
            --burgundy: #7a1f2b;         /* referensi barber pole */
            --bg-app: #f5f1ea;           /* krem */
            --card-bg: #ffffff;
            --text-main: #232326;
            --muted: #6b6b70;
            --border-color: #e4dccb;
            --surface-soft: #faf7f1;
            --sidebar-bg: #1c1c1e;
            --sidebar-text: #cfcabf;

            --success: #3f8f5b;
            --warning: #d9a21b;
            --danger: #b5413b;

            --font-heading: 'Playfair Display', Georgia, serif;
            --font-body: Inter, system-ui, -apple-system, sans-serif;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: var(--bg-app);
            color: var(--text-main);
            font: 1rem/1.5 var(--font-body);
        }

        main {
            box-sizing: border-box;
            width: min(28rem, calc(100% - 2rem));
            padding: 2rem;
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
            box-shadow: 0 4px 12px -2px rgb(28 28 30 / 0.08), 0 2px 4px -2px rgb(28 28 30 / 0.06);
        }

        h1, h2 { font-family: var(--font-heading); }
        h1 { margin: 0 0 1.25rem; font-size: 1.75rem; font-weight: 700; color: var(--primary); }

        label, input, select, textarea { display: block; }
        label { font-weight: 600; font-size: 0.875rem; color: #4a4a50; }

        input, select, textarea {
            box-sizing: border-box;
            width: 100%;
            margin: .3rem 0 .25rem;
            padding: .65rem .85rem;
            border: 1px solid var(--border-color);
            border-radius: .5rem;
            font: inherit;
            background: #ffffff;
            transition: all 0.2s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(201, 162, 75, 0.25);
        }

        /* Tombol utama: emas dengan teks gelap */
        button, .button {
            padding: .65rem 1rem;
            border: 0;
            border-radius: .5rem;
            background: var(--accent);
            color: var(--primary);
            font: inherit;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.1s ease;
        }

        button:hover, .button:hover { background: var(--accent-hover); }
        button:active, .button:active { transform: translateY(1px); }
        button:disabled { opacity: .55; cursor: default; }

        a { color: var(--primary); text-decoration: none; }
        a:hover { text-decoration: underline; }

        .wide { width: min(62rem, calc(100% - 2rem)); }
        .secondary { background: #efe9dc; color: var(--primary); }
        .secondary:hover { background: #e4dccb; }

        .table-scroll { overflow-x: auto; }
        .report-page { width: min(76rem, calc(100% - 2rem)); }
        .report-page input[type="search"] { max-width: 28rem; }

        .recap-filters, .recap-totals { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 13rem), 1fr)); gap: 1rem; }
        .recap-filters .field { margin-bottom: .5rem; }
        .recap-totals div { border: 1px solid var(--border-color); border-left: 3px solid var(--accent); border-radius: .75rem; padding: 1rem; background: var(--card-bg); box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05); }
        .recap-totals dd { margin: .2rem 0 0; font-size: 1.25rem; font-weight: 700; font-variant-numeric: tabular-nums; color: var(--primary); }

        /* ---------- Area Owner: sidebar gelap + konten terang ---------- */
        .owner-area { padding: 0; overflow: hidden; }
        .owner-area-layout { display: grid; grid-template-columns: 14rem minmax(0, 1fr); gap: 0; }
        .owner-sidebar {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 1.5rem;
            padding: 1.5rem 1rem;
            background: var(--sidebar-bg);
            border-right: 0;
        }
        .owner-brand {
            margin: 0 0 1.25rem;
            padding: 0 .8rem;
            font-family: var(--font-heading);
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--accent);
            letter-spacing: .02em;
        }
        .owner-sidebar ul, .dashboard-legend { margin: 0 0 1rem; padding: 0; list-style: none; }
        .owner-sidebar li + li { margin-top: .15rem; }
        .owner-sidebar a {
            display: block;
            padding: .6rem .8rem;
            border-left: 3px solid transparent;
            border-radius: .5rem;
            font-weight: 500;
            color: var(--sidebar-text);
        }
        .owner-sidebar a:hover { background: rgb(255 255 255 / 0.08); color: #ffffff; text-decoration: none; }
        .owner-sidebar a[aria-current="page"] {
            background: rgb(201 162 75 / 0.15);
            border-left-color: var(--accent);
            color: var(--accent);
            font-weight: 600;
        }
        .owner-sidebar button {
            width: 100%;
            background: transparent;
            border: 1px solid rgb(255 255 255 / 0.25);
            color: #e8e4da;
        }
        .owner-sidebar button:hover { background: var(--burgundy); border-color: var(--burgundy); color: #ffffff; }

        .owner-area-content { min-width: 0; padding: 2rem; }
        .income-catalog-actions { margin-bottom: 1rem; }

        .catalog-dialog { width: min(58rem, calc(100% - 1.5rem)); max-height: calc(100% - 1.5rem); padding: 0; border: 1px solid var(--border-color); border-radius: .75rem; color: inherit; box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.15); }
        .catalog-dialog::backdrop { background: rgb(28 28 30 / 65%); }
        .catalog-dialog-header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); background: var(--surface-soft); }
        .catalog-dialog-header h2 { margin: 0; font-size: 1.25rem; }
        .catalog-dialog iframe { display: block; width: 100%; height: min(72vh, 42rem); height: min(72dvh, 42rem); border: 0; }

        .owner-catalog { display: block; background: var(--bg-app); }
        .owner-catalog main { margin: 0 auto; width: min(32rem, 100%); border: 0; border-radius: 0; padding: 1.5rem; background: var(--card-bg); }
        .owner-catalog main.wide { width: min(62rem, 100%); }

        /* ---------- Dashboard ---------- */
        .dashboard-totals { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 14rem), 1fr)); gap: 1rem; }
        .dashboard-totals div { border: 1px solid var(--border-color); border-left: 3px solid var(--accent); border-radius: .75rem; padding: 1rem 1.25rem; background: var(--card-bg); box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05); }
        .dashboard-totals dd { margin: .3rem 0 0; font-size: 1.5rem; font-weight: 700; overflow-wrap: anywhere; color: var(--primary); }

        .dashboard-legend { display: flex; flex-wrap: wrap; gap: 1rem; }
        .dashboard-swatch { display: inline-block; width: .75rem; height: .75rem; margin-right: .4rem; border-radius: .25rem; }
        .dashboard-chart-section { --dashboard-income: #c9a24b; --dashboard-expenses: #7a1f2b; } /* Emas = pemasukan, Burgundy = pengeluaran */
        .dashboard-chart-section [hidden] { display: none; }

        .dashboard-controls, .dashboard-series, .dashboard-chart-types label { display: flex; align-items: center; flex-wrap: wrap; gap: .6rem; }
        .dashboard-controls { justify-content: space-between; margin-bottom: 1rem; }
        .dashboard-chart-types { display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; margin: 0; padding: .5rem .85rem; background: var(--surface-soft); border-radius: .5rem; border: 1px solid var(--border-color); }
        .dashboard-chart-types input { width: auto; margin: 0; accent-color: var(--accent); }

        .dashboard-series { align-self: end; }
        .dashboard-series button { padding: .45rem .75rem; border: 1px solid var(--border-color); background: white; color: var(--text-main); }
        .dashboard-series button:hover { background: var(--surface-soft); }
        .dashboard-series button[aria-pressed="true"] { background: var(--surface-soft); border-color: var(--accent); color: var(--primary); font-weight: 600; }
        .dashboard-series button[aria-pressed="false"] .dashboard-swatch { background: #cfc8b8; }

        .dashboard-income { background: var(--dashboard-income); }
        .dashboard-expenses { background: var(--dashboard-expenses); }

        .dashboard-chart-area { display: grid; grid-template-columns: auto minmax(0, 1fr); column-gap: .5rem; margin-bottom: .85rem; }
        .dashboard-axis { display: flex; flex-direction: column; justify-content: space-between; text-align: right; height: 10rem; max-width: 7.5rem; overflow-wrap: anywhere; font-size: .8rem; color: var(--muted); }
        .dashboard-chart-scroll { overflow-x: auto; }
        .dashboard-chart { position: relative; display: flex; gap: .3rem; width: max-content; min-width: 100%; }
        .dashboard-line { position: absolute; inset: 0 0 auto; width: 100%; height: 10rem; pointer-events: none; }
        .dashboard-chart[data-type="line"] .dashboard-bar { visibility: hidden; }
        .dashboard-day { flex: 1 0 2rem; text-align: center; font-size: .8rem; color: var(--muted); }
        .dashboard-bars { height: 10rem; display: flex; justify-content: center; align-items: flex-end; gap: .2rem; border-bottom: 1px solid var(--border-color); background: repeating-linear-gradient(to top, transparent 0 calc(25% - 1px), #efe9dc calc(25% - 1px) 25%); }
        .dashboard-bar { display: block; width: .7rem; border-radius: .2rem .2rem 0 0; }

        @media (max-width: 48rem) {
            .owner-area-layout { grid-template-columns: minmax(0, 1fr); }
            .owner-sidebar { padding: 1rem; gap: 1rem; }
            .owner-brand { margin-bottom: .75rem; }
            .owner-sidebar ul { display: flex; flex-wrap: wrap; gap: .25rem; margin-bottom: 0; }
            .owner-sidebar li + li { margin-top: 0; }
            .owner-sidebar a { border-left: 0; }
            .owner-area-content { padding: 1rem; }
        }

        /* ---------- Tabel ---------- */
        .number { text-align: right; white-space: nowrap; font-variant-numeric: tabular-nums; }
        .report-time { white-space: nowrap; }
        caption { text-align: left; font-weight: 600; margin-bottom: .5rem; color: var(--primary); }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { border-bottom: 1px solid var(--border-color); padding: .75rem; }
        th { font-weight: 600; color: #4a4a50; background: var(--surface-soft); }
        tbody tr:hover { background: var(--surface-soft); }

        /* ---------- Form ---------- */
        .actions { display: flex; flex-wrap: wrap; align-items: center; gap: .65rem; }
        nav.actions { margin-top: 1rem; }
        .field { margin-bottom: 1.25rem; }

        fieldset { border: 1px solid var(--border-color); border-radius: .75rem; margin: 1.25rem 0; padding: 1.25rem; min-width: 0; background: var(--card-bg); }
        legend { font-weight: 600; color: var(--primary); padding: 0 .5rem; }

        .entry-row { display: grid; align-items: start; grid-template-columns: repeat(auto-fit, minmax(9rem, 1fr)); gap: .8rem; border-bottom: 1px solid var(--border-color); padding-bottom: .75rem; margin-bottom: .75rem; }
        .entry-row p { margin: 0; }
        .entry-row [data-remove] { justify-self: start; }
        .entry-columns { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; align-items: start; }
        @media (max-width: 48rem) { .entry-columns { grid-template-columns: minmax(0, 1fr); gap: 0; } }

        .error { color: var(--danger); font-size: 0.85rem; margin-top: .25rem; display: block; }
        :focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

        /* ---------- Halaman login (aktif jika body-class = theme-dark) ---------- */
        /* Latar gelap ini sebaiknya dipakai juga di halaman beranda agar seragam */
        .theme-dark {
            --dark-bg: #100f0d;
            --dark-panel: #171512;
            --dark-field: #1c1815;
            --dark-line: rgb(255 255 255 / 0.09);
            --dark-text: #f3efe6;
            --dark-muted: #a59f93;
            padding: 1rem 0;
            color: var(--dark-text);
            background:
                radial-gradient(48rem 26rem at 12% 0%, rgb(201 162 75 / 0.16), transparent 60%),
                radial-gradient(40rem 28rem at 100% 100%, rgb(122 31 43 / 0.28), transparent 60%),
                var(--dark-bg);
        }

        .theme-dark main.login-shell {
            display: grid;
            grid-template-columns: 1.05fr 1fr;
            width: min(58rem, calc(100% - 2rem));
            padding: 0;
            overflow: hidden;
            background: var(--dark-panel);
            border: 1px solid var(--dark-line);
            border-radius: 1.25rem;
            box-shadow: 0 30px 60px -20px rgb(0 0 0 / 0.7);
        }

        /* Panel kiri: brand */
        .login-brand {
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            padding: 2.5rem 2.75rem 2.5rem 2.5rem;
            overflow: hidden;
            background:
                repeating-linear-gradient(135deg, rgb(255 255 255 / 0.025) 0 1px, transparent 1px 14px),
                linear-gradient(160deg, #241f18 0%, #14120f 100%);
        }
        /* Garis barber pole di tepi panel */
        .login-pole {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: .55rem;
            background: repeating-linear-gradient(45deg, #c9a24b 0 .7rem, #f3efe6 .7rem 1.4rem, #7a1f2b 1.4rem 2.1rem, #f3efe6 2.1rem 2.8rem);
            opacity: .9;
        }
        /* Gunting besar transparan sebagai watermark */
        .login-watermark {
            position: absolute;
            right: -2.5rem;
            bottom: -2.5rem;
            width: 16rem;
            height: 16rem;
            color: var(--accent);
            opacity: .07;
            pointer-events: none;
        }
        .login-logo {
            display: grid;
            place-items: center;
            width: 3.5rem;
            height: 3.5rem;
            border-radius: .85rem;
            background: var(--accent);
            color: #1c1c1e;
        }
        .login-logo svg { width: 1.9rem; height: 1.9rem; }
        .login-shop {
            margin: 0;
            font-family: var(--font-heading);
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: .02em;
            color: var(--dark-text);
        }
        .login-tagline { margin: 0; max-width: 22rem; color: var(--dark-muted); }
        .login-points { margin: auto 0 0; padding: 0; list-style: none; display: grid; gap: .75rem; position: relative; }
        .login-points li { display: flex; align-items: center; gap: .75rem; color: var(--dark-text); font-size: .95rem; }
        .login-points li::before {
            content: "";
            flex: none;
            width: .5rem;
            height: .5rem;
            border-radius: 50%;
            background: var(--accent);
            box-shadow: 0 0 0 4px rgb(201 162 75 / 0.18);
        }

        /* Panel kanan: form */
        .login-form { padding: 2.75rem 2.5rem; align-self: center; }
        .theme-dark h1 { margin-bottom: .35rem; color: var(--dark-text); }
        .login-sub { margin: 0 0 1.75rem; color: var(--dark-muted); }
        .theme-dark label {
            font-size: .75rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--dark-muted);
        }
        .theme-dark input {
            margin-top: .4rem;
            color: var(--dark-text);
            background: var(--dark-field);
            border-color: var(--dark-line);
        }
        .theme-dark input:focus { border-color: var(--accent); background: #211c18; }
        .theme-dark .error { color: #f19a93; }
        .theme-dark form button[type="submit"] {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .5rem;
            width: 100%;
            margin-top: .5rem;
            padding: .85rem 1rem;
        }
        .theme-dark form button[type="submit"] svg { width: 1.1rem; height: 1.1rem; }
        .login-back { margin: 1.5rem 0 0; text-align: center; font-size: .9rem; }
        .theme-dark a { color: var(--accent); }

        /* Tambahan beranda: lanjut sesi, tombol outline, pemisah */
        .theme-dark a.button { color: var(--primary); }
        .theme-dark a.button:hover { text-decoration: none; }
        .btn-block { display: block; width: 100%; box-sizing: border-box; text-align: center; padding: .85rem 1rem; }
        .login-continue {
            display: flex;
            flex-direction: column;
            gap: .1rem;
            margin-bottom: 1.5rem;
            padding: .85rem 1rem;
            border: 1px solid rgb(201 162 75 / 0.4);
            border-left: 3px solid var(--accent);
            border-radius: .6rem;
            background: rgb(201 162 75 / 0.08);
            color: var(--dark-muted);
            font-size: .85rem;
        }
        .login-continue strong { color: var(--dark-text); font-size: 1.05rem; font-weight: 600; }
        .login-continue:hover { text-decoration: none; background: rgb(201 162 75 / 0.14); }
        .login-divider { display: flex; align-items: center; gap: .75rem; margin: 1.5rem 0 1.25rem; color: var(--dark-muted); font-size: .8rem; text-transform: uppercase; letter-spacing: .12em; }
        .login-divider::before, .login-divider::after { content: ""; flex: 1; height: 1px; background: var(--dark-line); }
        .btn-outline {
            display: block;
            box-sizing: border-box;
            width: 100%;
            padding: .8rem 1rem;
            border: 1px solid rgb(255 255 255 / 0.25);
            border-radius: .5rem;
            color: var(--dark-text) !important;
            font-weight: 600;
            text-align: center;
            transition: background .2s ease, border-color .2s ease;
        }
        .btn-outline:hover { text-decoration: none; border-color: var(--accent); background: rgb(201 162 75 / 0.1); }

        @media (max-width: 48rem) {
            .theme-dark main.login-shell { grid-template-columns: minmax(0, 1fr); }
            .login-brand { padding: 1.5rem; gap: .75rem; }
            .login-brand .login-points, .login-brand .login-tagline, .login-watermark { display: none; }
            .login-pole { top: auto; right: 0; bottom: 0; left: 0; width: auto; height: .4rem; }
            .login-form { padding: 1.75rem 1.5rem 2rem; }
        }

        /* =========================================================
           HALAMAN KAPSTER (POS)
           Pakai: @section('body-class', 'theme-dark pos')
                  @section('main-class', 'pos-page')
           ========================================================= */
        .theme-dark { color-scheme: dark; }
        .theme-dark select { color: var(--dark-text); background: var(--dark-field); border-color: var(--dark-line); }
        .theme-dark select:focus { border-color: var(--accent); background: #211c18; }
        .theme-dark .secondary { background: rgb(255 255 255 / 0.07); color: var(--dark-text); border: 1px solid var(--dark-line); }
        .theme-dark .secondary:hover { background: rgb(255 255 255 / 0.13); }
        .theme-dark small { color: var(--dark-muted); }

        /* Tabel mode gelap */
        .theme-dark table { color: var(--dark-text); }
        .theme-dark th, .theme-dark td { border-bottom-color: var(--dark-line); }
        .theme-dark th { background: rgb(255 255 255 / 0.04); color: var(--dark-muted); font-size: .72rem; letter-spacing: .1em; text-transform: uppercase; }
        .theme-dark tbody tr:hover { background: rgb(255 255 255 / 0.03); }
        .theme-dark caption { padding: 1.1rem 1rem .8rem; color: var(--dark-text); font-family: var(--font-heading); font-size: 1.2rem; }
        .theme-dark td.amount { color: var(--accent); font-weight: 600; }

        /* Kerangka halaman */
        .pos { place-items: start center; padding: 1.5rem 0 3rem; }
        .theme-dark main.pos-page { display: grid; gap: 1.25rem; width: min(64rem, calc(100% - 2rem)); padding: 0; background: transparent; border: 0; border-radius: 0; box-shadow: none; }
        .pos-card { background: var(--dark-panel); border: 1px solid var(--dark-line); border-radius: 1rem; overflow: hidden; }
        .pos-pad { padding: 1.25rem; }

        /* Topbar */
        .pos-topbar { position: relative; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem 1.2rem; overflow: hidden; background: var(--dark-panel); border: 1px solid var(--dark-line); border-radius: 1rem; }
        .pos-topbar::after { content: ""; position: absolute; left: 0; right: 0; bottom: 0; height: .25rem; background: repeating-linear-gradient(45deg, #c9a24b 0 .7rem, #f3efe6 .7rem 1.4rem, #7a1f2b 1.4rem 2.1rem, #f3efe6 2.1rem 2.8rem); opacity: .9; }
        .pos-brand { display: flex; align-items: center; gap: .75rem; }
        .pos-logo { display: grid; place-items: center; width: 2.6rem; height: 2.6rem; border-radius: .7rem; background: var(--accent); color: #1c1c1e; }
        .pos-logo svg { width: 1.4rem; height: 1.4rem; }
        .pos-brand-name { font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; letter-spacing: .02em; text-transform: uppercase; }
        .pos-user { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; }
        .pos-user form { margin: 0; }
        .pos-chip { display: inline-flex; align-items: center; gap: .6rem; padding: .3rem 1rem .3rem .3rem; border-radius: 999px; background: rgb(255 255 255 / 0.07); font-weight: 600; }
        .pos-avatar { display: grid; place-items: center; width: 2rem; height: 2rem; border-radius: 50%; background: var(--accent); color: #1c1c1e; font-weight: 700; }
        .pos-page .pos-user button[type="submit"] { width: auto; margin: 0; padding: .55rem 1rem; }

        /* Judul */
        .pos-heading h1 { margin: 0 0 .25rem; }
        .pos-heading p { margin: 0; color: var(--dark-muted); }
        .pos-heading strong { color: var(--dark-text); }
        .theme-dark .pos-back { display: inline-flex; align-items: center; gap: .4rem; margin-bottom: .6rem; color: var(--dark-muted); font-size: .9rem; }
        .pos-back svg { width: 1rem; height: 1rem; }

        /* Notifikasi */
        .pos-alert { margin: 0; padding: .8rem 1rem; border: 1px solid rgb(63 143 91 / 0.5); border-radius: .7rem; background: rgb(63 143 91 / 0.12); color: #a9e0bd; }
        .theme-dark .pos-alert-error { list-style: none; border-color: rgb(181 65 59 / 0.55); background: rgb(181 65 59 / 0.14); color: #f4b4ae; }

        /* Tombol aksi besar di dashboard */
        .pos-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(16rem, 1fr)); gap: 1rem; }
        .theme-dark a.pos-action { display: flex; align-items: center; gap: 1rem; min-height: 5.5rem; padding: 1.2rem 1.25rem; background: var(--dark-panel); border: 1px solid var(--dark-line); border-radius: 1rem; color: var(--dark-text); transition: border-color .2s ease, background .2s ease, transform .15s ease; }
        .theme-dark a.pos-action:hover { text-decoration: none; border-color: var(--accent); background: #1d1a15; transform: translateY(-2px); }
        .pos-action-icon { flex: none; display: grid; place-items: center; width: 3.25rem; height: 3.25rem; border-radius: .85rem; background: rgb(201 162 75 / 0.15); color: var(--accent); }
        .pos-action-icon svg { width: 1.7rem; height: 1.7rem; }
        .pos-action-text { flex: 1; display: flex; flex-direction: column; }
        .pos-action-text strong { font-family: var(--font-heading); font-size: 1.25rem; }
        .pos-action-text small { font-size: .85rem; }
        .pos-action-arrow { flex: none; width: 1.4rem; height: 1.4rem; color: var(--accent); opacity: .75; }

        .pos-page .pos-pager { justify-content: flex-end; margin: 0; }
        .theme-dark .pos-pager a { padding: .55rem 1rem; border: 1px solid var(--dark-line); border-radius: .5rem; color: var(--dark-text); }
        .theme-dark .pos-pager a:hover { text-decoration: none; border-color: var(--accent); }

        /* Form pencatatan */
        .pos-form { display: grid; gap: 1.25rem; }
        .pos-form .field { margin: 0; }
        .pos-form input, .pos-form select { min-height: 3rem; font-size: 1rem; font-weight: 500; letter-spacing: 0; text-transform: none; }
        .pos-form .entry-row > label:first-child { grid-column: 1 / -1; }
        .pos-form .entry-row { grid-template-columns: repeat(auto-fit, minmax(8rem, 1fr)); align-items: end; }
        .pos-form .entry-row [data-remove] { align-self: end; justify-self: stretch; white-space: nowrap; }
        .theme-dark .pos-form fieldset { margin: 0; padding: 1.25rem; background: var(--dark-panel); border-color: var(--dark-line); border-radius: 1rem; }
        .theme-dark .pos-form legend { padding: 0 .5rem; color: var(--accent); font-family: var(--font-heading); font-size: 1.1rem; }
        .pos-form .entry-columns { margin: 0; }
        .theme-dark .pos-form .entry-row { margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px dashed var(--dark-line); }
        .pos-form .entry-row p { color: var(--dark-muted); font-size: .72rem; letter-spacing: .1em; text-transform: uppercase; }
        .pos-form .entry-row output { display: block; margin-top: .2rem; color: var(--accent); font-size: 1.3rem; font-weight: 700; letter-spacing: 0; text-transform: none; font-variant-numeric: tabular-nums; }
        .theme-dark .pos-form fieldset > button.secondary { width: 100%; min-height: 3rem; border-style: dashed; border-color: rgb(201 162 75 / 0.5); color: var(--accent); }
        .theme-dark .pos-form [data-remove] { min-height: 2.75rem; color: #f19a93; }
        .pos-form [data-remove]:disabled { opacity: .35; cursor: default; }

        /* Bar total + simpan (menempel di bawah layar) */
        .pos-summary { position: sticky; bottom: 1rem; z-index: 5; display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 1.25rem; background: rgb(23 21 18 / 0.94); border: 1px solid rgb(201 162 75 / 0.45); border-radius: 1rem; box-shadow: 0 12px 30px -10px rgb(0 0 0 / 0.8); backdrop-filter: blur(8px); }
        .pos-total { margin: 0; }
        .pos-total span { display: block; color: var(--dark-muted); font-size: .72rem; letter-spacing: .12em; text-transform: uppercase; }
        .pos-total output { display: block; color: var(--accent); font-size: 1.9rem; font-weight: 700; line-height: 1.2; font-variant-numeric: tabular-nums; }
        .pos-page .pos-summary button[type="submit"] { width: auto; min-width: 11rem; min-height: 3.2rem; margin: 0; padding: .8rem 1.5rem; font-size: 1.05rem; }

        @media (max-width: 40rem) {
            .pos { padding-top: 1rem; }
            .pos-brand-name { font-size: .9rem; }
            .pos-summary { padding: .85rem 1rem; }
            .pos-total output { font-size: 1.5rem; }
            .pos-page .pos-summary button[type="submit"] { min-width: 8rem; }
        }

        /* =========================================================
           AREA OWNER: sidebar, header halaman, kartu ringkasan, grafik
           ========================================================= */
        .owner-brand { display: flex; align-items: center; gap: .65rem; margin: 0 0 1.5rem; padding: 0 .5rem; font-size: 1.05rem; line-height: 1.2; }
        .owner-brand-logo { flex: none; display: grid; place-items: center; width: 2.1rem; height: 2.1rem; border-radius: .55rem; background: var(--accent); color: #1c1c1e; }
        .owner-brand-logo svg { width: 1.15rem; height: 1.15rem; }
        .owner-sidebar a { display: flex; align-items: center; gap: .65rem; }
        .owner-sidebar a svg { flex: none; width: 1.15rem; height: 1.15rem; opacity: .85; }

        .owner-page-header { margin-bottom: 1.75rem; padding-bottom: 1.25rem; border-bottom: 1px solid var(--border-color); }
        .owner-page-header h1 { margin: 0 0 .25rem; font-size: 2rem; }
        .owner-page-header p { margin: 0; color: var(--muted); }
        .owner-area-content h2 { margin: 0 0 1rem; font-size: 1.35rem; color: var(--primary); }

        .dashboard-totals { margin: 0 0 2rem; }
        .dashboard-totals dt { color: var(--muted); font-size: .75rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
        .dashboard-totals dd { font-variant-numeric: tabular-nums; }
        .dashboard-totals .stat-income { background: linear-gradient(135deg, #fbf4e3 0%, #ffffff 70%); }
        .dashboard-totals .stat-income dd { color: #8a6a1f; }
        .dashboard-totals .stat-expenses { border-left-color: var(--burgundy); background: linear-gradient(135deg, #f7e9eb 0%, #ffffff 70%); }
        .dashboard-totals .stat-expenses dd { color: var(--burgundy); }
        .dashboard-totals .stat-net { background: var(--sidebar-bg); border-color: var(--sidebar-bg); border-left-color: var(--accent); }
        .dashboard-totals .stat-net dt { color: var(--sidebar-text); }
        .dashboard-totals .stat-net dd { color: var(--accent); }

        .dashboard-chart-section { padding: 1.5rem; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 1rem; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05); }
        .dashboard-chart-section > h2 { margin-bottom: .2rem; }
        .dashboard-chart-section > p { margin: 0 0 1.25rem; color: var(--muted); }
        .dashboard-chart-section details { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color); }
        .dashboard-chart-section summary { cursor: pointer; color: var(--primary); font-weight: 600; }
        .dashboard-chart-section summary:hover { color: var(--accent-hover); }
        .dashboard-chart-section details .table-scroll { margin-top: .75rem; }

        /* Latar gelap SAJA (tanpa gaya komponen gelap). Dipakai halaman area owner yang kontennya terang. */
        .bg-dark {
            padding: 1.5rem 0;
            background:
                radial-gradient(48rem 26rem at 12% 0%, rgb(201 162 75 / 0.16), transparent 60%),
                radial-gradient(40rem 28rem at 100% 100%, rgb(122 31 43 / 0.28), transparent 60%),
                #100f0d;
        }

        /* ---------- Katalog (tombol + dialog) di halaman Pemasukan ---------- */
        .income-catalog-actions { margin-bottom: 1.5rem; }
        .owner-area-content .income-catalog-actions button { display: inline-flex; align-items: center; gap: .5rem; padding: .65rem 1.1rem; background: var(--surface-soft); border: 1px solid var(--border-color); color: var(--primary); }
        .owner-area-content .income-catalog-actions button:hover { background: #ffffff; border-color: var(--accent); }
        .income-catalog-actions svg { flex: none; width: 1.1rem; height: 1.1rem; color: var(--accent-hover); }

        .catalog-dialog { overflow: hidden; border: 0; border-radius: 1rem; box-shadow: 0 30px 60px -20px rgb(0 0 0 / 0.6); }
        .catalog-dialog::backdrop { background: rgb(16 15 13 / 0.72); backdrop-filter: blur(3px); }
        .catalog-dialog-header { background: var(--sidebar-bg); border-bottom: 3px solid var(--accent); }
        .owner-area-content .catalog-dialog-header h2 { margin: 0; color: var(--accent); font-size: 1.25rem; }
        .catalog-dialog-header .secondary { background: rgb(255 255 255 / 0.1); border: 1px solid rgb(255 255 255 / 0.2); color: #f3efe6; }
        .catalog-dialog-header .secondary:hover { background: var(--burgundy); border-color: var(--burgundy); }
        .catalog-dialog-header form { margin: 0; }

        /* =========================================================
           HALAMAN DAFTAR OWNER (Pemasukan, Pengeluaran, Rekap, Katalog)
           ========================================================= */
        .owner-toolbar { display: flex; flex-wrap: wrap; align-items: flex-end; gap: 1rem; margin: 0 0 1.25rem; padding: 1.1rem 1.25rem; background: var(--surface-soft); border: 1px solid var(--border-color); border-radius: .85rem; }
        .owner-toolbar .field { flex: 1 1 18rem; margin: 0; }
        .owner-toolbar input, .owner-toolbar select { min-height: 2.75rem; margin: .3rem 0 0; }
        .owner-toolbar .actions button, .owner-toolbar .actions .button { display: inline-flex; align-items: center; gap: .45rem; min-height: 2.75rem; box-sizing: border-box; }
        .owner-toolbar svg, .owner-catalog .button svg { flex: none; width: 1.05rem; height: 1.05rem; }

        .owner-total { margin: 0 0 1.25rem; padding: 1rem 1.25rem; background: var(--sidebar-bg); border-radius: .85rem; border-left: 3px solid var(--accent); }
        .owner-total strong { display: flex; flex-wrap: wrap; align-items: baseline; justify-content: space-between; gap: .5rem; color: var(--sidebar-text); font-size: .78rem; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; }
        .owner-total-value { color: var(--accent); font-size: 1.6rem; font-weight: 700; letter-spacing: 0; text-transform: none; font-variant-numeric: tabular-nums; }

        .owner-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 1rem; box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.05); overflow-x: auto; }
        .owner-card table { font-size: .875rem; }
        .owner-card th, .owner-card td { padding: .7rem .5rem; }
        .owner-card th:first-child, .owner-card td:first-child { padding-left: 1rem; }
        .owner-card th:last-child, .owner-card td:last-child { padding-right: 1rem; }
        .owner-card th { white-space: nowrap; font-size: .68rem; letter-spacing: .06em; text-transform: uppercase; }
        .owner-card tbody tr:last-child td { border-bottom: 0; }
        .owner-area td.amount, .owner-catalog td.amount { color: var(--primary); font-weight: 700; }

        .badge { display: inline-block; padding: .15rem .65rem; border-radius: 999px; background: #efe9dc; color: var(--primary); font-size: .78rem; font-weight: 600; white-space: nowrap; }
        .badge-service { background: #fbf0d3; color: #7a5c12; }
        .badge-product { background: #f3e1e4; color: var(--burgundy); }
        .badge-active { background: #dff0e5; color: #276a40; }
        .badge-archived { background: #e9e6df; color: var(--muted); }

        .owner-pager { justify-content: center; margin-top: 1.25rem; }
        .owner-pager a { padding: .5rem 1rem; background: #ffffff; border: 1px solid var(--border-color); border-radius: .5rem; }
        .owner-pager a:hover { border-color: var(--accent); text-decoration: none; }
        .owner-pager span { color: var(--muted); font-size: .9rem; }

        .owner-alert { margin: 0 0 1rem; padding: .75rem 1rem; background: #eaf6ee; border: 1px solid #b9dcc6; border-radius: .6rem; color: #276a40; }
        .owner-alert.owner-alert-error { background: #faeceb; border-color: #e6bcb8; color: #8f2f29; }

        .back-link { display: inline-flex; align-items: center; gap: .35rem; color: var(--muted); font-size: .9rem; }
        .back-link svg { width: 1rem; height: 1rem; }
        .owner-catalog h1 { margin-bottom: 1rem; }
        .owner-catalog .button { display: inline-flex; align-items: center; gap: .45rem; }
        .catalog-form { max-width: 26rem; }
        .catalog-form button[type="submit"] { min-height: 2.75rem; padding: .65rem 1.5rem; }

        /* ---------- Pengeluaran + Rekap + form owner ---------- */
        .owner-toolbar-stack { display: block; }
        .owner-toolbar-stack .field { margin: 0; }
        .owner-toolbar-stack .actions { margin-top: 1.1rem; }
        .owner-toolbar-stack .recap-filters { gap: 1rem; }
        .owner-area .owner-add { display: inline-flex; align-items: center; gap: .45rem; min-height: 2.75rem; box-sizing: border-box; }
        .owner-add svg { flex: none; width: 1.05rem; height: 1.05rem; }
        .owner-total--expenses { border-left-color: var(--burgundy); }

        .owner-form { max-width: 32rem; padding: 1.5rem; background: var(--surface-soft); border: 1px solid var(--border-color); border-radius: 1rem; }
        .owner-form input, .owner-form select { min-height: 3rem; }
        .owner-form textarea { resize: vertical; }
        .owner-form button[type="submit"] { min-height: 2.9rem; padding: .65rem 1.75rem; }

        .owner-card caption { padding: 1rem 1rem .75rem; color: var(--primary); font-family: var(--font-heading); font-size: 1.1rem; }
        .owner-card tfoot th, .owner-card tfoot td { background: var(--surface-soft); border-bottom: 0; }
        .owner-card tfoot th { color: var(--primary); font-size: .875rem; letter-spacing: 0; text-align: right; text-transform: none; }
        .owner-card tfoot tr:first-child > * { border-top: 2px solid var(--border-color); }
        .badge-income { background: #fbf0d3; color: #7a5c12; }
        .badge-expense, .badge-expenses { background: #f3e1e4; color: var(--burgundy); }

        /* ---------- Tombol tindakan katalog (Ubah / Arsipkan / Hapus) ---------- */
        .catalog-actions { flex-wrap: nowrap; gap: .4rem; }
        .catalog-actions form { margin: 0; }
        .catalog-actions .action-link, .catalog-actions button { display: inline-flex; align-items: center; box-sizing: border-box; min-height: 2.1rem; padding: .35rem .8rem; border-radius: .45rem; font-size: .85rem; font-weight: 600; white-space: nowrap; }
        .catalog-actions .action-link { background: #fffaf0; border: 1px solid var(--accent); color: #7a5c12; }
        .catalog-actions .action-link:hover { background: #fbf0d3; text-decoration: none; }
        .catalog-actions .secondary { background: #ffffff; border: 1px solid var(--border-color); color: var(--primary); }
        .catalog-actions .secondary:hover { background: var(--surface-soft); border-color: #cfc8b8; }
        .catalog-actions .danger { color: var(--danger); border-color: #e6bcb8; }
        .catalog-actions .danger:hover { background: #faeceb; border-color: var(--danger); }
        .catalog-actions button:disabled, .catalog-actions button:disabled:hover { background: #f4f1ea; border-color: var(--border-color); color: var(--muted); cursor: not-allowed; opacity: .6; }

        /* ---------- Katalog produk ---------- */
        .owner-catalog td.muted { color: var(--muted); font-size: .85rem; font-style: italic; font-weight: 400; }
        .catalog-form small { display: block; margin-top: .35rem; color: var(--muted); font-size: .82rem; }
        .catalog-form .error { margin: .35rem 0 0; }
    </style>
    @stack('styles')
</head>
<body class="@yield('body-class')">
    <main class="@yield('main-class')">@yield('content')</main>
    @stack('scripts')
</body>
</html>