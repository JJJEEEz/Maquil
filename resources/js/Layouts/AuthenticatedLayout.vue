<script>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

export default {
  components: { ApplicationLogo, Link },
  data() {
    return {
      drawer: true,
      year: new Date().getFullYear(),
    };
  },
};
</script>

<template>
    <v-app>
        <v-navigation-drawer v-model="drawer" app permanent>
            <div class="pa-4">
                <Link :href="route('dashboard')">
                    <ApplicationLogo class="h-10 w-auto" />
                </Link>
            </div>
            <v-list>
                <v-list-item>
                    <Link :href="route('dashboard')">Dashboard</Link>
                </v-list-item>
                <v-list-item>
                    <Link :href="route('admin.users.index')">Usuarios</Link>
                </v-list-item>
                <v-list-item>
                    <Link :href="route('admin.roles.index')">Roles</Link>
                </v-list-item>
            </v-list>
        </v-navigation-drawer>

        <v-app-bar app color="white" elevate-on-scroll>
            <v-app-bar-nav-icon @click="drawer = !drawer" />
            <v-toolbar-title>Maquil</v-toolbar-title>
            <v-spacer />
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

        <v-footer app>
            <v-row class="justify-center">
                <div class="text-center text-sm text-gray-500">© {{ year }} Maquil. Todos los derechos reservados.</div>
            </v-row>
        </v-footer>
    </v-app>
</template>
