<template>
    <div>
        <div>
            <alert-component :status="status"></alert-component>
        </div>
        <div>
            <v-row class="mr-12 mb-1">
                <!-- do 50% green -->
                <div v-if="percent < '50'">
                    <v-progress-circular
                        class="mt-2 mr-6"
                        :size="100"
                        :width="3"
                        :value="percent"
                        color="green"
                        >{{ percent }} %</v-progress-circular
                    >
                </div>
                <!-- od 50 do 80% -->
                <div v-else-if="percent > '50' && percent < '80'">
                    <v-progress-circular
                        class="mt-2 mr-6"
                        :size="100"
                        :width="3"
                        :value="percent"
                        color="orange"
                        >{{ percent }} %</v-progress-circular
                    >
                </div>

                <div v-else>
                    <v-progress-circular
                        class="mt-2 mr-6"
                        :size="100"
                        :width="3"
                        :value="percent"
                        color="red"
                        >{{ percent }} %</v-progress-circular
                    >
                </div>
            </v-row>
            <span class="mt-12 ml-5">
                <span class="body-1 mt-12 ">Ram</span>
            </span>
        </div>
    </div>
</template>
<script>
import AlertComponent from "../../AlertComponent";
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
        this.loadRam();
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
        loadRam() {
            window.axios.get("ram").then(response => {
                this.percent = response.data;
                if (this.percent > "80") {
                    this.status = {
                        status: "warning",
                        msg: "Vysoké vytížení Ram"
                    };
                }
            });
        }
    },
    mounted() {
        this.interval = setInterval(
            function() {
                this.loadRam();
            }.bind(this),
            1000
        );
    },
    beforeDestroy: function() {
        clearInterval(this.interval);
    }
};
</script>
