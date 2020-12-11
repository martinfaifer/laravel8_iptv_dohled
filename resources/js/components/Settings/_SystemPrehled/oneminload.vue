<template>
    <div>
        <v-card
            class="mx-auto text-center"
            flat
            color="#218F76"
            dark
            width="300"
        >
            <v-card-text>
                <v-container>
                    <span class="md-6 body-2">
                        <strong>
                            Průměrná zátěž systému za 1min
                        </strong>
                    </span>
                    <v-row no-gutters>
                        <v-col class="col-4">
                            <div class="body-1">
                                <strong> {{ systemLoad[0] }} % </strong>
                            </div>
                        </v-col>
                        <v-col class="col-8">
                            <v-sheet
                                class="ml-6"
                                color="transparent"
                                width="80%"
                            >
                                <v-sparkline
                                    :value="value"
                                    color="#01CBC6"
                                    height="100"
                                    padding="24"
                                    stroke-linecap="round"
                                    smooth
                                >
                                </v-sparkline>
                            </v-sheet>
                        </v-col>
                    </v-row>
                </v-container>
            </v-card-text>
        </v-card>
    </div>
</template>
<script>
export default {
    data() {
        return {
            systemLoad: null,
            interval: false,
            percent: "",
            value: [222, 380, 360, 800, 360, 370, 610, 560, 760],
            labels: []
        };
    },
    created() {
        this.LoadSystemAverageLoad();
    },
    watch: {},
    methods: {
        LoadSystemAverageLoad() {
            window.axios.get("system/avarage/load").then(response => {
                this.systemLoad = response.data;
            });
        }
    },
    mounted() {
        this.interval = setInterval(
            function() {
                this.LoadSystemAverageLoad();
            }.bind(this),
            5000
        );
    },
    beforeDestroy: function() {
        clearInterval(this.interval);
    }
};
</script>
