<script>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

export default {
  components: { ApplicationLogo, Link },
  data() {
    return {
      drawer: true,
      year: new Date().getFullYear(),
            darkMode: false,
    };
  },
    computed: {
        authPermissions() {
            return (this.$page && this.$page.props && this.$page.props.authPermissions) ? this.$page.props.authPermissions : [];
        },
        canViewOrdenes() {
            return this.authPermissions.includes('ordenes.view');
        },
        canViewUsers() {
            return this.authPermissions.includes('users.view');
        },
        canViewRoles() {
            return this.authPermissions.includes('roles.view');
        },
        canViewTiposPrendas() {
            return this.authPermissions.includes('tipos_prendas.view');
        },
        canRegistrarProcesos() {
            return this.authPermissions.includes('procesos.registrar');
        },
        canOperadorDashboard() {
            return this.authPermissions.includes('operador.dashboard');
        },
    },
    mounted() {
        this.initDarkMode();
        // expose toggle to window for quick testing (optional)
        // window.toggleDarkMode = this.toggleDarkMode;
        try {
            // Temporal: logear permisos compartidos para debug
            // Elimina este console.log cuando confirmes que funciona
                        console.log('authPermissions (shared):', JSON.parse(JSON.stringify(this.$page && this.$page.props ? this.$page.props.authPermissions : [])));
                        // Watch for changes when Inertia navigates and props update
                        if (this.$watch) {
                            this.$watch(() => this.$page && this.$page.props ? this.$page.props.authPermissions : [], (val) => {
                                try {
                                    console.log('authPermissions (changed):', JSON.parse(JSON.stringify(val || [])));
                                } catch (e) { console.log('authPermissions (changed):', val); }
                            });
                        }
        } catch (e) {
            // ignore
        }
    },
    methods: {
        initDarkMode() {
            try {
                const stored = localStorage.getItem('darkMode');
                if (stored !== null) {
                    this.darkMode = stored === 'true';
                } else {
                    // default to system preference
                    this.darkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                }
                if (this.darkMode) {
                    document.documentElement.classList.add('dark');
                    try { if (window.vuetify && window.vuetify.theme && window.vuetify.theme.global && window.vuetify.theme.global.name) window.vuetify.theme.global.name.value = 'dark'; } catch(e){}
                }
                else {
                    document.documentElement.classList.remove('dark');
                    try { if (window.vuetify && window.vuetify.theme && window.vuetify.theme.global && window.vuetify.theme.global.name) window.vuetify.theme.global.name.value = 'light'; } catch(e){}
                }
            } catch (e) {
                // ignore
            }
        },
        toggleDarkMode() {
            this.darkMode = !this.darkMode;
            try {
                localStorage.setItem('darkMode', this.darkMode ? 'true' : 'false');
            } catch (e) {}
            if (this.darkMode) {
                document.documentElement.classList.add('dark');
                try { if (window.vuetify && window.vuetify.theme && window.vuetify.theme.global && window.vuetify.theme.global.name) window.vuetify.theme.global.name.value = 'dark'; } catch(e){}
            }
            else {
                document.documentElement.classList.remove('dark');
                try { if (window.vuetify && window.vuetify.theme && window.vuetify.theme.global && window.vuetify.theme.global.name) window.vuetify.theme.global.name.value = 'light'; } catch(e){}
            }
        },
    },
};
</script>

<template>
    <v-app>
        <v-navigation-drawer v-model="drawer" app permanent color="surface">
            <div class="pa-4">
                <Link :href="route('welcome')">
                    <ApplicationLogo class="h-10 w-auto" />
                </Link>
            </div>
            <v-list>
                <v-list-subheader>Cadena de producción</v-list-subheader>
                <v-list-item v-if="canViewOrdenes">
                    <Link :href="route('admin.ordenes.index')">Ordenes de producción</Link>
                </v-list-item>
                <v-list-item v-if="canOperadorDashboard">
                    <Link :href="route('operador.dashboard')">
                        <v-icon class="me-2">mdi-worker</v-icon>
                        Mi Panel de Operador
                    </Link>
                </v-list-item>
                <v-list-subheader v-if="canViewTiposPrendas || canViewUsers || canViewRoles" >Panel de Administración</v-list-subheader>
                <v-list-item v-if="canViewTiposPrendas">
                    <Link :href="route('admin.tipos-prendas.index')">Diagrama de Procesos</Link>
                </v-list-item>
                <v-list-item v-if="canViewUsers">
                    <Link :href="route('admin.users.index')">Usuarios</Link>
                </v-list-item>
                <v-list-item v-if="canViewRoles">
                    <Link :href="route('admin.roles.index')">Roles</Link>
                </v-list-item>
            </v-list>
        </v-navigation-drawer>

        <v-app-bar app color="surface" elevate-on-scroll>
            <v-app-bar-nav-icon @click="drawer = !drawer" />
            <v-toolbar-title>Maquil</v-toolbar-title>
            <v-spacer />
            <v-btn icon title="Toggle dark mode" @click="toggleDarkMode">
                <v-icon v-if="!darkMode">mdi-weather-night</v-icon>
                <v-icon v-else>mdi-white-balance-sunny</v-icon>
            </v-btn>
            <v-menu>
                <template #activator="{ props }">
                    <v-btn v-bind="props" text>
                        {{ $page.props.auth.user.name }}
                        <v-icon class="ms-2">mdi-menu-down</v-icon>
                    </v-btn>
                </template>
                <v-list>
                    <v-list-item>
                        <Link :href="route('profile.edit')">Profile</Link>
                    </v-list-item>
                    <v-list-item>
                        <Link :href="route('logout')" method="post">Log Out</Link>
                    </v-list-item>
                </v-list>
            </v-menu>
        </v-app-bar>

        <v-main>
            <v-container fluid>
                <div v-if="$slots.header">
                    <slot name="header" />
                </div>
                <slot />
            </v-container>
        </v-main>

        <v-footer app color="surface">
            <v-row class="justify-center">
                <div class="text-center text-sm text-gray-500">© {{ year }} Maquil. Todos los derechos reservados.</div>
            </v-row>
        </v-footer>
    </v-app>
</template>
