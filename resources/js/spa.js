import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { router } from '@inertiajs/vue3'
// Naive UI can be used per-page; import on demand in pages
import 'flatpickr/dist/flatpickr.min.css'
import '@inertiajs/progress'
import './bootstrap'
import '../css/app.css'

createInertiaApp({
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
    app.use(plugin)
    // Optional: Ziggy if route helper is available; safe to ignore if not installed
    try { app.use(ZiggyVue) } catch (e) {}
    app.mount(el)
  },
  progress: { color: '#111827' }
})
