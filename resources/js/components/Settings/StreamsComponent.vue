<template>
    <v-main>
        <v-container fluid>
            <v-card color="transparent" class="elevation-0 body-2">
                <v-card-title>
                    <v-text-field
                        autofocus
                        v-model="search"
                        prepend-inner-icon="mdi-magnify"
                        label="Vyhledat kanál ..."
                        single-line
                        hide-details
                    ></v-text-field>
                    <v-spacer></v-spacer>
                    <v-btn
                        :loading="loadingCreateBtn"
                        @click="OpenCreateDialog()"
                        text
                        outlined
                        color="success"
                    >
                        <v-icon left dark>
                            mdi-plus
                        </v-icon>
                        Přidat
                    </v-btn>
                </v-card-title>
                <v-data-table
                    :loading="loadingTable"
                    :headers="streamHeader"
                    :items="streams"
                    :search="search"
                >
                    <!-- náhled -->
                    <template v-slot:item.image="{ item }">
                        <v-img
                            v-if="
                                item.is_problem === 0 && item.image != 'false'
                            "
                            height="25"
                            width="45"
                            :lazy-src="item.image"
                            :src="item.image"
                            :aspect-ratio="16 / 9"
                        >
                        </v-img>
                    </template>
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
                    <template v-slot:item.dohledAudia="{ item }">
                        <span v-if="item.dohledAudia == 1">
                            <v-icon color="success">mdi-check</v-icon>
                        </span>
                        <span v-else-if="item.dohledAudia == 0">
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

                    <template v-slot:item.slack="{ item }">
                        <span v-if="item.slack == 1">
                            <v-icon color="success">mdi-check</v-icon>
                        </span>
                        <span v-else-if="item.slack == 0">
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
                        <span v-if="item.status == 'running'">
                            <v-icon color="success">mdi-check</v-icon>
                        </span>
                        <span v-else-if="item.status == 'error'">
                            <v-icon color="red">mdi-close</v-icon>
                        </span>
                        <span v-else-if="item.status == 'stop'">
                            <v-icon color="blue">mdi-stop</v-icon>
                        </span>
                        <span v-else-if="item.status == 'issue'">
                            <v-icon color="orange">mdi-exclamation</v-icon>
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
                                    (audioDohled = item.dohledAudia),
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
            <v-dialog v-model="createDialog" persistent max-width="1000px">
                <v-card>
                    <v-card-title> </v-card-title>
                    <v-card-text>
                        <v-row>
                            <v-col cols="12" sm="12" lg="6" md="6">
                                <v-text-field
                                    autofocus
                                    v-model="stream_nazev"
                                    label="Název streamu"
                                    required
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="12" lg="6" md="6">
                                <v-text-field
                                    v-model="streamUrl"
                                    label="Url dohledovaného streamu"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" sm="12" md="12" lg="12">
                                <v-switch
                                    v-model="dohled"
                                    label="
                                            Dohled streamu
                                        "
                                ></v-switch>
                            </v-col>

                            <v-col
                                cols="12"
                                sm="12"
                                md="12"
                                lg="12"
                                v-if="dohled == true"
                            >
                                <v-switch
                                    v-model="streamIssues"
                                    label="
                                           Notifikace problémových stavu
                                        "
                                ></v-switch>
                            </v-col>

                            <!-- read only video bitrate -->
                            <v-col
                                cols="12"
                                sm="12"
                                md="3"
                                lg="3"
                                v-if="streamIssues == true && dohled == true"
                            >
                                <v-text-field
                                    disabled
                                    v-model="videoBitrateZero"
                                    type="number"
                                    label="notifikace pokud je nulový datový tok ve videu"
                                ></v-text-field>
                            </v-col>
                            <!-- video_discontinuities -->
                            <v-col
                                cols="12"
                                sm="12"
                                md="3"
                                lg="3"
                                v-if="streamIssues == true && dohled == true"
                            >
                                <v-text-field
                                    v-model="video_discontinuities"
                                    type="number"
                                    label="maximální počet CCR erroru ve videu"
                                ></v-text-field>
                            </v-col>
                            <!-- audio_discontinuities -->
                            <v-col
                                cols="12"
                                sm="12"
                                md="3"
                                lg="3"
                                v-if="streamIssues == true && dohled == true"
                            >
                                <v-text-field
                                    v-model="audio_discontinuities"
                                    type="number"
                                    label="maximální počet CCR erroru v audiu"
                                ></v-text-field>
                            </v-col>
                            <!-- audio_scrambled -->
                            <v-col
                                cols="12"
                                sm="12"
                                md="3"
                                lg="3"
                                v-if="streamIssues == true && dohled == true"
                            >
                                <v-text-field
                                    v-model="audio_scrambled"
                                    type="number"
                                    label="Počet scrambled packetů v audiu"
                                ></v-text-field>
                            </v-col>

                            <v-col
                                cols="12"
                                sm="12"
                                md="6"
                                lg="6"
                                v-if="dohled == true"
                            >
                                <v-switch
                                    v-model="audioDohled"
                                    label="
                                            Dohled audia
                                        "
                                ></v-switch>
                            </v-col>
                            <v-col
                                cols="12"
                                sm="12"
                                md="6"
                                lg="6"
                                v-if="dohled == true"
                            >
                                <v-switch
                                    v-model="vytvareniNahledu"
                                    label="
                                            Vytváření náhledu
                                        "
                                ></v-switch>
                            </v-col>

                            <v-col
                                cols="12"
                                sm="12"
                                md="6"
                                lg="6"
                                v-if="dohled == true"
                            >
                                <v-switch
                                    v-model="emailAlert"
                                    label="
                                            Zasílání e-mail notifikací
                                        "
                                ></v-switch>
                            </v-col>
                            <v-col
                                cols="12"
                                sm="12"
                                md="6"
                                lg="6"
                                v-if="dohled == true"
                            >
                                <v-switch
                                    v-model="smsAlert"
                                    label="
                                            Zasílání sms notifikací
                                        "
                                ></v-switch>
                            </v-col>
                        </v-row>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            @click="closeCreateDialog()"
                            color="red darken-1"
                            text
                            outlined
                        >
                            Zavřít
                        </v-btn>
                        <v-btn
                            :loading="isActionButtonLoading"
                            @click="createStream()"
                            color="green darken-1"
                            text
                            outlined
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
            <v-dialog v-model="editDialog" persistent max-width="1000px">
                <v-card>
                    <v-card-title>
                        <span class="headline text-center"
                            >Editace streamu {{ stream_nazev }}</span
                        >
                    </v-card-title>
                    <v-card-text>
                        <v-row>
                            <v-col cols="12" sm="12" md="6" lg="6">
                                <v-text-field
                                    autofocus
                                    label="Název streamu"
                                    v-model="stream_nazev"
                                    required
                                ></v-text-field>
                            </v-col>
                            <v-col cols="12" sm="12" md="6" lg="6">
                                <v-text-field
                                    disabled
                                    v-model="stream_url"
                                    label="Url streamu"
                                ></v-text-field>
                            </v-col>

                            <v-col cols="12" sm="12" md="12" lg="12">
                                <v-switch
                                    v-model="dohled"
                                    label="
                                            Dohled streamu
                                        "
                                ></v-switch>
                            </v-col>

                            <v-col
                                cols="12"
                                sm="12"
                                md="12"
                                lg="12"
                                v-if="dohled == true"
                            >
                                <v-switch
                                    v-model="streamIssues"
                                    label="
                                           Notifikace problémových stavu
                                        "
                                ></v-switch>
                            </v-col>

                            <!-- read only video bitrate -->
                            <v-col
                                cols="12"
                                sm="12"
                                md="3"
                                lg="3"
                                v-if="streamIssues == true && dohled == true"
                            >
                                <v-text-field
                                    disabled
                                    v-model="videoBitrateZero"
                                    type="number"
                                    label="notifikace pokud je nulový datový tok ve videu"
                                ></v-text-field>
                            </v-col>
                            <!-- video_discontinuities -->
                            <v-col
                                cols="12"
                                sm="12"
                                md="3"
                                lg="3"
                                v-if="streamIssues == true && dohled == true"
                            >
                                <v-text-field
                                    v-model="video_discontinuities"
                                    type="number"
                                    label="maximální počet CCR chyb ve videu"
                                ></v-text-field>
                            </v-col>
                            <!-- audio_discontinuities -->
                            <v-col
                                cols="12"
                                sm="12"
                                md="3"
                                lg="3"
                                v-if="streamIssues == true && dohled == true"
                            >
                                <v-text-field
                                    v-model="audio_discontinuities"
                                    type="number"
                                    label="maximální počet CCR chyb v audiu"
                                ></v-text-field>
                            </v-col>
                            <!-- audio_scrambled -->
                            <v-col
                                cols="12"
                                sm="12"
                                md="3"
                                lg="3"
                                v-if="streamIssues == true && dohled == true"
                            >
                                <v-text-field
                                    v-model="audio_scrambled"
                                    type="number"
                                    label="maximální počet scrambled packetů v audiu"
                                ></v-text-field>
                            </v-col>

                            <v-col
                                cols="12"
                                sm="12"
                                md="6"
                                lg="6"
                                v-if="dohled == true"
                            >
                                <v-switch
                                    v-model="audioDohled"
                                    label="
                                            Dohled audia
                                        "
                                ></v-switch>
                            </v-col>
                            <v-col
                                cols="12"
                                sm="12"
                                md="6"
                                lg="6"
                                v-if="dohled == true"
                            >
                                <v-switch
                                    v-model="vytvareniNahledu"
                                    label="
                                            Vytváření náhledu
                                        "
                                ></v-switch>
                            </v-col>

                            <v-col
                                cols="12"
                                sm="12"
                                md="6"
                                lg="6"
                                v-if="dohled == true"
                            >
                                <v-switch
                                    v-model="emailAlert"
                                    label="
                                            Zasílání e-mail notifikací
                                        "
                                ></v-switch>
                            </v-col>
                            <v-col
                                cols="12"
                                sm="12"
                                md="6"
                                lg="6"
                                v-if="dohled == true"
                            >
                                <v-switch
                                    v-model="smsAlert"
                                    label="
                                            Zasílání sms notifikací
                                        "
                                ></v-switch>
                            </v-col>
                        </v-row>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="red darken-1"
                            text
                            outlined
                            @click="closeEditDialog()"
                        >
                            Zavřít
                        </v-btn>
                        <v-btn
                            :loading="isActionButtonLoading"
                            color="green darken-1"
                            text
                            outlined
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
                            :loading="isActionButtonLoading"
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
    </v-main>
