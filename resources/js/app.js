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
        const vuetifyThemes = {
            light: {
                dark: false,
                colors: {
                    primary: '#374151',
                    secondary: '#6B7280',
                    tertiary: '#10B981',
                    success: '#10B981',
                    info: '#0EA5E9',
                    warning: '#F59E0B',
                    error: '#EF4444',
                    background: '#F8FAFC',
                    surface: '#FFFFFF',
                    'on-surface': '#0B1220',
                    'on-primary': '#FFFFFF',
                    'on-tertiary': '#FFFFFF'
                }
            },
            dark: {
                dark: true,
                colors: {
                    primary: '#9CA3AF',
                    secondary: '#4B5563',
                    tertiary: '#34D399',
                    success: '#34D399',
                    info: '#60A5FA',
                    warning: '#F59E0B',
                    error: '#EF4444',
                    background: '#16151bff',
                    surface: '#000000ff',
                    'on-surface': '#F8FAFC',
                    'on-primary': '#0B1220',
                    'on-tertiary': '#0B1220'
                }
            }
        };

        const vuetify = createVuetify({
            components,
            directives,
            locale: {
                locale: 'es',
                messages: { es },
            },
            theme: {
                defaultTheme: 'light',
                themes: vuetifyThemes,
            },
        });

        // Inject CSS variables for light and dark themes so our helpers (themed-*) work
        try {
            const light = vuetifyThemes.light.colors;
            const dark = vuetifyThemes.dark.colors;
            const css = `:root {
  --v-theme-primary: ${light.primary};
  --v-theme-on-primary: ${light['on-primary']};
  --v-theme-surface: ${light.surface};
  --v-theme-on-surface: ${light['on-surface']};
  --v-theme-outline: rgba(0,0,0,0.12);
}
.v-theme--dark {
  --v-theme-primary: ${dark.primary};
  --v-theme-on-primary: ${dark['on-primary']};
  --v-theme-surface: ${dark.surface};
  --v-theme-on-surface: ${dark['on-surface']};
  --v-theme-outline: rgba(255,255,255,0.08);
}`;
            const style = document.createElement('style');
            style.setAttribute('data-generated-by', 'vuetify-theme-vars');
            style.appendChild(document.createTextNode(css));
            document.head.appendChild(style);
        } catch (e) {
            // ignore if DOM not available during SSR/build
        }

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
