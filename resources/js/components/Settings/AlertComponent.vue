<template>
    <div>
        <div>
            <alert-component
                v-if="status != null"
                :status="status"
            ></alert-component>
        </div>
        <v-container>
            <!-- Soupis e-mailů, na které se zasílají alerty -->

            <v-card color="transparent" class="elevation-0 body-2">
                <v-card-title>
                    <v-text-field
                        dense
                        v-model="search"
                        append-icon="mdi-magnify"
                        label="Hledat ..."
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
                    v-if="emails === null"
                    dense
                    loading
                    loading-text="Načítají se data"
                >
                </v-data-table>
                <v-data-table
                    v-if="emails.status === 'empty'"
                    dense
                    no-data-text="Nejsou zde žádná data"
                >
                </v-data-table>
                <v-data-table
                    v-else
                    dense
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
                            @click="createDialog = false"
                        >
                            Zavřít
                        </v-btn>
                        <v-btn
                            color="green darken-1"
                            text
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
                            @click="closeEditDialog()"
                        >
                            Zavřít
                        </v-btn>
                        <v-btn color="green darken-1" text @click="sendEdit()">
                            Upravit
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>

        <!-- konec edit dialogu -->
    </div>
</template>

<script>
import AlertComponent from "../AlertComponent";
export default {
    data() {
        return {
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

                { text: "Zasílat alerty kanálů", value: "channels" },
                {
                    text: "Zasílat alerting s problémy u streamů",
                    value: "channels_issues"
                },
                { text: "Zasílat systémové alerty", value: "system" },
                { text: "Akce", value: "Akce" }
            ]
        };
    },
    components: {
        "alert-component": AlertComponent
    },
    created() {
        this.loadEmails();
    },
    methods: {
        closeEditDialog() {
            this.editDialog = false;
        },
        loadEmails() {
            window.axios.get("notifications/mails").then(response => {
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
            let currentObj = this;
            axios
                .post("notifications/create", {
                    email: this.email,
                    streamAlerts: this.streamAlerts,
                    systemAlerts: this.systemAlerts,
                    streamAlertsIssue: this.streamAlertsIssue
                })
                .then(function(response) {
                    if (response.data.status == "success") {
                        currentObj.status = response.data;
                        currentObj.createDialog = false;
                        // currentObj.returnToDefalt();
                        currentObj.loadEmails();
                        (currentObj.email = null),
                            (currentObj.streamAlerts = false),
                            (currentObj.systemAlerts = false);
                        currentObj.streamAlertsIssue = false;
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
        closeCreateDialog() {
            this.createDialog = false;
            (this.email = null),
                (this.streamAlerts = false),
                (this.systemAlerts = false);
        },
        deleteNotification(id) {
            let currentObj = this;
            axios
                .post("notifications/delete", {
                    emailId: id
                })
                .then(function(response) {
                    if (response.data.status == "success") {
                        currentObj.status = response.data;
                        currentObj.createDialog = false;
                        currentObj.loadEmails();
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
        openEditDialog() {
            this.editDialog = true;
        },
        closeCreateDialog() {
            this.editDialog = false;
            this.email = null;
        },
        sendEdit() {
            let currentObj = this;
            axios
                .post("notifications/edit", {
                    emailId: this.emailId,
                    email: this.email,
                    streamAlerts: this.streamAlerts,
                    systemAlerts: this.systemAlerts,
                    streamAlertsIssue: this.streamAlertsIssue
                })
                .then(function(response) {
                    if (response.data.status == "success") {
                        currentObj.status = response.data;
                        currentObj.editDialog = false;
                        currentObj.loadEmails();
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
        }
    }
};
</script>
