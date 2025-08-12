<?php
if (isset($_GET['save']) && isset($_GET['slug']) && isset($_GET['html'])) {
  $slug = trim($_GET['slug'], "/");
  $html = $_GET['html'];

  $folder = dirname(__DIR__) . "/$slug";
  $file = "$folder/index.html";

  if (!is_dir($folder)) {
    mkdir($folder, 0755, true);
  }

  file_put_contents($file, $html);
  echo "✅ Saved to /$slug/index.html";
  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Iframe to Static Page Saver</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
  <div class="container">
    <h1 class="mb-4">Iframe to Static Page Saver</h1>

    <div class="mb-3">
      <label for="targetUrl" class="form-label">Target URL (from SPA)</label>
      <input type="url" id="targetUrl" class="form-control" placeholder="https://myspa.com/about">
    </div>

    <div class="mb-3">
      <label for="slug" class="form-label">Save As Slug (e.g. "about")</label>
      <input type="text" id="slug" class="form-control" placeholder="about">
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
        const encoded = encodeURIComponent(html);
        window.location.href = `?save=1&slug=${slug}&html=${encoded}`;
      } catch (e) {
        alert('❌ Unable to access iframe. Must be same-origin.');
      }
    }
  </script>
</body>
</html>
