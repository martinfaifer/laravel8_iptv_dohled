<template>
    <v-main class="mt-12">
        <v-container fluid class="body-1">
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

            <!-- notifikace, pokud má stream na dnesni den plánovanou udalost -->
            <div v-if="todayEvent != null" class="text-center">
                <v-alert transition="scale-stransition" text type="info">
                    <span v-for="event in todayEvent" :key="event.id">
                        <strong>
                            Plánovaný výpadek od {{ event.start }} do
                            {{ event.end }}
                        </strong>
                    </span>
                </v-alert>
            </div>
            <v-row no-gutters>
                <v-col class="col-3">
                    <img-component></img-component>
                    <doku-component></doku-component>
                    <streaminfosheduler-component></streaminfosheduler-component>
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
import StreamInfoShedulerComponent from "./StreamInfoShedulerComponent";
export default {
    data() {
        return {
            status: null,
            todayEvent: null,
            interval: null
        };
    },

    created() {
        this.getStreamStatus();
        this.getTodayEvent();
    },
    components: {
        "img-component": ImgComponent,
        "ts-component": TsComponent,
        "history-component": HistoryComponent,
        "doku-component": DokuComponent,
        "streaminfosheduler-component": StreamInfoShedulerComponent
    },
    methods: {
        getStreamStatus() {
            window.axios
                .post("streamInfo/status", {
                    streamId: this.$route.params.id
                })
                .then(response => {
                    this.status = response.data.status;
                });
        },
        getTodayEvent() {
            window.axios
                .post("streamInfo/todayEvent", {
                    streamId: this.$route.params.id
                })
                .then(response => {
                    if (response.data.status == "empty") {
                        this.todayEvent = null;
                    } else {
                        this.todayEvent = response.data;
                    }
                });
        }
    },

    mounted() {
        this.interval = setInterval(
            function() {
                try {
                    this.getStreamStatus();
                } catch (error) {}
            }.bind(this),
            5000
        );
    },
    watch: {
        $route(to, from) {
            this.getStreamStatus();
            this.getTodayEvent();
        }
    },

    beforeDestroy: function() {
        clearInterval(this.interval);
    },
};
</script>
