<template>
    <v-main class="ml-12">
        <v-container>
            <v-col cols="12" sm="12" md="12" lg="12">
                <v-alert type="info" text outlined>
                    Zde si můžete přidat na jaké adresy se mají zasílat
                    notifikace o streamech
                </v-alert>
            </v-col>
            <v-col cols="12" sm="12" md="12" lg="12">
                <v-card flat>
                    <v-toolbar color="transparent" flat>
                        <v-spacer> </v-spacer>
                        <v-btn
                            outlined
                            text
                            type="submit"
                            small
                            color="success"
                            @click="openCreateDialog()"
                            >Založit novou adresu
                        </v-btn>
                    </v-toolbar>
                    <div v-if="items != null">
                        <v-virtual-scroll
                            :bench="benched"
                            :items="items"
                            height="260"
                            item-height="64"
                            class="mb-12"
                        >
                            <!-- <v-list two-line subheader dense> -->
                            <template v-slot:default="{ item }">
                                <v-list-item>
                                    <v-list-item-avatar>
                                        <v-icon>
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
                                                        v-if="
                                                            item.channels ===
                                                                'yes'
                                                        "
                                                    >
                                                        <v-icon
                                                            small
                                                            color="success"
                                                        >
                                                            mdi-check
                                                        </v-icon>
                                                    </span>
                                                    <span v-else>
                                                        <v-icon
                                                            small
                                                            color="error"
                                                        >
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
                                                        <v-icon
                                                            small
                                                            color="success"
                                                        >
                                                            mdi-check
                                                        </v-icon>
                                                    </span>
                                                    <span v-else>
                                                        <v-icon
                                                            small
                                                            color="error"
                                                        >
                                                            mdi-close
                                                        </v-icon>
                                                    </span>
                                                </div>
                                            </v-row>
                                        </v-list-item-subtitle>
                                    </v-list-item-content>
                                    <v-list-item-action>
                                        <v-btn
                                            icon
                                            @click="deleteEmail(item.id)"
                                        >
                                            <v-icon small color="red"
                                                >mdi-delete</v-icon
                                            >
                                        </v-btn>
                                    </v-list-item-action>
                                </v-list-item>
                                <v-divider inset></v-divider>
                                <!-- </v-list> -->
                            </template>
                        </v-virtual-scroll>
                    </div>
                    <div v-else class="text-center">
                        <v-card-subtitle>
                            Zatím není založen žádný email u Vašeho účtu
                        </v-card-subtitle>
                    </div>
                </v-card>
            </v-col>
            <!-- dialog -->
            <template>
                <v-row justify="center">
                    <v-dialog v-model="createDialog" persistent max-width="800">
                        <v-card>
                            <v-card-text>
                                <v-container>
                                    <v-col cols="12" sm="12" md="12" lg="12">
                                        <v-text-field
                                            autofocus
                                            v-model="newEmail"
                                            label="Emailová adresa"
                                            required
                                        ></v-text-field>
                                    </v-col>

                                    <v-col cols="12" sm="12" md="12" lg="12">
                                        <v-switch
                                            v-model="streamAlerts"
                                            label="Zasílání alertů při výpadku kanálu"
                                        ></v-switch>
                                    </v-col>

                                    <v-col cols="12" sm="12" md="12" lg="12">
                                        <v-switch
                                            v-model="streamAlertsIssue"
                                            label="Zasílání alertů při problému s kanálem"
                                        ></v-switch>
                                    </v-col>
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
                                    :loading="loading"
                                    color="green darken-1"
                                    text
                                    outlined
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
export default {
    data: () => ({
        loading: false,
        benched: 0,
        status: null,
        streamAlerts: false,
        streamAlertsIssue: false,
        systemAlerts: false,
        createDialog: false,
        newEmail: null,
        items: null
    }),
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
            this.loading = true;
            axios
                .post("notifications/create", {
                    email: this.newEmail,
                    streamAlerts: this.streamAlerts,
                    systemAlerts: this.systemAlerts,
                    streamAlertsIssue: this.streamAlertsIssue
                })
                .then(response => {
                    this.loading = false;
                    this.$store.state.alerts = response.data.alert;
                    this.loadAccountAlerts();
                    this.createDialog = false;
                    this.newEmail = null;
                    this.streamAlerts = false;
                    this.systemAlerts = false;
                    this.streamAlertsIssue = false;
                });
        },
        deleteEmail(id) {
            axios
                .post("notifications/delete", {
                    emailId: id
                })
                .then(response => {
                    this.$store.state.alerts = response.data.alert;
                    this.loadAccountAlerts();
                });
        }
    }
};
</script>
