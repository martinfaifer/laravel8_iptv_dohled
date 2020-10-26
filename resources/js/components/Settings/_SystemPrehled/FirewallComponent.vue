<template>
    <div>
        <div>
            <v-row class="mr-12 mt-2 mb-1">
                <v-avatar size="100" color="#2F363F" >
                    <v-icon v-if="firewallStatus === 'running'" x-large color="success">
                        mdi-shield-outline
                    </v-icon>
                    <v-icon v-else x-large color="red">
                        mdi-shield-outline
                    </v-icon>
                </v-avatar>
            </v-row>
            <span class="mt-12 ml-2">
                <span class="body-1 mt-12 ">Firewall</span>
            </span>
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
              return this.firewallStatus
          }
    },
};
</script>
