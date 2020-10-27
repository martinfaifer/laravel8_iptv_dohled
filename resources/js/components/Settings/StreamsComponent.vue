<template>
    <div>
        <div>
            <alert-component
                v-if="status != null"
                :status="status"
            ></alert-component>
        </div>
        <v-container>
            <v-card color="transparent" class="elevation-0 body-2">
                <v-card-title>
                    <v-text-field
                        autofocus
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Vyhledat kanál ..."
                        single-line
                        hide-details
                    ></v-text-field>
                    <v-spacer></v-spacer>
                    <v-btn
                        :loading="loadingCreateBtn"
                        @click="OpenCreateDialog()"
                        small
                        color="success"
                    >
                        <v-icon left dark>
                            mdi-plus
                        </v-icon>
                        Přidat
                    </v-btn>
                </v-card-title>
                <v-data-table
                    dense
                    :headers="streamHeader"
                    :items="streams"
                    :search="search"
                >
                    <!-- ZOBRZAENÍ STAVŮ STREAMU V TABULCE  -->
                    <!-- ikony zda se má stream dohledovat -->
                    <template v-slot:item.dohledovano="{ item }">
                        <span v-if="item.dohledovano == 1">
                            <v-icon color="success">mdi-check</v-icon>
                        </span>
                        <span v-else-if="item.dohledovano == 0">
                            <v-icon color="red">mdi-close</v-icon>
                        </span>
                    </template>
                    <!-- ikony zda se má doheldovat audio -->
                    <template v-slot:item.dohledVolume="{ item }">
                        <span v-if="item.dohledVolume == 1">
                            <v-icon color="success">mdi-check</v-icon>
                        </span>
                        <span v-else-if="item.dohledVolume == 0">
                            <v-icon color="red">mdi-close</v-icon>
                        </span>
                    </template>
                    <!-- ikony zda se ma vytvret nahled -->
                    <template v-slot:item.vytvaretNahled="{ item }">
                        <span v-if="item.vytvaretNahled == 1">
                            <v-icon color="success">mdi-check</v-icon>
                        </span>
                        <span v-else-if="item.vytvaretNahled == 0">
                            <v-icon color="red">mdi-close</v-icon>
                        </span>
                    </template>
                    <!-- ikony alertingu -->
                    <template v-slot:item.sendMailAlert="{ item }">
                        <span v-if="item.sendMailAlert == 1">
                            <v-icon color="success">mdi-check</v-icon>
                        </span>
                        <span v-else-if="item.sendMailAlert == 0">
                            <v-icon color="red">mdi-close</v-icon>
                        </span>
                    </template>
                    <!-- ikony alertingu -->
                    <template v-slot:item.sendSmsAlert="{ item }">
                        <span v-if="item.sendSmsAlert == 1">
                            <v-icon color="success">mdi-check</v-icon>
                        </span>
                        <span v-else-if="item.sendSmsAlert == 0">
                            <v-icon color="red">mdi-close</v-icon>
                        </span>
                    </template>
                    <!-- ikony statusu -->
                    <template v-slot:item.status="{ item }">
                        <span v-if="item.status == 'success'">
                            <v-icon color="success">mdi-check</v-icon>
                        </span>
                        <span v-else-if="item.status == 'error'">
                            <v-icon color="red">mdi-close</v-icon>
                        </span>
                        <span v-else-if="item.status == 'stop'">
                            <v-icon color="blue">mdi-stop</v-icon>
                        </span>
                        <span v-else>
                            <v-icon color="blue">mdi-repeat</v-icon>
                        </span>
                    </template>

                    <!-- AKCE U JEDNOTLIVÝC STREAMŮ -->
                    <template v-slot:item.akce="{ item }">
                        <!-- edit -->
                        <v-icon
                            @click="
                                openEditDialog(
                                    (streamId = item.id),
                                    (stream_nazev = item.nazev),
                                    (stream_url = item.stream_url),
                                    (dohled = item.dohledovano),
                                    (audioDohled = item.dohledVolume),
                                    (vytvareniNahledu = item.vytvaretNahled),
                                    (emailAlert = item.sendMailAlert),
                                    (smsAlert = item.sendSmsAlert)
                                )
                            "
                            small
                            class="mr-2"
                            >mdi-pencil</v-icon
                        >

                        <!-- delete -->
                        <v-icon
                            @click="
                                openDeleteDialog(
                                    (streamId = item.id),
                                    (stream_nazev = item.nazev)
                                )
                            "
                            small
                            color="red"
                            >mdi-delete</v-icon
                        >
                    </template>
                </v-data-table>
            </v-card>
        </v-container>

        <!-- dialogs -->

        <!-- create dialog -->
        <v-row justify="center">
            <v-dialog v-model="createDialog" persistent max-width="600px">
                <v-card>
                    <v-card-title> </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row
                                v-if="channelsFromDoku != null"
                                cols="12"
                                sm="12"
                                md="12"
                                class="mt-2"
                            >
                                <v-autocomplete
                                    v-model="streamUrl"
                                    :items="channelsFromDoku"
                                    item-value="url"
                                    item-text="nazev"
                                    dense
                                    label="Vyberte kanál, který chcete aby se dohledoval"
                                ></v-autocomplete>
                            </v-row>
                            <v-row v-if="channelsFromDoku == null">
                                <v-col cols="12" sm="6" md="6">
                                    <v-text-field
                                        autofocus
                                        v-model="stream_nazev"
                                        label="Název kanálu"
                                        required
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6" md="6">
                                    <v-text-field
                                        v-model="streamUrl"
                                        label="Url"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" sm="6" md="12">
                                    <v-switch
                                        v-model="dohled"
                                        label="
                                            Dohled kanálu
                                        "
                                    ></v-switch>
                                </v-col>
                            </v-row>
                            <v-row v-if="dohled == true">
                                <v-col cols="12" sm="6" md="6">
                                    <v-switch
                                        v-model="audioDohled"
                                        label="
                                            Dohled audia
                                        "
                                    ></v-switch>
                                </v-col>
                                <v-col cols="12" sm="6" md="6">
                                    <v-switch
                                        v-model="vytvareniNahledu"
                                        label="
                                            Vytváření náhledu
                                        "
                                    ></v-switch>
                                </v-col>
                            </v-row>
                            <v-row v-if="dohled == true">
                                <v-col cols="12" sm="6" md="6">
                                    <v-switch
                                        v-model="emailAlert"
                                        label="
                                            Zasílání e-mail alertu
                                        "
                                    ></v-switch>
                                </v-col>
                                <v-col cols="12" sm="6" md="6">
                                    <v-switch
                                        v-model="smsAlert"
                                        label="
                                            Zasílání sms alertu
                                        "
                                    ></v-switch>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            @click="closeCreateDialog()"
                            color="red darken-1"
                            text
                        >
                            Zavřít
                        </v-btn>
                        <v-btn
                            @click="createStream()"
                            color="green darken-1"
                            text
                        >
                            Vytvořit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
        <!-- end create dialog -->

        <!-- edit dialog -->
        <v-row justify="center">
            <v-dialog v-model="editDialog" persistent max-width="600px">
                <v-card>
                    <v-card-title>
                        <span class="headline text-center"
                            >Editace streamu {{ stream_nazev }}</span
                        >
                    </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12" sm="6" md="6">
                                    <v-text-field
                                        autofocus
                                        label="Název kanálu"
                                        v-model="stream_nazev"
                                        required
                                    ></v-text-field>
                                </v-col>
                                <v-col cols="12" sm="6" md="6">
                                    <v-text-field
                                        v-model="stream_url"
                                        label="Url"
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" sm="6" md="12">
                                    <v-switch
                                        v-model="dohled"
                                        label="
                                            Dohled kanálu
                                        "
                                    ></v-switch>
                                </v-col>
                            </v-row>
                            <v-row v-if="dohled == true">
                                <v-col cols="12" sm="6" md="6">
                                    <v-switch
                                        v-model="audioDohled"
                                        label="
                                            Dohled audia
                                        "
                                    ></v-switch>
                                </v-col>
                                <v-col cols="12" sm="6" md="6">
                                    <v-switch
                                        v-model="vytvareniNahledu"
                                        label="
                                            Vytváření náhledu
                                        "
                                    ></v-switch>
                                </v-col>
                            </v-row>
                            <v-row v-if="dohled == true">
                                <v-col cols="12" sm="6" md="6">
                                    <v-switch
                                        v-model="emailAlert"
                                        label="
                                            Zasílání e-mail alertu
                                        "
                                    ></v-switch>
                                </v-col>
                                <v-col cols="12" sm="6" md="6">
                                    <v-switch
                                        v-model="smsAlert"
                                        label="
                                            Zasílání sms alertu
                                        "
                                    ></v-switch>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="red darken-1"
                            text
                            @click="closeEditDialog()"
                        >
                            Zavřít
                        </v-btn>
                        <v-btn
                            color="green darken-1"
                            text
                            @click="saveEditDialog()"
                        >
                            Editovat
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>

        <!-- konec edit dialogu -->

        <!-- delete dialog -->

        <v-row justify="center">
            <v-dialog v-model="deleteDialog" persistent max-width="600px">
                <v-card>
                    <v-card-title>
                        <span class="headline text-center"
                            >Smazat {{ stream_nazev }} ?</span
                        >
                    </v-card-title>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="red darken-1"
                            text
                            @click="closeDeleteDialog()"
                        >
                            Zavřít
                        </v-btn>
                        <v-btn
                            color="green darken-1"
                            text
                            @click="sendDelete()"
                        >
                            Smazat
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>

        <!-- konec delete dialogu -->
    </div>
