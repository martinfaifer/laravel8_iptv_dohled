<template>
    <v-main class="mt-12">
        <v-row class="mb-3 mt-3 ml-3 mr-3">
            <v-col cols="12" sm="12" md="4" lg="4">
                <ram-component></ram-component>
            </v-col>
            <v-col cols="12" sm="12" md="4" lg="4">
                <hdd-component></hdd-component>
            </v-col>
            <v-col cols="12" sm="12" md="4" lg="4">
                <swap-component></swap-component>
            </v-col>
        </v-row>

        <cpu-component></cpu-component>

        <v-divider class="mt-6 mb-2"></v-divider>
        <v-container fluid>
            <v-row>
                <v-col cols="12" sm="12" md="9" lg="9">
                    <monitoredstreams-component></monitoredstreams-component>
                </v-col>
                <v-col cols="12" sm="12" md="3" lg="3">
                    <donutstreamschart-component></donutstreamschart-component>
                </v-col>
            </v-row>
        </v-container>
        <v-container fluid class="mt-2">
            <v-row>
                <v-col cols="12" sm="12" md="9" lg="9">
                    <!-- <ccerrorstreams-component></ccerrorstreams-component> -->
                </v-col>
                <v-col cols="12" sm="12" md="3" lg="3">
                    <streamhistory-component></streamhistory-component>
                </v-col>
            </v-row>
        </v-container>
    </v-main>
</template>
<script>
import RamComponent from "./_SystemPrehled/RamComponent";
import SwapComponent from "./_SystemPrehled/SwapComponent";
import HddComponent from "./_SystemPrehled/HddComponent";
import CpuComponent from "./_SystemPrehled/CpuComponent.vue";

import MonitoredStreamsComponent from "./_SystemPrehled/_StreamsPrehled/MonitoredStreamsComponent.vue";
import DonutStreamsChartComponent from "./_SystemPrehled/_StreamsPrehled/DonutStreamsChartComponent.vue";
import StreamHistoryComponent from "./_SystemPrehled/_StreamsPrehled/StreamsHistoryComponent";

export default {
    data() {
        return {};
    },

    created() {},
    components: {
        "ram-component": RamComponent,
        "swap-component": SwapComponent,
        "hdd-component": HddComponent,
        "cpu-component": CpuComponent,
        "monitoredstreams-component": MonitoredStreamsComponent,
        "donutstreamschart-component": DonutStreamsChartComponent,

        "streamhistory-component": StreamHistoryComponent
    },
    methods: {},
    watch: {}
};
</script>
