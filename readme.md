Perfect — here's the updated `README.md` with `:to` behavior interception handled **inside the boot file**, keeping your components clean:

---

# 🚀 Quasar But Cached (`quasar-but-cached`)

This setup allows you to **pre-render your Quasar SPA** into static HTML files with a lightweight PHP caching tool — great for SEO, faster load times, and static hosting.

---

## 📁 Folder Structure

```
project-root/
└── cacher/
    ├── spa/              <-- Quasar SPA build output (e.g. dist/spa)
    ├── [cached pages]/   <-- Static HTML snapshots (e.g. /services/index.html)
    └── index.php         <-- Page saver UI and PHP logic
```

---

## 🔧 Quasar Config (`quasar.config.js`)

```js
build: {
  publicPath: '/cacher/spa/', // Required for correct resource loading
  routerMode: 'hash',         // Hash mode enables reliable static routing
},
boot: ['quasar-but-cached-support'],
```

---

## ⚙️ Boot File: `quasar-but-cached-support.js`

This boot file **blocks hydration** on cached HTML and also rewrites all `:to` navigation into static-safe `<a href="...">` links dynamically.

```js
export default async ({ app, router }) => {
  const { pathname } = window.location
  const isStatic = !pathname.startsWith('/cacher/spa/')

  if (isStatic) {
    // ❌ Prevent Vue from booting
    throw new Error('Skipping Vue mount: prerendered page.')
  }

  // ✅ Patch router-link globally to avoid broken SPA routing in dev/build mix
  const originalPush = router.push
  router.push = function (location, ...args) {
    if (typeof location === 'string' && !location.startsWith('/cacher/spa/')) {
      window.location.href = location.includes('#') ? location : `${location}#${location}`
      return
    }
    return originalPush.call(this, location, ...args)
  }
}
```

This keeps all your component logic unchanged — `:to="..."` will just work in both SPA and cached views.

---

## 🧪 Deploy Steps

1. **Build SPA:**

   ```bash
   quasar build
   ```

2. **Copy output:**

   ```bash
   cp -r dist/spa/* /path/to/server/cacher/spa/
   ```

3. **Upload caching UI:**
   Place the `index.php` tool inside `/cacher`.

4. **Visit caching UI:**
   Open [`yourdomain.com/cacher`](https://yourdomain.com/cacher) to generate snapshots.

---

## 🧠 How It Works

| What                        | Path / Action                             |
| --------------------------- | ----------------------------------------- |
| Build output                | `/cacher/spa/`                            |
| Caching interface           | `/cacher/`                                |
| Cached page (e.g. About)    | `/cacher/about/`                          |
| Vue hydration is disabled   | Everywhere **except** `/cacher/spa/`      |
| Vue `router.push()` rewrite | Always redirects to static-friendly links |

---

Let me know if you'd like `.htaccess` rules for fallback or routing normalization too.
