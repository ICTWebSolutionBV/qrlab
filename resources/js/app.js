import './bootstrap';
import '../css/app.css';

import { createApp, h, watch } from 'vue';
import { createInertiaApp, usePage } from '@inertiajs/vue3';
import { ZiggyVue } from 'ziggy-js';
import { applyTheme } from './composables/useTheme';

window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    const page = usePage();
    const pref = page.props?.auth?.user?.theme_preference
        || localStorage.getItem('theme')
        || 'auto';
    if (pref === 'auto') {
        applyTheme('auto');
    }
});

createInertiaApp({
    title: (title) => {
        const appName = 'QR Lab';
        return title ? `${title} - ${appName}` : appName;
    },
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);

        const userPref = props.initialPage.props?.auth?.user?.theme_preference;
        const initialPref = userPref || localStorage.getItem('theme') || 'auto';
        applyTheme(initialPref);

        watch(
            () => usePage().props?.auth?.user?.theme_preference,
            (pref) => applyTheme(pref || 'auto'),
        );

        return app;
    },
    progress: {
        color: '#10b981',
    },
});
