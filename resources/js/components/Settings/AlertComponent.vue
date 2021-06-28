<template>
    <v-main>
        <v-container fluid>
            <v-col cols="12" sm="12" md="12" lg="12">
                <!-- Soupis e-mailů, na které se zasílají alerty -->
                <v-card color="transparent" class="elevation-0 body-2">
                    <v-card-title>
                        <v-text-field
                            v-model="search"
                            prepend-inner-icon="mdi-magnify"
                            label="Hledat e-mailovou adresu..."
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
                            Nový e-mail
                        </v-btn>
                    </v-card-title>
                    <v-data-table
                        v-if="emails === null"
                        :loading="loadingTable"
                        :search="search"
                        loading-text="Načítají se data"
                    >
                    </v-data-table>
                    <v-data-table
                        v-if="emails.status === 'empty'"
                        no-data-text="Nejsou zde žádná data"
                        :loading="loadingTable"
                        :search="search"
                    >
                    </v-data-table>
                    <v-data-table
                        :loading="loadingTable"
                        v-else
                        :headers="header"
                        :items="emails"
                        :search="search"
                    >
                        <template v-slot:item.Akce="{ item }">
                            <!-- edit -->
                            <v-icon
                                @click="
                                    openEditDialog(),
                                        (emailId = item.id),
                                        (email = item.email)
                                "
                                small
                                color="green"
                                class="mr-2"
                                >mdi-pencil</v-icon
                            >
                            <!-- delete -->
                            <v-icon
                                @click="deleteNotification(item.id)"
                                small
                                color="red"
                                >mdi-delete</v-icon
                            >
                        </template>

                        <template v-slot:item.channels="{ item }">
                            <span v-if="item.channels == 'yes'">
                                <v-icon color="success">mdi-check</v-icon>
                            </span>
                            <span v-else>
                                <v-icon color="red">mdi-close</v-icon>
                            </span>
                        </template>

                        <template v-slot:item.channels_issues="{ item }">
                            <span v-if="item.channels_issues == 'yes'">
                                <v-icon color="success">mdi-check</v-icon>
                            </span>
                            <span v-else>
                                <v-icon color="red">mdi-close</v-icon>
                            </span>
                        </template>

                        <template v-slot:item.system="{ item }">
                            <span v-if="item.system == 'yes'">
                                <v-icon color="success">mdi-check</v-icon>
                            </span>
                            <span v-else>
                                <v-icon color="red">mdi-close</v-icon>
                            </span>
                        </template>
                    </v-data-table>
                </v-card>
            </v-col>
            <!-- slack -->
            <v-col cols="12" sm="12" md="12" lg="12">
                <v-card color="transparent" class="elevation-0 body-2">
                    <v-card-title>
                        <v-spacer></v-spacer>
                        <v-btn
                            @click="OpenCreateSlackDialog()"
                            text
                            outlined
                            color="success"
                        >
                            <v-icon left dark>
                                mdi-plus
                            </v-icon>
                            Nový notifikační kanál
                        </v-btn>
                    </v-card-title>
                    <v-data-table :headers="header_slacks" :items="slacks">
                        <template v-slot:item.Akce="{ item }">
                            <v-icon
                                @click="deleteChannel(item.id)"
                                small
                                color="red"
                                >mdi-delete</v-icon
                            >
                        </template>
                    </v-data-table>
                </v-card>
            </v-col>
        </v-container>

        <!-- dialogs -->
        <!-- create dialog -->

        <v-row justify="center">
            <v-dialog v-model="createDialog" persistent max-width="600px">
                <v-card>
                    <v-card-title> </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12" sm="12" md="12">
                                    <v-text-field
                                        autofocus
                                        v-model="email"
                                        label="Emailová adresa"
                                        required
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" sm="12" md="12">
                                    <v-switch
                                        v-model="streamAlerts"
                                        label="Zasílání alertů při výpadku kanálu"
                                    ></v-switch>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" sm="12" md="12">
                                    <v-switch
                                        v-model="streamAlertsIssue"
                                        label="Zasílání alertů při problému s kanálem"
                                    ></v-switch>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" sm="12" md="12">
                                    <v-switch
                                        v-model="systemAlerts"
                                        label="Zasílání alertů na při problémů se systémem"
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
                            outlined
                            @click="createDialog = false"
                        >
                            Zavřít
                        </v-btn>
                        <v-btn
                            color="green darken-1"
                            text
                            outlined
                            @click="sendCreate()"
                        >
                            Vytvořit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
        <!-- konec create dialogu -->

        <!-- edit dialog -->

        <v-row justify="center">
            <v-dialog v-model="editDialog" persistent max-width="600px">
                <v-card>
                    <v-card-title> </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12" sm="12" md="12">
                                    <v-text-field
                                        disabled
                                        readonly
                                        v-model="email"
                                        label="Emailová adresa"
                                        required
                                    ></v-text-field>
                                </v-col>
                            </v-row>

                            <v-row>
                                <v-col cols="12" sm="12" md="12">
                                    <v-switch
                                        v-model="streamAlerts"
                                        label="Zasílání alertů při výpadku kanálu"
                                    ></v-switch>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" sm="12" md="12">
                                    <v-switch
                                        v-model="streamAlertsIssue"
                                        label="Zasílání alertů při problému s kanálem"
                                    ></v-switch>
                                </v-col>
                            </v-row>
                            <v-row>
                                <v-col cols="12" sm="12" md="12">
                                    <v-switch
                                        v-model="systemAlerts"
                                        label="Zasílání alertů na při problémů se systémem"
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
                            outlined
                            @click="closeEditDialog()"
                        >
                            Zavřít
                        </v-btn>
                        <v-btn
                            color="green darken-1"
                            text
                            @click="sendEdit()"
                            outlined
                        >
                            Upravit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>

            <v-dialog v-model="createSlackDialog" persistent max-width="600px">
                <v-card>
                    <v-card-title> </v-card-title>
                    <v-card-text>
                        <v-container>
                            <v-row>
                                <v-col cols="12" sm="12" md="12">
                                    <v-text-field
                                        v-model="slack_channel"
                                        label="Nový channel"
                                        required
                                    ></v-text-field>
                                </v-col>
                            </v-row>
                        </v-container>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            @click="closeDialog()"
                            color="red darken-1"
                            text
                            outlined
                        >
                            Zavřít
                        </v-btn>
                        <v-btn
                            @click="createNewChannel()"
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

        <!-- konec edit dialogu -->
    </v-main>
