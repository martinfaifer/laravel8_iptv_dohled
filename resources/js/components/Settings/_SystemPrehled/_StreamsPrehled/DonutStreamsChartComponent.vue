<template>
    <v-main>
        <v-card class="mt-1" flat light elevation="0" color="transparent">
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
                        <div v-if="chartOptionsDonut != null" class="mt-12">
                            <apexchart
                                height="250"
                                type="donut"
                                :options="chartOptionsDonut"
                                :series="seriesDonut"
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
            loadingDonutChart: true,
            interval: null,
            seriesDonut: null,
            chartOptionsDonut: null
        };
    },
    created() {
        this.loadStreamsPie();
    },
    methods: {
        loadStreamsPie() {
            axios.get("system/streams/donutChart").then(response => {
                if (response.data.status === "success") {
                    this.chartOptionsDonut = response.data.chartOptionsDonut;
                    this.seriesDonut = response.data.seriesDonut;
                    this.loadingDonutChart = false;
                } else {
                    this.loadingDonutChart = false;
                }
            });
        }
    },

    mounted() {
        setInterval(() => {
            this.loadStreamsPie();
        }, 30000);
    }
};
</script>
