<?php
// Simple page that consumes actions/fetch_remote_site_info.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Remote Site Info Fetcher</title>
    <style>
        :root {
            --bg: #f4f7fb;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --primary: #0ea5e9;
            --primary-dark: #0284c7;
            --border: #e5e7eb;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background: radial-gradient(circle at top right, #dbeafe, #f4f7fb 50%);
            color: var(--text);
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 20px;
        }

        .app {
            width: min(860px, 100%);
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
            overflow: hidden;
        }

        .header {
            padding: 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .title {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 700;
        }

        .subtitle {
            margin: 6px 0 0;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .btn {
            border: 0;
            background: var(--primary);
            color: #fff;
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.95rem;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn:hover { background: var(--primary-dark); }
        .btn:disabled { opacity: 0.6; cursor: not-allowed; }

        .content { padding: 24px; }

        .status {
            margin: 0 0 16px;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
        }

        .row {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 14px;
            background: #fcfdff;
        }

        .label {
            display: block;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted);
            margin-bottom: 6px;
        }

        .value {
            margin: 0;
            font-size: 1rem;
            line-height: 1.45;
            word-break: break-word;
        }

        .list {
            margin: 0;
            padding-left: 18px;
        }

        @media (max-width: 640px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <main class="app">
        <section class="header">
            <div>
                <h1 class="title">RecyclingPaperArts Info Fetcher</h1>
                <p class="subtitle">Fetch and display key page content from recyclingpaperarts.com</p>
            </div>
            <button id="fetchBtn" class="btn" type="button">Fetch Info</button>
        </section>

        <section class="content">
            <p id="status" class="status">Ready.</p>

            <div class="grid">
                <article class="row">
                    <span class="label">Source</span>
                    <p id="source" class="value">-</p>
                </article>

                <article class="row">
                    <span class="label">Title</span>
                    <p id="title" class="value">-</p>
                </article>

                <article class="row">
                    <span class="label">Tagline</span>
                    <p id="tagline" class="value">-</p>
                </article>

                <article class="row">
                    <span class="label">Welcome Block</span>
                    <p id="welcome" class="value">-</p>
                </article>

                <article class="row">
                    <span class="label">Admin Portal Link</span>
                    <p id="adminLink" class="value">-</p>
                </article>

                <article class="row">
                    <span class="label">Features</span>
                    <ul id="features" class="list"></ul>
                </article>
            </div>
        </section>
    </main>

    <script>
        const fetchBtn = document.getElementById('fetchBtn');
        const statusEl = document.getElementById('status');
        const sourceEl = document.getElementById('source');
        const titleEl = document.getElementById('title');
        const taglineEl = document.getElementById('tagline');
        const welcomeEl = document.getElementById('welcome');
        const adminLinkEl = document.getElementById('adminLink');
        const featuresEl = document.getElementById('features');

        function resetView() {
            sourceEl.textContent = '-';
            titleEl.textContent = '-';
            taglineEl.textContent = '-';
            welcomeEl.textContent = '-';
            adminLinkEl.textContent = '-';
            featuresEl.innerHTML = '';
        }

        function setText(el, value) {
            el.textContent = value && String(value).trim() ? value : '-';
        }

        async function fetchInfo() {
            fetchBtn.disabled = true;
            statusEl.textContent = 'Fetching...';
            resetView();

            try {
                const response = await fetch('../actions/fetch_remote_site_info.php', { cache: 'no-store' });
                const payload = await response.json();

                if (!response.ok || !payload.success) {
                    throw new Error(payload.error || 'Fetch failed');
                }

                const data = payload.data || {};
                setText(sourceEl, payload.source || '');
                setText(titleEl, data.title || '');
                setText(taglineEl, data.tagline || '');
                setText(welcomeEl, data.welcome || '');
                setText(adminLinkEl, data.admin_portal_link || '');

                const features = Array.isArray(data.features) ? data.features : [];
                if (features.length === 0) {
                    const li = document.createElement('li');
                    li.textContent = 'No features found';
                    featuresEl.appendChild(li);
                } else {
                    features.forEach((item) => {
                        const li = document.createElement('li');
                        li.textContent = item;
                        featuresEl.appendChild(li);
                    });
                }

                statusEl.textContent = `Fetched successfully at ${payload.fetched_at || 'unknown time'}.`;
            } catch (error) {
                statusEl.textContent = `Error: ${error.message}`;
            } finally {
                fetchBtn.disabled = false;
            }
        }

        fetchBtn.addEventListener('click', fetchInfo);
    </script>
</body>
</html>
