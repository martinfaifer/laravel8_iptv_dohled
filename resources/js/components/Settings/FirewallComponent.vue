<template>
    <div>
        <div>
            <alert-component
                v-if="status != null"
                :status="status"
            ></alert-component>
        </div>
        <v-container  fluid>
            <v-alert class="mt-6" v-if="firewallStatus == false" dense text type="warning">
                <strong>Firewall není aktivní</strong>
            </v-alert>
            <v-alert v-else dense class="mt-6" text type="success">
                <strong>Firewall je aktivní</strong>
            </v-alert>
        </v-container>
        <v-container>
            <v-row>
                <!-- Logy -->

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

                <!-- Povolená IP -->
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
                <!-- Stav Firewallu -->

                <v-row>
                    <v-card color="transparent" class="elevation-0 body-2">
                        <v-switch v-model="firewallStatus" label="Stav Firewallu"></v-switch>
                    </v-card>
                </v-row>
            </v-row>
        </v-container>

        <!-- dialogs -->
    </div>
</template>

<script>
import AlertComponent from "../AlertComponent";
export default {
    data() {
        return {
            status: null,
            firewallStatus: false,
            searchLog: "",
            firewallLogs: null,
            firewallLogHeader: [
                {
                    text: "IP",
                    align: "start",
                    value: "ip"
                },

                { text: "Vytvořeno", value: "crated_at" }
            ]
        };
    },
    components: {
        "alert-component": AlertComponent
    },
    created() {
        this.lodFirewallLogs();
    },
    methods: {
        lodFirewallLogs() {
            window.axios.get("firewall/logs").then(response => {
                this.firewallLogs = response.data;
            });
        }
    }
};
</script>
