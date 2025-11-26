import '../css/app.css';
import './bootstrap';

// Vuetify
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.min.css';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { es } from 'vuetify/locale';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Maquil';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const vuetify = createVuetify({
            components,
            directives,
            locale: {
                locale: 'es',
                messages: { es },
            },
            theme: {
                defaultTheme: 'light',
                themes: {
                    light: {
                        colors: {
                            primary: '#4F46E5',   // indigo-600
                            secondary: '#06B6D4', // cyan-500
                            tertiary: '#7C3AED',  // violet-600 (tertiary)
                            success: '#10B981',   // emerald-500
                            info: '#0EA5E9',      // sky-500
                            warning: '#F59E0B',   // amber-500
                            error: '#EF4444',     // red-500
                            background: '#FFFFFF',
                            surface: '#FFFFFF',
                            'on-surface': '#111827',
                            'on-primary': '#FFFFFF',
                            'on-tertiary': '#FFFFFF',
                        },
                    },
                    dark: {
                        dark: true,
                        colors: {
                            primary: '#6366F1',
                            secondary: '#0891B2',
                            tertiary: '#7C3AED',
                            success: '#10B981',
                            info: '#0EA5E9',
                            warning: '#F59E0B',
                            error: '#EF4444',
                            background: '#121212',
                            surface: '#1F1F1F',
                            'on-surface': '#FFFFFF',
                            'on-primary': '#FFFFFF',
                            'on-tertiary': '#FFFFFF',
                        },
                    },
                },
            },
        });

        const app = createApp({ render: () => h(App, props) });

        app.use(plugin)
            .use(ZiggyVue)
            .use(vuetify);

        // expose vuetify to window so components can toggle theme at runtime
        try {
            window.vuetify = vuetify;
        } catch (e) {}

        // Auto-register all components in resources/js/Components as global components
        const globalComponents = import.meta.glob('./Components/**/*.vue', { eager: true });
        Object.entries(globalComponents).forEach(([path, definition]) => {
            const name = path.split('/').pop().replace('.vue', '');
            app.component(name, definition.default);
        });

        app.mount(el);
        return app;
    },
    progress: {
        color: '#4B5563',
    },
});
