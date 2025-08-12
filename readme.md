

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

This boot file **blocks hydration** on cached HTML.

```js
export default async () => {
  const isCacher = window.location.pathname.startsWith('/cacher/');

  // Abort *before* hydration and router kick in
  if (!isCacher) {
    // Skip hydration AND prevent router from being created
    throw new Error('Skipping Vue mount: prerendered page.');
  }
}
```


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




## 🔗 Smart Links for Cached + SPA Routing

When using **Quasar But Cached**, standard Vue router links like `:to="..."` don’t work properly in cached (static HTML) mode. This is because client-side routing is disabled in cached builds, and navigation must use hash-based fallback instead.

To address this, use the custom `QuasarButCachedLink` component instead of `<q-item :to="...">`.

### ✅ Features

* Outputs clean `<a href="...">` links for SEO and bots
* Intercepts clicks and dynamically triggers Vue Router navigation (if active)
* Works in both cached and full SPA environments
* Fully compatible with all `q-item` props and slots

### 📦 Usage

```vue
<QuasarButCachedLink
  smart-to="/services"
  class="q-pl-lg text-h5"
  :style="isActive(item) ? 'border-bottom: white solid 5px;' : 'border-bottom: rgba(0,0,0,0) solid 5px;'"
>
  <q-item-section>Services</q-item-section>
</QuasarButCachedLink>
```

### 🧠 Component Source

```vue
<template>
  <q-item
    clickable
    tag="a"
    :href="smartTo"
    @click="handleClick"
    v-bind="passThroughAttrs"
  >
    <slot />
  </q-item>
</template>

<script>
export default {
  name: 'QuasarButCachedLink',
  props: {
    smartTo: {
      type: String,
      required: true
    }
  },
  computed: {
    passThroughAttrs() {
      const passAttrs = { ...this.$attrs };
      delete passAttrs.href;
      delete passAttrs.to;
      delete passAttrs.smartTo;
      return passAttrs;
    },
  },
  methods: {
    handleClick(e) {
        e.preventDefault()
        this.$router.push(this.smartTo);
    }
  }
};
</script>
```

### 📁 Location

Place the file in:

```
/src/components/QuasarButCachedLink.vue
```

