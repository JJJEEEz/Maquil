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
                        dark: false, // Indica que este es el tema claro
                        colors: {
                            // Colores Principales Lujo Dorado
                            primary: '#d1ac3a',   // Oro Viejo (El foco principal en ambas versiones)
                            secondary: '#021561ff', 
                            tertiary: '#00A36C',  // Verde Esmeralda (Acento 2)

                            // Colores Funcionales (Alto contraste sobre fondo CLARO)
                            success: '#10B981',   // Éxito
                            info: '#0EA5E9',      // Información
                            warning: '#F59E0B',   // Advertencia (Tono original)
                            error: '#EF4444',     // Error

                            // Fondos y Superficies (Tema CLARO)
                            background: '#FFFFFF', // Fondo principal BLANCO
                            surface: '#F5F5F5',    // Superficie/Tarjetas (Gris claro)
                            // Colores de Texto (On-Colors)
                            'on-surface': '#111827',  // Texto sobre fondo/superficie CLARO (Negro oscuro)
                            'on-primary': '#111827',  // Texto sobre Dorado (Negro oscuro para mayor legibilidad)
                            'on-tertiary': '#FFFFFF'  // Texto sobre Esmeralda (Blanco)
                        }
                    },

                    // === MODO OSCURO (DARK) ===
                    dark: {
                        dark: true, // Indica que este es el tema oscuro
                        colors: {
                            // Colores Principales Lujo Dorado (Se mantienen los mismos tonos vibrantes)
                            primary: '#FFD700',   // Oro Viejo (El foco principal en ambas versiones)
                            secondary: '#546090ff', // Borgoña Profundo (Acento 1)
                            tertiary: '#00A36C',  // Verde Esmeralda (Acento 2)

                            // Colores Funcionales (Se mantienen los tonos vibrantes para contraste)
                            success: '#10B981',   // Éxito
                            info: '#0EA5E9',      // Información
                            warning: '#FFC107',   // Advertencia (Más vibrante sobre fondo oscuro)
                            error: '#EF4444',     // Error

                            // Fondos y Superficies (Tema OSCURO - El modo que creamos antes)
                            background: '#121212', // Negro Azabache (Fondo principal)
                            surface: '#1D1D1D',    // Superficie/Tarjetas

                            // Colores de Texto (On-Colors)
                            'on-surface': '#F5F5F5',  // Texto sobre fondo/superficie OSCURO (Blanco roto)
                            'on-primary': '#111827',  // Texto sobre Dorado (Negro oscuro para legibilidad)
                            'on-tertiary': '#F5F5F5'  // Texto sobre Esmeralda (Blanco roto)
                        }
                    }
                }
                },
            },
        );

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
