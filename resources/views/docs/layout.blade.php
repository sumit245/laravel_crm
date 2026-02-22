<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Code Documentation — Laravel CRM</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.css"
        rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-tertiary: #334155;
            --bg-card: #1e293b;
            --bg-hover: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --accent: #6366f1;
            --accent-hover: #818cf8;
            --accent-subtle: rgba(99, 102, 241, 0.15);
            --border: #334155;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
            --sidebar-width: 300px;
            --header-height: 64px;
            --radius: 8px;
        }

        [data-theme="light"] {
            --bg-primary: #f8fafc;
            --bg-secondary: #ffffff;
            --bg-tertiary: #f1f5f9;
            --bg-card: #ffffff;
            --bg-hover: #f1f5f9;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --text-muted: #94a3b8;
            --accent: #4f46e5;
            --accent-hover: #6366f1;
            --accent-subtle: rgba(79, 70, 229, 0.08);
            --border: #e2e8f0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            overflow: hidden;
            height: 100vh;
        }

        /* ── Header ── */
        .doc-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: var(--bg-secondary);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            z-index: 100;
            gap: 16px;
        }

        .doc-header .logo {
            font-weight: 700;
            font-size: 18px;
            color: var(--accent);
            letter-spacing: -0.5px;
            white-space: nowrap;
        }

        .doc-header .logo span {
            color: var(--text-muted);
            font-weight: 400;
        }

        .search-wrapper {
            flex: 1;
            max-width: 480px;
            position: relative;
        }

        .search-wrapper input {
            width: 100%;
            padding: 8px 16px 8px 40px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 24px;
            color: var(--text-primary);
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s;
        }

        .search-wrapper input:focus {
            border-color: var(--accent);
        }

        .search-wrapper .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 14px;
        }

        .theme-toggle {
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-secondary);
            padding: 8px 12px;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.2s;
        }

        .theme-toggle:hover {
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        .hamburger {
            display: none;
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 22px;
            cursor: pointer;
        }

        /* ── Sidebar ── */
        .doc-sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--header-height));
            overflow-y: auto;
            background: var(--bg-secondary);
            border-right: 1px solid var(--border);
            padding: 16px 0;
            z-index: 90;
            transition: transform 0.3s ease;
        }

        .doc-sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .doc-sidebar::-webkit-scrollbar-thumb {
            background: var(--bg-tertiary);
            border-radius: 4px;
        }

        .sidebar-category {
            margin-bottom: 4px;
        }

        .sidebar-category-header {
            display: flex;
            align-items: center;
            padding: 8px 20px;
            color: var(--text-secondary);
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            user-select: none;
            transition: color 0.2s;
        }

        .sidebar-category-header:hover {
            color: var(--text-primary);
        }

        .sidebar-category-header .chevron {
            margin-right: 8px;
            font-size: 10px;
            transition: transform 0.2s;
        }

        .sidebar-category-header.open .chevron {
            transform: rotate(90deg);
        }

        .sidebar-category-header .cat-count {
            margin-left: auto;
            background: var(--bg-tertiary);
            color: var(--text-muted);
            font-size: 10px;
            padding: 1px 6px;
            border-radius: 10px;
        }

        .sidebar-files {
            display: none;
        }

        .sidebar-files.open {
            display: block;
        }

        .sidebar-files a {
            display: block;
            padding: 5px 20px 5px 44px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 13px;
            transition: all 0.15s;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-files a:hover {
            color: var(--text-primary);
            background: var(--accent-subtle);
        }

        .sidebar-files a.active {
            color: var(--accent);
            background: var(--accent-subtle);
            border-right: 2px solid var(--accent);
        }

        /* ── Content ── */
        .doc-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            height: calc(100vh - var(--header-height));
            overflow-y: auto;
            padding: 32px 40px;
        }

        .doc-content::-webkit-scrollbar {
            width: 6px;
        }

        .doc-content::-webkit-scrollbar-thumb {
            background: var(--bg-tertiary);
            border-radius: 4px;
        }

        /* Cards */
        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 24px;
            margin-bottom: 24px;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 20px;
            text-align: center;
            transition: all 0.25s;
        }

        .stat-card:hover {
            border-color: var(--accent);
            transform: translateY(-2px);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
        }

        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--accent);
        }

        .stat-card .stat-label {
            font-size: 12px;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        /* Page Title */
        .page-title {
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 8px;
        }

        .page-subtitle {
            color: var(--text-secondary);
            font-size: 15px;
            margin-bottom: 28px;
        }

        /* Section Headers */
        .section-header {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 16px;
            padding-bottom: 8px;
            border-bottom: 1px solid var(--border);
        }

        /* Tables */
        .doc-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .doc-table th {
            text-align: left;
            padding: 10px 14px;
            background: var(--bg-tertiary);
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border);
        }

        .doc-table td {
            padding: 10px 14px;
            border-bottom: 1px solid var(--border);
            vertical-align: top;
        }

        .doc-table tr:hover td {
            background: var(--accent-subtle);
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
        }

        .badge-public {
            background: rgba(34, 197, 94, 0.15);
            color: var(--success);
        }

        .badge-protected {
            background: rgba(245, 158, 11, 0.15);
            color: var(--warning);
        }

        .badge-private {
            background: rgba(239, 68, 68, 0.15);
            color: var(--danger);
        }

        .badge-static {
            background: rgba(6, 182, 212, 0.15);
            color: var(--info);
        }

        .badge-type {
            background: var(--accent-subtle);
            color: var(--accent);
        }

        /* Method Cards */
        .method-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            margin-bottom: 16px;
            overflow: hidden;
            transition: border-color 0.2s;
        }

        .method-card:hover {
            border-color: var(--accent);
        }

        .method-header {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 14px 18px;
            background: var(--bg-tertiary);
            border-bottom: 1px solid var(--border);
            flex-wrap: wrap;
        }

        .method-name {
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            font-size: 14px;
            color: var(--accent);
        }

        .method-body {
            padding: 16px 18px;
        }

        .method-desc {
            color: var(--text-secondary);
            font-size: 13px;
            margin-bottom: 12px;
            line-height: 1.5;
        }

        .param-table {
            margin-top: 8px;
        }

        .param-table th {
            font-size: 10px;
        }

        .param-table td {
            font-size: 12px;
        }

        .return-type {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: var(--info);
        }

        /* Source Code */
        .source-section {
            margin-top: 32px;
        }

        pre[class*="language-"] {
            border-radius: var(--radius) !important;
            font-size: 13px !important;
            line-height: 1.6 !important;
            max-height: 600px;
            overflow: auto;
        }

        code {
            font-family: 'JetBrains Mono', monospace;
        }

        :not(pre)>code {
            background: var(--bg-tertiary);
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 12px;
            color: var(--accent);
        }

        /* File List Cards */
        .file-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
            color: var(--text-primary);
            transition: all 0.2s;
        }

        .file-card:hover {
            border-color: var(--accent);
            transform: translateY(-1px);
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
        }

        .file-card .file-icon {
            font-size: 28px;
            opacity: 0.7;
        }

        .file-card .file-info {
            flex: 1;
            min-width: 0;
        }

        .file-card .file-name {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 2px;
            word-break: break-word;
        }

        .file-card .file-meta {
            font-size: 12px;
            color: var(--text-muted);
        }

        .file-card .file-summary {
            font-size: 12px;
            color: var(--text-secondary);
            margin-top: 4px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .breadcrumb a {
            color: var(--accent);
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .meta-row {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }

        .meta-item {
            font-size: 13px;
            color: var(--text-secondary);
        }

        .meta-item strong {
            color: var(--text-primary);
        }

        /* Route table filter */
        .table-filter {
            padding: 8px 14px;
            background: var(--bg-tertiary);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text-primary);
            font-size: 13px;
            outline: none;
            margin-bottom: 12px;
            width: 100%;
            max-width: 320px;
        }

        .table-filter:focus {
            border-color: var(--accent);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .doc-sidebar {
                transform: translateX(-100%);
            }

            .doc-sidebar.mobile-open {
                transform: translateX(0);
            }

            .doc-content {
                margin-left: 0;
            }

            .hamburger {
                display: block;
            }

            .card-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Category page grid */
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 12px;
        }
    </style>
</head>

<body>
    {{-- Header --}}
    <header class="doc-header">
        <button class="hamburger" onclick="toggleSidebar()" aria-label="Menu">☰</button>
        <a href="{{ route('docs.code.index') }}" style="text-decoration:none">
            <div class="logo">CodeDocs <span>/ Laravel CRM</span></div>
        </a>
        <div class="search-wrapper">
            <span class="search-icon">🔍</span>
            <input type="text" id="globalSearch" placeholder="Search files, methods, classes..." autocomplete="off">
        </div>
        <button class="theme-toggle" onclick="toggleTheme()" title="Toggle theme">🌙</button>
    </header>

    {{-- Sidebar --}}
    <nav class="doc-sidebar" id="sidebar">
        @foreach($tree as $slug => $cat)
            <div class="sidebar-category" data-category="{{ $slug }}">
                <div class="sidebar-category-header {{ request()->segment(3) === $slug ? 'open' : '' }}"
                    onclick="toggleCategory(this)">
                    <span class="chevron">▶</span>
                    {{ $cat['label'] }}
                    <span class="cat-count">{{ count($cat['files']) }}</span>
                </div>
                <div class="sidebar-files {{ request()->segment(3) === $slug ? 'open' : '' }}">
                    @foreach($cat['files'] as $f)
                        <a href="{{ route('docs.code.show', [$slug, $f]) }}"
                            class="{{ request()->segment(4) === $f ? 'active' : '' }}" data-filename="{{ strtolower($f) }}">
                            {{ str_replace('.', ' / ', $f) }}
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach
    </nav>

    {{-- Content --}}
    <main class="doc-content" id="content">
        @yield('content')
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-php.min.js"></script>
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.js"></script>
    <script>
        // Theme toggle
        function toggleTheme() {
            const html = document.documentElement;
            const current = html.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('doc-theme', next);
            document.querySelector('.theme-toggle').textContent = next === 'dark' ? '🌙' : '☀️';
        }
        (function () {
            const saved = localStorage.getItem('doc-theme');
            if (saved) {
                document.documentElement.setAttribute('data-theme', saved);
                document.querySelector('.theme-toggle').textContent = saved === 'dark' ? '🌙' : '☀️';
            }
        })();

        // Sidebar category toggle
        function toggleCategory(el) {
            el.classList.toggle('open');
            const files = el.nextElementSibling;
            files.classList.toggle('open');
        }

        // Mobile sidebar toggle
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('mobile-open');
        }

        // Global search/filter
        document.getElementById('globalSearch').addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            document.querySelectorAll('.sidebar-category').forEach(cat => {
                const links = cat.querySelectorAll('.sidebar-files a');
                let hasMatch = false;
                links.forEach(a => {
                    const match = a.dataset.filename.includes(q) || a.textContent.toLowerCase().includes(q);
                    a.style.display = match ? '' : 'none';
                    if (match) hasMatch = true;
                });
                cat.style.display = (q === '' || hasMatch) ? '' : 'none';
                if (q && hasMatch) {
                    cat.querySelector('.sidebar-category-header').classList.add('open');
                    cat.querySelector('.sidebar-files').classList.add('open');
                }
            });
        });
    </script>
    @stack('scripts')
</body>

</html>