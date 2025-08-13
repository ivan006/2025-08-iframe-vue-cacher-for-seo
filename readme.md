
# 🚀 Iframe Vue Cacher for SEO

Run your **Iframe Vue Cacher for SEO** and generate static HTML snapshots for specific routes.

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

* Upload the entire `/cacher/` folder into your site’s root (`project-root/`).

### Step 2 — Build & upload SPA to root

* Build your app:

  ```bash
  quasar build
  ```
* Copy **everything inside** `dist/spa/` to the **root** (`project-root/`), e.g.:

  * `index.html`
  * `assets/**`

### Step 3 — Cache pages

* Visit `https://yourdomain.com/cacher/`
* Use the UI to generate/update static HTML snapshots.
* Snapshots are saved as `/route/index.html` in the root.

---

## 📌 Important: Link format

All links to cached pages **must** end with a trailing slash (`/`).
Examples:

* ✅ `/services/`
* ❌ `/services`
