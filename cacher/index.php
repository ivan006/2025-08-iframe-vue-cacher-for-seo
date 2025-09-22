<?php
$base = dirname(__DIR__);

function rrmdir($dir) {
    if (!is_dir($dir)) {
        return;
    }
    $objects = scandir($dir);
    foreach ($objects as $object) {
        if ($object != "." && $object != "..") {
            $path = $dir . DIRECTORY_SEPARATOR . $object;
            if (is_dir($path)) {
                rrmdir($path);
            } else {
                unlink($path);
            }
        }
    }
    rmdir($dir);
}

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
            // NON-HOMEPAGE: delete the entire folder
            $folder = $base . "/$slug";
            if (is_dir($folder)) {
                rrmdir($folder);
                echo "🗑️ Deleted /$slug folder and its contents";
            } else {
                echo "ℹ️ No /$slug folder found to delete.";
            }
        }

        exit;
    }

    if ($action === 'save') {
        $html = base64_decode($_POST['html'] ?? '');
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

        // 🚫 Remove Google Tag Manager / gtm.js scripts
        $html = preg_replace('/<script[^>]+src=["\']https:\/\/www\.googletagmanager\.com\/gtm\.js[^"\']*["\'][^>]*>\s*<\/script>/i', '', $html);

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
<div class="container my-4">

  <!-- Title -->
  <h1 class="mb-4">Iframe Vue Cacher for SEO</h1>

  <!-- Backup -->
  <div class="card mb-3">
    <div class="card-header">Backup Root Index</div>
    <div class="card-body">
      <button id="btnBackup" class="btn btn-outline-secondary">Backup Root Index</button>
    </div>
  </div>

  <!-- Page Selection -->
  <div class="card mb-3">
    <div class="card-header">Pages</div>
    <div class="card-body" id="pageList">
      <!-- checkboxes injected here -->
    </div>
    <div class="card-footer small text-muted">
      Homepage is always included by default. Other pages come from <code>pages.json</code>.
    </div>
  </div>

  <!-- Actions -->
  <div class="card mb-3">
    <div class="card-header">Actions</div>
    <div class="card-body">
      <div class="btn-group" role="group">
        <button id="btnDelete" class="btn btn-outline-danger">
          Delete Selected
        </button>
        <button id="btnSave" class="btn btn-success">
          Cache Selected
        </button>
      </div>
    </div>
    <div class="card-footer small text-muted">
      Selected pages can be cached or deleted.  
      Homepage restore uses the backup if available; other pages are removed or re-cached in <code>/slug/index.html</code>.
    </div>
  </div>

  <!-- Current Status -->
  <div class="card mb-3">
    <div class="card-header">Current Status</div>
    <div class="card-body">
      <div class="mb-2">
        <span class="fw-bold">SPA URL:</span>
        <span id="targetUrl" class="text-monospace">—</span>
      </div>
      <iframe id="preview" class="w-100 border" style="height:500px;"></iframe>
    </div>
  </div>

  <!-- Activity Log -->
  <div class="card">
    <div class="card-header">Activity Log</div>
    <div class="card-body" id="result">
      <p class="text-muted mb-0">No actions yet.</p>
    </div>
  </div>

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
        const selected = [...document.querySelectorAll('#pageList input:checked')]
            .map(el => el.value); // '' means homepage

        if (!selected.length) {
            alert('Please select at least one page.');
            return;
        }

        for (const slug of selected) {
            const body = new URLSearchParams({ action: 'delete', slug });
            const res = await fetch('', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body
            });
            const text = await res.text();

            // append results instead of overwriting
            const div = document.createElement('div');
            div.textContent = text;
            $('result').appendChild(div);
        }
    };




    $('btnSave').onclick = () => {
        const selected = [...document.querySelectorAll('#pageList input:checked')]
            .map(el => el.value); // '' means homepage

        if (!selected.length) {
            alert('Please select at least one page.');
            return;
        }

        const base = window.location.origin;
        const iframe = $('preview');

        // process each page sequentially
        (async function processPages() {
            for (const slug of selected) {
            const url = slug ? `${base}/${slug}/` : `${base}/`;
            $('targetUrl').value = url;
            iframe.src = url;

            await new Promise(resolve => {
                iframe.onload = () => {
                setTimeout(async () => {
                    try {
                    const html = iframe.contentDocument.documentElement.outerHTML;
                    const encoded = btoa(unescape(encodeURIComponent(html)));
                    const body = new URLSearchParams({ action: 'save', slug, html: encoded });
                    const res = await fetch('', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body
                    });
                    $('result').textContent = await res.text();
                    } catch (e) {
                    alert('❌ Unable to access iframe. Must be same-origin.');
                    }
                    resolve();
                }, 1000); // wait 1s
                };
            });
            }
        })();
    };





    // on page load, set the homepage URL + iframe
    window.addEventListener('DOMContentLoaded', () => {
        const base = window.location.origin;
        const url = `${base}/`;
        $('targetUrl').value = url;
        $('preview').src = url;
    });

    async function loadPages() {
        const container = $('pageList');

        // Always add homepage
        const home = document.createElement('div');
        home.className = 'form-check';
        home.innerHTML = `
            <input class="form-check-input" type="checkbox" value="" id="page-home">
            <label class="form-check-label" for="page-home">Homepage</label>
        `;
        container.appendChild(home);

        try {
            const res = await fetch('pages.json');
            const pages = await res.json();

            pages.forEach((slug, i) => {
            const clean = slug.replace(/^\/+|\/+$/g, ''); // normalize
            const div = document.createElement('div');
            div.className = 'form-check';
            div.innerHTML = `
                <input class="form-check-input" type="checkbox" value="${clean}" id="page-${i}">
                <label class="form-check-label" for="page-${i}">/${clean}/</label>
            `;
            container.appendChild(div);
            });
        } catch (e) {
            container.innerHTML += `<div class="text-danger">❌ Could not load pages.json</div>`;
        }
    }

    loadPages();




</script>
</body>
</html>
