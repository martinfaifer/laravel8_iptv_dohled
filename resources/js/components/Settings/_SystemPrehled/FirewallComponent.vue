<template>
    <div>
        <div>
            <v-card
                class="mx-auto text-center"
                flat
                color="#218F76"
                dark
                width="300"
            >
                <v-card-text>
                    <v-container fluid>
                        <span class="md-6 body-2">
                            <strong>
                                Firewall
                            </strong>
                        </span>
                        <v-row no-gutters>
                            <v-col class="col-12">
                                <v-sheet
                                    class="ml-6"
                                    color="transparent"
                                    width="80%"
                                >
                                    <v-icon
                                        v-if="firewallStatus === 'running'"
                                        x-large
                                        color="success"
                                    >
                                        mdi-shield-outline
                                    </v-icon>
                                    <v-icon v-else x-large color="#AE1438">
                                        mdi-shield-outline
                                    </v-icon>
                                </v-sheet>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>
            </v-card>
        </div>
    </div>
</template>
<script>
export default {
    data() {
        return {
            firewallStatus: null
        };
    },

    components: {},
    created() {
        this.loadFirewallStatus();
    },

    methods: {
        loadFirewallStatus() {
            window.axios.get("firewall/status").then(response => {
                this.firewallStatus = response.data.status;
            });
        }
    },
    mounted() {
        this.interval = setInterval(
            function() {
                this.loadFirewallStatus();
            }.bind(this),
            20000
        );
    },
    beforeDestroy: function() {
        clearInterval(this.interval);
    },
    watch: {
        firewallStatus() {
            return this.firewallStatus;
        }
    }
};
</script>
