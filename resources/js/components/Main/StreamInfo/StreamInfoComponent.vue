<template>
    <v-main class="mt-12">
        <v-container fluid class="body-1">
            <!-- náhled na kanál -> komponent img -->
            <div
                v-if="status === 'waiting' || status === 'stop'"
                class="text-center"
            >
                <v-alert transition="scale-transition" outlined text type="warning">
                    <strong>
                        Stream se nedohleduje
                    </strong>
                </v-alert>
            </div>

            <!-- notifikace, pokud má stream na dnesni den plánovanou udalost -->
            <div v-if="todayEvent != null" class="text-center">
                <v-alert transition="scale-stransition" outlined text type="info">
                    <span v-for="event in todayEvent" :key="event.id">
                        <strong>
                            Plánovaný výpadek od {{ event.start }} do
                            {{ event.end }}
                        </strong>
                    </span>
                </v-alert>
            </div>
            <v-row no-gutters>
                <v-col cols="12" sm="12" md="4" lg="4">
                    <img-component></img-component>
                </v-col>

                <!-- konec náhledu na kanál -->

                <v-col cols="12" sm="12" md="8" lg="8" class="mb-12">
                    <ts-component></ts-component>
                </v-col>
            </v-row>
            <v-divider></v-divider>
            <v-row class="mt-6">
                <v-col cols="12" sm="12" md="6" lg="6">
                    <history-component></history-component>
                </v-col>
                <v-divider vertical></v-divider>
                <v-col cols="12" sm="12" md="6" lg="6">
                    <doku-component></doku-component>
                </v-col>
            </v-row>
        </v-container>
    </v-main>
</template>
<script>
import DokuComponent from "./StreamInfoApiDokuComponent";
import ImgComponent from "./StreamInfoImageComponent";
import TsComponent from "./StreamInfoTSComponent";
import HistoryComponent from "./StreamInfoHistoryComponent";
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
        "doku-component": DokuComponent
    },
    methods: {
        getStreamStatus() {
            try {
                axios
                    .post("streamInfo/status", {
                        streamId: this.$route.params.id
                    })
                    .then(response => {
                        this.status = response.data.status;
                    });
            } catch (error) {
                console.log(error);
            }
        },
        getTodayEvent() {
            try {
                axios
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
            } catch (error) {
                console.log(error);
            }
        }
    },

    mounted() {
        this.interval = setInterval(
            function() {
                try {
                    this.getStreamStatus();
                } catch (error) {}
            }.bind(this),
            20000
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
    }
};
</script>
