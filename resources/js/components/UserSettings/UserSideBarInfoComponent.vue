<template>
    <v-main>
        <v-navigation-drawer
            class="mt-12"
            fixed
            width="18%"
            color="transparent"
        >
            <div class="text-center">
                <v-avatar size="108" color="grey" class="mb-12 mt-12">
                    <h1>{{ user.inicial }}</h1>
                </v-avatar>

                <h3 class="mb-12">{{ user.name }}</h3>

                <v-spacer>
                    <span class="body-2 mt-6">
                        Registrováno: {{ user.created_at }}
                    </span>
                </v-spacer>
                <v-spacer></v-spacer>
                <span class="body-2 mt-6">
                    <strong>
                        {{ user.email }}
                    </strong>
                </span>
                <v-spacer></v-spacer>

                <span class="body-2 mt-6">
                    Uživatelská role:
                    <strong class="ml-1 blue--text">
                        {{ user.role }}
                    </strong>
                </span>
                <v-file-input
                    v-if="openNewImage === true"
                    accept="image/*"
                    label="File input"
                ></v-file-input>
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
