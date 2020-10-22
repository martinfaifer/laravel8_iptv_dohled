<template>
    <div>
        <v-row>
            <span class="headline">Přehled</span>
        </v-row>
        <v-spacer></v-spacer>
        <br />
        <!-- container pro sum informací -->
        <v-container fluid>
            <v-row>
                <v-row>
                    <v-hover v-slot:default="{ hover }">
                        <v-card
                            :elevation="hover ? 3 : 0"
                            height="100%"
                            width="40em"
                            class="text-center pl-10 pr-10 transition-fast-in-fast-out ml-12"
                            color="transparent"
                            flat
                        >
                            <span>
                                <strong>
                                    Informace o uživateli
                                </strong>
                            </span>
                            <v-container>
                                <v-card color="transparent" flat>
                                    <v-list-item>
                                        Jméno:
                                        <span class="ml-3">
                                            <strong>
                                                {{ user.name }}
                                            </strong>
                                        </span>
                                    </v-list-item>
                                    <v-list-item>
                                        email:
                                        <span class="ml-3">
                                            <strong>
                                                {{ user.email }}
                                            </strong>
                                        </span>
                                    </v-list-item>
                                    <div v-if="user.userDetail != 'false'">
                                        <v-list-item
                                            v-if="
                                                user.userDetail.company != null
                                            "
                                        >
                                            Společnost:
                                            <span class="ml-3">
                                                <strong>
                                                    {{
                                                        user.userDetail.company
                                                    }}
                                                </strong>
                                            </span>
                                        </v-list-item>
                                        <v-list-item
                                            v-if="
                                                user.userDetail.tel_number !=
                                                    null
                                            "
                                        >
                                            Tel:
                                            <span class="ml-3">
                                                <strong>
                                                    {{
                                                        user.userDetail
                                                            .tel_number
                                                    }}
                                                </strong>
                                            </span>
                                        </v-list-item>
                                        <v-list-item
                                            v-if="
                                                user.userDetail.nickname != null
                                            "
                                        >
                                            Přezdívka:
                                            <span class="ml-3">
                                                <strong>
                                                    {{
                                                        user.userDetail.nickname
                                                    }}
                                                </strong>
                                            </span>
                                        </v-list-item>
                                    </div>
                                </v-card>
                            </v-container>
                        </v-card>
                    </v-hover>
                </v-row>
                <!-- {{ user }} -->
                <!-- container pro nastavení GUI -->
                <v-row>
                    <v-hover v-slot:default="{ hover }">
                        <v-card
                            :elevation="hover ? 3 : 0"
                            height="100%"
                            width="40em"
                            class="text-center pl-10 pr-10 transition-fast-in-fast-out ml-12 mr-12"
                            color="transparent"
                            flat
                        >
                            <span>
                                <strong>
                                    Nastavení prostředí:
                                </strong>
                            </span>
                            <v-container>
                                <v-card color="transparent" flat> </v-card>
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
                                <br />
                                <span
                                    class="text-start"
                                    v-if="user.mozaika == 'custom'"
                                >
                                    Statické kanály
                                </span>
                                <v-list-item v-if="user.mozaika == 'custom'">
                                    <ul>
                                        <li
                                            v-for="staticChannel in user.staticChannels"
                                            class="ml-3"
                                        >
                                            <strong>
                                                {{ staticChannel.nazev }}
                                            </strong>
                                        </li>
                                    </ul>
                                </v-list-item>
                            </v-container>
                        </v-card>
                    </v-hover>
                </v-row>
                <!-- container pro historii uzivatele -->
            </v-row>
            <v-row class="mt-8">
                <v-hover v-slot:default="{ hover }">
                    <v-card
                        :elevation="hover ? 3 : 0"
                        height="100%"
                        width="100%"
                        class="text-center pl-10 pr-10 transition-fast-in-fast-out"
                        color="transparent"
                        flat
                    >
                        <span>
                            <strong>
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
                                            <small class="green--text">
                                                <strong>
                                                    {{ userHist.created_at }} =>
                                                    {{ userHist.message }}
                                                </strong>
                                            </small>
                                        </div>
                                    </div>
                                </v-timeline-item>
                            </v-timeline>
                        </v-container>
                        <v-container v-else>
                            <div>
                                <v-alert dense text type="info">
                                    <strong
                                        >Není evidována žádná historie
                                    </strong>
                                </v-alert>
                            </div>
                        </v-container>
                    </v-card>
                </v-hover>
            </v-row>
        </v-container>
        <br />
    </div>
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
