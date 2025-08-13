<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $slug = trim($_POST['slug'] ?? '', "/");
  $html = $_POST['html'] ?? '';

  if ($html === '') {
    http_response_code(400);
    echo "❌ Missing HTML content.";
    exit;
  }

  // Determine folder and file paths
  $folder = $slug === '' ? dirname(__DIR__) : dirname(__DIR__) . "/$slug";
  $file = "$folder/index.html";

  if (!is_dir($folder)) {
    mkdir($folder, 0755, true);
  }

  file_put_contents($file, $html);
  echo "✅ Saved to /" . ($slug === '' ? '' : "$slug/") . "index.html";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Iframe Vue Cacher for SEO</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="container">
    <h1 class="mb-4">Iframe Vue Cacher for SEO</h1>

    <div class="mb-3">
      <label for="targetUrl" class="form-label">Target URL (from SPA)</label>
      <input type="url" id="targetUrl" class="form-control" placeholder="https://myspa.com/about">
    </div>

    <div class="mb-3">
      <label for="slug" class="form-label">Save As Slug (e.g. "about", or leave blank for homepage)</label>
      <input type="text" id="slug" class="form-control" placeholder="about or leave blank for homepage">
    </div>

    <div class="mb-3">
      <button onclick="loadIframe()" class="btn btn-primary">Render Page</button>
      <button onclick="saveHtml()" class="btn btn-success">Save HTML</button>
    </div>

    <iframe id="preview" style="width:100%; height:600px; border:1px solid #ccc;"></iframe>

    <div id="result" class="mt-3 fw-bold text-success"></div>
  </div>

  <script>
    function loadIframe() {
      const url = document.getElementById('targetUrl').value;
      document.getElementById('preview').src = url;
    }

    function saveHtml() {
      const iframe = document.getElementById('preview');
      const slug = document.getElementById('slug').value;

      try {
        const html = iframe.contentDocument.documentElement.outerHTML;

        fetch('', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: new URLSearchParams({ slug, html })
        })
        .then(res => res.text())
        .then(msg => {
          document.getElementById('result').textContent = msg;
        });

      } catch (e) {
        alert('❌ Unable to access iframe. Must be same-origin.');
      }
    }
  </script>
</body>
</html>
