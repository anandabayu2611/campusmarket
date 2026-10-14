<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'CampusMarket')</title>
    <style>
        :root{--bg:#f5f7fb;--card:#fff;--text:#1d2433;--muted:#667085;--primary:#2f6fed;--primary-dark:#2458c4;--danger:#d92d20;--ok:#067647;--warn:#b54708;--border:#e4e7ec}
        *{box-sizing:border-box}
        body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;background:var(--bg);color:var(--text);line-height:1.5}
        .nav{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;background:var(--card);border-bottom:1px solid var(--border)}
        .brand{font-weight:700;color:var(--primary);text-decoration:none;font-size:1.1rem}
        .nav nav{display:flex;gap:14px;align-items:center}
        .nav a{color:var(--text);text-decoration:none;font-size:.95rem}
        .container{max-width:520px;margin:20px auto;padding:0 16px}
        .card{background:var(--card);border:1px solid var(--border);border-radius:14px;padding:20px;margin-bottom:16px}
        h1{font-size:1.4rem;margin:0 0 4px} h2{font-size:1.1rem;margin:0 0 12px}
        .muted{color:var(--muted);font-size:.9rem}
        label{display:block;font-weight:600;font-size:.9rem;margin:14px 0 4px}
        input,select,textarea{width:100%;padding:11px 12px;border:1px solid var(--border);border-radius:10px;font:inherit;background:#fff}
        input:focus,select:focus,textarea:focus{outline:2px solid #bcd0fb;border-color:var(--primary)}
        input[disabled]{background:#f2f4f7;color:var(--muted)}
        input[type=checkbox]{width:auto;margin-right:8px}
        .check{display:flex;align-items:center;font-weight:400;margin:14px 0}
        .btn{display:inline-block;width:100%;margin-top:18px;padding:12px;border:0;border-radius:10px;background:var(--primary);color:#fff;font:inherit;font-weight:600;text-align:center;text-decoration:none;cursor:pointer}
        .btn:hover{background:var(--primary-dark)} .btn.secondary{background:#eef2ff;color:var(--primary)}
        .link{background:none;border:0;color:var(--primary);cursor:pointer;font:inherit;padding:0}
        .alert{padding:12px 14px;border-radius:10px;margin-bottom:14px;font-size:.92rem}
        .alert.ok{background:#ecfdf3;color:var(--ok)} .alert.warn{background:#fffaeb;color:var(--warn)}
        .error{color:var(--danger);font-size:.85rem;margin-top:4px}
        .hint{color:var(--muted);font-size:.82rem;margin-top:4px}
        .avatar{width:84px;height:84px;border-radius:50%;object-fit:cover;background:#dbe5ff;color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:1.8rem;font-weight:700}
        .head{display:flex;gap:16px;align-items:center}
        .badge{display:inline-block;padding:2px 10px;border-radius:99px;font-size:.78rem;font-weight:600}
        .badge.ok{background:#ecfdf3;color:var(--ok)} .badge.warn{background:#fffaeb;color:var(--warn)} .badge.bad{background:#fef3f2;color:var(--danger)}
        dl{margin:12px 0 0;display:grid;grid-template-columns:110px 1fr;gap:8px 12px} dt{color:var(--muted);font-size:.9rem} dd{margin:0}
        .ratings{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:14px}
        .rating{background:var(--bg);border-radius:10px;padding:10px 12px;text-align:center} .rating b{font-size:1.15rem;display:block}
        .center{text-align:center} .row{display:flex;gap:10px} .row>*{flex:1}
        /* Katalog produk */
        .container.wide{max-width:1040px}
        .page-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}
        .btn.sm{width:auto;margin:0;padding:9px 16px}
        .searchbar{display:flex;gap:8px;margin-top:14px}
        .searchbar input{flex:1;min-width:0}
        .searchbar .btn{margin:0;width:auto;padding:0 20px}
        .chips{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}
        .chip{display:inline-block;padding:7px 14px;border-radius:99px;border:1px solid var(--border);background:#fff;color:var(--text);font-size:.88rem;text-decoration:none;white-space:nowrap}
        .chip:hover{border-color:var(--primary);color:var(--primary)}
        .chip.active{background:var(--primary);border-color:var(--primary);color:#fff}
        .chips-label{font-size:.8rem;color:var(--muted);margin:16px 0 0;text-transform:uppercase;letter-spacing:.04em}
        details.filters{margin-top:14px;border:1px solid var(--border);border-radius:10px;padding:0 14px}
        details.filters summary{cursor:pointer;padding:12px 0;font-weight:600;font-size:.92rem}
        .filter-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:0 14px;padding-bottom:14px}
        .filter-actions{display:flex;gap:10px;padding-bottom:14px}
        .filter-actions .btn{margin:0;flex:1}
        .product-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:16px}
        .product-card{display:flex;flex-direction:column;background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden;text-decoration:none;color:inherit;transition:box-shadow .15s}
        .product-card:hover{box-shadow:0 4px 14px rgba(16,24,40,.12)}
        .product-card img,.product-card .noimg{width:100%;aspect-ratio:4/3;object-fit:cover;background:#eef2ff;display:block}
        .product-body{padding:12px 14px 14px;display:flex;flex-direction:column;gap:6px;flex:1}
        .product-body h3{margin:0;font-size:1rem;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .price{font-weight:700;color:var(--primary);font-size:1.05rem}
        .tags{display:flex;flex-wrap:wrap;gap:6px}
        .tag{padding:2px 9px;border-radius:99px;background:var(--bg);color:var(--muted);font-size:.76rem}
        .tag.cat{background:#eef2ff;color:var(--primary)}
        .product-body .loc{font-size:.82rem;color:var(--muted);margin-top:auto}
        .pager{display:flex;justify-content:center;align-items:center;gap:14px;margin:8px 0 24px}
        .pager .btn{margin:0;width:auto;padding:9px 16px}
    </style>
</head>
<body>
<header class="nav">
    <a class="brand" href="{{ url('/') }}">🎓 CampusMarket</a>
    @auth
    <nav>
        <a href="{{ route('products.index') }}">Katalog</a>
        <a href="{{ route('products.create') }}">Jual Produk</a>
        <a href="{{ route('profile.show') }}">Profil</a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="link" type="submit">Keluar</button>
        </form>
    </nav>
@endauth
</header>
<main class="container @yield('container_class')">
    @if (session('status'))<div class="alert ok">{{ session('status') }}</div>@endif
    @if (session('warning'))<div class="alert warn">{{ session('warning') }}</div>@endif
    @yield('content')
</main>
</body>
</html>
