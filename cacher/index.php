<?php
$base = dirname(__DIR__);

// --- Quasar SPA shell used when clearing homepage ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $slug = trim($_POST['slug'] ?? '', "/");

    if ($action === 'backup') {
        $file = $base . '/index.html';
        $backup = $base . '/index.backup.html';

        if (!file_exists($file)) {
            echo "ℹ️ No /index.html to backup.";
        } elseif (copy($file, $backup)) {
            echo "✅ Backup created at /index.backup.html";
        } else {
            echo "❌ Failed to create backup.";
        }
        exit;
    }


    if ($action === 'delete') {
        if ($slug === '') {
            // HOMEPAGE: restore from backup if it exists
            $file = $base . '/index.html';
            $backup = $base . '/index.backup.html';

            if (file_exists($backup)) {
                copy($backup, $file);
                echo "🔁 Restored /index.html from backup.";
            } elseif (file_exists($file)) {
                echo "ℹ️ /index.html exists but no backup available.";
            } else {
                echo "ℹ️ No /index.html found to replace.";
            }
        } else {
            // NON-HOMEPAGE: delete only if /slug/index.html exists
            $file = $base . "/$slug/index.html";
            if (file_exists($file)) {
                @unlink($file);
                echo "🗑️ Deleted /$slug/index.html";
            } else {
                echo "ℹ️ No /$slug/index.html found to delete.";
            }
        }
        exit;
    }

    if ($action === 'save') {
        $html = $_POST['html'] ?? '';
        if ($html === '') {
            http_response_code(400);
            echo "❌ Missing HTML content.";
            exit;
        }

        $folder = ($slug === '') ? $base : $base . "/$slug";
        $file = $folder . "/index.html";

        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        // Remove structured data scripts
        $html = preg_replace('/<script[^>]+type=["\']application\/ld\+json["\'][^>]*>.*?<\/script>/is', '', $html);

        // Remove canonical links
        $html = preg_replace('/<link[^>]+rel=["\']canonical["\'][^>]*>/i', '', $html);

        // Remove SEO/social meta tags (description, og, twitter, etc.)
        $html = preg_replace('/<meta[^>]+name=["\'](description|twitter:[^"\']+)["\'][^>]*>/i', '', $html);
        $html = preg_replace('/<meta[^>]+property=["\']og:[^"\']+["\'][^>]*>/i', '', $html);

        file_put_contents($file, $html);
        echo "✅ Saved to " . ($slug === '' ? '/index.html' : "/$slug/index.html");
        exit;
    }

    http_response_code(400);
    echo "❌ Unknown action.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"/>
    <title>Iframe Vue Cacher for SEO</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h1 class="mb-4">Iframe Vue Cacher for SEO</h1>

    <div class="mt-3">
        <button id="btnBackup" class="btn btn-outline-secondary " type="button">Step 0 — Backup Root Index</button>
    </div>
    <!-- Step 1 -->
    <div class="mb-3">
        <label for="slug" class="form-label">Step 1 — Enter slug</label>
        <input type="text" id="slug" class="form-control" placeholder='e.g. "services" — leave blank for homepage'>
        <div class="form-text">Saves to <code>/slug/index.html</code> or <code>/index.html</code> when blank.</div>
    </div>

    <!-- Step 2 -->
    <div class="mb-3">
        <button id="btnDelete" class="btn btn-outline-danger" type="button">Step 2 — Delete existing index.html</button>
        <div class="form-text">
            Homepage: replaces <code>/index.html</code> with the backup.<br>
            Other slugs: deletes only <code>/slug/index.html</code>.
        </div>
    </div>

    <!-- Step 3 -->
    <div class="mb-3">
        <label for="targetUrl" class="form-label">Step 3 — Enter SPA URL and render</label>
        <div class="input-group">
            <input type="url" id="targetUrl" class="form-control" placeholder="https://yourdomain.com/services/">
            <button id="btnRender" class="btn btn-primary" type="button">Render</button>
        </div>
        <div class="form-text">Must be same-origin so the iframe can read HTML.</div>
    </div>

    <iframe id="preview" style="width:100%; height:600px; border:1px solid #ccc;"></iframe>

    <!-- Step 4 -->
    <div class="mt-3">
        <button id="btnSave" class="btn btn-success" type="button">Step 4 — Save HTML</button>
    </div>

    <div id="result" class="mt-3 fw-bold"></div>
</div>

<script>
    const $ = (id) => document.getElementById(id);

    $('btnBackup').onclick = async () => {
        const body = new URLSearchParams({ action: 'backup', slug: '' });
        const res = await fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body
        });
        $('result').textContent = await res.text();
    };


    $('btnDelete').onclick = async () => {
        const slug = $('slug').value.trim().replace(/^\/+|\/+$/g, '');
        const body = new URLSearchParams({action: 'delete', slug});
        const res = await fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body
        });
        $('result').textContent = await res.text();
    };

    $('btnRender').onclick = () => {
        const url = $('targetUrl').value.trim();
        if (!url) {
            alert('Enter a URL to render.');
            return;
        }
        $('preview').src = url;
    };

    $('btnSave').onclick = async () => {
        const slug = $('slug').value.trim().replace(/^\/+|\/+$/g, '');
        const iframe = $('preview');
        try {
            const html = iframe.contentDocument.documentElement.outerHTML;
            const body = new URLSearchParams({action: 'save', slug, html});
            const res = await fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body
            });
            $('result').textContent = await res.text();
        } catch (e) {
            alert('❌ Unable to access iframe. Must be same-origin.');
        }
    };
</script>
</body>
</html>
