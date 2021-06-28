<template>
    <v-main class="ml-12">
        <v-container>
            <v-row class="mr-3 ml-3">
                <v-col cols="12" sm="12" md="6" lg="6">
                    <v-card class="text-center" height="100%" flat>
                        <v-card-text>
                            <span>
                                <strong class="white--text">
                                    Informace o uživateli
                                </strong>
                            </span>
                            <v-col cols="12" sm="12" md="6" lg="6">
                                <v-list-item>
                                    Jméno:
                                    <span class="ml-3">
                                        <strong>
                                            {{ user.name }}
                                        </strong>
                                    </span>
                                </v-list-item>
                            </v-col>

                            <v-col cols="12" sm="12" md="6" lg="6">
                                <v-list-item>
                                    Email:
                                    <span class="ml-3">
                                        <strong>
                                            {{ user.email }}
                                        </strong>
                                    </span>
                                </v-list-item>
                            </v-col>
                        </v-card-text>
                    </v-card>
                </v-col>
                <!-- {{ user }} -->
                <!-- container pro nastavení GUI -->
                <v-col cols="12" sm="12" md="6" lg="6">
                    <v-card height="100%" class="text-center" flat>
                        <v-card-text>
                            <span>
                                <strong class="white--text">
                                    Nastavení prostředí:
                                </strong>
                            </span>
                            <v-col cols="12" sm="12" lg="12" md="12">
                                <v-list-item>
                                    Nastavení mozaiky:
                                    <span
                                        v-if="user.mozaika != 'custom'"
                                        class="ml-3"
                                    >
                                        <strong class="green--text">
                                            základní nastavení
                                        </strong>
                                    </span>
                                    <span v-else class="ml-3">
                                        <strong class="green--text">
                                            uživatelem nadefinovaná
                                        </strong>
                                    </span>
                                </v-list-item>
                            </v-col>

                            <v-divider
                                v-if="user.mozaika == 'custom'"
                                class="mb-6"
                            ></v-divider>
                            <span
                                class="text-start"
                                v-if="user.mozaika == 'custom'"
                            >
                                <strong class="white--text">
                                    Statické kanály
                                </strong>
                            </span>
                            <v-col
                                cols="12"
                                sm="12"
                                lg="12"
                                md="12"
                                v-if="user.mozaika == 'custom'"
                            >
                                <v-list-item
                                    v-for="staticChannel in user.staticChannels"
                                    :key="staticChannel.id"
                                >
                                    <v-list-item-content class="ml-3">
                                        <v-list-item-subtitle
                                            v-text="staticChannel.nazev"
                                        ></v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                            </v-col>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
        <br />
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
