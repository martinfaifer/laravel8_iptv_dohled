<template>
    <div>
        <div>
            <alert-component
                v-if="status != null"
                :status="status"
            ></alert-component>
        </div>
        <v-container fluid>
            <v-alert
                class="mt-6"
                v-if="firewallStatus == false"
                dense
                text
                type="warning"
            >
                <strong>Firewall není aktivní</strong>
            </v-alert>
            <v-alert v-else dense class="mt-6" text type="success">
                <strong>Firewall je aktivní</strong>
            </v-alert>
        </v-container>
        <v-container>
            <v-row>
                <!-- Logy -->
                <v-row>
                    <v-card color="transparent" class="elevation-0 body-2">
                        <v-card-title>
                            <v-text-field
                                dense
                                v-model="searchLog"
                                append-icon="mdi-magnify"
                                label="Hledat v logách ..."
                                single-line
                                hide-details
                            ></v-text-field>
                        </v-card-title>
                        <v-data-table
                            v-if="firewallLogs == null"
                            dense
                            class="elevation-0"
                            loading
                            loading-text="Načítám"
                        ></v-data-table>
                        <v-data-table
                            v-else-if="firewallLogs.status == 'empty'"
                            dense
                            class="elevation-0"
                            loading-text="Žádná data"
                        ></v-data-table>
                        <v-data-table
                            v-else
                            dense
                            :headers="firewallLogHeader"
                            :items="firewallLogs"
                            :search="searchLog"
                        >
                        </v-data-table>
                    </v-card>
                </v-row>
                <!-- Povolená IP -->
                <v-row>
                    <v-card color="transparent" class="elevation-0 body-2">
                        <v-card-title>
                            <v-text-field
                                dense
                                v-model="searchAllowedIp"
                                append-icon="mdi-magnify"
                                label="Hledat v povolených IP ..."
                                single-line
                                hide-details
                            ></v-text-field>
                        </v-card-title>
                        <v-data-table
                            v-if="firewallAllowedIps == null"
                            dense
                            class="elevation-0"
                            loading
                            loading-text="Načítám"
                        >
                            <template v-slot:item.Akce="{ item }">
                                <!-- delete -->
                                <v-icon
                                    @click="
                                        openDeleteDialog(
                                            (allowedIPid = item.id)
                                        )
                                    "
                                    small
                                    color="red"
                                    >mdi-delete</v-icon
                                >
                            </template>
                        </v-data-table>
                        <v-data-table
                            v-else-if="firewallAllowedIps.status == 'empty'"
                            dense
                            class="elevation-0"
                            loading-text="Žádná data"
                        >
                            <template v-slot:item.Akce="{ item }">
                                <!-- delete -->
                                <v-icon
                                    @click="
                                        openDeleteDialog(
                                            (allowedIPid = item.id)
                                        )
                                    "
                                    small
                                    color="red"
                                    >mdi-delete</v-icon
                                >
                            </template>
                        </v-data-table>

                        <v-data-table
                            v-else
                            dense
                            :headers="firewallAllowdIpsHeader"
                            :items="firewallAllowedIps"
                            :search="searchAllowedIp"
                        >
                            <template v-slot:item.Akce="{ item }">
                                <!-- delete -->
                                <v-icon
                                    @click="
                                        openDeleteDialog(
                                            (allowedIPid = item.id)
                                        )
                                    "
                                    small
                                    color="red"
                                    >mdi-delete</v-icon
                                >
                            </template>
                        </v-data-table>
                    </v-card>
                </v-row>
                <!-- Stav Firewallu -->

                <v-row>
                    <v-card color="transparent" class="elevation-0 body-2">
                        <v-switch
                            @change="changeFirewallStatus()"
                            v-model="firewallStatus"
                            label="Stav Firewallu"
                        ></v-switch>
                    </v-card>
                </v-row>
            </v-row>
        </v-container>

        <!-- dialogs -->

        <v-row justify="center">
            <v-dialog v-model="deleteDialog" persistent max-width="600px">
                <v-card>
                    <v-card-title>
                        <span class="headline text-center">Odebrat IP?</span>
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
        <!-- end dialogs -->
    </div>
</template>

<script>
import AlertComponent from "../AlertComponent";
export default {
    data() {
        return {
            deleteDialog: false,
            allowedIPid: null,
            searchAllowedIp: "",
            status: null,
            firewallStatus: false,
            searchLog: "",
            firewallLogs: null,
            firewallAllowedIps: null,
            firewallLogHeader: [
                {
                    text: "IP",
                    align: "start",
                    value: "ip"
                },

                { text: "Vytvořeno", value: "created_at" },
                { text: "Akce", value: "Akce" }
            ],
            firewallAllowdIpsHeader: [
                {
                    text: "IP",
                    align: "start",
                    value: "allowed_ip"
                },

                { text: "Vytvořeno", value: "created_at" },
                { text: "Akce", value: "Akce" }
            ]
        };
    },
    components: {
        "alert-component": AlertComponent
    },
    created() {
        this.lodFirewallLogs();
        this.loadFirewallStatus();
        this.loadFirewallAllowedIps();
    },
    methods: {
        lodFirewallLogs() {
            window.axios.get("firewall/logs").then(response => {
                this.firewallLogs = response.data;
            });
        },

        loadFirewallStatus() {
            window.axios.get("firewall/status").then(response => {
                if (response.data.status === "stopped") {
                    this.firewallStatus = false;
                } else {
                    this.firewallStatus = true;
                }
            });
        },

        loadFirewallAllowedIps() {
            window.axios.get("firewall/ips").then(response => {
                this.firewallAllowedIps = response.data;
            });
        },

        changeFirewallStatus() {
            let currentObj = this;
            axios
                .post("firewall/settings", {
                    firewallStatus: this.firewallStatus
                })
                .then(function(response) {});
        },

        openDeleteDialog() {
            this.deleteDialog = true;
        },
        closeDeleteDialog() {
            this.deleteDialog = false;
            this.allowedIPid = null;
        },
        sendDelete() {
            let currentObj = this;
            axios
                .post("firewall/ip/delete", {
                    allowedIPid: this.allowedIPid
                })
                .then(function(response) {
                    currentObj.status = response.data;
                    currentObj.deleteDialog = false;
                    currentObj.loadFirewallAllowedIps();

                    setTimeout(function() {
                        currentObj.status = null;
                    }, 5000);
                });
        }
    }
};
</script>
