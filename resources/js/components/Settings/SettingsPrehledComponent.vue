<template>
    <v-main class="mt-12">
        <v-container>
            <v-row>
                <cpu-component></cpu-component>
                <ram-component></ram-component>
                <swap-component></swap-component>
                <hdd-component></hdd-component>
                <v-spacer></v-spacer>
                <workingstreams-component></workingstreams-component>
                <firewallstatus-component></firewallstatus-component>
            </v-row>
        </v-container>
        <v-divider class="mt-2 mb-2"></v-divider>
        <v-container fluid>
            <v-row class="ml-12">
                <!--  -->
                <serverinformation-component></serverinformation-component>
                <v-row>
                    Stav aktualně nefunkční streamy ...
                </v-row>
                <v-row>
                    neco zde bude ...
                </v-row>
            </v-row>
        </v-container>
    </v-main>
</template>
<script>
import CpuComponent from "./_SystemPrehled/CpuComponent";
import RamComponent from "./_SystemPrehled/RamComponent";
import SwapComponent from "./_SystemPrehled/SwapComponent";
import HddComponent from "./_SystemPrehled/HddComponent";
import UptimeComponent from "./_SystemPrehled/UptimeComponent";

// firewall
import FirewallStatusComponent from "./_SystemPrehled/FirewallComponent";
// stream blok
import WorkingStreamsComponent from "./_SystemPrehled/_StreamsPrehled/WorkingStreamsComponent";
// serverInformation
import ServerInformationComponent from "./_SystemPrehled/ServerInformationComponent";
export default {
    data() {
        return {};
    },

    created() {},
    components: {
        "cpu-component": CpuComponent,
        "uptime-component": UptimeComponent,
        "ram-component": RamComponent,
        "swap-component": SwapComponent,
        "hdd-component": HddComponent,
        "workingstreams-component": WorkingStreamsComponent,
        "serverinformation-component": ServerInformationComponent,
        "firewallstatus-component": FirewallStatusComponent
    },
    methods: {},

    mounted() {},
    watch: {}
};
</script>
