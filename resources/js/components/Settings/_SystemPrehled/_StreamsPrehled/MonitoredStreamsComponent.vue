<template>
    <v-main>
        <v-card class="mt-1" flat elevation="0" light color="transparent">
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
                        <div v-if="chartOptions.xaxis.categories != null">
                            <apexchart
                                height="285"
                                type="area"
                                :options="chartOptions"
                                :series="series"
                            ></apexchart>
                        </div>
                        <div v-else>
                            <v-alert outlined text type="info" class="mt-6">
                                <strong>Zatím neexistuje žádný záznam</strong>
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
                </v-container>
            </v-card-text>
        </v-card>
    </v-main>
</template>
<script>
export default {
    data() {
        return {
            loadingAreaChart: true,
            chartOptions: {
                dataLabels: {
                    enabled: false
                },
                chart: {
                    id: "Historie Streamů"
                },
                xaxis: {
                    categories: null,
                    labels: {
                        show: false
                    }
                },
                yaxis: {
                    show: false,
                    labels: {
                        show: false
                    }
                }
            },
            series: [
                {
                    name: "počet aktivních streamů",
                    data: null
                }
            ]
        };
    },
    created() {
        this.loadStreamsAreaData();
    },
    methods: {
        loadStreamsAreaData() {
            axios.get("system/working_streams/areacharts").then(response => {
                if (response.data.status === "exist") {
                    this.chartOptions.xaxis.categories = response.data.xaxis;
                    this.series[0].data = response.data.seriesData;
                    this.loadingAreaChart = false;
                } else {
                    this.loadingAreaChart = false;
                }
            });
        }
    },

    mounted() {
        setInterval(() => {
            this.loadStreamsAreaData();
        }, 30000);
    }
};
</script>
