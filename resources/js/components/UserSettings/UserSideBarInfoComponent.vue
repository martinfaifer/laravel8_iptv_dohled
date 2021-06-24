<template>
    <v-main>
        <v-navigation-drawer
            class="mt-12"
            fixed
            color="transparent"
        >
            <div class="text-center">
                <v-col cols="12" sm="12" md="12" lg="12">
                    <v-avatar size="108" color="grey" class="mb-12 mt-12">
                        <v-icon dark x-large>
                            mdi-account-circle
                        </v-icon>
                    </v-avatar>
                </v-col>
                <v-col cols="12" sm="12" md="12" lg="12">
                    <h3 class="mb-12">{{ user.name }}</h3>
                </v-col>

                <v-col cols="12" sm="12" md="12" lg="12">
                    <span class="body-2 mt-6">
                        Registrováno:
                        <span class="green--text">{{ user.created_at }}</span>
                    </span>
                </v-col>
                <v-col cols="12" sm="12" md="12" lg="12">
                    <span class="body-2 mt-6">
                        <strong>
                            {{ user.email }}
                        </strong>
                    </span>
                </v-col>
                <v-col cols="12" sm="12" md="12" lg="12">
                    <span class="body-2 mt-6">
                        Uživatelská role:
                        <strong class="ml-1 green--text">
                            {{ user.role }}
                        </strong>
                    </span>
                </v-col>
            </div>
        </v-navigation-drawer>
    </v-main>
</template>
<script>
export default {
    props: ["user"],
    data: () => ({}),
    created() {},

    methods: {}
};
</script>