</template>

<script>
export default {
    data() {
        return {
            loadingTable: true,
            isActionButtonLoading: false,
            videoBitrateZero: 0,
            video_discontinuities: 2,
            audio_discontinuities: 2,
            audio_scrambled: 2,
            streamIssues: false,
            streamUrl: null,
            loadingCreateBtn: false,
            channelsFromDoku: null,
            createDialog: false,
            deleteDialog: false,
            status: null,
            streamId: null,
            stream_nazev: null,
            stream_url: null,
            dohled: false,
            audioDohled: true,
            vytvareniNahledu: true,
            emailAlert: true,
            smsAlert: false,
            editDialog: false,
            search: "",
            streams: [],
            streamHeader: [
                { text: "Náhled", value: "image" },
                { text: "Název", value: "nazev" },
                { text: "Uri", value: "stream_url" },
                { text: "Dohled", value: "dohledovano" },
                { text: "Dohled audia", value: "dohledAudia" },
                { text: "Tvorba náhledu", value: "vytvaretNahled" },
                { text: "E-mail alert", value: "sendMailAlert" },
                { text: "Slack alert", value: "slack" },
                { text: "SMS alert", value: "sendSmsAlert" },
                { text: "Status", value: "status" },
                { text: "Akce", value: "akce" }
            ]
        };
    },
    created() {
        this.loadStreams();
    },
    methods: {
        OpenCreateDialog() {
            this.createDialog = true;
        },
        closeCreateDialog() {
            this.createDialog = false;
            this.channelsFromDoku = null;
        },
        createStream() {
            this.isActionButtonLoading = true;
            axios
                .post("stream/create", {
                    stream_nazev: this.stream_nazev,
                    streamUrl: this.streamUrl,
                    dohled: this.dohled,
                    dohledAudia: this.audioDohled,
                    vytvareniNahledu: this.vytvareniNahledu,
                    emailAlert: this.emailAlert,
                    smsAlert: this.smsAlert,
                    isDohled: true,
                    video_discontinuities: this.video_discontinuities,
                    audio_discontinuities: this.audio_discontinuities,
                    audio_scrambled: this.audio_scrambled,
                    streamIssues: this.streamIssues
                })
                .then(response => {
                    this.isActionButtonLoading = false;
                    this.$store.state.alerts = response.data.alert;
                    this.createDialog = false;
                    this.returnToDefalt();
                    this.loadStreams();
                });
        },
        loadStreams() {
            axios.get("streams").then(response => {
                this.loadingTable = false;
                this.streams = response.data;
            });
        },
        // fn pro crácení hodnot do zakladu
        returnToDefalt() {
            this.streamId = null;
            this.stream_nazev = null;
            this.stream_url = null;
            this.streamUrl = null;
            this.dohled = false;
            this.audioDohled = true;
            this.vytvareniNahledu = true;
            this.emailAlert = true;
            this.smsAlert = false;
            this.streamIssues = false;
        },

        // fn pro otevření dialogu pro editaci streamu + načtení informací o daném streamu
        openEditDialog(nazev, streamUrl) {
            axios
                .post("stream/issues", {
                    streamId: this.streamId
                })
                .then(response => {
                    this.editDialog = true;
                    if (response.data.status == "empty") {
                        this.streamIssues = false;
                    } else {
                        this.streamIssues = true;
                        this.video_discontinuities =
                            response.data.video_discontinuities;
                        this.audio_discontinuities =
                            response.data.audio_discontinuities;
                        this.audio_scrambled = response.data.audio_scrambled;
                        this.video_discontinuities =
                            response.data.video_discontinuities;
                    }
                });
        },
        closeEditDialog() {
            this.editDialog = false;
            this.returnToDefalt();
        },
        saveEditDialog() {
            this.isActionButtonLoading = true;
            axios
                .post("stream/edit", {
                    streamId: this.streamId,
                    nazev: this.stream_nazev,
                    stream_url: this.stream_url,
                    dohledovano: this.dohled,
                    dohledAudia: this.audioDohled,
                    vytvaretNahled: this.vytvareniNahledu,
                    sendMailAlert: this.emailAlert,
                    sendSmsAlert: this.smsAlert,

                    video_discontinuities: this.video_discontinuities,
                    audio_discontinuities: this.audio_discontinuities,
                    audio_scrambled: this.audio_scrambled,
                    streamIssues: this.streamIssues
                })
                .then(response => {
                    this.isActionButtonLoading = false;
                    this.$store.state.alerts = response.data.alert;
                    this.status = response.data;
                    this.editDialog = false;
                    this.loadStreams();
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
            this.isActionButtonLoading = true;
            axios
                .post("stream/delete", {
                    streamId: this.streamId
                })
                .then(response => {
                    this.isActionButtonLoading = false;
                    this.$store.state.alerts = response.data.alert;
                    this.deleteDialog = false;
                    this.loadStreams();
                });
        }
    },
    watch: {
        // vyhledání názvu streamu
        // streamUrl() {
        //     this.channelsFromDoku.forEach(element => {
        //         if (element.url == this.streamUrl) {
        //             return (this.stream_nazev = element.nazev);
        //         }
        //     });
        // }
    }
};
</script>
