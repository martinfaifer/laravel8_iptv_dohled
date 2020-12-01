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
        <!-- <v-container fluid>
            <ramareachart-component></ramareachart-component>
        </v-container> -->
        <v-divider class="mt-2 mb-2"></v-divider>
        <v-container fluid>
            <v-row class="ml-12">
                <!--  -->
                <serverinformation-component></serverinformation-component>
                <v-row>
                    <crashedorissuedstreams-component></crashedorissuedstreams-component>
                </v-row>
                <v-row>
                    <streamhistory-component></streamhistory-component>
                </v-row>
            </v-row>
        </v-container>
        <v-row class="ml-12" v-if="userRole == '1'">
            <!-- admin information  -->
            <!-- informace o běžících procesech , queue a pod -->
            <admininformation-component></admininformation-component>
        </v-row>
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
// notifikace nefunkčních streamů
import CrashedOrIssuedStreamsComponent from "./_SystemPrehled/_StreamsPrehled/CrashedOrIssuedStreamsComponent";
// historie streamů
import StreamHistoryComponent from "./_SystemPrehled/_StreamsPrehled/StreamsHistoryComponent";
// admin zona
import AdminInformationComponent from "./_SystemPrehled/AdminInformationComponent";

// ram area chart
import RamAreaChartComponent from "./_SystemPrehled/RamAreaChartComponent";

export default {
    data() {
        return { userRole: null };
    },

    created() {
        this.loadUser();
    },
    components: {
        "cpu-component": CpuComponent,
        "uptime-component": UptimeComponent,
        "ram-component": RamComponent,
        "swap-component": SwapComponent,
        "hdd-component": HddComponent,
        "workingstreams-component": WorkingStreamsComponent,
        "serverinformation-component": ServerInformationComponent,
        "firewallstatus-component": FirewallStatusComponent,
        "crashedorissuedstreams-component": CrashedOrIssuedStreamsComponent,
        "streamhistory-component": StreamHistoryComponent,
        "admininformation-component": AdminInformationComponent,
        "ramareachart-component": RamAreaChartComponent
    },
    methods: {
        loadUser() {
            let currentObj = this;
            window.axios.get("user").then(response => {
                currentObj.userRole = response.data.role_id;
            });
        }
    },

    mounted() {},
    watch: {}
};
</script>