</template>

<script>
import AlertComponent from "../AlertComponent";
export default {
    data() {
        return {
            streamUrl: null,
            loadingCreateBtn: false,
            channelsFromDoku: null,
            createDialog: false,
            deleteDialog: false,
            status: null,
            streamId: null,
            stream_nazev: null,
            stream_url: null,
            dohled: null,
            audioDohled: null,
            vytvareniNahledu: null,
            emailAlert: null,
            smsAlert: null,
            editDialog: false,
            search: "",
            streams: [],
            streamHeader: [
                {
                    text: "Název",
                    align: "start",
                    value: "nazev"
                },
                { text: "Uri", value: "stream_url" },
                { text: "Dohled", value: "dohledovano" },
                { text: "Dohled audia", value: "dohledVolume" },
                { text: "Tvorba náhledu", value: "vytvaretNahled" },
                { text: "E-mail alert", value: "sendMailAlert" },
                { text: "SMS alert", value: "sendSmsAlert" },
                { text: "Status", value: "status" },
                { text: "Akce", value: "akce" }
            ]
        };
    },
    components: {
        "alert-component": AlertComponent
    },
    created() {
        this.loadStreams();
    },

    computed: {
        iptvDokuConnectionStatus() {
            return this.$store.state.iptvDokuConnectionStatus;
        }
    },
    methods: {
        OpenCreateDialog() {
            this.loadingCreateBtn = true;
            if (this.iptvDokuConnectionStatus === "success") {
                window.axios
                    .get("api/iptvdoku/get/streams_for_monitoring")
                    .then(response => {
                        this.channelsFromDoku = response.data;
                        this.createDialog = true;
                        this.loadingCreateBtn = false;
                        this.dohled = false;
                        this.audioDohled = false;
                        this.vytvareniNahledu = false;
                        this.emailAlert = false;
                        this.smsAlert = false;
                    });
            } else {
                this.createDialog = true;
                this.loadingCreateBtn = false;
                this.dohled = false;
                this.audioDohled = false;
                this.vytvareniNahledu = false;
                this.emailAlert = false;
                this.smsAlert = false;
            }
        },
        closeCreateDialog() {
            this.createDialog = false;
            this.channelsFromDoku = null;
        },
        createStream() {
            let currentObj = this;
            axios
                .post("stream/add", {
                    stream_nazev: this.stream_nazev,
                    streamUrl: this.streamUrl,
                    dohled: this.dohled,
                    audioDohled: this.audioDohled,
                    vytvareniNahledu: this.vytvareniNahledu,
                    emailAlert: this.emailAlert,
                    smsAlert: this.smsAlert
                })
                .then(function(response) {
                    if (response.data.status == "success") {
                        currentObj.status = response.data;
                        currentObj.createDialog = false;
                        currentObj.returnToDefalt();
                        currentObj.loadStreams();
                        setTimeout(function() {
                            currentObj.status = null;
                        }, 5000);
                    } else {
                        currentObj.status = response.data;
                        setTimeout(function() {
                            currentObj.status = null;
                        }, 5000);
                    }
                });
        },
        loadStreams() {
            // get req
            window.axios.get("streams").then(response => {
                this.streams = response.data;
            });
        },
        // fn pro crácení hodnot do zakladu
        returnToDefalt() {
            (this.streamId = null),
                (this.stream_nazev = null),
                (this.stream_url = null),
                (this.streamUrl = null),
                (this.dohled = null),
                (this.audioDohled = null),
                (this.vytvareniNahledu = null),
                (this.emailAlert = null),
                (this.smsAlert = null);
        },

        // fn pro otevření dialogu pro editaci streamu + načtení informací o daném streamu
        openEditDialog(nazev, streamUrl) {
            this.editDialog = true;
        },
        closeEditDialog() {
            this.editDialog = false;
            this.returnToDefalt();
        },
        saveEditDialog() {
            let currentObj = this;
            axios
                .post("stream/edit", {
                    streamId: this.streamId,
                    nazev: this.stream_nazev,
                    stream_url: this.stream_url,
                    dohledovano: this.dohled,
                    dohledVolume: this.audioDohled,
                    vytvaretNahled: this.vytvareniNahledu,
                    sendMailAlert: this.emailAlert,
                    sendSmsAlert: this.smsAlert
                })
                .then(function(response) {
                    currentObj.status = response.data;
                    currentObj.editDialog = false;
                    currentObj.loadStreams();

                    setTimeout(function() {
                        currentObj.status = null;
                    }, 5000);
                });
        },

        // delete
        openDeleteDialog() {
            this.deleteDialog = true;
        },

        closeDeleteDialog() {
            this.deleteDialog = false;
            this.returnToDefalt();
        },

        sendDelete() {
            let currentObj = this;
            axios
                .post("stream/delete", {
                    streamId: this.streamId
                })
                .then(function(response) {
                    currentObj.status = response.data;
                    currentObj.deleteDialog = false;
                    currentObj.loadStreams();

                    setTimeout(function() {
                        currentObj.status = null;
                    }, 5000);
                });
        }
    },
    watch: {
        // vyhledání názvu streamu
        streamUrl() {
            let currentObj = this;
            this.channelsFromDoku.forEach(function(element) {
                if (element.url == currentObj.streamUrl) {
                    return (currentObj.stream_nazev = element.nazev);
                }
            });
        }
    }
};
</script>