</template>

<script>
import axios from "axios";
export default {
    data() {
        return {
            createSlackDialog: false,
            slack_channel: null,
            slacks: [],
            loadingTable: true,
            streamAlertsIssue: false,
            editDialog: false,
            alertId: null,
            email: null,
            streamAlerts: false,
            systemAlerts: false,
            createDialog: false,
            loadingCreateBtn: false,
            emailId: null,
            deleteDialog: false,
            emails: [],
            search: "",
            status: null,
            header: [
                {
                    text: "E-mail",
                    align: "start",
                    value: "email"
                },
                { text: "Vlastník", value: "belongsTo" },
                { text: "Zasílat alerty kanálů", value: "channels" },
                {
                    text: "Zasílat alerting s problémy u streamů",
                    value: "channels_issues"
                },
                { text: "Zasílat systémové alerty", value: "system" },
                { text: "Akce", value: "Akce" }
            ],

            header_slacks: [
                {
                    text: "Slack kanál",
                    align: "start",
                    value: "channel"
                },
                { text: "Akce", value: "Akce" }
            ]
        };
    },
    created() {
        this.loadEmails();
        this.loadSlack();
    },
    methods: {
        deleteChannel(id) {
            axios
                .delete("notifications/slack", {
                    data: {
                        id: id
                    }
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                    this.loadSlack();
                });
        },
        createNewChannel() {
            axios
                .post("notifications/slack", {
                    channel: this.slack_channel
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                    this.closeDialog();
                });
        },
        closeDialog() {
            this.slack_channel = null;
            this.createSlackDialog = false;
        },
        OpenCreateSlackDialog() {
            this.slack_channel = null;
            this.createSlackDialog = true;
        },
        loadSlack() {
            axios.get("notifications/slack").then(response => {
                this.slacks = response.data;
            });
        },
        closeEditDialog() {
            this.editDialog = false;
        },
        loadEmails() {
            axios.get("notifications/mails").then(response => {
                this.loadingTable = false;
                this.emails = response.data;
            });
        },
        OpenCreateDialog() {
            this.createDialog = true;
        },
        CloseCreateDialog() {
            this.createDialog = false;
            this.email = false;
            this.streamAlerts = false;
            this.systemAlerts = false;
        },
        sendCreate() {
            axios
                .post("notifications/create", {
                    email: this.email,
                    streamAlerts: this.streamAlerts,
                    systemAlerts: this.systemAlerts,
                    streamAlertsIssue: this.streamAlertsIssue
                })
                .then(response => {
                    this.createDialog = false;
                    this.$store.state.alerts = response.data.alert;
                    this.loadEmails();
                    this.email = null;
                    this.streamAlerts = false;
                    this.systemAlerts = false;
                    this.streamAlertsIssue = false;
                });
        },
        closeCreateDialog() {
            this.createDialog = false;
            (this.email = null),
                (this.streamAlerts = false),
                (this.systemAlerts = false);
        },
        deleteNotification(id) {
            axios
                .post("notifications/delete", {
                    emailId: id
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                    this.createDialog = false;
                    this.loadEmails();
                });
        },
        openEditDialog() {
            this.editDialog = true;
        },
        closeCreateDialog() {
            this.editDialog = false;
            this.email = null;
        },
        sendEdit() {
            axios
                .post("notifications/edit", {
                    emailId: this.emailId,
                    email: this.email,
                    streamAlerts: this.streamAlerts,
                    systemAlerts: this.systemAlerts,
                    streamAlertsIssue: this.streamAlertsIssue
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                    this.editDialog = false;
                    this.loadEmails();
                });
        }
    }
};
</script>
