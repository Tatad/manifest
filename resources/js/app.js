import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import Vue3EasyDataTable from 'vue3-easy-data-table';
import 'vue3-easy-data-table/dist/style.css';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        // return createApp({ render: () => h(App, props) })
        //     .use(plugin)
        //     .use(ZiggyVue, Ziggy)
        //     .component('EasyDataTable', Vue3EasyDataTable)
        //     .mount(el);

        const app =  createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .component('EasyDataTable', Vue3EasyDataTable)

        app.config.globalProperties.$filters = {
            currency(value) {
                return '$' + new Intl.NumberFormat('en-US').format(value)
            },
            truncate: function (text, length, suffix) {
                length = 30;
                if (text.length > length) {
                    return text.substring(0, length)+'...';
                } else {
                    return text;
                }
            }
            // Put the rest of your filters here
        }

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

