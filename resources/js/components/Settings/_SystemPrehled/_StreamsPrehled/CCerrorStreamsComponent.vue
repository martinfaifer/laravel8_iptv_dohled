<template>
    <v-main>
        <div class="ml-12 body-1">
            <v-row>
                <strong>
                    Výpis 9 streamů s cc errory za poslední 1h
                </strong>
            </v-row>
        </div>

        <div v-if="streams != null" class="mt-6">
            <v-card color="transparent" flat>
                <v-row>
                    <v-card
                        v-for="stream in streams"
                        :key="stream.id"
                        height="250"
                        width="460"
                        flat
                        class="elevation-0 mt-1 ma-1 mr-1"
                        color="#202020"
                        light
                    >
                        <v-card-text>
                            <div class="text-center">
                                <span class="body-1 white--text">
                                    <strong>
                                        {{ stream.stream }} - počet chyb
                                        <span class="red--text">
                                            {{ stream.ccerrors }}
                                        </span>
                                    </strong>
                                </span>
                            </div>
                            <apexchart
                                width="100%"
                                height="200"
                                type="bar"
                                :options="stream.chartOptions"
                                :series="stream.series"
                            ></apexchart>
                        </v-card-text>
                    </v-card>
                </v-row>
            </v-card>
        </div>
        <div v-else>
            <v-alert outlined text type="info" class="mt-6">
                Žádný stream nechyboval
            </v-alert>
        </div>
    </v-main>
</template>
<script>
export default {
    data() {
        return {
            chartOptions: {
                fill: {
                    colors: ["#F44336", "#E91E63", "#9C27B0"]
                },
                chart: {
                    id: "CC Errory"
                },
                xaxis: {
                    type: "datetime",
                    categories: null // cas
                }
            },
            series: [
                {
                    name: "CC Errory streamu",
                    data: null // data
                }
            ],
            streams: null,
            headers: [
                {
                    text: "Stream",
                    align: "start",
                    value: "stream"
                },
                {
                    text: "Počet CC errorů",
                    value: "ccerrors"
                }
            ]
        };
    },

    created() {
        // this.loadCC();
    },
    methods: {
        async loadCC() {
            try {
                await axios.get("streams/cc").then(response => {
                    if (response.data.status === "empty") {
                        this.streams = null;
                    } else {
                        this.streams = response.data.streams;
                    }
                });
            } catch (error) {}
        }
    },
    mounted() {
        this.interval = setInterval(
            function() {
                this.loadCC();
            }.bind(this),
            60000
        );
    }
};
</script>
