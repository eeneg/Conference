import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import AuthenticatedLayout from './Layouts/AuthenticatedLayout.vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/index.js';

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        let page = pages[`./Pages/${name}.vue`]
        page.default.layout = page.default.layout
        || AuthenticatedLayout
        return page
    },
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .mount(el);
    },
    created(){
        window.Echo.channel(`chat`)
        .listen('App\\Events\\MessageSentEvent', (e) => {
            console.log(e);
        });
    },
    progress: {
        color: '#4B5563',
    },
});
