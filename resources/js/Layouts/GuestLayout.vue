<script>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';

export default {
    components: { ApplicationLogo, Link },
};
</script>

<template>
    <v-app>
        <v-main>
            <v-container fluid class="fill-height">
                <v-row align="center" justify="center">
                    <v-col cols="12" sm="8" md="4">
                        <v-card elevation="12" rounded style="border-radius:12px;">
                            <div class="d-flex justify-center mb-6 mt-6">
                                <Link href="/">
                                    <ApplicationLogo class="h-20 w-20 fill-current" color="primary" />
                                </Link>
                            </div>
                            <v-card-text>
                                <slot />
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>
    </v-app>
</template>
