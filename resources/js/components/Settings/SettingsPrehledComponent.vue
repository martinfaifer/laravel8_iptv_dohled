<template>
    <v-main class="mt-12">
        <v-container fluid class="mt-12">
            <v-card flat color="transparent" fixed>
                <v-row>
                    <fiveminload-component class="ml-3"></fiveminload-component>
                    <v-spacer></v-spacer>
                    <ram-component></ram-component>
                    <v-spacer></v-spacer>
                    <hdd-component></hdd-component>
                    <v-spacer></v-spacer>
                    <swap-component class="mr-3"></swap-component>
                    <!-- <sslexpiration-Component></sslexpiration-Component> -->
                </v-row>
            </v-card>
        </v-container>
        <v-divider class="mt-2 mb-2"></v-divider>
        <v-container fluid>
            <v-row>
                <v-col class="col-9">
                    <v-card class="mt-1" flat elevation="0" light :color="cardColor">
                        <v-card-text>
                            <v-container fluid>
                                <div class="text-center">
                                    <span class="body-1 white--text">
                                        <strong>
                                            Historie počtu dohledovaných streamů
                                        </strong>
                                    </span>
                                </div>
                                <div v-if="loadingAreaChart === false">
                                    <div
                                        v-if="
                                            chartOptions.xaxis.categories !=
                                                null
                                        "
                                    >
                                        <apexchart
                                            width="98%"
                                            height="280"
                                            type="area"
                                            :options="chartOptions"
                                            :series="series"
                                        ></apexchart>
                                    </div>
                                    <div v-else>
                                        <v-alert text type="info" class="mt-6">
                                            <strong
                                                >Zatím neexistuje žádný
                                                záznam</strong
                                            >
                                        </v-alert>
                                    </div>
                                </div>
                                <div v-else>
                                    <!-- loading animace -->
                                    <v-row
                                        align="center"
                                        justify="space-around"
                                    >
                                        <span class="mt-12">
                                            <i
                                                style="color:#EAF0F1"
                                                class="fas fa-spinner fa-spin fa-5x"
                                            ></i>
                                        </span>
                                    </v-row>
                                </div>
                            </v-container>
                        </v-card-text>
                    </v-card>
                </v-col>
                <v-col class="col-3">
                    <v-card
                        class="mt-1"
                        flat
                        light
                        elevation="0"
                        :color="cardColor"
                    >
                        <v-card-text>
                            <v-container fluid>
                                <div class="text-center">
                                    <span class="body-1 white--text">
                                        <strong>
                                            Přehled stavů streamů
                                        </strong>
                                    </span>
                                </div>
                                <div v-if="loadingDonutChart === false">
                                    <div
                                        v-if="chartOptionsDonut != null"
                                        class="mt-12"
                                    >
                                        <apexchart
                                            type="donut"
                                            :options="chartOptionsDonut"
                                            :series="seriesDonut"
                                        ></apexchart>
                                    </div>
                                    <div v-else>
                                        <v-alert text type="info" class="mt-6">
                                            <strong
                                                >Zatím neexistuje žádný
                                                záznam</strong
                                            >
                                        </v-alert>
                                    </div>
                                </div>
                                <div v-else>
                                    <!-- loading animace -->
                                    <v-row
                                        align="center"
                                        justify="space-around"
                                    >
                                        <span class="mt-12">
                                            <i
                                                style="color:#EAF0F1"
                                                class="fas fa-spinner fa-spin fa-5x"
                                            ></i>
                                        </span>
                                    </v-row>
                                </div>
                            </v-container>
                        </v-card-text>
                    </v-card>
                </v-col>
            </v-row>
        </v-container>
        <v-container fluid>
            <v-row class="ml-1">
                <v-col class="col-9">
                    <ccerrorstreams-component></ccerrorstreams-component>
                </v-col>
                <v-col class="col-3">
                    <streamhistory-component></streamhistory-component>
                </v-col>
            </v-row>
        </v-container>
    </v-main>
</template>
<script>
import Loadomponent from "./_SystemPrehled/LoadComponent";
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
// ssl epirace
import SslExpirationComponent from "./_SystemPrehled/SslExpirationComponent";
// ccErrory
import CCerrorStreamsComponent from "./_SystemPrehled/_StreamsPrehled/CCerrorStreamsComponent";

// zatez systemu 5min prumer
import fiveminload from "./_SystemPrehled/fiveminload";

export default {
    data() {
        return {
            benched: 0,
            streams: [],
            users: [],
            loadingAreaChart: true,
            loadingDonutChart: true,
            interval: null,
            seriesDonut: null,
            chartOptionsDonut: null,
            cardColor: "#1F1F1F",
            userRole: null,
            chartOptions: {
                dataLabels: {
                    enabled: false
                },
                chart: {
                    id: "Historie Streamů"
                },
                xaxis: {
                    categories: null // cas
                }
            },
            series: [
                {
                    name: "počet aktivních streamů",
                    data: null // data
                }
            ]
        };
    },

    created() {
        this.loadUser();
        this.loadStreamsAreaData();
        this.loadStreamsPie();
        this.loadStatsUsers();
        this.loadStatsStreams();
    },
    components: {
        "load-component": Loadomponent,
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
        "fiveminload-component": fiveminload,
        "sslexpiration-Component": SslExpirationComponent,
        "ccerrorstreams-component": CCerrorStreamsComponent
    },
    methods: {
        loadUser() {
            let currentObj = this;
            window.axios.get("user").then(response => {
                currentObj.userRole = response.data.role_id;
            });
        },

        loadStatsUsers() {
            axios.get("users/get/last/ten").then(resposne => {
                this.users = resposne.data.data;
            });
        },

        async loadStreamsAreaData() {
            try {
                await axios.get("working_streams/areacharts").then(response => {
                    if (response.data.status === "exist") {
                        this.chartOptions.xaxis.categories =
                            response.data.xaxis;
                        this.series[0].data = response.data.seriesData;
                        this.loadingAreaChart = false;
                    } else {
                        this.loadingAreaChart = false;
                    }
                });
            } catch (error) {}
        },

        loadStatsStreams() {
            axios.get("streams/get/last/ten").then(response => {
                this.streams = response.data;
            });
        },

        async loadStreamsPie() {
            try {
                await axios.get("streams/donutChart").then(response => {
                    if (response.data.status === "success") {
                        this.chartOptionsDonut =
                            response.data.chartOptionsDonut;
                        this.seriesDonut = response.data.seriesDonut;
                        this.loadingDonutChart = false;
                    } else {
                        this.loadingDonutChart = false;
                    }
                });
            } catch (error) {}
        }
    },

    mounted() {
        this.interval = setInterval(
            function() {
                this.loadStreamsAreaData();
                this.loadStreamsPie();
            }.bind(this),
            60000
        );
    },
    watch: {}
};
</script>
