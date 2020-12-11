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
                            Uptime
                        </strong>
                    </span>
                    <v-row no-gutters>
                        <v-col class="col-12">
                            <v-sheet
                                class="mt-5"
                                color="transparent"
                                width="100%"
                            >
                                <div class="body-1">
                                    <strong> {{uptime}} </strong>
                                </div>
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
            uptime: "",
            value: [222, 380, 360, 800, 360, 370, 610, 560, 760]
        };
    },
    created() {
        this.loadUptime();
    },
    methods: {
        loadUptime() {
            window.axios.get("uptime").then(response => {
                this.uptime = response.data;
            });
        }
    },
    mounted() {
        this.interval = setInterval(
            function() {
                this.loadUptime();
            }.bind(this),
            60000
        );
    },
    beforeDestroy: function() {
        clearInterval(this.interval);
    }
};
</script>
