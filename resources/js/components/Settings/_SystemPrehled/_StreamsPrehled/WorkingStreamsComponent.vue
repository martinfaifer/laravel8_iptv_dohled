<template>
    <div>
        <div>
            <alert-component :status="status"></alert-component>
        </div>
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
                                Funkční streamy
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
                                        <strong> {{ percent }} % </strong>
                                    </div>
                                </v-sheet>
                            </v-col>
                        </v-row>
                    </v-container>
                </v-card-text>
            </v-card>
        </div>
    </div>
</template>
<script>
import AlertComponent from "../../../AlertComponent";
export default {
    data() {
        return {
            interval: false,
            percent: "",
            status: []
        };
    },

    components: {
        "alert-component": AlertComponent
    },
    created() {
        this.loadWorkingStreams();
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
        loadWorkingStreams() {
            window.axios.get("working/streams").then(response => {
                this.percent = response.data;
                if (this.percent < "80") {
                    this.status = {
                        status: "warning",
                        msg: "Velkmé množství nefunkčních streamů"
                    };
                }
            });
        }
    },
    mounted() {
        this.interval = setInterval(
            function() {
                this.loadWorkingStreams();
            }.bind(this),
            30000
        );
    },
    beforeDestroy: function() {
        clearInterval(this.interval);
    }
};
</script>
