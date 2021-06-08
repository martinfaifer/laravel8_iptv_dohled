<template>
    <div>
        <div>
            <!-- <alert-component :status="status"></alert-component> -->
        </div>

        <div>
            <v-card
                class="mx-auto text-center"
                flat
                color="#202020"
                dark
                width="400"
            >
                <v-card-text>
                    <v-container>
                        <v-toolbar dense flat color="transparent" justify-end>
                            <v-spacer></v-spacer>
                            <span class="body-1">
                                <strong> {{ percent }} % </strong>
                            </span>
                            <v-spacer></v-spacer>
                            <v-icon small @click="openAreaChartDialog()">
                                mdi-magnify
                            </v-icon>
                        </v-toolbar>
                        <v-row no-gutters>
                            <div class="body-2 text-center white--text">
                                <small> <strong> Využití SWAP</strong> </small>
                            </div>
                            <v-progress-linear
                                v-model="percent"
                                color="teal"
                            ></v-progress-linear>
                        </v-row>
                    </v-container>
                </v-card-text>
            </v-card>
        </div>
        <v-row justify="center">
            <v-dialog v-model="areaChartDialog" persistent max-width="1920">
                <v-card :color="cardColor" flat light>
                    <v-card-text>
                        <div v-if="loading === false">
                            <div
                                v-if="chartOptions.xaxis.categories != null"
                                class="pt-12"
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
                                        >Zatím neexistuje žádný záznam</strong
                                    >
                                </v-alert>
                            </div>
                        </div>
                        <div v-else>
                            <!-- loading animace -->
                            <v-row align="center" justify="space-around">
                                <span class="mt-12">
                                    <i
                                        style="color:#EAF0F1"
                                        class="fas fa-spinner fa-spin fa-5x"
                                    ></i>
                                </span>
                            </v-row>
                        </div>
                    </v-card-text>
                    <v-card-actions>
                        <v-spacer></v-spacer>
                        <v-btn
                            color="green darken-1"
                            text
                            @click="areaChartDialog = false"
                        >
                            Zavřít
                        </v-btn>
                    </v-card-actions>
                </v-card>
            </v-dialog>
        </v-row>
    </div>
</template>
<script>
import AlertComponent from "../../AlertComponent";
export default {
    data() {
        return {
            loading: false,
            cardColor: "#1F1F1F",
            areaChartDialog: false,
            interval: false,
            percent: "",
            status: [],
            value: [222, 380, 360, 800, 360, 370, 610, 560, 760],
            chartOptions: {
                dataLabels: {
                    enabled: false
                },
                chart: {
                    id: "Historie zátěže systému"
                },
                xaxis: {
                    categories: null // cas
                }
            },
            series: [
                {
                    name: "využito",
                    data: null // data
                }
            ]
        };
    },

    components: {
        "alert-component": AlertComponent
    },
    created() {
        this.loadSwap();
    },
    watch: {
        status() {
            if (this.status != null) {
                setTimeout(function() {
                    this.status = null;
                }, 5000);
            }
        }
    },
    methods: {
        loadSwap() {
            try {
                axios.get("system/swap").then(response => {
                    this.percent = response.data;
                    if (this.percent > "80") {
                        this.status = {
                            status: "warning",
                            msg: "System začal swapovat!"
                        };
                    }
                });
            } catch (error) {
                console.log(error);
            }
        },
        openAreaChartDialog() {
            this.areaChartDialog = true;
            this.loading = true;
            window.axios.get("system/swap/history").then(response => {
                if (response.data.status === "exist") {
                    this.chartOptions.xaxis.categories = response.data.xaxis;
                    this.series[0].data = response.data.seriesData;
                    this.loading = false;
                } else {
                    this.loading = false;
                }
            });
        },
        close() {
            this.areaChartDialog = false;
            this.series[0].data = null;
            this.chartOptions.xaxis.categories = null;
        }
    },
    mounted() {
        this.interval = setInterval(
            function() {
                this.loadSwap();
            }.bind(this),
            10000
        );
    },
    beforeDestroy: function() {
        clearInterval(this.interval);
    }
};
</script>
