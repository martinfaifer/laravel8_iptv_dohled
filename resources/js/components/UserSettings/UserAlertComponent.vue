<template>
    <v-main>
        <div>
            <alert-component
                transition="scroll-y-transition"
                v-if="status != null"
                :status="status"
            ></alert-component>
        </div>
        <v-container fluid>
            <v-alert type="info" text width="80%">
                Zde si můžete přidat na jaké adresy se mají zasílat notifikace o
                streamech
            </v-alert>
            <v-card flat width="80%">
                <v-toolbar color="transparent" flat>
                    <v-spacer> </v-spacer>
                    <v-btn
                        type="submit"
                        small
                        color="success"
                        @click="openCreateDialog()"
                        >Založit novou adresu
                    </v-btn>
                </v-toolbar>
                <div v-if="items != null">
                    <v-list two-line subheader dense>
                        <v-list-item v-for="item in items" :key="item.id">
                            <v-list-item-avatar color="#1E1E1E">
                                <v-icon dark>
                                    mdi-email
                                </v-icon>
                            </v-list-item-avatar>

                            <v-list-item-content>
                                <v-list-item-title class="ml-3">
                                    <strong>
                                        {{ item.email }}
                                    </strong>
                                </v-list-item-title>
                                <v-list-item-subtitle class="ml-3 mt-1">
                                    <v-row class="ml-3">
                                        <div>
                                            Výpadky kanálu:
                                            <span
                                                v-if="item.channels === 'yes'"
                                            >
                                                <v-icon small color="success">
                                                    mdi-check
                                                </v-icon>
                                            </span>
                                            <span v-else>
                                                <v-icon small color="error">
                                                    mdi-close
                                                </v-icon>
                                            </span>
                                        </div>
                                        <div class="ml-6">
                                            Problémy s kanálem:
                                            <span
                                                v-if="
                                                    item.channels_issues ===
                                                        'yes'
                                                "
                                            >
                                                <v-icon small color="success">
                                                    mdi-check
                                                </v-icon>
                                            </span>
                                            <span v-else>
                                                <v-icon small color="error">
                                                    mdi-close
                                                </v-icon>
                                            </span>
                                        </div>
                                    </v-row>
                                </v-list-item-subtitle>
                            </v-list-item-content>
                            <v-list-item-action>
                                <v-btn icon  @click="deleteEmail(item.id)">
                                    <v-icon small color="red">mdi-delete</v-icon>
                                </v-btn>
                            </v-list-item-action>
                        </v-list-item>
                    </v-list>
                </div>
                <div v-else class="text-center">
                    <v-card-subtitle>
                        Zatím není založen žádný email u Vašeho účtu
                    </v-card-subtitle>
                </div>
            </v-card>

            <!-- dialog -->
            <template>
                <v-row justify="center">
                    <v-dialog v-model="createDialog" persistent max-width="600">
                        <v-card>
                            <v-card-text>
                                <v-container>
                                    <v-row>
                                        <v-col cols="12" sm="12" md="12">
                                            <v-text-field
                                                autofocus
                                                v-model="newEmail"
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
                                    @click="saveCreateDialog()"
                                >
                                    Uložit
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-dialog>
                </v-row>
            </template>
        </v-container>
    </v-main>
</template>
<script>
import AlertComponent from "../AlertComponent";
export default {
    data: () => ({
        status: null,
        streamAlerts: false,
        streamAlertsIssue: false,
        systemAlerts: false,
        createDialog: false,
        newEmail: null,
        items: null
        // items: [
        //     {
        //         title: "Brunch this weekend?"
        //     },
        //     { divider: true, inset: true },
        //     {
        //         title:
        //             'Summer BBQ <span class="grey--text text--lighten-1">4</span>'
        //     },
        //     { divider: true, inset: true },
        //     {
        //         title: "Oui oui"
        //     },
        //     { divider: true, inset: true },
        //     {
        //         title: "Birthday gift"
        //     },
        //     { divider: true, inset: true },
        //     {
        //         title: "Recipe to try"
        //     }
        // ]
    }),
    components: {
        "alert-component": AlertComponent
    },
    created() {
        this.loadAccountAlerts();
    },
    methods: {
        loadAccountAlerts() {
            axios.get("user/notificationAccounts").then(response => {
                if (response.data.status === "success") {
                    this.items = response.data.data;
                } else {
                    this.items = null;
                }
            });
        },

        openCreateDialog() {
            this.createDialog = true;
        },

        saveCreateDialog() {
            let currentObj = this;
            axios
                .post("notifications/create", {
                    email: this.newEmail,
                    streamAlerts: this.streamAlerts,
                    systemAlerts: this.systemAlerts,
                    streamAlertsIssue: this.streamAlertsIssue
                })
                .then(function(response) {
                    if (response.data.status == "success") {
                        currentObj.status = response.data;
                        currentObj.createDialog = false;
                        // currentObj.returnToDefalt();
                        currentObj.loadAccountAlerts();
                        currentObj.newEmail = null;
                        currentObj.streamAlerts = false;
                        currentObj.systemAlerts = false;
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
        deleteEmail(id) {
            let currentObj = this;
            axios
                .post("notifications/delete", {
                    emailId: id
                })
                .then(function(response) {
                    if (response.data.status == "success") {
                        currentObj.status = response.data;
                        currentObj.loadAccountAlerts();
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
