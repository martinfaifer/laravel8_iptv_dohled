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

            <!-- container pro historii uzivatele -->

            <v-row class="mt-8 ml-3 mr-3">
                <v-col cols="12" sm="12" md="12" lg="12">
                    <v-card class="text-center" flat>
                        <v-card-text>
                            <span>
                                <strong class="white--text">
                                    Historie
                                </strong>
                            </span>
                            <v-container v-if="history != 'none'">
                                <v-timeline dense>
                                    <v-timeline-item
                                        v-for="userHist in history"
                                        :key="userHist.id"
                                        small
                                    >
                                        <template v-slot:opposite>
                                            <span
                                                class="
                                                body-1 font-weight-bold
                                            "
                                                v-text="userHist.created_at"
                                            ></span>
                                        </template>
                                        <div class="py-4">
                                            <div>
                                                <strong class="green--text">
                                                    {{ userHist.created_at }}
                                                    =>
                                                    {{ userHist.message }}
                                                </strong>
                                            </div>
                                        </div>
                                    </v-timeline-item>
                                </v-timeline>
                            </v-container>
                            <v-container v-else>
                                <div>
                                    <v-alert outlined text type="info">
                                        Není evidována žádná historie
                                    </v-alert>
                                </div>
                            </v-container>
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
    data: () => ({
        history: null
    }),
    created() {
        this.loaduserHistory();
    },

    methods: {
        loaduserHistory() {
            window.axios
                .post("user/history", {
                    streamId: this.streamId
                })
                .then(response => {
                    this.history = response.data;
                });
        }
    }
};
</script>
