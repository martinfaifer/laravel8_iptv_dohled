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
        <v-container fluid>
            <v-row class="ml-1">
                <v-col class="col-4">
                    <!-- naposledy pridaní uživatelé -->
                    <v-card flat>
                        <v-card-text>
                            <div class="text-center">
                                <span class="body-1 white--text">
                                    <strong>
                                        Naposledy přidaní uživatelé
                                    </strong>
                                </span>
                            </div>
                        </v-card-text>
                        <v-virtual-scroll
                            :bench="benched"
                            :items="users"
                            height="300"
                            item-height="64"
                        >
                            <!-- <v-list
                            two-line
                            v-for="userData in users"
                            :key="userData.id"
                        > -->
                            <template v-slot:default="{ item }">
                                <v-list-item>
                                    <v-list-item-avatar>
                                        <v-icon dark>
                                            mdi-account-circle
                                        </v-icon>
                                    </v-list-item-avatar>

                                    <v-list-item-content>
                                        <v-list-item-title
                                            class="subtitle-1"
                                            v-html="item.email"
                                        ></v-list-item-title>
                                        <v-list-item-subtitle
                                            v-html="item.role"
                                        ></v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider inset></v-divider>
                                <!-- </v-list> -->
                            </template>
                        </v-virtual-scroll>
                    </v-card>
                </v-col>
                <v-col class="col-4">
                    <!-- naposledy pridané kanály -->
                    <v-card flat>
                        <v-card-text>
                            <div class="text-center">
                                <span class="body-1 white--text">
                                    <strong>
                                        Naposledy přidané streamy
                                    </strong>
                                </span>
                            </div>
                        </v-card-text>

                        <v-virtual-scroll
                            :bench="benched"
                            :items="streams"
                            height="300"
                            item-height="64"
                        >
                            <template v-slot:default="{ item }">
                                <!-- <v-list
                                    dense
                                    v-for="streamData in streams"
                                    :key="streamData.id"
                                > -->
                                <v-list-item dense>
                                    <v-list-item-avatar>
                                        <v-icon dark>
                                            mdi-television-guide
                                        </v-icon>
                                    </v-list-item-avatar>

                                    <v-list-item-content>
                                        <v-list-item-title
                                            class="subtitle-1"
                                            v-html="item.nazev"
                                        ></v-list-item-title>
                                    </v-list-item-content>
                                </v-list-item>
                                <v-divider inset></v-divider>
                                <!-- </v-list> -->
                            </template>
                        </v-virtual-scroll>
                    </v-card>
                </v-col>
                <v-col class="col-4">
                    <!-- naposledy pridaní uživatelé -->
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
