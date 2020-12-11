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
                            Expirace certifikátu
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
                                    <strong> {{ssl}} </strong>
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
            ssl: "",
            value: [222, 380, 360, 800, 360, 370, 610, 560, 760]
        };
    },
    created() {
        this.loadSSL();
    },
    methods: {
        loadSSL() {
            window.axios.get("certifikate").then(response => {
                this.ssl = response.data;
            });
        }
    },
    mounted() {
        this.interval = setInterval(
            function() {
                this.loadSSL();
            }.bind(this),
            36000000
        );
    },
    beforeDestroy: function() {
        clearInterval(this.interval);
    }
};
</script>
