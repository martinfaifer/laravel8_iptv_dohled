<template>
    <v-main>
        <v-col
            cols="12"
            sm="12"
            md="12"
            lg="12"
            class="mt-3 mb-3 pr-10"
            v-if="series2.length > 0"
        >
            <v-card flat color="transparent" light>
                <div class="text-center">
                    <span class="body-1 white--text">
                        <strong>
                            Využití CPU
                        </strong>
                    </span>
                </div>
                <apexchart
                    height="285"
                    type="area"
                    :options="chartOptions2"
                    :series="series2"
                ></apexchart>
            </v-card>
        </v-col>
        <v-col v-else cols="12" sm="12" md="12" lg="12" class="mt-3 mb-3 pr-10">
            <v-row align="center" justify="space-around">
                <span class="mt-12">
                    <i
                        style="color:#BDBDBD"
                        class="fas fa-spinner fa-spin fa-5x"
                    ></i>
                </span>
            </v-row>
        </v-col>
    </v-main>
</template>
<script>
export default {
    data() {
        return {
            chartOptions2: {
                dataLabels: {
                    enabled: false
                },
                chart: {
                    id: "Využití CPU"
                },
                xaxis: {
                    show:false,
                    categories: [],
                    labels: {
                       show:false
                    }
                },
                yaxis: {
                    show:false,
                    labels: {
                       show:false
                    }
                }
            },

            series2: []
        };
    },
    created() {
        this.laodCpuUsage();
    },
    methods: {
        laodCpuUsage() {
            axios.get("system/cpu/data").then(response => {
                if (response.data.status === "success") {
                    this.chartOptions2.xaxis.categories = response.data.xaxis;
                    this.series2 = response.data.series;
                } else {
                    this.series2 = [];
                }
            });
        }
    },

    mounted() {
        setInterval(() => {
            this.laodCpuUsage();
        }, 60000);
    }
};
</script>
