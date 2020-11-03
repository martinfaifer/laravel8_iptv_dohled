<template>
    <v-main class="mt-12">
        <v-container>
            <!-- náhled na kanál -> komponent img -->
            <div
                v-if="status === 'waiting' || status === 'stop'"
                class="text-center"
            >
                <v-alert transition="scale-transition" text type="warning">
                    <strong>
                        Stream se nedohleduje
                    </strong>
                </v-alert>
            </div>
            <v-row no-gutters>
                <v-col class="col-3">
                    <img-component></img-component>
                    <doku-component></doku-component>
                </v-col>

                <!-- konec náhledu na kanál -->
                <v-spacer></v-spacer>
                <v-col class="col-5">
                    <ts-component></ts-component>
                </v-col>
                <v-spacer></v-spacer>
                <v-col class="col-4">
                    <history-component></history-component
                ></v-col>
            </v-row>
        </v-container>
    </v-main>
</template>
<script>
import ImgComponent from "./StreamInfoImageComponent";
import TsComponent from "./StreamInfoTSComponent";
import HistoryComponent from "./StreamInfoHistoryComponent";
import DokuComponent from "./StreamInfoApiDokuComponent";
export default {
    data() {
        return {
            status: null
        };
    },

    created() {
        this.getStreamHistory();
    },
    components: {
        "img-component": ImgComponent,
        "ts-component": TsComponent,
        "history-component": HistoryComponent,
        "doku-component": DokuComponent
    },
    methods: {
        getStreamHistory() {
            window.axios
                .post("streamInfo/status", {
                    streamId: this.$route.params.id
                })
                .then(response => {
                    this.status = response.data.status;
                });
        }
    },

    mounted() {
        setInterval(
            function() {
                try {
                    this.getStreamHistory();
                } catch (error) {}
            }.bind(this),
            5000
        );
    },
    watch: {
        $route(to, from) {
            this.getStreamHistory();
        }
    }
};
</script>
