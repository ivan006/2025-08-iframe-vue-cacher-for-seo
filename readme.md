

# 🚀 Quasar But Cached

Run your **Quasar SPA at the root** of your site **and** generate static HTML snapshots for specific routes.
You get fast, SEO-friendly cached pages **without** disabling hydration or changing how links work.

---

## 1️⃣ Final Structure

```
project-root/                     <- web root
├── index.html                     <- SPA entry file
├── assets/**                      <- SPA assets (from dist/spa/)
├── cacher/
│   └── index.php                  <- cache UI (create/clear snapshots)
└── services/
    └── index.html                 <- cached page for /services/
```

* All files from `dist/spa/` are deployed directly into `project-root/`.
* Cached pages are placed in subfolders with `index.html` (pretty URLs).

---

## 2️⃣ Steps

### Step 1 — Upload cacher folder

* Upload the entire `/cacher/` folder into your site’s root directory (`project-root/`).

### Step 2 — Cache pages

* Visit `https://yourdomain.com/cacher/`
* Use the UI to generate/update static HTML snapshots for routes.
* Snapshots are saved as `/route/index.html` in `project-root/`.

---

## 🔗 Active Link Check (optional)

If your cached routes are `/route/` format, normalize trailing slashes before comparing:

```js
isActive(item) {
  const normalize = (url) => url.replace(/\/+$/, '');
  return normalize(item.URL) === normalize(this.activeRoute);
}
```
