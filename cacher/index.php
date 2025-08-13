<?php
$base = dirname(__DIR__);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $slug   = trim($_POST['slug'] ?? '', "/");

  if ($action === 'delete') {
    $folder = ($slug === '') ? $base : $base . "/$slug";
    $file   = $folder . "/index.html";

    if (file_exists($file)) {
      @unlink($file);
      echo "🗑️ Deleted " . ($slug === '' ? '/index.html' : "/$slug/index.html");
    } else {
      echo "ℹ️ No index.html found to delete.";
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
    $file   = $folder . "/index.html";

    if (!is_dir($folder)) {
      mkdir($folder, 0755, true);
    }

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
  <meta charset="UTF-8" />
  <title>Vue Cacher for SEO</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="container">
    <h1 class="mb-4">Vue Cacher for SEO</h1>

    <!-- Step 1 -->
    <div class="mb-3">
      <label for="slug" class="form-label">Step 1 — Enter slug</label>
      <input type="text" id="slug" class="form-control" placeholder='e.g. "services" — leave blank for homepage'>
    </div>

    <!-- Step 2 -->
    <div class="mb-3">
      <button id="btnDelete" class="btn btn-outline-danger" type="button">Step 2 — Delete existing index.html</button>
    </div>

    <!-- Step 3 -->
    <div class="mb-3">
      <label for="targetUrl" class="form-label">Step 3 — Enter SPA URL and render</label>
      <div class="input-group">
        <input type="url" id="targetUrl" class="form-control" placeholder="https://yourdomain.com/services/">
        <button id="btnRender" class="btn btn-primary" type="button">Render</button>
      </div>
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

    $('btnDelete').onclick = async () => {
      const slug = $('slug').value.trim().replace(/^\/+|\/+$/g, '');
      const body = new URLSearchParams({ action: 'delete', slug });
      const res = await fetch('', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
        const body = new URLSearchParams({ action: 'save', slug, html });
        const res = await fetch('', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
