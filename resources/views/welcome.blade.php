<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'HealthHub') }} — API Status</title>
    <style>
        :root {
            --brand: #0f766e;
            --brand-dark: #115e59;
            --ink: #0f172a;
            --muted: #64748b;
            --line: #e2e8f0;
            --bg: #f8fafc;
            --card: #ffffff;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Instrument Sans', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
            background: var(--bg);
            color: var(--ink);
            line-height: 1.55;
        }
        .wrap { max-width: 1080px; margin: 0 auto; padding: 32px 20px 64px; }
        header.hero {
            background: linear-gradient(135deg, var(--brand) 0%, #0e7490 100%);
            color: #fff;
            border-radius: 18px;
            padding: 28px 28px 26px;
            box-shadow: 0 18px 40px -24px rgba(15, 118, 110, .8);
        }
        header.hero h1 { margin: 0 0 6px; font-size: 26px; letter-spacing: -.02em; }
        header.hero p { margin: 0; opacity: .92; font-size: 15px; }
        .pills { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
        .pill {
            background: rgba(255, 255, 255, .16);
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: 999px;
            padding: 5px 12px;
            font-size: 13px;
        }
        .pill.ok { background: rgba(255, 255, 255, .95); color: var(--brand-dark); font-weight: 600; border-color: transparent; }
        .pill.bad { background: #fee2e2; color: #991b1b; border-color: transparent; font-weight: 600; }
        h2 { font-size: 17px; margin: 34px 0 12px; letter-spacing: -.01em; }
        .grid { display: grid; gap: 14px; grid-template-columns: repeat(auto-fit, minmax(268px, 1fr)); }
        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 16px 18px;
        }
        .card h3 { margin: 0 0 6px; font-size: 15px; }
        .card p { margin: 0 0 10px; color: var(--muted); font-size: 13.5px; }
        code, .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 12.5px; }
        .endpoint { display: flex; gap: 10px; align-items: baseline; padding: 7px 0; border-bottom: 1px dashed var(--line); font-size: 13.5px; }
        .endpoint:last-child { border-bottom: 0; }
        .method { font-weight: 700; font-size: 11px; letter-spacing: .04em; padding: 2px 7px; border-radius: 6px; background: #e2e8f0; color: #334155; min-width: 46px; text-align: center; }
        .method.get { background: #dcfce7; color: #166534; }
        .method.post { background: #dbeafe; color: #1d4ed8; }
        .method.patch { background: #fef3c7; color: #92400e; }
        table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        th, td { text-align: left; padding: 7px 6px; border-bottom: 1px solid var(--line); }
        th { color: var(--muted); font-weight: 600; }
        .btn {
            display: inline-block; background: var(--brand); color: #fff; border: 0; border-radius: 9px;
            padding: 9px 16px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none;
        }
        .btn:hover { background: var(--brand-dark); }
        textarea {
            width: 100%; min-height: 66px; resize: vertical; border: 1px solid var(--line);
            border-radius: 9px; padding: 10px 12px; font: inherit; font-size: 14px;
        }
        pre.out {
            margin: 12px 0 0; background: #0f172a; color: #e2e8f0; padding: 12px 14px;
            border-radius: 10px; overflow: auto; max-height: 260px; white-space: pre-wrap; font-size: 12.5px;
        }
        .muted { color: var(--muted); font-size: 13px; }
        footer { margin-top: 40px; color: var(--muted); font-size: 13px; text-align: center; }
    </style>
</head>
<body>
<div class="wrap">
    <header class="hero">
        <h1>{{ config('app.name', 'HealthHub') }}</h1>
        <p>Backend REST API Rumah Sakit — Laravel {{ Illuminate\Foundation\Application::VERSION }} · PHP {{ PHP_VERSION }}</p>
        <div class="pills">
            <span class="pill" id="status-pill">memeriksa API…</span>
            <span class="pill">Environment: {{ app()->environment() }}</span>
            <span class="pill">Database: {{ config('database.default') }}</span>
        </div>
    </header>

    <h2>Data master (live dari database)</h2>
    <div class="grid">
        <div class="card">
            <h3>Poliklinik</h3>
            <ul id="polyclinics" class="muted" style="margin:0; padding-left:18px"><li>memuat…</li></ul>
        </div>
        <div class="card">
            <h3>Dokter aktif</h3>
            <ul id="doctors" class="muted" style="margin:0; padding-left:18px"><li>memuat…</li></ul>
        </div>
        <div class="card">
            <h3>Coba AI Triage</h3>
            <p>Tulis keluhan (mis. “nyeri dada dan sesak”) — red-flag emergency akan otomatis terdeteksi.</p>
            <textarea id="triage-input">Demam 3 hari disertai batuk berdahak dan nyeri tenggorokan</textarea>
            <p style="margin:10px 0 0"><button class="btn" id="triage-btn">Analisis gejala</button></p>
            <pre class="out" id="triage-out" hidden></pre>
        </div>
    </div>

    <h2>Endpoint utama</h2>
    <div class="grid">
        <div class="card">
            <h3>Publik</h3>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/health</code></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/doctors</code> <span class="muted">?polyclinicId=</span></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/polyclinics</code></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/specializations</code></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/services</code></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/schedules</code></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/articles</code></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/settings</code></div>
            <div class="endpoint"><span class="method post">POST</span> <code>/api/auth/login</code></div>
            <div class="endpoint"><span class="method post">POST</span> <code>/api/auth/register</code></div>
            <div class="endpoint"><span class="method post">POST</span> <code>/api/ai/triage</code></div>
        </div>
        <div class="card">
            <h3>Butuh token Sanctum</h3>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/auth/me</code></div>
            <div class="endpoint"><span class="method post">POST</span> <code>/api/auth/logout</code></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/appointments</code></div>
            <div class="endpoint"><span class="method post">POST</span> <code>/api/appointments</code></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/queues</code></div>
            <div class="endpoint"><span class="method post">POST</span> <code>/api/queues</code> <span class="muted">call/serve/complete</span></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/medical-records</code></div>
            <div class="endpoint"><span class="method post">POST</span> <code>/api/medical-records</code></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/prescriptions</code></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/lab-orders</code></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/invoices</code></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/beds</code></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/notifications</code></div>
            <div class="endpoint"><span class="method get">GET</span> <code>/api/audit-logs</code></div>
        </div>
    </div>

    <h2>Akun demo (password: <code>password123</code>)</h2>
    <div class="card">
        <table>
            <thead><tr><th>Peran</th><th>Email</th></tr></thead>
            <tbody>
            @foreach ([
                'Super Admin' => 'superadmin@healthhub.id',
                'Admin RS' => 'admin@healthhub.id',
                'Dokter' => 'doctor@healthhub.id',
                'Perawat' => 'nurse@healthhub.id',
                'Farmasi' => 'pharmacy@healthhub.id',
                'Analis Lab' => 'lab@healthhub.id',
                'Kasir' => 'cashier@healthhub.id',
                'Pasien' => 'patient@healthhub.id',
            ] as $role => $email)
                <tr><td>{{ $role }}</td><td><code>{{ $email }}</code></td></tr>
            @endforeach
            </tbody>
        </table>
        <p class="muted" style="margin:12px 0 0">
            Login: <code>POST /api/auth/login {"email":"doctor@healthhub.id","password":"password123"}</code> →
            pakai <code>Authorization: Bearer &lt;token&gt;</code> untuk endpoint terproteksi.
        </p>
    </div>

    <footer>
        HealthHub · Laravel {{ Illuminate\Foundation\Application::VERSION }} · <span id="footer-status">status belum diketahui</span>
    </footer>
</div>

<script>
    const pill = document.getElementById('status-pill');
    const footerStatus = document.getElementById('footer-status');

    fetch('/api/health', { headers: { Accept: 'application/json' } })
        .then((r) => r.json())
        .then((json) => {
            pill.textContent = 'API aktif · ' + json.data.laravel;
            pill.className = 'pill ok';
            footerStatus.textContent = 'API aktif pada ' + json.data.time;
        })
        .catch(() => {
            pill.textContent = 'API tidak merespons';
            pill.className = 'pill bad';
            footerStatus.textContent = 'API tidak merespons';
        });

    const fill = (id, items, render) => {
        const el = document.getElementById(id);
        el.innerHTML = items.length ? items.map(render).join('') : '<li>belum ada data — jalankan <code>php artisan migrate --seed</code></li>';
    };

    fetch('/api/polyclinics', { headers: { Accept: 'application/json' } })
        .then((r) => r.json())
        .then((json) => fill('polyclinics', json.data || [], (p) =>
            `<li><strong>${p.name}</strong> — ${p.location_note || '-'} <span class="mono">(${p.queue_prefix})</span></li>`))
        .catch(() => fill('polyclinics', [], () => ''));

    fetch('/api/doctors', { headers: { Accept: 'application/json' } })
        .then((r) => r.json())
        .then((json) => fill('doctors', json.data || [], (d) =>
            `<li><strong>${d.name}</strong> — ${d.polyclinic?.name || '-'} · rating ${d.rating}</li>`))
        .catch(() => fill('doctors', [], () => ''));

    const triageBtn = document.getElementById('triage-btn');
    const triageOut = document.getElementById('triage-out');
    triageBtn.addEventListener('click', async () => {
        triageOut.hidden = false;
        triageOut.textContent = 'Menganalisis…';
        try {
            const res = await fetch('/api/ai/triage', {
                method: 'POST',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: document.getElementById('triage-input').value }),
            });
            const json = await res.json();
            triageOut.textContent = JSON.stringify(json.data ?? json, null, 2);
        } catch (e) {
            triageOut.textContent = 'Gagal memanggil API: ' + e.message;
        }
    });
</script>
</body>
</html>
